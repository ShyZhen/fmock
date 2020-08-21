<?php
/**
 * Created by PhpStorm.
 * User shyZhen <huaixiu.zhen@gmail.com>
 * Date: 2019/12/2
 * Time: 11:25
 */

namespace App\Library\ElasticSearch;

use App\Library\ElasticSearch\Base\ElasticSearch;

class PostElasticSearch extends ElasticSearch
{
    // 文章索引
    private $indexKey = 'post_index';

    public $fields = ['title', 'content', 'username'];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 返回当前index名字
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return string
     */
    public function getIndexName()
    {
        return $this->esConfig[$this->indexKey];
    }

    /**
     * es:init 初始化操作
     * 创建article索引
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @return array
     */
    public function createIndex()
    {
        $mappings = $this->articleMappingsConfig();
        $params = $this->articleSettingConfig($mappings);
        $response = $this->esClient->indices()->create($params);

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
    private function articleMappingsConfig()
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

        // 添加单独的时间字段
        $properties['date'] = [
            'type' => 'date',
            'format' => 'year_month_day ',
        ];

        return $properties;
    }

    /**
     * 获取setting参数
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $properties
     *
     * @return array
     */
    private function articleSettingConfig($properties)
    {
        $params = [
            'index' => $this->getIndexName(),
            'body' => [
                'settings' => [
                    'number_of_shards' => $this->esConfig['number_of_shards'],       // 分片 默认5
                    'number_of_replicas' => $this->esConfig['number_of_replicas'],   // 副本、备份 默认1
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
                                'tokenizer' => $this->tokenizer,
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
