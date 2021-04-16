<?php
/**
 * DuomaiController
 *
 * @author DELL
 * 2021/4/16 11:22
 **/

namespace App\Http\Controllers\Api\V1;

use Duomai\CpsClient\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\BaseService\RedisService;

class DuomaiController extends Controller
{
    private $client;

    private $config;

    private $redisServer;

    private $allowPlatform = ['custom', 'jd', 'pdd', 'suning', 'youzan', 'alimama', 'kaola', 'vip', 'b1688'];

    // 缓存相关
    private $pre = 'ganfanzu:lists';
    private $ttl = 7200;

    public function __construct(RedisService $redisServer)
    {
        $this->redisServer = $redisServer;

        if (!$this->config) {
            $this->config = config('duomai');
        }

        if (!$this->client) {
            $config = [
                'host' => $this->config['host'],
                'auth' => [
                    'app_key' => $this->config['appKey'],
                    'app_secret' => $this->config['appSecret'],
                ],
            ];

            $this->client = new Client($config);
        }
    }

    /**
     * 获取默认商品列表
     * platform
     */
    public function getList($platform, Request $request)
    {
        if (!in_array($platform, $this->allowPlatform)) {
            return response()->json(
                ['message' => 'no support this platform'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // jd 使用京粉接口，不再使用通用接口
        // 通用接口价格有问题，没有优惠卷领取
        if ($platform == 'jd') {
            $categoryId = $request->get('category_id', 28);
            $page = (int) $request->get('page') ?: 1;

            return $this->getJingFenList($categoryId, $page);
        }

        // pdd 使用推荐-实时热销接口
        if ($platform == 'pdd') {
            // 推荐类型 0-1.9包邮, 1-今日爆款, 2-品牌清仓,3-相似商品推荐,4-猜你喜欢,5-实时热销,6-实时收益,7-今日畅销,8-高佣榜单
            $categoryId = $request->get('category_id', 6);
            $page = (int) $request->get('page') ?: 1;

            return $this->getPddRecommendList($categoryId, $page);
        }
    }

    /**
     * 获取京粉商品列表
     */
    public function getJingFenList($categoryId, $page)
    {
        // redis缓存
        $key = $this->pre . ':' . __FUNCTION__ . ':' . md5($categoryId . '-' . $page);
        $data = $this->getRedis($key);
        if (!is_null($data)) {
            return response()->json(
//                ['data' => $data],
                $data,
                Response::HTTP_OK
            );
        }

        try {
            $api = 'cps-mesh.cpslink.jd.jingfen-product.get';

            $data = $this->client->Request($api, [
                'category_id' => $categoryId,
                'page' => $page,
                'order_field' => 'commission_rate',  // 排序字段 commission_rate 佣金比例 price价格 volume 销量
            ]);

            $this->setRedis($key, $data);

            return response()->json(
//                ['data' => $data],
                $data,
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {
            return response()->json(
                ['message' => 'network error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * 获取拼多多推荐商品
     */
    public function getPddRecommendList($categoryId, $page)
    {
        // redis缓存
        $key = $this->pre . ':' . __FUNCTION__ . ':' . md5($categoryId . '-' . $page);
        $data = $this->getRedis($key);
        if (!is_null($data)) {
            return response()->json(
//                ['data' => $data],
                $data,
                Response::HTTP_OK
            );
        }

        try {
            $api = 'cps-mesh.cpslink.pdd.recommend-products.get';

            $data = $this->client->Request($api, [
                'channel_type' => $categoryId,
                'page' => $page,
            ]);

            // redis
            $this->setRedis($key, $data);

            return response()->json(
//                ['data' => $data],
                $data,
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {
            return response()->json(
                ['message' => 'network error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function getRedis($key)
    {
        return json_decode($this->redisServer->getRedis($key), true);
    }

    /**
     * 列表加入缓存，2小时失效
     *
     * @param $key
     * @param $data
     *
     * @return string|null
     */
    private function setRedis($key, $data)
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->redisServer->setRedis($key, $data, 'EX', $this->ttl);
    }

    /**
     * 搜索列表
     *
     * @param $platform
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQueryList($platform, Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->get('query', '下饭');
        $page = (int) $request->get('page') ?: 1;

        if (!in_array($platform, $this->allowPlatform)) {
            return response()->json(
                ['message' => 'no support this platform'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($platform == 'custom') {
            return response()->json(
//                ['data' => []],
                [],
                Response::HTTP_OK
            );
        }

        try {
            $api = "cps-mesh.cpslink.{$platform}.products.get";

            $data = $this->client->Request($api, [
                'query' => $query,
                'page' => $page,
                'is_hot' => 1,
                'is_coupon' => 1,
                // 'max_coupon' => 1,
                'order_field' => 'volume ',  // 排序字段 commission_rate 佣金比例 price价格 volume 销量
            ]);

            return response()->json(
//                ['data' => $data],
                $data,
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {
            return response()->json(
                ['message' => 'network error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * 使用getLink生成后，直接返回给前端（写可以写接口调用，但是循环调用性能差，条目不宜过多）
     * http://ganfanzu-test.com:81/?module=Api&controller=Index&action=getLink
     */
    public function getCustomProduct()
    {
        $itemUrls = ['data' => [
            [
                'item_id' => '100007827998',
                'coupon' => 'https://coupon.jd.com/ilink/couponSendFront/send_index.action?key=548ce38b10244309ae7d1c4f07b916dc&roleId=47024698&to=https://book.jd.com,https://pro.m.jd.com/mall/active/3u1Q7ZjCfQKrRb52c623WNf3Cjz5/index.html',
                'coupon_price' => '10',
                'platform' => 'jd',

                'item_url' => 'https://item.jd.com/100007827998.html',
                'short_url' => 'https://u.jd.com/uoPViuu',
                'seller_name' => '京东超市',
                'product_title' => '李子柒 广西柳州特产(煮食)袋装 方便速食面粉米线 螺蛳粉 335g*3包',
                'product_main_picture' => 'https://img.duomai.com/20210310003005_uai9hpux9x.jpg',
                'product_original_price' => '39.7',
                'product_coupon_after_price' => '39.7',
            ],
            [
                'item_id' => '65443604411',
                'coupon' => '',
                'coupon_price' => '',
                'platform' => 'jd',

                'item_url' => 'https://item.jd.com/65443604411.html',
                'short_url' => 'https://u.jd.com/uJu6TNd',
                'seller_name' => '京东超市',
                'product_title' => 'RIO锐澳樱花风味微醺系列鸡尾酒330ml*10罐（七种口味）',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/156249/13/11903/95705/603c60c1E7d736c31/a3629165ecea4a0b.jpg',
                'product_original_price' => '88',
                'product_coupon_after_price' => '79.2',
            ],
            [
                'item_id' => '7532054',
                'coupon' => 'https://coupon.jd.com/ilink/couponSendFront/send_index.action?key=g4uci3dbe02403194e7d1c4f07b916dc&roleId=47024698&to=https://book.jd.com,https://pro.m.jd.com/mall/active/3u1Q7ZjCfQKrRb52c623WNf3Cjz5/index.html',
                'coupon_price' => '10',
                'platform' => 'jd',

                'item_url' => 'https://item.jd.com/7532054.html',
                'short_url' => 'https://u.jd.com/urPJ210',
                'seller_name' => '京东超市',
                'product_title' => '卜珂零点 雪花酥蔓越莓味 110g 牛轧糖奶芙沙琪玛手工网红甜品办公室零食小吃糕点早餐',
                'product_main_picture' => 'https://img.duomai.com/20210305003005_jdqls1ga4u.jpg',
                'product_original_price' => '11.8',
                'product_coupon_after_price' => '11.8',
            ],
            [
                'item_id' => '71043158714',
                'coupon' => '',
                'coupon_price' => '',
                'platform' => 'jd',

                'item_url' => 'https://item.jd.com/71043158714.html',
                'short_url' => 'https://u.jd.com/u0P5f8G',
                'seller_name' => '京东超市',
                'product_title' => '百事可乐 碳酸汽水饮料330*24听 细长罐 Pepsi百事出品 新老包装随机发',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/170932/15/193/124151/5fed8b8fEf030f771/6004c08ef8232078.jpg',
                'product_original_price' => '50.9',
                'product_coupon_after_price' => '50.9',
            ],
            [
                'item_id' => '100013092738',
                'coupon' => '',
                'coupon_price' => '',
                'platform' => 'jd',

                'item_url' => 'https://item.jd.com/100013092738.html',
                'short_url' => 'https://u.jd.com/uuPXDQw',
                'seller_name' => '京东超市',
                'product_title' => '西麦 西澳阳光 水果燕麦片 营养代餐 麦片早餐  酸奶伴侣 干吃零食 轻食非油炸 酸奶果粒烘焙燕麦片350g',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/128668/4/158/114678/5eb3afeeE9fbd70dd/1812bec81f667323.jpg',
                'product_original_price' => '54.8',
                'product_coupon_after_price' => '45.9',
            ],
            [
                'item_id' => '100010088575',
                'coupon' => '',
                'coupon_price' => '',
                'platform' => 'jd',

                'item_url' => 'https://item.jd.com/100010088575.html',
                'short_url' => 'https://u.jd.com/uj0CqMg',
                'seller_name' => '京东超市',
                'product_title' => '希腊进口哇尔塔Epsa混合果汁汽水果汁型碳酸饮料232ml*6瓶 玻璃瓶装饮品',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/167166/25/3508/253690/600e86f6Eb73ff38c/b97e52ded2badcab.jpg',
                'product_original_price' => '89',
                'product_coupon_after_price' => '89',
            ],
            [
                'item_id' => '10023311281258',
                'coupon' => '',
                'coupon_price' => '',
                'platform' => 'jd',

                'item_url' => 'https://item.jd.com/10023311281258.html',
                'short_url' => 'https://u.jd.com/srqwSfU',
                'seller_name' => '京东自营',
                'product_title' => '乐小蜜 油菜蜂花粉 破壁蜂花粉 180克瓶装 食用蜂蜜花粉',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/132475/39/12999/93848/5f8e7e94E073f6f09/c622caf4c5bac620.jpg',
                'product_original_price' => '67.9',
                'product_coupon_after_price' => '67.9',
            ],
            [
                'item_id' => '100006148569',
                'coupon' => '',
                'coupon_price' => '',
                'platform' => 'jd',

                'item_url' => 'https://item.jd.com/100006148569.html',
                'short_url' => 'https://u.jd.com/sqqzfTM',
                'seller_name' => '京东自营',
                'product_title' => '君乐宝简醇梦幻盖 0添加蔗糖 高端风味酸牛奶250g*10礼盒装',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/150811/17/12309/95683/5fe53e1dE6c4e9266/a43c2075f60be8db.jpg',
                'product_original_price' => '69',
                'product_coupon_after_price' => '69',
            ],
            [
                'item_id' => '5175009',
                'coupon' => '',
                'coupon_price' => '',
                'platform' => 'jd',

                'item_url' => 'https://item.jd.com/5175009.html',
                'short_url' => 'https://u.jd.com/s3qGvxo',
                'seller_name' => '京东自营',
                'product_title' => '海底捞 自热火锅方便速食 清油麻辣嫩牛自煮自嗨小火锅懒人食品零食435g',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/154716/5/10135/144782/5fdabe74E73743983/d9990d8a10472a40.jpg',
                'product_original_price' => '34',
                'product_coupon_after_price' => '34',
            ],
            [
                'item_id' => '100006711834',
                'coupon' => 'https://coupon.jd.com/ilink/couponActiveFront/front_index.action?key=gau3ibd0ea510d0399d0185950f5bd93&roleId=47514248&to=https://item.jd.com/100006711834.html,https://item.m.jd.com/product/100006711834.html',
                'coupon_price' => '5',
                'platform' => 'jd',

                'item_url' => 'https://item.jd.com/100006711834.html',
                'short_url' => 'https://u.jd.com/uaJjyxv',
                'seller_name' => '京东自营',
                'product_title' => '青源堂 红枸杞子 500克家庭装 宁夏头茬免洗特级中宁枸杞子茶 滋补品送礼物精洗苟杞子黑枸杞',
                'product_main_picture' => 'https://img14.360buyimg.com/pop/jfs/t1/159756/3/11754/239736/6046d83dEd651b0b9/2d81199fa87ad24d.jpg',
                'product_original_price' => '42.9',
                'product_coupon_after_price' => '36.9',
            ],
        ]];

        return response()->json(
//            ['data' => $itemUrls],
            $itemUrls,
            Response::HTTP_OK
        );
    }

    /**
     * 根据item_id获取详情（与list中的一样）
     *
     * @param $platform
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($platform, Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->get('item_id');

        if (!in_array($platform, $this->allowPlatform)) {
            return response()->json(
                ['message' => 'no support this platform'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $api = "cps-mesh.cpslink.{$platform}.products.detail";

            if (!$query) {
                return response()->json(
                    ['message' => 'item_id required'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $data = $this->client->Request($api, [
                'id' => $query,
            ]);

            return response()->json(
//                ['data' => $data],
                $data,
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {
            return response()->json(
                ['message' => 'network error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * 转链 返回小程序url
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLink(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->get('item_url');

        try {
            $api = 'cps-mesh.cpslink.links.post';

            if (!$query) {
                return response()->json(
                    ['message' => 'item_url required'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $data = $this->client->Request($api, [
                'site_id' => $this->config['siteId'],
                'url' => $query,  // 形如 'https://item.jd.com/100014809454.html'
                'original' => 1,
            ]);

            return response()->json(
//                ['data' => $data],
                $data,
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {
            return response()->json(
                ['message' => 'network error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * 获取图文详情
     *
     * @param $platform
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHtml($platform, Request $request): \Illuminate\Http\JsonResponse
    {
        $itemId = $request->get('item_id');

        if (!in_array($platform, $this->allowPlatform)) {
            return response()->json(
                ['message' => 'no support this platform'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $api = "cps-mesh.cpslink.{$platform}.desc.get";

            if (!$itemId) {
                return response()->json(
                    ['message' => 'item_id required'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $data = $this->client->Request($api, [
                'id' => $itemId,
            ]);

            return response()->json(
//                ['data' => $data],
                $data,
                Response::HTTP_OK
            );
        } catch (\Exception $exception) {
            return response()->json(
                ['message' => 'network error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
