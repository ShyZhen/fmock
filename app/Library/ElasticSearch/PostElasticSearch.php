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
    public static $pre = 'post_';

    public static $type = 'post';

    // 在config中进行配置索引名
    private $indexKey = 'post_index';

    /**
     * Override
     *
     * text通用字段、需要分词的字段
     * @var string[]
     */
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
    public function getIndexName() :string
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
        $mappings = $this->getMappingsConfig();
        $params = $this->getSettingConfig($mappings);
        $response = $this->esClient->indices()->create($params);

        return $response;
    }

    /**
     * @overload 获取mapping配置参数
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return array
     */
    protected function getMappingsConfig()
    {
        return parent::getMappingsConfig();
    }

    /**
     * @overload 获取setting参数
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
        return parent::getSettingConfig($properties);
    }
}
