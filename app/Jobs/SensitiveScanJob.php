<?php
/**
 * 敏感词ES分段匹配存储示例代码
 *
- 先执行`php artisan es:init`进行初始化，将已存在的有效数据加入ES中，feed/user/comment
- feed/user/commemt(pgc&ugc)新建数据的时候进行插入es
- 查询所有扫描历史记录 history表
- 查看扫描数据 scan表（history_id和分类进行筛选查看）
- 扫描动作，通过所有敏感词进行es匹配，写history和scan表
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SensitiveScanJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $data;

    public $timeout = 3600 * 12;

    /**
     * Create a new job instance.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            set_time_limit(0);

            // 写入历史表数据,获取该次扫描ID，扫描完成后进行修改状态以及补全字段
            $sensitiveWordCount = SensitiveModel::count();
            $history = SensitiveScanHistory::create(['sensitive_word_count' => $sensitiveWordCount]);
            $historyId = $history->id;

            // 循环遍历敏感词，进行ES搜索，命中则入库
            SensitiveModel::orderBy('id')->chunk(500, function ($sensitives) use ($historyId) {
                foreach ($sensitives as $sensitive) {
                    $this->searchEsScroll($historyId, $sensitive['word']);
                }
            });

            // 更新history表
            $historyRes = [
                'illegal_name_count' => SensitiveScan::where(['history_id' => $historyId, 'body_type' => 'name'])->count(),
                'illegal_bio_count' => SensitiveScan::where(['history_id' => $historyId, 'body_type' => 'bio'])->count(),
                'illegal_feed_count' => SensitiveScan::where(['history_id' => $historyId, 'body_type' => 'feed'])->count(),
                'illegal_comment_count' => SensitiveScan::where(['history_id' => $historyId, 'es_index' => 'comments'])->count(),
                'status' => 1,
            ];
            $history->update($historyRes);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            $sensitiveHistory = SensitiveScanHistory::orderBy('id', 'desc')->first();
            if ($sensitiveHistory) {
                $sensitiveHistory->status = 2; // 失败
                $sensitiveHistory->save();
            }
        }
    }

    /**
     * 不会自动调用
     *
     * @param null $exception
     */
    public function fail($exception = null)
    {
        if ($this->job) {
            $this->job->fail($exception);
        }
    }

    /**
     * 搜索ES
     * 把所有敏感词匹配的所有es数据查出来，并加入到新表中（同时存高亮字段，如果存在，直接加进去）
     *
     * @param $historyId
     * @param $sensitiveWord
     *
     * @return bool
     */
    private function searchEs($historyId, $sensitiveWord)
    {
        $esArray = [new FeedElasticSearch(), new UserElasticSearch(), new CommentElasticSearch(),];
        $limit = config('elasticsearch')['web_search_size'];

        foreach ($esArray as $es) {

            // 实现ES的分段取出
            $page = 1;

            do {
                $search = $es->search($sensitiveWord, [], $page);
                foreach ($search as $item) {
                    $this->insertScan($historyId, $sensitiveWord, $item);
                }

                $countResult = count($search);
                if ($countResult == 0) {
                    break;
                }

                unset($search);

                $page++;
            } while ($limit == $countResult);
        }

        return true;
    }

    /**
     * 搜索ES （滚动查询方式）
     * 把所有敏感词匹配的所有es数据查出来，并加入到新表中（同时存高亮字段，如果存在，直接加进去）
     *
     * @param $historyId
     * @param $sensitiveWord
     *
     * @return bool
     */
    private function searchEsScroll($historyId, $sensitiveWord)
    {
        $esArray = [new FeedElasticSearch(), new UserElasticSearch(), new CommentElasticSearch(),];

        foreach ($esArray as $es) {

            // 实现ES的滚动取出
            $es->searchScroll($sensitiveWord, [], function ($search) use ($historyId, $sensitiveWord) {
                foreach ($search as $item) {
                    $this->insertScan($historyId, $sensitiveWord, $item);
                }
            });
        }

        return true;
    }

    /**
     * 写入/合并扫描结果
     *
     * @param $historyId
     * @param $sensitiveWord
     * @param $esItem
     */
    private function insertScan($historyId, $sensitiveWord, $esItem)
    {
        $highlight = $this->getBetweenStr($esItem['highlight']);

        // 同一个es_id需要合并敏感词以及高亮数据(正则匹配)
        $scan = SensitiveScan::where(['history_id' => $historyId, 'es_id' => $esItem['_id']])->first();
        if ($scan) {
            $tempWord = $scan->sensitive_word . ',' . $sensitiveWord;
            $tempHighlight = array_merge($highlight, $scan->highlight);
            $scan->update(['sensitive_word' => $tempWord, 'highlight' => json_encode($tempHighlight)]);
        } else {
            $data = [
                'history_id' => $historyId,
                'es_index' => $esItem['_index'],
                'es_id' => $esItem['_id'],
                'body_type' => $esItem['_source']['type'],
                'sensitive_word' => $sensitiveWord,
                'body' => json_encode($esItem['_source']),
                'highlight' => json_encode($highlight),
            ];
            SensitiveScan::create($data);
        }
        usleep(100);
    }

    /**
     * 合并高亮字段的匹配字段，供前端自己匹配高亮，标签即为高亮默认<em>
     *
     * @param $highlight
     *
     * @return array
     */
    private function getBetweenStr($highlight): array
    {
        $temp = [];
        foreach ($highlight as $item) {
            foreach ($item as $value) {
                preg_match_all('/<em>([\s\S]*?)<\/em>/i', $value, $matches);
                if ($matches[1]) {
                    $temp = array_merge($temp, $matches[1]);
                }
            }
        }

        return $temp;
    }
}
