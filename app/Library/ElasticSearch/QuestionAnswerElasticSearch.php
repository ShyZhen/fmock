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
     *
     * @return void
     */
    public function createIndex()
    {
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
     * 自定义搜索
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

        $questions = [];
        foreach ($response['hits']['hits'] as $v) {
            $questions[] = [
                'id' => ltrim($v['_id'], 'question_'),
                'text' => $v['_source']['text'],
            ];
        }

        // 第二步， 遍历每个question_id,找出点赞数最高的答案
        if ($questions) {
            foreach ($questions as &$q) {
                $q['answer'] = $this->getAnswerByQuestionId('question_' . $q['id'], $keyword);
            }
            unset($q);
        }

        // 第三步，按优先级排序（问题+答案都有关键词 > 问题有关键词 > 答案有关键词，同类结果按时间倒序）
        $data = $one = $two = $three = [];
        if ($questions) {
            foreach ($questions as $q) {
                $questionHasKeyword = mb_strpos($q['text'], $keyword) !== false;
                if ($questionHasKeyword && $q['answer']) { // 问题+答案都有关键词
                    $one[] = $q;
                } elseif ($questionHasKeyword) { // 问题有关键词
                    $two[] = $q;
                } else {
                    $three[] = $q;
                }
            }
            $data = array_merge($one, $two, $three);
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
        if (empty($response['hits']['hits'][0])) {
            return [];
        }

        $hit = $response['hits']['hits'][0];

        // 只返回匹配到关键词的第一个段落
        $pattern = '#<p>(?:(?<!</p>).)*' . $keyword . '.*?</p>#';
        preg_match($pattern, $hit['_source']['text'], $matches);
        $text = !empty($matches[0]) ? strip_tags($matches[0]) : '';

        $id = ltrim($hit['_id'], 'answer_');

        return ['id' => $id, 'text' => $text];
    }
}
