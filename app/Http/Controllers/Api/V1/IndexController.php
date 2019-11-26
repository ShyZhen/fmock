<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/21
 * Time: 13:04
 */
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BaseService\ElasticSearchService;

class IndexController extends Controller
{
    public function getLocale(ElasticSearchService $elasticSearchService)
    {
        throw new \Exception('索引字段必须包含');
//        dd($elasticSearchService->createDoc(env('ES_INDEX'), 1, ['title'=>'标题','username'=>'shyZhen','content'=>'hello 你好世界']));
//        dd($elasticSearchService->getDoc('test', 1));
//        dd($elasticSearchService->search(env('ES_INDEX'),'标'));
        return __('app.test');
    }
}
