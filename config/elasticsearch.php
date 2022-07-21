<?php
/*
|--------------------------------------------------------------------------
| ES 配置文件
| USER huaixiu.zhen
|--------------------------------------------------------------------------
| 所有ES配置文件将在此处进行
| 解放env文件
*/

return [

    // 集群配置
    'elasticsearch_host_node' => [
        '127.0.0.1:9200',
    ],

    'username' => '',
    'password' => '',

    // 文章index
    'post_index' => 'posts',

    // 问答index
    'answer_index' => 'answer',

    // 分片
    'number_of_shards' => 5,

    // 备份
    'number_of_replicas' => 1,

    // 链接重试次数
    'retries' => 2,

    // 前端搜索分页每页size
    'web_search_size' => 15,

    // 选择器
    'selector' => [
        'round_robin' => '\Elasticsearch\ConnectionPool\Selectors\RoundRobinSelector',
        'sticky_round_robin' => '\Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector',
        'random' => '\Elasticsearch\ConnectionPool\Selectors\RandomSelector',
    ],
];
