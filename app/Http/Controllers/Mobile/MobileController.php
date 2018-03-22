<?php


namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AppCrash;
use App\Models\AppVersion;
use App\Models\AppVersionIOS;
use App\Models\Goods;
use App\Models\ImagesText;
use App\Models\MobileUseLogs;
use App\Models\Pack;
use App\Models\Users;
use App\Services\SiteService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Mews\Captcha\Facades\Captcha;
use Symfony\Component\HttpFoundation\JsonResponse;

class MobileController extends Controller
{
    const ACTIVITY_LIST_PAGE_SIZE = 15;
    const MOUNT_LIST_PAGE_SIZE = 0;

    public function __construct()
    {
        config()->set('auth.defaults.guard', 'mobile');
    }

    /**
     * 移动端首页
     * 美女主播x4   全部主播x4
     */
    public function index()
    {
        $lists = [
            'rec' => [
                'key' => 'rec',
                'num' => 4,
            ],
            'all' => [
                'key' => 'all',
                'num' => 4,
            ],
        ];
        $redis = $this->make('redis');
        foreach ($lists as $key => &$list) {
            $list['data'] = json_decode($redis->get('m:index:list:' . $key));
            if (!$list['data']) {
                $tmpList = json_decode(@file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/videolist$key.json"));
                $rooms = empty($tmpList->rooms) ? [] : $tmpList->rooms;
                $list['data'] = array_slice($rooms, 0, $list['num']);
                $redis->set('m:index:list:' . $key, json_encode($list['data']), 180);
            }
        }
        return JsonResponse::create($lists);
    }

    /**
     * 移动端排行榜
     * @author Young <Young@wisdominfo.my>
     * @return rank page
     */
    public function rank()
    {
        return $this->render('Mobile/rank', []);
    }

    public function test()
    {
        if (empty($_GET['s']) || $_GET['s'] != "axwv4w8khj23") {
            return;
        }
        $username = $_GET['uname'];
        $password = $_GET['pwd'];

        $jwt = $this->make('JWTAuth');


        $token = $jwt->login([
            'username' => $username,
            'password' => $password,
        ]);
        echo $token;
    }

    /**
     * 获取用户信息
     */
    public function userInfo()
    {
        $uid = Auth::id();
        $remote_js_url = $GLOBALS['REMOTE_JS_URL'];
        $redis = $this->make('redis');
        $userinfo = (object)$redis->hgetall('huser_info:' . $uid) ?: Users::find($uid);
        if (!$userinfo) {
            return JsonResponse::create([
                'status' => 0,
                'msg' => '无效的用户',
                'js_url' => $remote_js_url,
            ]);
        }
        return JsonResponse::create()->setContent(urldecode(json_encode([
                'status' => 1,
                'uid' => $userinfo->uid,
                'nickname' => $userinfo->nickname,
                'headimg' => $this->getHeadimg($userinfo->headimg),
                'points' => $userinfo->points,
                'roled' => $userinfo->roled,
                'rid' => $userinfo->rid,
                'vip' => $userinfo->vip,
                'vip_end' => $userinfo->vip_end,
                'lv_rich' => $userinfo->lv_rich,
                'lv_exp' => $userinfo->lv_exp,
                'safemail' => $userinfo->safemail ?? '',
//                'mails' => $this->make('messageServer')->getMessageNotReadCount($userinfo->uid, $userinfo->lv_rich),// 通过服务取到数量
                'icon_id' => intval($userinfo->icon_id),
            ]))
        );
    }

    /**
     *用户特权
     */
    public function userPrivilege()
    {
        $uid = Auth::id();
        $user = Users::find($uid);

        //判断隐身权限
        $allowStealth = resolve(UserService::class)->getUserHiddenPermission($user);
        $return = [
            'allow_stealth' => $allowStealth,//可以隐身
            'hidden' => $allowStealth && $user->hidden,//当前隐身状态

        ];
        // 是贵族才验证 下掉贵族状态
        if ($user->vip && ($user->vip_end < date('Y-m-d H:i:s'))) {
            $user->vip = 0;
            $user->vip_end = null;
            $user->save();

            // 删除坐骑
            Pack::where('uid', $uid)->where('gid', '<=', 120107)->where('gid', '>=', 120101)->delete();
            $this->make('redis')->hSet('huser_info:' . $uid, 'vip', 0);
            $this->make('redis')->hSet('huser_info:' . $uid, 'vip_end', '');
            $this->make('redis')->del('user_car:' . $uid);
        }
        $return['vip'] = $user->vip;
        $return['vip_end'] = $user->vip_end;

        return JsonResponse::create($return);
    }

    /**
     * 座驾列表
     */
    public function mountList()
    {
        $uid = Auth::id();
//        $page = $this->make("request")->input('page',1);
        $list = Pack::with('mountGroup')->where('uid', $uid)->simplePaginate(self::MOUNT_LIST_PAGE_SIZE);
//        $result['user'] = $this->userInfo;
        $result['list'] = $list->toArray();
        $result['equip'] = $this->make('redis')->hgetall('user_car:' . $uid);
        //判断是否过期
        if ($result['equip'] != null && current($result['equip']) < time()) {
            $this->make('redis')->del('user_car:' . $uid);//检查过期直接删除对应的缓存key
        }
        //道具图片路径
        $result['sceneIcoUrl'] = '/flash/' . $this->flash_url_v . '/image/gift_material/';
        return JsonResponse::create($result);
    }

    /**
     * 装备坐骑
     */
    public function mount($gid)
    {
        $handle = $this->_getEquipHandle($gid);
        if (is_array($handle)) {
            return JsonResponse::create($handle);

        } else {
            return JsonResponse::create(['status' => 101, 'msg' => '操作出现未知错误！']);
        }
    }

    /**
     * 装备操作逻辑处理
     * @param $gid
     * @return array|bool
     * @author D.C
     * @update 2014.12.10
     * 复制自MemberController
     */
    private function _getEquipHandle($gid)
    {

        $uid = Auth::id();
        if (!$gid || !$uid) {
            return false;
        }

        $pack = Pack::where('uid', $uid)->where('gid', $gid)->first();
        if (!$pack) {
            return false;
        }

        /**
         * 判定道具类型,
         * @todo 跟[Antony]确认，规定category字段1000-1999ID范围为可装备道具,增加查询道具类型。
         */
        $goods = Goods::find($gid);
        if ($goods['gid'] < 120001 || $goods['gid'] > 121000) {
            return ['status' => 2, 'msg' => '该道具限房间内使用,不能装备！'];
        }

        /**
         * 使用Redis进行装备道具
         * @todo   目前道具道备只在Redis上实现，并未进行永久化存储。目前产品部【Antony】表示保持现状。
         * @update 2014.12.15 14:35 pm (Antony要求将道具改为同时只能装备一个道具！)
         */
        $redis = $this->make('redis');
        $redis->del('user_car:' . $uid);
        $redis->hset('user_car:' . $uid, $gid, $pack['expires']);
        return ['status' => 1, 'msg' => '装备成功'];
    }

    /**
     * 取消坐骑
     */
    public function unmount()
    {
        $this->make('redis')->del('user_car:' . Auth::id());//检查过期直接删除对应的缓存key
        return JsonResponse::create(['status' => 0, 'msg' => '操作成功']);
    }

    /**
     * 隐身
     */
    public function stealth($status)
    {
        if (!in_array($status, ['0', '1'])) return JsonResponse::create(['status' => 0, 'message' => '参数错误']);

        $uid = Auth::id();
        if (!$uid) return JsonResponse::create(['status' => 0, 'message' => '用户错误']);
        $userServer = resolve(UserService::class);
        $user = $userServer->getUserByUid($uid);
        //判断用户是否有隐身权限
        if (!$userServer->getUserHiddenPermission($user)) return JsonResponse::create(['status' => 0, 'message' => '没有权限!']);

        //更新数据库隐身状态
        Users::where('uid', $uid)->update(['hidden' => $status]);
        //更新用户redis
        $userServer->getUserReset($uid);

        return JsonResponse::create(['status' => 1, 'msg' => '操作成功']);
    }

    /**
     * 登录验证码
     */
    public function loginCaptcha()
    {
        return $captcha = Captcha::create();
//        $png = $captcha->getContent();
//        return JsonResponse::create(['captcha' => base64_encode($png), Session::getName() => Session::getId()]);
    }


    /**
     * 移动端登录
     */
    public function login(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');
        $captcha = $request->get('captcha');
        if (!app(SiteService::class)->config('SKIP_CAPTCHA_LOGIN')) {
            if (empty($captcha)) {
                return JsonResponse::create(['status' => 0, 'msg' => '验证码错误']);
            }
            if (!Captcha::check($captcha)) {
                return JsonResponse::create(['status' => 0, 'msg' => '验证码错误']);
            }
        }
        if (!$username || !$password) {
            return JsonResponse::create(['status' => 0, 'msg' => '用户名密码不能为空']);
        }
        $user = null;
        $jwt = Auth::guard();

        if (!$jwt->attempt(['username' => $username, 'password' => $password])) {
            return JsonResponse::create(['status' => 0, 'msg' => '用户名密码错误']);
        }
        $user = $jwt->user();
        $statis_date = date('Y-m-d');
        MobileUseLogs::create([
            'imei' => $request->get('imei'),
            'uid' => $user->getAuthIdentifier(),
            'ip' => $request->getClientIp(),
            'statis_date' => $statis_date,
        ]);
        return JsonResponse::create(['status' => 1, 'jwt' => (string)$jwt->getToken()]);
    }

    public function logintest()
    {
        return JsonResponse::create(['status' => Auth::check(), 'user' => Auth::user()]);
    }

    /**
     *移动端轮播图获取
     */
    public function sliderList()
    {
        $list = Redis::get('vbos.images:type:' . $this->request()->input('type', 1));
        $list = collect(json_decode($list))->map(function ($img) {
            return [
                'id' => $img->id,
                'url' => $img->url,
                'img_name' => $img->temp_name,
            ];
        });
        return JsonResponse::create($list);
    }

    /**
     * 移动端活动列表
     */
    public function activityList()
    {
        $page = $this->request()->input('page', 1);
        $redis = $this->make('redis');
        /*  if ($list = $redis->get('image.text:activity.list:page:' . $page)) {
              return JsonResponse::create()->setContent($list);
          }*/
        $list = ImagesText::where('dml_flag', '<>', 3)->where('pid', 0)->selectRaw('img_text_id id,title,temp_name,url,init_time')
            ->orderBy('sort')->orderBy('img_text_id', 'desc')->simplePaginate(static::ACTIVITY_LIST_PAGE_SIZE);
        $redis->set('image.text:activity.list:page:' . $page, $list->toJson(), 180);
        return JsonResponse::create($list->toArray());
    }

    /**
     * 移动端活动详情
     */
    public function activityDetail($id)
    {
        $redis = $this->make('redis');
        /* if ($activity = $redis->get('image.text:activity.detail:id:' . $id)) {
             return JsonResponse::create()->setContent($activity);
         }*/
        $activity = ImagesText::where('dml_flag', '<>', 3)->where('pid', $id)->selectRaw($id . ' id,temp_name,init_time')->first();
        //如果为空，返回默认json数据
        $is_array = [
            'id' => $id,
            'init_time' => '',
            'title' => '',
            'url' => [],
        ];
        if (!$activity) return JsonResponse::create($is_array);
        $parent = ImagesText::where('dml_flag', '<>', 3)->select('title')->find($id);
        $activity->title = $parent->title;
        $activity->url = explode(',', $activity->temp_name);
        $activity->setHidden(['temp_name']);
        $redis->set('image.text:activity.detail:id:' . $id, $activity->toJson(), 180);

        return JsonResponse::create($activity);
    }

    /**
     * 主播列表
     * @param $type string all:所有|rec:推荐|ord:一对一|ticket:一对多
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function videoList($type)
    {
        header('Content-type: application:json;charset=utf-8');
        header('Location: ' . "/videolist$type.json");
//        $list = @file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/videolist$type.json") ?: '[]';
//        return JsonResponse::create()->setContent($list);
    }

    /**
     * 用户关注列表
     */
    public function userFollowing()
    {
//        $page = $this->request()->input('page', 1);
//        $userServer = resolve(UserService::class);
        $arr = include(BASEDIR . '/app/cache/cli-files/anchor-search-data.php');
        $hasharr = [];
        foreach ($arr as $value) {
            $hasharr[$value['uid']] = $value;
        }
        unset($arr);
        $myfavArr = $this->make('redis')->zrevrange('zuser_attens:' . Auth::id(), 0, -1);
        $myfav = [];
        if (!!$myfavArr) {
            //过滤出主播
            foreach ($myfavArr as $item) {
                if (isset($hasharr[$item])) {
                    $myfav[] = $hasharr[$item];
                }
            }
        }
        return JsonResponse::create($myfav);
    }

    /**
     * 统计留存接口,一天只保存一条
     *   'imki'
     *   'uid'
     *   'ip'
     */
    public function statistic()
    {
        //如果有则更新，没有则创建
        $request = $this->request();
        $imei = $request->input('imei');
        $uid = $request->input('uid') ?: null;
        $ip = $request->input('ip') ?: '';
        if (!$imei) return JsonResponse::create([
            'status' => 0,
            'msg' => '请求参数错误',
        ]);
        if ($uid && !Users::find($uid)) {
            return JsonResponse::create([
                'status' => 0,
                'msg' => '请求参数错误',
            ]);
        }
        $statis_date = date('Y-m-d');
        MobileUseLogs::create([
            'imei' => $imei,
            'uid' => $uid,
            'ip' => $ip,
            'statis_date' => $statis_date,
        ]);
        return JsonResponse::create(['status' => 1, 'data' => '成功']);
    }

    /**
     * 关注
     */
    public function follow()
    {
        $request = $this->request();

        //获取操作类型  请求类型  0:查询 1:添加 2:取消
        $ret = $request->input('ret');
        //获取被关注用户uid
        $pid = $request->input('pid');
        if (!in_array($ret, [0, 1, 2]) || !$pid) {
            return JsonResponse::create([
                'status' => 0,
                'msg' => '请求参数错误',
            ]);
        };
        //获取当前用户id
        $uid = Auth::id();
        if (($ret != 0) && ($uid == $pid)) return JsonResponse::create(['status' => 0, 'msg' => '请勿关注自己！']);

        $userService = resolve(UserService::class);
        $userInfo = $userService->getUserByUid($pid);

        if (!is_array($userInfo)) {
            return JsonResponse::create([
                'status' => 0,
                'msg' => '请求参数错误',
            ]);
        };

        //查询关注操作
        if ($ret == 0) {
            if ($userService->checkFollow($uid, $pid)) {
                return new JsonResponse(['status' => 1, 'msg' => '已关注']);
            } else {
                return new JsonResponse(['status' => 0, 'msg' => '未关注']);
            }
        }

        //添加关注操作
        if ($ret == 1) {
            $follows = intval($this->getUserAttensCount($uid));
            if ($follows >= 1000) {
                return new JsonResponse(['status' => 3, 'msg' => '您已经关注了1000人了，已达上限，请清理一下后再关注其他人吧']);
            }

            if ($userService->setFollow($uid, $pid)) {
                return new JsonResponse(['status' => 1, 'msg' => '关注成功']);
            } else {
                return new JsonResponse(['status' => 0, 'msg' => '请勿重复关注']);
            }
        }

        //取消关注操作
        if ($ret == 2) {
            if ($userService->delFollow($uid, $pid)) {
                return new JsonResponse(['status' => 1, 'msg' => '取消关注成功']);
            } else {
                return new JsonResponse(['status' => 0, 'msg' => '取消关注失败']);
            }
        }


    }

    public function appVersion()
    {
        if (!isset($_REQUEST['agent']) && !isset($_REQUEST['branch']) || $_SERVER['REQUEST_METHOD'] === 'POST')
            return $this->appVersionIOS();
        $branches = $this->request()->input('branch');
        if ($branches) {
            $branches = explode(',', $branches);
        } else {
            $branches = range(1, 5);
        }
        $redis = $this->make('redis');
        $versions = [];
        foreach ($branches as $branch) {
            $version = $redis->get('m:app:versions:branch:' . $branch);
            if (!$version) {
                $version = AppVersion::whereRaw('released_at<=now()')
                    ->where('branch', $branch)->whereNull('deleted_at')->orderBy('ver_code', 'desc')->first();
                if ($version) {
                    $redis->set('m:app:versions:branch:' . $branch, json_encode($version), 300);
                    $versions[$branch] = $version;
                }
            } else {
                $versions[$branch] = json_decode($version);
            }
        }
        return JsonResponse::create(['status' => empty($versions) ? 0 : 1, 'data' => $versions]);
    }

    public function appVersionIOS()
    {
        $branches = $this->request()->input('branch');
        if ($branches) {
            $branches = explode(',', $branches);
        } else {
            $branches = range(1, 5);
        }
        $redis = $this->make('redis');
        $versions = [];
        foreach ($branches as $branch) {
            $version = $redis->get('m:app:versionsIOS:branch:' . $branch);
            if (!$version) {
                $version = AppVersionIOS::whereRaw('released_at<=now()')
                    ->where('branch', $branch)->whereNull('deleted_at')->orderBy('ver_code', 'desc')->first();
                if ($version) {
                    $redis->set('m:app:versions:branchIOS:' . $branch, json_encode($version), 300);
                    $versions[$branch] = $version;
                }
            } else {
                $versions[$branch] = json_decode($version);
            }
        }
        return JsonResponse::create(['status' => empty($versions) ? 0 : 1, 'data' => $versions]);
    }

    public function searchAnchor()
    {
        //$uname = isset($_GET['nickname'])?$_GET['nickname']:'';//解码？
        $uname = $this->make('request')->get('nickname') ?: '';

        $arr = include BASEDIR . '/app/cache/cli-files/anchor-search-data.php';
        $pageStart = isset($_REQUEST['pageStart']) ? ($_REQUEST['pageStart'] < 1 ? 1 : intval($_REQUEST['pageStart'])) : 1;
        $pageLimit = isset($_REQUEST['pageLimit']) ? (($_REQUEST['pageLimit'] > 40 || $_REQUEST['pageLimit'] < 1) ? 40 : intval($_REQUEST['pageLimit'])) : 40;

        if ($uname == '') {
            $pageStart = ($pageStart - 1) * $pageLimit;
            $data = array_slice(array_values($arr), $pageStart, $pageLimit);
            $i = count($arr);
        } else {
            $pageEnd = $pageStart * $pageLimit;
            $pageStart = ($pageStart - 1) * $pageLimit;
            $i = 0;
            $data = [];
            foreach ($arr as $key => $item) {
                if ((mb_strpos($item['username'], $uname) !== false) || (mb_strpos($item['uid'], $uname) !== false)) {
                    if ($i >= $pageStart && $i < $pageEnd) {
                        $data[] = $item;
                    }
                    ++$i;
                }
            }
        }
        return new JsonResponse(['data' => $data, 'status' => 1, 'total' => $i]);
    }

    public function saveCrash()
    {
        $key = '123';
        $crash = $this->request()->input('crash');
        $sign = $this->request()->input('sign');
        if (strtolower($sign) !== hash('sha256', $crash . $key)) {
            return JsonResponse::create(['status' => 0, 'msg' => 'sign error']);
        }
        $errors = json_decode(base64_decode($crash), JSON_OBJECT_AS_ARRAY);
        if ($errors === null) return JsonResponse::create(['status' => 0, 'msg' => 'json error']);
        foreach ($errors as $error) {
            AppCrash::create($error);
        }
        return JsonResponse::create(['status' => 1, 'msg' => 'success']);

    }
}
