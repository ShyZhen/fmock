<?php

/**
 *  问答索引，完成写入和搜索
 **/

namespace App\Library\ElasticSearch;

use stdClass;
use App\Library\ElasticSearch\Base\ElasticSearch;

use function GuzzleHttp\Promise\queue;

class QuestionAnswerElasticSearch extends ElasticSearch
{

    public $indexKey = 'question_answer';

    public static $size = 20;



    /**
     *  获取index
     *
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexKey;
    }

    /**
     * 第一步设置mapping
     * @return void
     */
    public function createIndex()
    {
        // join查询，mapping设置参考
        // https://www.elastic.co/guide/en/elasticsearch/reference/8.4/query-dsl-has-child-query.html#has-child-top-level-params
        // https://www.cnblogs.com/xiaowei123/p/14066151.html
        $properties =
        '{
            "properties": {
                "join": {
                    "type": "join",
                    "relations": {
                        "question": "answer"
                    }
                },
                "text": {
                    "type": "text"
                },
                "created_at": {
                    "type": "integer"
                },
                "like_count": {
                    "type": "integer"
                }
            }
        }';
    }


    /**
     * 创建或更新文档
     *
     * @param array $params
     * @return mixed
     */
    public function createOrUpdateDoc($params)
    {
        if (empty($params['body'])) {
            return false;
        }

        $routing = $params['routing'] ?? '';
        if ($this->existsDoc($params['id'])) {
            $params = [
                'index' => $this->index,
                'id' => $params['id'],
                'refresh' => true,
                'body' => [
                    'doc' => $params['body']
                ],
            ];
            $routing && $params['routing'] = $routing;

            $response = $this->esClient->update($params);
        } else {
            $params = [
                'index' => $this->index,
                'id' => $params['id'],
                'refresh' => true,
                'body' => $params['body']
            ];
            $routing && $params['routing'] = $routing;

            $response = $this->esClient->index($params);
        }

        return $response;
    }

    /**
     * 最后一步 搜索
     * 自定义搜索 搜索参考
     *
     * @param array $word
     *
     * @return array
     */
    public function customSearch($keyword, $page = 1, $size = 20)
    {
        // 第一步，查出符合条件的question_id及总数
        $query = [
            'bool' => [
                'filter' => [
                    "bool" => [
                        "must" => [
                            [
                                "bool" => [
                                    "should" => [
                                        [
                                            "match_phrase" => ["text" => $keyword],
                                        ],
                                        [
                                            "has_child" => [
                                                "type" => 'answer',
                                                "query" => ["match_phrase" => ["text" => $keyword]],
                                            ],
                                        ],
                                    ]
                                ]
                            ],
                            [
                                "has_child" => [
                                    "type" => 'answer',
                                    "query" => ["match_all" => new stdClass()],
                                    'min_children' => 1, // 最少一个
                                ]
                            ],

                        ]
                    ],
                ],
            ]
        ];

        // 总数
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => $query
            ],

        ];
        $response = $this->esClient->count($params);
        if (empty($response['count'])) {
            return ['total' => 0, 'data' => []];
        }
        $total = intval($response['count']);

        $from = ($page - 1) * $size;
        if ($from >= $total) {
            return ['total' => $total, 'data' => []];
        }
        $params = [
            'from' => $from,
            'size' => $size,
            'index' => $this->index,
            'body' => [
                '_source' => ["include" => ['text']],
                'query' => $query,
                'sort' => [
                    'created_at' => [
                        'order' => 'desc',
                    ],
                ],
            ],
        ];
        $response = $this->esClient->search($params);
        if (empty($response['hits']['hits'])) {
            return ['total' => $total, 'data' => []];
        }

        $questions = $questionIds = [];
        foreach ($response['hits']['hits'] as $v) {
            $questions[] = [
                'id' => ltrim($v['_id'], 'question_'),
                'title' => $v['_source']['text'],
            ];
            $questionIds[] =  ltrim($v['_id'], 'question_');
        }

        // 第二步， 遍历每个question_id,找出点赞数最高的答案
        $answerIds = [];
        if ($questions) {
            foreach ($questions as &$q) {
                $q['best_answer'] = $this->getAnswerByQuestionId('question_' . $q['id'], $keyword);
                isset($q['best_answer']['id']) && $answerIds[] = $q['best_answer']['id'];
            }
            unset($q);
        }

        // 第三步，按优先级排序（问题+答案都有关键词 > 问题有关键词 > 答案有关键词，同类结果按时间倒序）
        $data = $one = $two = $three = [];
        if ($questions) {
            foreach ($questions as $q) {
                $questionHasKeyword = mb_strpos($q['title'], $keyword) !== false;
                if ($questionHasKeyword && $q['best_answer']) { // 问题+答案都有关键词
                    $one[] = $q;
                } elseif ($questionHasKeyword) { // 问题有关键词
                    $two[] = $q;
                } else {
                    $three[] = $q;
                }
            }
            $data = array_merge($one, $two, $three);
        }

        // 补充其它信息
        if ($questionIds) {
            $service = new QuestionService();
            $qData = $service->querySimple($questionIds);    // ->with(['user'])->whereIn('id', $ids)->get(['id', 'user_id'])
            $_qData = [];
            foreach ($qData as $v) {
                $_qData[$v['id']] = $v;
            }
            unset($aQata);
        }
        if ($answerIds) {
            $service  = new AnswerService();
            $aData = $service->querySimple($answerIds);    // ->with(['user'])->whereIn('id', $ids)->get(['id', 'user_id'])
            $_aData = [];
            foreach ($aData as $v) {
                $_aData[$v['id']] = $v;
            }
            unset($aData);
        }


        foreach ($data as &$v) {
            if (isset($_qData[$v['id']])) {
                $v['images'] = $_qData[$v['id']]['images'];
                $v['video'] = $_qData[$v['id']]['video'];
                $v['user_id'] = $_qData[$v['id']]['user_id'];
            }
            if (isset($_aData[$v['id']])) {
                $v['best_answer']['user_id'] = $_aData[$v['id']]['user_id'];
            }
        }

        return ['total' => $total, 'data' => $data];
    }

    /**
     * 获取问题下满足条件的一个答案
     *
     * @param int $id
     * @param string $keyword
     *
     * @return array
     */
    protected function getAnswerByQuestionId($id, $keyword)
    {
        $params = [
            'size' => 1,
            'index' => $this->index,
            'body' => [
                '_source' => ["include" => ['text']],
                'query' => [
                    'bool' => [
                        'filter' => [
                            "bool" => [
                                "must" => [
                                    [
                                        "match_phrase" => ["text" => $keyword]
                                    ],
                                    [
                                        "parent_id" => [
                                            "type" => 'answer',
                                            "id" => $id,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                    ]
                ],
                'sort' => [
                    'like_count' => [
                        'order' => 'desc',
                    ],
                ],
            ],
        ];
        $response = $this->esClient->search($params);

        // 再查一次不匹配关键词的
        if (empty($response['hits']['hits'][0])) {
            $params = [
                'size' => 1,
                'index' => $this->index,
                'body' => [
                    '_source' => ["include" => ['text']],
                    'query' => [
                        'bool' => [
                            'filter' => [
                                "bool" => [
                                    "must" => [
                                        [
                                            "parent_id" => [
                                                "type" => 'answer',
                                                "id" => $id,
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ],
                    'sort' => [
                        'like_count' => [
                            'order' => 'desc',
                        ],
                    ],
                ],
            ];
            $response = $this->esClient->search($params);
        }

        if (empty($response['hits']['hits'][0])) {
            return [];
        }

        $hit = $response['hits']['hits'][0];

        // 只返回匹配到关键词的第一个段落
        $pattern = '#<p>(?:(?<!</p>).)*' . $keyword . '.*?</p>#';
        preg_match($pattern, $hit['_source']['text'], $matches);
        $text = !empty($matches[0]) ? strip_tags($matches[0]) : '';

        $id = ltrim($hit['_id'], 'answer_');

        return ['id' => $id, 'summary' => $text];
    }









    /**
     * 第二步索引/创建文档doc
     * 创建、索引一个新文档 创建上面mapping的doc参考
     *
     * @return string
     */
    public function handleDoc()
    {
        if (empty($this->data)) {
            return '';
        }

        $es = new QuestionAnswerElasticSearch();
        if ($this->data['action'] == 'create') {
            // 因为问题和答案都在同一个index, 所以id要加上前缀
            switch ($this->data['type']) {
                case 'question':
                    $params = [
                        'id' => $this->data['type'] . '_' . $this->data['id'],
                        'body' => [
                            'text' => $this->data['text'],
                            'created_at' => $this->data['created_at'],
                            'join' => ['name' => 'question'],
                        ]
                    ];

                    break;
                case 'answer':
                    $questionId = 'question_' . $this->data['question_id']; // 注意这里要加前缀
                    $params = [
                        'id' => $this->data['type'] . '_' . $this->data['id'],
                        'routing' => $questionId,  // 答案要加routing参数
                        'body' => [
                            'text' => $this->data['text'],
                            'created_at' => $this->data['created_at'],
                            'like_count' => $this->data['like_count'],
                            'join' => ['name' => 'answer', 'parent' => $questionId],

                        ],
                    ];
                    break;
            }

            $es->createOrUpdateDoc($params);
        } elseif ($this->data['action'] == 'delete') {
            $id = $this->data['type'] . '_' . $this->data['id'];
            $es->deleteDoc($id);
        } elseif ($this->data['action'] == 'update') {
            // 答案点赞或取消点赞后ES同步更新
            if ($this->data['type'] == 'answer') {
                $questionId = 'question_' . $this->data['question_id']; // 注意这里要加前缀
                $params = [
                    'id' => $this->data['type'] . '_' . $this->data['id'],
                    'routing' => $questionId,  // 答案要加routing参数
                    'body' => [
                        'like_count' => $this->data['like_count'],
                    ],
                ];

                $es->createOrUpdateDoc($params);
            }
        }
    }


}
