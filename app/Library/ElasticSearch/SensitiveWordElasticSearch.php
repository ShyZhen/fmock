<?php

/**
 * 存储所有敏感词，实现敏感词反向查询
 *
 * Created by PhpStorm.
 * User shyZhen <huaixiu.zhen@gmail.com>
 * Date: 2019/12/2
 * Time: 11:25
 **/

namespace App\Library\ElasticSearch;

use stdClass;
use App\Library\ElasticSearch\Base\ElasticSearch;

class SensitiveWordElasticSearch extends ElasticSearch
{

    public $indexKey = 'sensitive';

    public static $size = 20;

    public static $word = 'word';


    /**
     *  获取index
     *
     * @return string
     */
    public function getIndexName() :string
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
     * 重写, 写入敏感词
     *
     * @param int $id
     * @param array $body
     * @return void
     */
    public function createDoc($id, $word)
    {
        $params = [
            'index' => $this->index,
            'id' => $id,
            'body' => [
                'query' => [
                    'match_phrase' => [
                        self::$word  => $word
                    ]
                ],
                self::$word  => $word,
            ],
        ];
        return $this->esClient->index($params);
    }

    /**
     * 反向查询敏感词
     *
     * @param array $word
     *
     * @return array
     */
    public function percolateSearch($word)
    {
        $params = [
            'size' => self::$size,
            'index' => $this->index,
            'body' => [
                'query' => [
                    'percolate' => [
                        'field' => 'query',
                        'document' => [self::$word => $word],
                    ]
                ],
                'highlight' => [
                    'fields' => [
                        self::$word  => new stdClass(),
                        // self::$word  => [
                        //     'pre_tags' => ["<b style='color:red'>"],
                        //     'post_tags' => ["</b>"],
                        // ]
                    ]
                ],
            ],
        ];
        $response = $this->esClient->search($params);

        return $response['hits']['hits'] ?? [];
    }
}
