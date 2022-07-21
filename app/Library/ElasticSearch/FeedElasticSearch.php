<?php
/**
 * 不在初始化设置分词
 *
 * FeedEnElasticSearch
 *
 * @author huaixiu.zhen
 * @link https://www.litblc.com
 * 2022/7/4 10:07
 **/

namespace App\Library\ElasticSearch;

use App\Library\ElasticSearch\Base\ElasticSearch;

class FeedElasticSearch extends ElasticSearch
{
    public static $pre = 'feed_';
    public static $type = 'feed';

    // 在config中进行配置索引名
    private $indexKey = 'feed_index';

    /**
     * Override
     *
     * text通用字段、需要分词的字段
     * @var string[]
     */
    public $fields = ['title', 'content'];

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
    public function getIndexName(): string
    {
        return $this->esConfig[$this->indexKey];
    }

    /**
     * es:init 初始化操作
     * 创建索引
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @return array
     */
    public function createIndex()
    {
        $mappings = $this->getMappingsConfig();
        $params = $this->getSettingConfig($mappings);
        return $this->esClient->indices()->create($params);
    }
}
