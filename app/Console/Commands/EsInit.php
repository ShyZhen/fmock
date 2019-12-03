<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\ElasticSearch\PostElasticSearch;

class EsInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init elasticsearch(create index for post,use ik,set mappings)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 所有需要创建index的操作
        $es = new PostElasticSearch();
        $res = $es->createIndex();

        if (array_key_exists('acknowledged', $res) && $res['acknowledged']) {
            $this->info('===== create index successed =====');
        } else {
            $this->info('===== create index failed:' . $res['message'] . '=====');
        }
    }
}
