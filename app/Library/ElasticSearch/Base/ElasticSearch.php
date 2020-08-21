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

    public $tokenizer = 'ik_max_word';

    /**
     * 需要索引的字段、规定添加doc时必须的字段，由子类override
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @var array
     */
    public $fields = [];

    /**
     * 初始化链接
     * ElasticSearchUtil constructor.
     */
    public function __construct()
    {
        $this->esConfig = config('elasticsearch');

        if (!$this->esClient) {
            $host = $this->esConfig['elasticsearch_host_node'];

            $this->esClient = ClientBuilder::create()
                ->setHosts($host)
                ->setSelector($this->esConfig['selector']['sticky_round_robin'])
                ->setRetries($this->esConfig['retries'])
                ->build();
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
    abstract public function getIndexName();

    /**
     * 设置当前index
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return mixed
     */
    public function setIndexName()
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
     * @param $page int
     * @param $analyzer string
     *
     * @return array
     */
    public function search($query, int $page = 1, $analyzer = '')
    {
        $size = $this->esConfig['web_search_size'];
        $page = $page > 0 ? $page : 1;
        $from = $size * ($page - 1);

        $params = [
            'index' => $this->index,
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
                        'fields' => $this->fields,
                        'analyzer' => $analyzer ?: $this->tokenizer,
                    ],
                ],
                'sort' => [
                    '_id' => [
                        'order' => 'desc',
                    ],
                ],
            ],

        ];

        $response = $this->esClient->search($params);

        return $response['hits']['hits'];
    }
}
