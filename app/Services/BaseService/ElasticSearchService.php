<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2019/11/21
 * Time: 16:41
 */
namespace App\Services\BaseService;

use App\Services\Service;
use Elasticsearch\ClientBuilder;

class ElasticSearchService extends Service
{
    private static $esClient;

    /**
     * 链接重试次数
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @var int
     */
    private static $retries = 2;

    /**
     * 需要索引的字段、规定添加doc时必须的字段
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @var array
     */
    private static $fields = ['title', 'content', 'username'];

    /**
     * 初始化链接
     * ElasticSearchUtil constructor.
     */
    public function __construct()
    {
        if (!self::$esClient) {
            $host = [
                env('ELASTICSEARCH_HOST_NODE_1')
            ];

            self::$esClient = ClientBuilder::create()
                ->setHosts($host)
                ->setSelector('\Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector')
                ->setRetries(self::$retries)
                ->build();
        }
    }

    /**
     * es:init 初始化操作
     * 创建一个索引（index,类似于创建一个库）
     * 6.0版本以后一个index只能有一个type
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $index
     * @return array
     */
    public function createIndex($index)
    {
        // 获取mapping设置
        $properties = $this->contentMappingsConfig();

        $params = [
            'index' => $index,
            'body' => [
                'settings' => [
                    'number_of_shards' => env('NUMBER_OF_SHARDS', 5),      // 分片 默认5
                    'number_of_replicas' => env('NUMBER_OF_REPLICAS', 1)   // 副本、备份 默认1
                ],
                'mappings' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => $properties
                ]
            ]
        ];

        $response = self::$esClient->indices()->create($params);
        return $response;
    }

    /**
     * 删除一个索引（index,类似于删除一个库）
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $index
     *
     * @return array
     */
    public function deleteIndex($index)
    {
        $params = [
            'index' => $index
        ];

        $response =  self::$esClient->indices()->delete($params);
        return $response;
    }

    /**
     * 更改或增加 文章 post 索引的映射
     * 在创建完post的index后使用
     *
     * Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $index
     *
     * @return array
     */
    public function putMappingsForPost($index)
    {
        $properties = $this->contentMappingsConfig();

        $params = [
            'index' => $index,
            'body' => [
                '_source' => [
                    'enabled' => true
                ],
                'properties' => $properties
            ]
        ];

        $response = self::$esClient->indices()->putMapping($params);
        return $response;
    }

    /**
     * 获取某个index的mappings设置信息
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $index
     * @return array
     */
    public function getMappings($index)
    {
        $params = [
            'index' => $index
        ];

        $response = self::$esClient->indices()->getMapping($params);
        return $response;
    }

    /**
     * 设置某个index的setting
     * 不推荐在这里设置，尽量在创建index的时候设置好
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $index
     * @param array $settings ：['number_of_replicas' => 0, 'refresh_interval' => -1]
     *
     * @return array
     */
    public function putSettings($index, array $settings)
    {
        $params = [
            'index' => $index,
            'body' => [
                'settings' => $settings
            ]
        ];

        $response = self::$esClient->indices()->putSettings($params);
        return $response;
    }

    /**
     * 获取一个或者多个index的设置信息
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param string||array $index
     *
     * @return array
     */
    public function getSettings($index)
    {
        $params = [
            'index' => $index
        ];

        $response = self::$esClient->indices()->getSettings($params);
        return $response;
    }

    /**
     * 创建一条数据（索引一个文档）
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $index string
     * @param $id int 推荐使用数据库的ID
     * @param $body array('key' => 'val')
     *
     * @return array
     * @throws \Exception
     */
    public function createDoc($index, $id, $body)
    {
        // 规定必须字段
        foreach (self::$fields as $val) {
            if (!array_key_exists($val, $body)) {
                throw new \Exception('索引字段必须包含' . $val);
                break;
            }
        }

        $body['date'] = date('Y-m-d');
        $params = [
            'index' => $index,
            'id' => $id,
            'body' => $body,
        ];

        $response = self::$esClient->index($params);
        return $response;
    }

    /**
     * 获取一个文档
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $index string
     * @param $id int
     *
     * @return array
     */
    public function getDoc($index, $id)
    {
        $params = [
            'index' => $index,
            'id' => $id
        ];

        $response = self::$esClient->get($params);
        return $response;
    }

    /**
     * 获取一个文档的 _source
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $index string
     * @param $id int
     *
     * @return array
     */
    public function getDocSource($index, $id)
    {
        $params = [
            'index' => $index,
            'id' => $id
        ];

        $response = self::$esClient->getSource($params);
        return $response;
    }

    /**
     * 新增字段或者修改已有字段
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $index
     * @param $id
     * @param $body
     *
     * @return array
     */
    public function updateDoc($index, $id, $body)
    {
        $body['date'] = date('Y-m-d');
        $params = [
            'index' => $index,
            'id'    => $id,
            'body'  => [
                'doc' => $body
            ]
        ];

        $response = self::$esClient->update($params);
        return $response;
    }

    /**
     * 搜索文档 doc
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $index string
     * @param $query string
     * @param $page int
     *
     * @return array
     */
    public function search($index, $query, int $page = 1)
    {
        $size = env('SEARCH_SIZE');
        $page = $page > 0 ? $page : 1;
        $from = $size * ($page - 1);

        $params = [
            'index' => $index,
            'from' => $from,
            'size' => $size,
            'body' => [
                'query' => [
                    // 单字段
                    // 'match' => [
                    //     'key1' => $query
                    // ]
                    // 多字段
                    'multi_match' => [
                        'query' => $query,
                        'type' => 'best_fields',
                        'operator' => 'or',
                        'fields' => self::$fields
                    ]
                ],
                'sort' => [
                    '_id' => [
                        'order' => 'desc'
                    ]
                ]
            ],

        ];

        $response = self::$esClient->search($params);
        return $response['hits']['hits'];
    }

    /**
     * 删除一条记录（文档） doc
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $index
     * @param $id
     *
     * @return array
     */
    public function deleteDoc($index, $id)
    {
        $params = [
            'index' => $index,
            'id' => $id
        ];

        $response = self::$esClient->delete($params);
        return $response;
    }

    /**
     * 获取mapping配置参数
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return array
     */
    private function contentMappingsConfig()
    {
        $properties = [];
        $common = [
            'type' => 'text',
            'analyzer' => 'ik_smart',
            'search_analyzer' => 'ik_smart',
            'search_quote_analyzer' => 'ik_smart'
        ];

        foreach (self::$fields as $val) {
            $properties[$val] = $common;
        }

        // 添加单独的时间字段
        $properties['date'] = [
            'type' => 'date',
            'format' => 'year_month_day '
        ];

        return $properties;
    }
}
