<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2019/11/21
 * Time: 16:41
 */

namespace App\Library\ElasticSearch\Base;

use Elasticsearch\ClientBuilder;

/**
 * ES工具类
 *
 * Class ElasticSearchUtil
 */
abstract class ElasticSearch
{
    public $esConfig;

    public $index;

    public $esClient;

    // 默认分词器
    public $tokenizer = 'pinyin_analyzer';  // 'ik_max_word';

    /**
     * 需要索引的字段、规定添加doc时必须的字段，由子类override
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @var array
     */
    public $fields = [];

    /**
     * 游标查询参数 设置大一点，防止出现 No search context found for id
     *
     * @var string
     */
    public $scrollTtl = '3m';

    /**
     * 游标查询参数 超过一定数量要删除scroll_id，因为最多保留500个
     *
     * @var int
     */
    public $scrollMaxLimit = 400;

    /**
     * 初始化链接
     * ElasticSearchUtil constructor.
     */
    public function __construct()
    {
        $this->esConfig = config('elasticsearch');

        if (!$this->esClient) {
            $host = $this->esConfig['elasticsearch_host_node'];
            $username = $this->esConfig['username'] ?? '';
            $password = $this->esConfig['password'] ?? '';

            $this->esClient = ClientBuilder::create()
                ->setHosts($host)
                ->setSelector($this->esConfig['selector']['sticky_round_robin'])
                ->setRetries($this->esConfig['retries']);

            if ($username && $password) {
                $this->esClient->setBasicAuthentication($username, $password);
            }

            $this->esClient = $this->esClient->build();
        }

        $this->setIndexName();
    }

    /**
     * 获取当前index
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return string
     */
    abstract public function getIndexName(): string;

    /**
     * 设置当前index
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return mixed
     */
    public function setIndexName(): string
    {
        return $this->index = $this->getIndexName();
    }

    /**
     * 创建一个索引（index,类似于创建一个库）
     * 6.0版本以后一个index只能有一个type
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @return array
     */
    abstract public function createIndex();

    /**
     * 判断一个索引是否存在
     *
     * @return bool
     */
    public function existsIndex(): bool
    {
        $params = [
            'index' => $this->index,
        ];

        if ($this->esClient->indices()->exists($params)) {
            return true;
        }

        return false;
    }

    /**
     * 判断doc文档是否存在
     *
     * @param $id
     *
     * @return bool
     */
    public function existsDoc($id): bool
    {
        $params = [
            'index' => $this->index,
            'id' => $id,
        ];

        if ($this->esClient->exists($params)) {
            return true;
        }

        return false;
    }

    /**
     * 删除一个索引（index,类似于删除一个库）
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @return array
     */
    public function deleteIndex()
    {
        $params = [
            'index' => $this->index,
        ];

        $response = $this->esClient->indices()->delete($params);

        return $response;
    }

    /**
     * 获取某个index的mappings设置信息
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return array
     */
    public function getMappings()
    {
        $params = [
            'index' => $this->index,
        ];

        $response = $this->esClient->indices()->getMapping($params);

        return $response;
    }

    /**
     * 获取一个或者多个index的设置信息
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return array
     */
    public function getSettings()
    {
        $params = [
            'index' => $this->index,
        ];

        $response = $this->esClient->indices()->getSettings($params);

        return $response;
    }

    /**
     * 创建一条数据（索引一个文档）
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $id int 推荐使用数据库的ID
     * @param $body array('key' => 'val')
     *
     * @throws \Exception
     *
     * @return array
     */
    public function createDoc($id, $body)
    {
        // 规定必须字段
        foreach ($this->fields as $val) {
            if (!array_key_exists($val, $body)) {
                throw new \Exception('索引字段必须包含' . $val);
                break;
            }
        }

        $body['date'] = date('Y-m-d');
        $params = [
            'index' => $this->index,
            'id' => $id,
            'body' => $body,
        ];

        $response = $this->esClient->index($params);

        return $response;
    }

    /**
     * 获取一个文档
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $id int
     *
     * @return array
     */
    public function getDoc($id)
    {
        $params = [
            'index' => $this->index,
            'id' => $id,
        ];

        $response = $this->esClient->get($params);

        return $response;
    }

    /**
     * 获取一个文档的 _source
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $id int
     *
     * @return array
     */
    public function getDocSource($id)
    {
        $params = [
            'index' => $this->index,
            'id' => $id,
        ];

        $response = $this->esClient->getSource($params);

        return $response;
    }

    /**
     * 新增字段或者修改已有字段
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $id
     * @param $body
     *
     * @return array
     */
    public function updateDoc($id, $body)
    {
        $body['date'] = date('Y-m-d');
        $params = [
            'index' => $this->index,
            'id' => $id,
            'body' => [
                'doc' => $body,
            ],
        ];

        $response = $this->esClient->update($params);

        return $response;
    }

    /**
     * 删除一条记录（文档） doc
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $id
     *
     * @return array
     */
    public function deleteDoc($id)
    {
        $params = [
            'index' => $this->index,
            'id' => $id,
        ];

        $response = $this->esClient->delete($params);

        return $response;
    }

    /**
     * 搜索文档 doc
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $query string
     * @param $filter array doc中的筛选条件，键值对方式
     * @param $page int
     * @param $analyzer string
     *
     * @return array
     */
    public function search($query, array $filter = [], int $page = 1, $analyzer = '')
    {
        $size = $this->esConfig['web_search_size'];
        $page = $page > 0 ? $page : 1;
        $from = $size * ($page - 1);

        $params = [
            'index' => $this->index,
            'from' => $from,
            'size' => $size,
            'body' => [

//                'query' => [
//                    // 单字段
//                    // 'match' => [
//                    //     'key1' => $query
//                    // ]
//                    // 多字段
//                    'multi_match' => [
//                        'query' => $query,
//                        'type' => 'best_fields',  // 完全匹配 'type' => 'phrase',
//                        'operator' => 'or',
//                        'fields' => $this->fields,
//                        'analyzer' => $analyzer ?: $this->tokenizer,
//                    ],
//                ],

                'query' => [
                    'bool' => [
                        'must' => [
                            // 多字段
                            'multi_match' => [
                                'query' => $query,
                                'type' => 'phrase',
                                'operator' => 'or',
                                'fields' => $this->fields,
                                'analyzer' => $analyzer ?: $this->tokenizer,
                            ],
                        ],
                    ],
                ],

                'sort' => [
                    'id' => [
                        'order' => 'desc',
                    ],
                ],

                // 匹配到多个敏感词供前端高亮，解决ES高亮数据不完整问题
                'highlight' => [
                    'fields' => [
                        'title' => [
                            'pre_tags' => ['<em>'],
                            'post_tags' => ['</em>'],
                        ],
                        'content' => [
                            'pre_tags' => ['<em>'],
                            'post_tags' => ['</em>'],
                        ],
                    ],
                ],
            ],
        ];

        // 添加筛选doc的filter
        if (count($filter)) {
            $params['body']['query']['bool']['filter']['term'] = $filter;
        }

        $response = $this->esClient->search($params);

        return $response['hits']['hits'];
    }

    /**
     * 搜索文档 doc 滚动查询
     *
     * startOffset must be non-negative, and endOffset must be >= startOffset, and offsets must not go backwards
     * https://github.com/medcl/elasticsearch-analysis-pinyin/issues/261
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $query string
     * @param $filter array doc中的筛选条件，键值对方式
     * @param $callback callable
     * @param $analyzer string
     */
    public function searchScroll($query, array $filter = [], $callback = null, $analyzer = '')
    {
        $size = $this->esConfig['web_search_size'];

        $params = [
            'index' => $this->index,
            'scroll' => $this->scrollTtl,  // 每次翻页的时间间隔
            'size' => $size,
            'body' => [

                'query' => [
                    'bool' => [
                        'must' => [
                            // 多字段
                            'multi_match' => [
                                'query' => $query,
                                'type' => 'phrase',
                                'operator' => 'or',
                                'fields' => $this->fields,
                                'analyzer' => $analyzer ?: $this->tokenizer,
                            ],
                        ],
                    ],
                ],

                'sort' => [
                    'id' => [
                        'order' => 'desc',
                    ],
                ],

                // 匹配到多个敏感词供前端高亮，解决ES高亮数据不完整问题
                'highlight' => [
                    'fields' => [
                        'title' => [
                            'pre_tags' => ['<em>'],
                            'post_tags' => ['</em>'],
                        ],
                        'content' => [
                            'pre_tags' => ['<em>'],
                            'post_tags' => ['</em>'],
                        ],
                    ],
                ],
            ],
        ];

        // 添加筛选doc的filter
        if (count($filter)) {
            $params['body']['query']['bool']['filter']['term'] = $filter;
        }

        $response = $this->esClient->search($params);

        while (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
            $callback($response['hits']['hits']);

            $scrollId = $response['_scroll_id'];
            $response = $this->esClient->scroll([
                'scroll_id' => $scrollId,
                'scroll' => $this->scrollTtl,
            ]);
        }

        $this->clearScroll($response['_scroll_id']);

        return true;
    }

    /**
     * Clears the current scroll window if there is a scroll_id stored
     *
     * @param $scrollId
     */
    public function clearScroll($scrollId)
    {
        if ($scrollId) {
            $this->esClient->clearScroll(['scroll_id' => $scrollId]);
        }
    }

    /**
     * 通用mapping配置参数
     * 可override
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return array
     */
    protected function getMappingsConfig()
    {
        $properties = [];
        $common = [
            'type' => 'text',
            'analyzer' => $this->tokenizer,
            'search_analyzer' => $this->tokenizer,
            'search_quote_analyzer' => $this->tokenizer,
        ];

        foreach ($this->fields as $val) {
            $properties[$val] = $common;
        }

        // 单独的时间字段
        $properties['date'] = [
            'type' => 'date',
            'format' => 'year_month_day ',
        ];

        // 单独的ID字段
        $properties['id'] = [
            'type' => 'integer',
        ];

        return $properties;
    }

    /**
     * 通用setting参数 （先使用ik分词，后使用拼音分词）
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $properties
     *
     * @return array
     */
    protected function getSettingConfig($properties)
    {
        $params = [
            'index' => $this->getIndexName(),
            'body' => [
                'settings' => [
                    'number_of_shards' => $this->esConfig['number_of_shards'],       // 分片 默认5
                    'number_of_replicas' => $this->esConfig['number_of_replicas'],   // 副本、备份 默认1
                    // 自定义分析过滤器
                    'analysis' => [
                        'analyzer' => [
                            "$this->tokenizer" => [
                                'type' => 'custom',
                                'tokenizer' => 'ik_smart',
                                'filter' => [
                                    'my_pinyin',
                                ],
                            ],
                        ],
                        'filter' => [
                            'my_pinyin' => [
                                'type' => 'pinyin',
                                'keep_first_letter' => false,
                                'keep_joined_full_pinyin' => true,
                                'limit_first_letter_length' => 32,
                                'keep_original' => true,
                            ],
                        ],
                    ],
                ],
                // 设置mappings
                'mappings' => [
                    '_source' => [
                        'enabled' => true,
                    ],
                    'properties' => $properties,
                ],
            ],
        ];

        return $params;
    }

    /**
     * 通用setting参数(单独使用拼音的，经测试也可以满足当前需求的搜索)
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $properties
     *
     * @return array
     */
    protected function getSettingPYConfig($properties)
    {
        $params = [
            'index' => $this->getIndexName(),
            'body' => [
                'settings' => [
                    'number_of_shards' => $this->esConfig['number_of_shards'],
                    'number_of_replicas' => $this->esConfig['number_of_replicas'],
                    // 自定义分析过滤器
                    'analysis' => [
                        'analyzer' => [
                            "$this->tokenizer" => [
                                'tokenizer' => 'my_pinyin',
                            ],
                        ],
                        'tokenizer' => [
                            'my_pinyin' => [
                                'type' => 'pinyin',
                                'keep_first_letter' => false,
                                'keep_joined_full_pinyin' => false,
                                'keep_original' => false,
                                'keep_full_pinyin' => true,
                                'ignore_pinyin_offset' => false,
                            ],
                        ],
                    ],
                ],
                // 设置mappings
                'mappings' => [
                    '_source' => [
                        'enabled' => true,
                    ],
                    'properties' => $properties,
                ],
            ],
        ];

        return $params;
    }

    /**
     * 通用setting参数(单独使用IK分词的设置)
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $properties
     *
     * @return array
     */
    protected function getSettingIKConfig($properties)
    {
        $tokenizer = 'ik_max_word';
        $params = [
            'index' => $this->getIndexName(),
            'body' => [
                'settings' => [
                    'number_of_shards' => $this->esConfig['number_of_shards'],
                    'number_of_replicas' => $this->esConfig['number_of_replicas'],
                    // 自定义分析过滤器
                    'analysis' => [
                        'filter' => [
                            'my_english_stemmer' => [
                                'type' => 'stemmer',
                                'name' => 'english',
                            ],
                        ],
                        'analyzer' => [
                            'optimizeIK' => [
                                'type' => 'custom',
                                'tokenizer' => $tokenizer,  //$this->tokenizer,
                                'filter' => [
                                    'my_english_stemmer',
                                ],
                            ],
                        ],
                    ],
                ],
                // 设置mappings
                'mappings' => [
                    '_source' => [
                        'enabled' => true,
                    ],
                    'properties' => $properties,
                ],
            ],
        ];

        return $params;
    }
}
