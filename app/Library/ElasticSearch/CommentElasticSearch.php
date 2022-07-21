<?php
/**
 * CommentElasticSearch
 *
 * @author huaixiu.zhen
 * @link https://www.litblc.com
 * 2022/7/5 16:37
 **/

namespace App\Library\ElasticSearch;

use App\Library\ElasticSearch\Base\ElasticSearch;

class CommentElasticSearch extends ElasticSearch
{
    public static $pre = [
        'PGC' => 'comment_pgc_',
        'UGC' => 'comment_ugc_',
    ];

    public static $type = [
        'PGC' => 'pgc',
        'UGC' => 'ugc',
    ];

    private $indexKey = 'comment_index';

    /**
     * Override
     *
     * text通用字段、需要分词的字段
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
    public function getIndexName() :string
    {
        return $this->esConfig[$this->indexKey];
    }

    /**
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
