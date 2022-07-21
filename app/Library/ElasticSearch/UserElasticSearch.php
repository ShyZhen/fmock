<?php
/**
 * UserElasticSearch
 *
 * @author huaixiu.zhen
 *
 * @link https://www.litblc.com
 * 2022/7/5 17:06
 **/

namespace App\Library\ElasticSearch;

use App\Library\ElasticSearch\Base\ElasticSearch;

class UserElasticSearch extends ElasticSearch
{
    public static $pre = [
        'NAME' => 'user_name_',
        'BIO' => 'user_bio_',
    ];

    /**
     * 用于区分类型，同时支持ES筛选
     *
     * @var string[]
     */
    public static $type = [
        'NAME' => 'name',
        'BIO' => 'bio',
    ];

    private $indexKey = 'user_index';

    /**
     * Override
     *
     * text通用字段、需要分词的字段
     * 通过type进行区分是username还是bio
     *
     * @var string[]
     */
    public $fields = ['content'];

    public function __construct()
    {
        parent::__construct();
    }

    /**
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
     * 如不用通用的设置，getMappingsConfig getSettingConfig需要在此处override
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
