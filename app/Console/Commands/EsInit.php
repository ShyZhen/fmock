<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BaseService\ElasticSearchService;

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
        //
        $es = new ElasticSearchService();
        $index = env('ES_INDEX');
        $res = $es->createIndex($index);

        if (array_key_exists('acknowledged', $res) && $res['acknowledged']) {
            $this->info('===== create index successed =====');
        } else {
            $this->info('===== create index failed:' . $res['message'] . '=====');
        }
    }
}
