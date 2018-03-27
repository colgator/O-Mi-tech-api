<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Facades\SiteSer;
use App\Models\AgentsPriv;
use App\Models\AgentsRelationship;
use App\Models\Anchor;
use App\Models\CarGame;
use App\Models\CarGameBetBak;
use App\Models\Goods;
use App\Models\LevelRich;
use App\Models\MallList;
use App\Models\Messages;
use App\Models\Pack;
use App\Models\Recharge;
use App\Models\RoomAdmin;
use App\Models\RoomDuration;
use App\Models\RoomOneToMore;
use App\Models\RoomStatus;
use App\Models\TimecostMallList;
use App\Models\Transfer;
use App\Models\UserBuyGroup;
use App\Models\UserBuyOneToMore;
use App\Models\UserCommission;
use App\Models\UserGroup;
use App\Models\Users;
use App\Models\WithDrawalList;
use App\Services\Message\MessageService;
use App\Services\Room\RoomService;
use App\Services\User\UserService;
use Core\Exceptions\NotFoundHttpException;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;
use Mews\Captcha\Facades\Captcha;

class MemberController extends Controller
{
    private $_menus = [
        /**
         * role: 0: 所有角色都有， 1, 普通用户才有， 2, 主播才有, 3, 需要后台人工设置
         */
        [
            'role' => 0,
            'action' => 'index',
            'name' => '基本信息',
        ],
        // array(
        //     'action' => 'invite',
        //     'name' => '推广链接',
        //     'ico' => 7,
        // ),
        [
            'role' => 0,
            'action' => 'attention',
            'name' => '我的关注',
        ],
        [
            'role' => 0,
            'action' => 'scene',
            'name' => '我的道具',
        ],
        [
            'role' => 0,
            'action' => 'charge',
            'name' => '充值记录',
        ],
        [
            'role' => 0,
            'action' => 'consume',
            'name' => '消费记录',
        ],
        //消费统计，用户
        [
            'role' => 1,
            'action' => 'count',
            'name' => '消费统计',
        ],
        //收入统计，主播
        [
            'role' => 2,
            'action' => 'count',
            'name' => '收入统计',
        ],
        [
            'role' => 0,
            'action' => 'vip',
            'name' => '贵族体系',
        ],
        [
            'role' => 0,
            'action' => 'password',
            'name' => '密码管理',
        ],//主播才有
        [
            'role' => 0,
            'action' => 'reservation',
            'name' => '我的预约',
        ],//主播才有
        [
            'role' => 2,
            'action' => 'roomset',
            'name' => '房间管理',//房间管理员
        ],//主播才有
        [
            'role' => 3,
            'action' => 'transfer',
            'name' => '转账',
        ],//主播才有
        [
            'role' => 2,
            'action' => 'withdraw',
            'name' => '提现',
        ],
        [
            'role' => 2,
            'action' => 'anchor',
            'name' => '主播中心',
        ],//主播才有
        [
            'role' => 0,
            'action' => 'gamelist',
            'name' => '房间游戏',
        ],//主播才有
        [
            'role' => 2,
            'action' => 'commission',
            'name' => '佣金统计',
        ],//主播才有
        [
            'role' => 2,
            'action' => 'live',
            'name' => '直播记录',
        ],//主播才有
        [
            'role' => 0,
            'action' => 'msglist',
            'name' => '消息',
        ],
        [
            'role' => 0,
            'action' => 'agents',
            'name' => '代理数据',
        ],
    ];

    public function getMenu()
    {
        $user = Auth::user();
        $hasAgentsPriv = AgentsPriv::where('uid', $this->userInfo['uid'])->count();

        $params['menus_list'] = [];

        foreach ($this->_menus as $key => $item) {

            //后台设置转帐菜单
            //if ((!isset($this->userInfo['transfer']) || !$this->userInfo['transfer']) && $item['action'] == 'transfer') continue;

            //代理菜单
            if (!$hasAgentsPriv && $item['action'] == 'agents') continue;

            //role == 0 为所有用户权限
            if ($item['role'] == 0) {
                $params['menus_list'][] = $item;
                //continue;
            }

            //role == 1 普通用户 role == 2 主播
            //如果是主播
            if ($user['roled'] == 3 && $item['role'] == 2) {
                $params['menus_list'][] = $item;
            }

            //如果不是主播
            if ($user['roled'] != 3 && $item['role'] == 1) {
                $params['menus_list'][] = $item;
            }

            //如果需要人工设置
            if ($item['role'] == 3 && (isset($user[$item['action']]) && $user[$item['action']])) {
                $params['menus_list'][] = $item;
            }

        }
        return JsonResponse::create(['status' => 1, 'data' => $params]);
    }

    /**
     * 用户中心 基本信息
     */
    public function index()
    {
        $userService = resolve(UserService::class);
        $modNickName = $userService->getModNickNameStatus();
        return JsonResponse::create(['status' => 1, 'data' => ['modNickName' => $modNickName]]);
    }

    /**
     * 用户中心 消息 列表页面
     *
     * @param int $type 消息类型
     *                  1 系统消息
     *                  2 私信
     * @return JsonResponse
     * @author Nicholas
     */
    public function msglist($type = 1)
    {
        // 调用消息服务
        $msg = resolve(MessageService::class);

        // 根据用户登录的uid或者用户消息的分页数据
        $ms = $msg->getMessageByUidAndType(Auth::id(), $type, '', Auth::user()->lv_rich);

        // 更新读取的状态
        $msg->updateMessageStatus(Auth::id());

        return JsonResponse::create([
            'status' => 1,
            'data' => $ms,
        ]);
    }

    /**
     * [transfer 转帐功能]
     */
    public function transfer(Request $request)
    {
        $uid = Auth::id();
        $user = Auth::user();
        //转帐菜单
        if (!$user->transfer) {
            return JsonResponse::create(['status' => 0, 'msg' => '没有权限']);
        }
        /**
         * 转帐处理过程
         * @todo 待优化
         */
        if (!Captcha::check($request->get('code'))) return new JsonResponse(['status' => 0, 'message' => '验证码错误!']);;
        //收款人信息
        $username = $request->get('username');
        $points = $request->get('points');
        $content = $request->get('content');

        if ($username == $user['username']) return new JsonResponse(['status' => 0, 'message' => '不能转给自己!']);

        if (intval($points) < 1) return new JsonResponse(['status' => 0, 'message' => '转帐金额错误!']);

        //获取转到用户信息
        $userTo = resolve(UserService::class)->getUserByUsername($username);

        if (!$userTo) return new JsonResponse(['status' => 0, 'message' => '对不起！该用户不存在']);

        if (!$user['transfer']) return new JsonResponse(['status' => 0, 'message' => '对不起！您没有该权限！']);

        if ($user['points'] < $points) return new JsonResponse(['status' => 0, 'message' => '对不起！您的钻石余额不足!']);

        //开始转帐事务处理
        DB::beginTransaction();
        try {
            DB::table((new Users)->getTable())->where('uid', $uid)->decrement('points', $points);
            //update(array('points' => $this->userInfo['points'] - $points));

            DB::table((new Users)->getTable())->where('uid', $userTo['uid'])->increment('points', $points);
            //update(array('points' => $user['points'] + $points));

            //记录转帐
            DB::table((new Transfer)->getTable())->insert(['by_uid' => $uid, 'by_nickname' => $user['nickname'], 'to_uid' => $userTo['uid'], 'to_nickname' => $userTo['nickname'], 'points' => $points, 'content' => $content, 'datetime' => date('Y-m-d H:i:s'), 'status' => 1]);

            //写入recharge表方便保级运算
            DB::table((new Recharge)->getTable())->insert(['uid' => $userTo['uid'], 'points' => $points, 'paymoney' => round($points / 10, 2), 'created' => date('Y-m-d H:i:s'), 'order_id' => 'transfer_' . $uid . '_to_' . $userTo['uid'] . '_' . uniqid(), 'pay_type' => 7, 'pay_status' => 2, 'nickname' => $userTo['nickname']]);


            //发送成功消息给转帐人
            $from_user_transfer_message = ['mail_type' => 3, 'rec_uid' => $uid, 'content' => '您成功转出' . $points . '钻石到 ' . $username . ' 帐户'];
            resolve(MessageService::class)->sendSystemToUsersMessage($from_user_transfer_message);

            //发送成功消息给收帐人
            $to_user_transfer_message = ['mail_type' => 3, 'rec_uid' => $userTo['uid'], 'content' => '您成功收到由 "' . $user['nickname'] . '" 转到您帐户' . $points . '钻石'];
            resolve(MessageService::class)->sendSystemToUsersMessage($to_user_transfer_message);

            DB::commit();//事务提交

            //检查收款人用户VIP保级状态 一定要在事务之后进行验证
            $this->checkUserVipStatus($userTo);
            //更新转帐人用户redis信息
            resolve(UserService::class)->getUserReset($uid);

            //更新收款人用户redis信息
            resolve(UserService::class)->getUserReset($userTo['uid']);


            return new JsonResponse(['status' => 1, 'message' => '您成功转出' . $points . '钻石']);
        } catch (\Exception $e) {
            DB::rollBack();//事务回滚
            logger()->debug($e);
            return new JsonResponse(['status' => 0, 'message' => '对不起！转帐失败!']);
        }
    }

    public function transferHistory(Request $request)
    {
        $mintime = $request->get('mintime');
        $maxtime = $request->get('maxtime');
        $uid = Auth::id();
        $transfers = Transfer::where(function ($query) use ($uid) {
            $query->where('by_uid', $uid)->orWhere('to_uid', $uid);
        });
        if ($mintime && $maxtime) {
            $v['mintime'] = date('Y-m-d 00:00:00', strtotime($mintime));
            $v['maxtime'] = date('Y-m-d 23:59:59', strtotime($maxtime));
            $transfers->where('datetime', '>=', $mintime)->where('datetime', '<=', $maxtime);
        }
        $transfers = $transfers->orderBy('datetime', 'desc')->paginate(10)
            ->appends(['mintime' => $mintime, 'maxtime' => $maxtime]);
        return $this->render('Member/transfer', ['status' => 1, 'data' => $transfers]);

    }

    /**
     * 用户中心 代理数据
     * @author raby
     * @update 2016.06.30
     * @return Response
     */
    public function agents()
    {
        $agentsPriv = AgentsPriv::where('uid', $this->userInfo['uid'])->with("agents")->first();
        if (!$agentsPriv) {
            return new RedirectResponse('/');
        }
        $agentsRelationship = AgentsRelationship::where('aid', $agentsPriv->aid)->get()->toArray();
        $uidArray = array_column($agentsRelationship, 'uid');

        $mintimeDate = $this->request()->get('mintimeDate') ?: date('Y-m-d', strtotime('-1 month'));
        $maxtimeDate = $this->request()->get('maxtimeDate') ?: date('Y-m-d');
        $mintime = date('Y-m-d H:i:s', strtotime($mintimeDate));
        $maxtime = date('Y-m-d H:i:s', strtotime($maxtimeDate . '23:59:59'));

        $recharge_points = Recharge::whereIn('uid', $uidArray)->whereBetween('created', [$mintime, $maxtime])
            ->where('pay_status', 1)
            ->whereIn('pay_type', [1, 4, 7])
            ->sum('paymoney');
        $recharge_points = $recharge_points * 10;
        $rebate_points = ($recharge_points * $agentsPriv->agents->rebate) / 100;

        $agent_members = AgentsRelationship::where('aid', $agentsPriv->aid)->count();

        $list = [
            [
                'aid' => $agentsPriv->aid,
                'username' => $agentsPriv->agents->agentaccount,
                'nickname' => $agentsPriv->agents->nickname,
                'members' => $agent_members,
                'recharge_points' => $recharge_points,
                'rebate_points' => $rebate_points,
            ],
        ];
        return $this->render('Member/agents', ['data' => $list, 'mintimeDate' => $mintimeDate, 'maxtimeDate' => $maxtimeDate]);
    }

    /**
     * [roomadmin 个人中心房间管理员管理]
     *
     * @author  dc <dc#wisdominfo.my>
     * @version 2015-11-17
     * @return  JsonResponse
     */
    public function roomadmin()
    {
        $rid = Auth::id();
        $request = $this->make('request');

        //删除操作
        if ($request->getMethod() == 'POST' && $request->get('type') == 'delete') {
            $uid = $request->get('uid');
            //管理员软删除操作
            RoomAdmin::where('rid', $rid)->where('uid', $uid)->update(['dml_flag' => 3]);
            //删除redis管理员记录
            $this->make('redis')->srem('video:manage:' . $rid, $uid);
            return new JsonResponse(['status' => 1, 'message' => '删除成功!']);
        }

        //管理员数据列表
        $v['roomadmin'] = RoomAdmin::where('rid', $rid)->where('dml_flag', '!=', 3)->with('user')->paginate(30);
        return $this->render('Member/roomadmin', $v);
    }


    /**
     * 用户中心 贵族体系
     */
    public function vip()
    {

        /**
         * 获取开通过的日志 最新一条就是当前
         */
        $log = [];
        // 如果用户还是贵族状态的话  就判断充值的情况用于保级
        $startTime = strtotime($this->userInfo['vip_end']) - 30 * 24 * 60 * 60;
        if ($this->userInfo['vip']) {
            $group = LevelRich::where('level_id', $this->userInfo['vip'])->first();
            if (!$group) {
                return true;// 用户组都不在了没保级了
            }

            $userGid = $group->gid;

            //$log = UserBuyGroup::with('group')->where('uid', Auth::id())
            //dc修改增加status字段筛选
            $log = UserBuyGroup::with('group')->where('uid', Auth::id())->where('status', 1)
                ->where('gid', $userGid)->orderBy('end_time', 'desc')->first();

            // 获取最近一个月充值的总额
            if ($startTime < time()) {
                //如果还没保级成功的话
                $charge = Recharge::where('uid', Auth::id())
                    ->where('created', '>=', date('Y-m-d H:i:s', $startTime))
                    ->where('pay_status', 2)->where(function ($query) {
                        //$query->orWhere('pay_type', 1)->orWhere('pay_type', 4);
                        //@author dc 修改，增加转帐保级统计
                        $query->orWhere('pay_type', 1)->orWhere('pay_type', 4)->orWhere('pay_type', 7);
                    })->sum('points');
            } else {
                // 如果已经保级过了，就应该再往前减一个月才是当前月充值的
                $charge = Recharge::where('uid', Auth::id())
                    ->where('created', '>=', date('Y-m-d H:i:s', $startTime - 30 * 24 * 60 * 60))
                    ->where('pay_status', 2)->where(function ($query) {
                        //$query->orWhere('pay_type', 1)->orWhere('pay_type', 4);
                        //@author dc 修改，增加转帐保级统计
                        $query->orWhere('pay_type', 1)->orWhere('pay_type', 4)->orWhere('pay_type', 7);
                    })
                    ->sum('points');
            }
            //dc修改容错处理
            if ($log) $log->charge = $charge;
        }
        $data = [];
        $data['item'] = $log;

        return $this->render('Member/vip', $data);
    }

    /**
     * 用户中心 主播佣金统计
     * @author raby
     * @date   2019-9-29
     */
    public function commission(Request $request)
    {
        $type = 'open_vip';
        $uid = Auth::id();
        $mintimeDate = $request->get('mintime') ?: date('Y-m-d', strtotime('-1 year'));
        $maxtimeDate = $request->get('maxtime') ?: date('Y-m-d');
        $mintime = date('Y-m-d H:i:s', strtotime($mintimeDate));
        $maxtime = date('Y-m-d H:i:s', strtotime($maxtimeDate . '23:59:59'));

        $all_data = UserCommission::where('uid', $uid)
            ->where('create_at', '>', $mintime)->where('create_at', '<', $maxtime)->where('type', $type)
            ->where('dml_flag', '!=', 3)
            ->orderBy('create_at', 'desc')->with('user')->with('userGroup')->paginate(10);

        $total = UserCommission::selectRaw('sum(points) as points')
            ->where('uid', $uid)
            ->where('create_at', '>', $mintime)->where('create_at', '<', $maxtime)->where('type', $type)
            ->where('dml_flag', '!=', 3)
            ->first();

        $total_points = ceil($total->points / 10);
        $all_data->appends(['mintime' => $mintimeDate, 'maxtime' => $maxtimeDate]);

        return JsonResponse::create([
            'status' => 1,
            'data' => [
                'list' => $all_data,
                'total_points' => $total_points,
            ],
        ]);
    }

    /**
     * 用户中心 邀请推广
     * @author D.C
     * @update 2014.12.12
     * @return Response
     */
    public function invite()
    {
        //获取用户ID
        $uid = Auth::id();

        //组装推广网址
        $userUrl = rtrim($this->make('request')->getSchemeAndHttpHost(), '/') . '/?u=' . $uid;

        //构建短网址
        //$googleURL = $this->_buildGoogleShortUrl($userUrl);
        $short_url = $this->_buildWeiboShortUrl($userUrl);
        $inviteUrl = $short_url ?: $userUrl;

        return $this->render('Member/invite', ['inviteurl' => $inviteUrl]);
    }

    /**
     * 通过新浪微薄平台生成短网址
     * @param $url
     * @return bool|string
     * @author  D.C
     * @update  2015-02-05
     * @version 1.0
     * @todo    目前由于该api-key身份验证问题，新浪有一定的流量限制，无法保证所有用户都能生成短网址
     */
    private function _buildWeiboShortUrl($url)
    {
        if (!$url) return false;
        $api = 'http://api.t.sina.com.cn/short_url/shorten.json';
        $key = '942825741';
        $api_url = sprintf($api . "?source=%s&url_long=%s", $key, $url);

        $uid = Auth::id();
        $redis = $this->make('redis');
        if ($redis->hexists('user_url_t', $uid)) {
            return $redis->hget('user_url_t', $uid);
        } else {

            $result = @file_get_contents($api_url);
            if (!is_null(json_decode($result))) {
                $result = json_decode($result);
                if (isset($result[0]->url_short)) {
                    $url_short = trim($result[0]->url_short);
                    if (stristr($url_short, 't.cn')) {
                        $redis->hset('user_url_t', $uid, $url_short);
                        return $url_short;
                    } else {
                        return false;
                    }

                } else {
                    return false;
                }

            } else {
                return false;
            }


        }
    }

    /**
     * 用户中心 我的关注接口
     * @Author Nicholas
     * @return Response
     */
    public function attention(Request $request)
    {
        $page = $request->get('page', 1);
        $userServer = resolve(UserService::class);
        $data = $userServer->getUserAttens(Auth::id(), $page)
            ->setPath($request->getPathInfo());
        return JsonResponse::create($data);
    }

    /**
     * 用户中心 我的道具
     */
    public function scene(Request $request)
    {
        $uid = Auth::id();
        //道具装备操作
        if ($request->get('handle') == 'equip') {
            $handle = $this->_getEquipHandle($request->get('gid'));
            if (is_array($handle)) {
                return new JsonResponse($handle);
            }
            return JsonResponse::create(['status' => 0, 'messages' => '操作出现未知错误！']);

        }
        $data = Pack::with('mountGroup')->where('uid', $uid)->paginate();
        //判断是否过期
        $equip = Redis::hgetall('user_car:' . $uid);
        if ($equip != null && current($equip) < time()) {
            Redis::del('user_car:' . $uid);//检查过期直接删除对应的缓存key
        }
        return JsonResponse::create([
            'status' => 1,
            'data' => $data,
            'equip' => $equip,
        ]);
    }

    /**
     * 装备操作逻辑处理
     * @param $gid
     * @return array|bool
     * @author D.C
     * @update 2014.12.10
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
            return ['status' => 0, 'msg' => '对不起！该道具限房间内使用,不能装备！'];
        }

        /**
         * 使用Redis进行装备道具
         * @todo   目前道具道备只在Redis上实现，并未进行永久化存储。目前产品部【Antony】表示保持现状。
         * @update 2014.12.15 14:35 pm (Antony要求将道具改为同时只能装备一个道具！)
         */
        $redis = resolve('redis');
        $redis->del('user_car:' . $uid);
        $redis->hset('user_car:' . $uid, $gid, $pack['expires']);
        return ['status' => 1, 'msg' => '装备成功'];
    }

    /**
     * 用户中心 个人充值列表
     *
     * @author cannyco<cannyco@weststarinc.co>
     * @update 2015.01.30
     */
    public function charge()
    {
        //获取用户ID
        $uid = Auth::id();
        //获取下用户信息
        $chargelist = Recharge::where('uid', $uid)->where('del', 0)
            ->orderBy('id', 'DESC')->paginate();
        return JsonResponse::create(['status' => 1, 'msg' => $chargelist]);
    }

    /**
     * 用户中心 消费记录
     */
    public function consume()
    {
        $uid = Auth::id();
        $data = MallList::with('goods')->where('send_uid', $uid)
            ->where('gid', '>', 0)
            ->orderBy('created', 'DESC')->paginate();
        return JsonResponse::create([
            'status' => 1,
            'data' => $data,
        ]);
    }

    /**
     * 用户中心 密码管理
     */
    public function passwordChange(Request $request)
    {
        $uid = Auth::id();
        $post = $request->all();
        if (!Captcha::check(Input::get('captcha'))) {
            return JsonResponse::create(['status' => 0, 'msg' => '对不起，验证码错误!']);
        }

        if (empty($post['password'])) {
            return JsonResponse::create(['status' => 0, 'msg' => '原始密码不能为空！']);
        }


        if (strlen($post['password1']) < 6 || strlen($post['password2']) < 6) {
            return JsonResponse::create(['status' => 0, 'msg' => '请输入大于或等于6位字符串长度']);
        }

        if ($post['password1'] != $post['password2']) {
            return JsonResponse::create(['status' => 0, 'msg' => '新密码两次输入不一致!']);
        }

        $old_password = Redis::hget('huser_info:' . $uid, 'password');
        $new_password = md5($post['password2']);
        if (md5($post['password']) != $old_password) {
            return JsonResponse::create(['status' => 0, 'msg' => '原始密码错误!']);
        }
        if ($old_password == $new_password) {
            return JsonResponse::create(['status' => 0, 'msg' => '新密码和原密码相同']);
        }

        $user = Users::find($uid);
        $user->password = $new_password;
        if (!$user->save()) {
            return JsonResponse::create(['status' => 304, 'msg' => '修改失败!']);
        }
        resolve(UserService::class)->getUserReset($uid);
        Auth::logout();
        return new JsonResponse(['status' => 0, 'msg' => '修改成功!请重新登录']);
    }

    /**
     *用户中心  房间设置  TODO  尼玛
     * @author TX
     * @update 2015.4.16
     * @return Response
     */
    public function roomset()
    {
        //$type = $this->request()->get('type');
        $page1 = $this->request()->input('page1', 1);
        $page2 = $this->request()->input('page2', 1);
        $tab = $this->request()->input('tab', 'one2one');
        $status = [];
        for ($i = 1; $i <= 7; $i++) {//获取房间状态，从2到7的状态
            $ROOM = $this->getRoomStatus(Auth::id(), $i);//获取对应的房间权限状态
            if (!empty($ROOM)) {
                array_push($status, $ROOM);
            }
        }
        $result['types'] = $status;
        $result['tab'] = $tab;
        Paginator::currentPageResolver(function () use ($page2) {
            return $page2;
        });
        $result['roomlistOneToMore'] = RoomOneToMore::where('uid', Auth::id())->where('status', 0)
            ->orderBy('starttime', 'DESC')
            ->paginate(10)->appends(['tab' => 'one2many', 'page1' => $page1])->setPageName('page2')->setPath('');
        Paginator::currentPageResolver(function () use ($page1) {
            return $page1;
        });
        $result['roomlistOneToOne'] = RoomDuration::where('uid', Auth::id())->where('status', 0)
            ->orderBy('starttime', 'DESC')
            ->paginate(10)->appends(['tab' => 'one2one', 'page2' => $page2])->setPageName('page1')->setPath('');
        for ($i = 0; $i < 25; $i++) {//生成前端的小时下拉框
            if ($i < 10) $result['hoption'][$i]['option'] = '0' . $i;
            else $result['hoption'][$i]['option'] = $i;
        }
        for ($i = 0; $i < 12; $i++) {//生成前端分钟的下拉框,每五分钟一次
            if ($i < 2) $result['ioption'][$i]['option'] = '0' . ($i * 5);
            else $result['ioption'][$i]['option'] = $i * 5;
        }

        //时长房间
        $roomStatus = $this->getRoomStatus(Auth::id(), 6);
        $result['roomStatus'] = $roomStatus;

        return new JsonResponse($result);

    }

    /**
     * @description 获取房间权限
     * @author      TX
     * @date        2015.4.20
     */
    public function getRoomStatus($uid, $tid)
    {
        $hasname = 'hroom_status:' . $uid . ':' . $tid;
        $status = $this->make('redis')->hget($hasname, 'status');
        if (!empty($status)) {
            if ($status == 1) {
                $data = $this->make('redis')->hgetall($hasname);
            } else {
                return null;
            }
        } else {
//            $datas =  $this->getDoctrine()->getRepository('Video\ProjectBundle\Entity\VideoRoomStatus')->createQueryBuilder('r')
//                ->where('r.uid='.$uid.'  and  r.tid='.$tid.' and r.status = 1')
//                ->orderby('r.id','ASC')
//                ->getQuery();
//            $roomdata = $datas->getResult();
            $data = RoomStatus::where('uid', $uid)
                ->where('tid', $tid)->where('status', 1)
                ->orderBy('id', 'ASC')->first();
            /**
             * dc修改，有数据时再转换数组
             */
            $data = $data ? $data->toArray() : $data;
            /*
             * 因上面$roomdata被注释,改用eloquent查询方式.忘记注释判断,导致房间获取失败
             * 现添加注释
             * @author dc
             * @version 20160407
             * */
            if (empty($roomdata)) {
                return null;
            }

            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $this->make('redis')->hset('hroom_status:' . $uid . ':' . $tid, $key, $value);
                }
            }
        }

        return $data;
    }

    /**
     * 一对多设置
     * @return JsonResponse
     */

    /**
     * 一对多记录详情-购买用户
     * @return JsonResponse
     */
    public function getBuyOneToMore()
    {
        $onetomore = $this->make('request')->get('onetomore');
        $userService = resolve(UserService::class);
        $buyOneToMore = UserBuyOneToMore::where('onetomore', $onetomore)->where('type', 2)->get();
        $buyOneToMore->map(function (&$item) use ($userService) {
            $user = $userService->getUserByUid($item->uid);
            $item->nickname = isset($user['nickname']) ? $user['nickname'] : '';
        });
        return new JsonResponse(['code' => 1, 'msg' => $buyOneToMore]);
    }

    /**
     * 开发：
     * 1.代码增加来源----------------无需处理
     * 2.代码修改hroom_whitelist_key------添加，删除ok
     * 3.主播设置一对多金额--------前端处理(待处理)，后台功能ok
     *
     * todo 增加默认值---ok
     *
     *
     * @return JsonResponse
     */
    public function roomOneToMore(Request $request)
    {
        $data = [];
        $data = $request->only(['mintime', 'hour', 'minute', 'tid', 'duration', 'points', 'origin']);

        $data['uid'] = Auth::guard()->id();
        //  var_dump($data);exit;
        $roomservice = resolve(RoomService::class);
        $result = $roomservice->addOnetomore($data);
        return new JsonResponse($result);

    }

    /**
     * 时长房间金额设置
     * @author raby
     * @create 2016.9.16
     */
    public function roomSetTimecost()
    {
        return JsonResponse::create(['code' => 0, 'msg' => '非法操作']);//禁止用户修改
        $timecost = $this->make('request')->get('timecost');
        if ($timecost <= 0 || $timecost > 999999) return new JsonResponse(['code' => "301", 'msg' => '金额设置有误']);

        //todo 时长房直播，并且开启时，不处理
        $timecost_status = $this->make("redis")->hget("htimecost:" . Auth::id(), "timecost_status");
        if ($timecost_status == 1) return new JsonResponse(['code' => "302", 'msg' => '时长房直播中,不能设置']);

        RoomStatus::where("uid", Auth::id())->where("tid", 6)->where("status", 1)->update(['timecost' => $timecost]);

        $this->make("redis")->hset("hroom_status:" . Auth::id() . ":6", "timecost", $timecost);
        return new JsonResponse(['code' => 1, 'msg' => '设置成功']);
    }

    /**
     *含时长房间设置  TODO 优化。。。。
     * @author TX
     * @update 2015.4.27
     * @return Response
     */
    public function roomSetDuration(Request $request)
    {

        $data = [];
        $data = $request->only(['mintime', 'hour', 'minute', 'tid', 'duration']);

        if (!in_array($data['duration'], [25, 55])) return new JsonResponse(['status' => 0, 'msg' => '请求错误']);
        // 判断是否为手动输入 如果手动输入需要大于1万才行
        $select_points = $request->get('select-points');
        $input_points = $request->get('input-points');
        if (empty($select_points) && $input_points < 10000) {
            return new JsonResponse(['status' => 0, 'msg' => '手动设置的钻石数必须大于1万']);
        } else {
            $points = $input_points;
        }
        if (!empty($select_points)) {
            $points = $select_points;
        }
        $data['points'] = $points;

        $roomservice = resolve(RoomService::class);
        $result = $roomservice->addOnetoOne($data);
        return new JsonResponse($result);


    }

    /**
     *房间密码设置
     * @author TX
     * @update 2015.4.16
     * @return Response
     */
    public function roomSetPwd()
    {
        $room_radio = $this->make('request')->get('room-radio');
        $password = '';
        if ($room_radio == 'true') $password = $this->make('request')->get('password');
        if (empty($password) && $room_radio == 'true') {
            return new JsonResponse(['code' => 2, 'msg' => '密码不能为空']);
        }
        if ($room_radio == 'true')//判断密码格式,密码格式和用户注册的密码格式是一样的
            if ($room_radio == 'true' && strlen($password) < 6 || strlen($password) > 22 || !preg_match('/^\w{6,22}$/', $password)) {
                return new JsonResponse(['code' => 3, 'msg' => '密码格式不对']);
            }
//        $this->getRedis();
        $this->make('redis')->hset('hroom_status:' . Auth::id() . ':2', 'pwd', $password);
//        $em = $this->getDoctrine()->getManager();
//        $roomtype =  $em->getRepository('Video\ProjectBundle\Entity\VideoRoomStatus')->findOneBy(array('uid'=>$this->_uid,'tid'=>2));
//        $roomtype->setPwd($password);
//        $em->persist($roomtype);
//        $em->flush();
        $roomtype = RoomStatus::where('uid', Auth::id())->where('tid', 2)->update(['pwd' => $password]);
        if ($room_radio == 'false') return new JsonResponse(['code' => 1, 'msg' => '密码关闭成功']);
        return new JsonResponse(['code' => 1, 'msg' => '密码修改成功']);
    }

    /**
     *房间密码验证
     * @author TX
     * updata 2015.4.16
     * @return Response
     */
    public function checkroompwd()
    {
        $password = $this->request()->get('password');
        $rid = $this->request()->get('roomid');
        $type = $this->getAnchorRoomType($rid);
        if ($type != 2) return new JsonResponse(['code' => 0, 'msg' => '密码房异常,请联系运营重新开启一下密码房间的开关']);
        if (empty($rid)) return new JsonResponse(['code' => 0, 'msg' => '房间号错误!']);
        if (empty($password)) {
            return $this->geterrorsAction();
        }
//        $this->get('session')->start();
        $sessionid = $this->request()->getSession()->getId();
        //房间进入密码，超过五次就要输入验证码，这个五次是通过phpsessionid来判断的
        $roomstatus = $this->getRoomStatus($rid, 2);
        $keys_room = 'keys_room_passwd:' . $sessionid . ':' . $rid;
        $times = $this->make('redis')->get($keys_room);
        if ($times >= 5) {
            $captcha = $this->request()->get('captcha');
            if (empty($captcha)) {
                return new JsonResponse(['code' => 4, 'msg' => '请输入验证码!', 'times' => $times]);
            }
            if (!Captcha::check($captcha)) return new JsonResponse(['code' => 0, 'msg' => '验证码错误!', 'times' => $times]);
        }
        if (strlen($password) < 6 || strlen($password) > 22 || !preg_match('/^\w{6,22}$/', $password)) {
            $this->make('redis')->set($keys_room, $times + 1);
            $this->make('redis')->expire($keys_room, 3600);
            return new JsonResponse([
                "code" => 0,
                "msg" => "密码格式错误!",
                'times' => $times + 1,
            ]);
        }
        if ($password != $roomstatus['pwd']) {
            if (empty($times)) {
                $this->make('redis')->set($keys_room, 1);
                $this->make('redis')->expire($keys_room, 3600);
            } else {
                $this->make('redis')->set($keys_room, $times + 1);
                $this->make('redis')->expire($keys_room, 3600);
            }
            return new JsonResponse([
                "code" => 0,
                "msg" => "密码错误!",
                'times' => $times + 1,
            ]);
        }
        $this->make('redis')->hset('keys_room_passwd:' . $rid . ':' . $sessionid, 'status', 1);
        return new JsonResponse(['code' => 1, 'msg' => '登陆成功']);
    }

    /**
     *房间密码错误次数请求
     * @author TX
     * updata 2015.4.16
     * @return Response
     */
    public function geterrorsAction()
    {
        $rid = $this->request()->get('roomid');
        if (empty($rid)) return new JsonResponse(['code' => 2, 'msg' => '房间号错误!']);
//        $this->get('session')->start();
        $session_name = $this->request()->getSession()->getName();
        if (isset($_POST[$session_name])) {
            $this->request()->getSession()->setId($_POST[$session_name]);
        }
        $sessionid = $this->request()->getSession()->getId();
        $keys_room = 'keys_room_errorpasswd:' . $sessionid . ':' . $rid;
        $times = $this->make('redis')->hget($keys_room, 'times');
        if (empty($times)) $times = 0;
        return new JsonResponse(['code' => 1, 'times' => $times]);
    }

    /**
     *用户中心 我的预约
     */
    public function reservation(Request $request)
    {
        $type = $request->get('type', 1);
        $rooms = ['status' => 1];
        $rooms['type'] = $type;
        $rooms['data'] = [];
        $userServer = resolve(UserService::class);
        switch ($type) {
            case 2:
                $data = UserBuyOneToMore::where('uid', Auth::id())->orderBy('starttime', 'DESC')->paginate();
                break;
            case 1:
            default:
                $data = RoomDuration::where('reuid', Auth::id())
                    ->where('starttime', '>', time() . '-duration')
                    ->orderBy('starttime', 'DESC')
                    ->paginate();;
        }
        $items = $data->getCollection();
        foreach ($items as &$item) {
            $rid = $type == 2 ? $item->rid : $item->uid;
            $userinfo = $userServer->getUserByUid($rid);
            $item['nickname'] = $userinfo['nickname'];
            $item['starttime'] = date('Y-m-d H:i:s', strtotime($item['starttime']));
            $item['endTime'] = date('Y-m-d H:i:s', strtotime($item->starttime) + ($item->duration));//date('Y-m-d H:i:s',strtotime($time1)+30*60);//注意引号内的大小写,分钟是i不是m
            $item['duration'] = ceil($item->duration / 60);
            $item['points'] = $item->points;
            $item['uid'] = $userinfo['uid'];
            $item['now'] = date('Y-m-d H:i:s');
            $item['url'] = '/' . $userinfo['uid'];
        }
        $thispage = $this->make("request")->get('page') ?: 1;
        //我的预约推荐是从redis中取数据的，先全部取出数据，做排序
        $roomlists = $this->getReservation(Auth::id());
        $roomlist = array_slice($roomlists, ($thispage - 1) * 6, 6);
        $Count = count($roomlists);
        $rooms['pagination'] = [
            'page' => $thispage,
            'count' => $Count,
            'pages' => ceil($Count / 6),
        ];
        $rooms['room'] = [];

        $redis = $this->make('redis');
        foreach ($roomlist as $keys => $value) {
            $userinfo = $userServer->getUserByUid($value['uid']);
            $merge = array_merge($value, $userinfo);
            $merge['duration'] = ceil($merge['duration'] / 60);
            $rooms['room'][$keys] = $merge;
            $rooms['room'][$keys]['datenu'] = date('YmdHis', strtotime($value['starttime']));
            $rooms['room'][$keys]['points'] = $value['points'];
            $rooms['room'][$keys]['roomid'] = $value['id'];
            $rooms['room'][$keys]['headimg'] = $userinfo['headimg'];
            $cover = $redis->get('shower:cover:version:' . $userinfo['uid']);
            $image_server = SiteSer::config('img_host') . '/';
            $rooms['room'][$keys]['cover'] = $cover ? $image_server . $cover : false;
        }
        $rooms['uri'] = [];
        $uriParammeters = $request->all();
        foreach ($uriParammeters as $p => $v) {
            if (strstr($p, '?')) continue;
            if (!empty($v)) {
                $rooms['uri'][$p] = $v;
            }
        }
        return JsonResponse::create($rooms);
    }

    /**
     * 获取我的预约推荐列表TODO
     *
     * @param $uid
     * @return array
     * @Author TX
     */
    public function getReservation($uid)
    {
        $uids = $this->getRoomUid($uid);
        $rooms = [];
        $user_key = [];
        array_push($user_key, 'hroom_duration:' . $uid . ':4');
        $key = 'hroom_duration:*';
//        $keys = array();
        $keys = $this->make('redis')->getKeys($key);
        if ($keys == false) {
            $keys = [];
        }

        $room_reservation = [];
        foreach ($uids['reservation'] as $value) {
            array_push($user_key, 'hroom_duration:' . $value . ':4');
            $roomlist = $this->make('redis')->hGetAll('hroom_duration:' . $value . ':4');
            foreach ($roomlist as $item) {
                $room = json_decode($item, true);
                if ($room['status'] == 0 && $room['reuid'] == 0 && $room['uid'] != $uid && strtotime($room['starttime']) > time()) {
                    array_push($room_reservation, $room);
                }
            }
            foreach ($room_reservation as $key => $row) {
                $edition[$key] = $row['starttime'];
            }
            if (count($room_reservation) > 0) array_multisort($edition, SORT_ASC, $room_reservation);
        }
        $room_attens = [];
        foreach ($uids['attens'] as $value) {
            array_push($user_key, 'hroom_duration:' . $value . ':4');
            $roomlist = $this->make('redis')->hGetAll('hroom_duration:' . $value . ':4');
            foreach ($roomlist as $item) {
                $room = json_decode($item, true);
                if ($room['status'] == 0 && $room['reuid'] == 0 && $room['uid'] != $uid && strtotime($room['starttime']) > time()) {
                    array_push($room_attens, $room);
                }
            }
            foreach ($room_attens as $key => $row) {
                $edition_attens[$key] = $row['starttime'];
            }
            if (count($room_attens) > 0) array_multisort($edition_attens, SORT_ASC, $room_attens);
        }
        $keys = array_diff($keys, $user_key);
        $room_list = [];
        foreach ($keys as $value) {
            array_push($user_key, $value);
            $roomlist = $this->make('redis')->hGetAll($value);
            foreach ($roomlist as $item) {
                $room = json_decode($item, true);
                if ($room['status'] == 0 && $room['reuid'] == 0 && $room['uid'] != $uid && strtotime($room['starttime']) > time()) {
                    array_push($room_list, $room);
                }
            }
            foreach ($room_list as $key => $row) {
                $edition_list[$key] = $row['starttime'];
            }
            if (count($room_list) > 0) array_multisort($edition_list, SORT_ASC, $room_list);
        }
        foreach ($room_reservation as $value) {
            array_push($rooms, $value);
        }
        foreach ($room_attens as $value) {
            array_push($rooms, $value);
        }
        foreach ($room_list as $value) {
            array_push($rooms, $value);
        }
        return $rooms;
    }

    /**
     *立即预约 TODO 优化优化再优化 要重写
     * @author TX
     * @update 2015.4.27
     * @return Response
     */
    public function doReservation()
    {
        $roomid = $this->request()->get('rid');
        $flag = $this->request()->get('flag');
        if (empty($roomid) || empty($flag)) {
            return new JsonResponse(['status' => 0, 'msg' => '请求错误']);
        }

        $duroom = RoomDuration::find($roomid);
        if (empty($duroom)) return new JsonResponse(['status' => 0, 'msg' => '请求错误']);
        if (empty($duroom)) return new JsonResponse(['status' => 0, 'msg' => '您预约的房间不存在']);
        if ($duroom['status'] == 1) return new JsonResponse(['status' => 0, 'msg' => '当前的房间已经下线了，请选择其他房间。']);
        if ($duroom['reuid'] != '0') return new JsonResponse(['status' => 0, 'msg' => '当前的房间已经被预定了，请选择其他房间。']);
        if ($duroom['uid'] == Auth::id()) return new JsonResponse(['status' => 0, 'msg' => '自己不能预约自己的房间']);
        if (Auth::user()->points < $duroom['points']) return new JsonResponse(['status' => 0, 'msg' => '余额不足哦，请充值！']);
        //关键点，这个时段内有没有其他的房间重复，标志位为flag 默认值为false 当用户确认后传入的值为true
        if (!$this->checkRoomUnique($duroom, Auth::id()) && $flag == 'false') {
            return new JsonResponse(['status' => 0, 'msg' => '您这个时间段有房间预约了，您确定要预约么']);
        }
        $duroom['reuid'] = Auth::id();
        $duroom->save();
        $this->set_durationredis($duroom);
        //记录一个标志位，在我的预约列表查询中需要优先显示查询已经预约过的主播，已经预约过的主播的ID会写到这个redis中类似关注一样的
        if (!($this->checkUserAttensExists(Auth::id(), $duroom['uid'], true, true))) {
            $this->make('redis')->zadd('zuser_reservation:' . Auth::id(), time(), $duroom['uid']);
        }
        Users::where('uid', Auth::id())->update(['points' => (Auth::user()->points - $duroom['points']), 'rich' => (Auth::user()->rich + $duroom['points'])]);
        resolve(UserService::class)->getUserReset(Auth::id());// 更新redis TODO 好屌
        RoomDuration::where('id', $duroom['id'])
            ->update(['reuid' => Auth::id(), 'invitetime' => time()]);
        //增加消费记录查询
        MallList::create([
            'send_uid' => Auth::id(),
            'rec_uid' => $duroom['uid'],
            'gid' => 410001,
            //$duroom['roomtid'],irwin
            'gnum' => 1,
            'created' => date('Y-m-d H:i:s'),
            'rid' => $duroom['uid'],
            'points' => $duroom['points'],
        ]);
        // 用户增加预约排行榜的排名
        $this->make('redis')->zIncrBy('zrank_appoint_month' . date('Ym'), 1, $duroom['uid']);
        //修改用户日，周，月排行榜数据
        //zrank_rich_history: 用户历史消费    zrank_rich_week ：用户周消费   zrank_rich_day ：用户日消费  zrank_rich_month ：用户月消费
        $expire_day = strtotime(date('Y-m-d 00:00:00', strtotime('next day'))) - time();
        $expire_week = strtotime(date('Y-m-d 00:00:00', strtotime('next week'))) - time();
        $zrank_user = ['zrank_rich_history', 'zrank_rich_week', 'zrank_rich_day', 'zrank_rich_month:' . date('Ym')];
        foreach ($zrank_user as $value) {
            $this->make('redis')->zIncrBy($value, $duroom['points'], Auth::id());
            if ('zrank_rich_day' == $value) {
                $this->make('redis')->expire('zrank_rich_day', $expire_day);
            }
            if ('zrank_rich_week' == $value) {
                $this->make('redis')->expire('zrank_rich_week', $expire_week);
            }
        }
        //修改主播日，周，月排行榜数据
        //zrank_pop_history ：主播历史消费   zrank_pop_month  ：主播周消费 zrank_pop_week ：主播日消费 zrank_pop_day ：主播月消费
        $zrank_pop = ['zrank_pop_history', 'zrank_pop_month:' . date('Ym'), 'zrank_pop_week', 'zrank_pop_day'];
        foreach ($zrank_pop as $value) {
            $this->make('redis')->zIncrBy($value, $duroom['points'], $duroom['uid']);
            if ('zrank_pop_day' == $value) {
                $this->make('redis')->expire('zrank_pop_day', $expire_day);
            }
            if ('zrank_pop_week' == $value) {
                $this->make('redis')->expire('zrank_pop_week', $expire_week);
            }
        }
        $this->make('redis')->lPush('lanchor_is_sub:' . $duroom['uid'], date('YmdHis', strtotime($duroom['starttime'])));
        Log::channel('room')->info('buyOneToOne',$duroom->toArray());

        return new JsonResponse(['code' => 1, 'msg' => '预约成功']);
    }

    /**
     * 发送私信
     * @return string
     */
    public function domsg()
    {
        // $fid = $this->get('request')->get('fid');
        $tid = $this->make('request')->get('tid');
        if (!Users::find($tid)) {
            return new Response(json_encode([
                'ret' => false,
                'info' => '接受者用户不存在！',
            ]));
        }
        if (Auth::id() == $tid) {
            return new Response(json_encode([
                'ret' => false,
                'info' => '不能给自己发私信！',
            ]));
        }
        $content = $this->make('request')->get('content');
        $len = $this->count_chinese_utf8($content);
        if ($len < 0 || $len > 200) {
            return new Response(json_encode([
                'ret' => false,
                'info' => '输入为空或者输入内容过长，字符长度请限制200以内！',
            ]));
        }
        $userInfo = $this->userInfo;
        if ($userInfo['roled'] == 0 && $userInfo['lv_rich'] < 3) {
            return new Response(json_encode([
                'ret' => false,
                'info' => '财富等级达到二富才能发送私信哦，请先去给心爱的主播送礼物提升财富等级吧。',
            ]));
        }

        $num = $this->checkAstrictUidDay(Auth::id(), 1000, 'video_mail');//验证每天发帖次数
        if ($num == 0) {
            return new Response(json_encode([
                'ret' => false,
                'info' => '本日发送私信数量已达上限，请明天再试！',
            ]));
        }

        $message = new Messages();
        $res = $message->create([
            'send_uid' => Auth::id(),
            'rec_uid' => $tid,
            'content' => htmlentities($content),
            'category' => 2,
            'status' => 0,
            'created' => date('Y-m-d H:i:s'),
        ]);
        if ($res) {
            $this->setAstrictUidDay(Auth::id(), $num, 'video_mail');//更新每天发帖次数
            return new Response(json_encode([
                'ret' => true,
                'info' => '私信发送成功！',
            ]));
        } else {
            return new Response(json_encode([
                'ret' => false,
                'info' => '私信发送失败！',
            ]));
        }
    }

    /**
     * 用户中心 提现页面
     */
    public function withdrawHistory(Request $request)
    {
        $user = Auth::user();
        if ($user['roled'] != 3) {
            abort(404);
        }
        $mintime = $request->get('mintime') ?: date('Y-m-d', strtotime('-1 month'));
        $maxtime = $request->get('maxtime') ?: date('Y-m-d', strtotime('now'));
        $status = $request->get('status') ?: 0;
        $availableBalance = $this->getAvailableBalance(Auth::id());

        $maxtime = date('Y-m-d' . ' 23:59:59', strtotime($maxtime));

//        $thispage = $this->make('request')->get('page') ?: 1;
        $data = WithDrawalList::where('uid', Auth::id())->where('created', '<', $maxtime)->where('created', '>', $mintime)
            ->where('status', $status)
            ->orderBy('created', 'DESC')
            ->paginate();

        $status_array = [
            '0' => '审批中',
            '1' => '已审批',
            '2' => '拒绝',
        ];
        $data->getCollection()->each(function ($item) use ($status_array) {
            $item['status'] = $status_array[$item->status];
        });

        return JsonResponse::create(['status' => 1, 'data' => ['list' => $data, 'available_balance' => $availableBalance]]);
    }

    /**
     *  提现申请
     */
    public function withdraw(Request $request)
    {
        $money = $request->get('withdrawnum', 0);
        if (empty($money) || $money < 200) {
            return new JsonResponse(['status' => 0, 'msg' => '每次提现不能少于200！']);
        }
        $uid = Auth::id();
        $avila_points = $this->getAvailableBalance($uid);
        if ($money > $avila_points['availmoney']) {
            return new JsonResponse(['status' => 0, 'msg' => '提现金额不能大于可用余额！']);
        }
        $wd = date('ymdhis') . substr(microtime(), 2, 4);
        $withrawal = new WithDrawalList();
        $withrawal->uid = $uid;
        $withrawal->created = date('Y-m-d H:i:s');
        $withrawal->money = $money;
        $withrawal->moneypoints = $this->BalanceToOponts($money, $uid);
        $withrawal->withdrawalnu = $wd;
        $withrawal->status = 0;//0表示审批中
        $withrawal->save();
        return new JsonResponse(['status' => 1, 'msg' => '申请成功！请等待审核']);
    }

    /**
     * 用户中心 主播中心
     */
    public function anchor()
    {
        $user = $this->userInfo;
        if (!$user['uid'] || $user['roled'] != 3) {
            throw $this->createAccessDeniedException();
        }

        //更新相册
        if (in_array($this->make('request')->get('handle'), ['del', 'get', 'set'])) {
            $id = sprintf("%u", $this->make('request')->get('id'));
            if (!$id) {
                return new Response(json_encode(['code' => 101, 'info' => '操作失败']));
            }
            $result = $this->_anchorHandle($this->make('request')->get('handle'), $id);
            if ($result) {
                $result = is_array($result) ? json_encode($result) : $result;
                return new Response(json_encode(['code' => 0, 'info' => '操作成功', 'data' => $result]));
            } else {
                return new Response(json_encode(['code' => 103, 'info' => '操作失败', 'data' => $result]));
            }
        }

        $data = Anchor::where('uid', Auth::id())->orderBy('jointime', 'DESC')
            ->paginate();
//        $result['IMGHOST'] = trim($this->container->getParameter('REMOTE_PIC_URL'), '/') . '/';
        $result['gallery'] = $data;
        $result['totals'] = count($result['gallery']);
        $result['userinfo'] = $user;
        $result['userinfo']['headimg'] = $this->getHeadimg($result['userinfo']['headimg'], 180);
        $result['sessid'] = session_id();
        return $this->render('Member/anchor', $result);
    }

    /**
     * @description 主播中心相册操作附加方法
     * @author      D.C
     * @param null $type
     * @param int  $id
     * @return array|bool
     * @update      2014.11.7
     */
    private function _anchorHandle($type = null, $id = 0)
    {
        if (!$type || !$id) return false;
        $anchor = Anchor::find($id);
        if (!$anchor) return false;
        $pic_used_size = $this->userInfo['pic_used_size'] - $anchor['size'];

        switch ($type) {
            case 'del':
                //将用户剩余图片空间同步更新数据库及redis
                Users::find(Auth::id())->update(['pic_used_size' => $pic_used_size]);
                $this->make('redis')->hMset('huser_info:' . Auth::id(), Users::find(Auth::id())->toArray());
                Anchor::find($id)->delete();
                break;

            case 'get':
                return Anchor::find($id)->toArray();
                break;

            case 'set':
                //更新图片名称与备注
                Anchor::find($id)->update(['name' => $this->make('request')->get('name'), 'summary' => $this->make('request')->get('summary')]);
                break;

            default:
                return false;
        }
        return true;
    }

    /**
     *用户中心 房间游戏
     */
    public function gamelist($type = 1)
    {
        $data = [];
        // 我参与的
        if ($type == 1) {
            $data = CarGameBetBak::with(['game' => function ($query) {
                $query->with('gameMasterUser');
            }])->with('gameRoomUser')
                ->where('uid', Auth::id())
                ->where('dml_flag', '!=', 3)
                ->orderBy('created', 'desc')
                ->paginate();
        }
        // 我做庄的
        if ($type == 2) {
            $data = CarGame::with('gameRoomUser')
                ->where('uid', Auth::id())
                ->where('dml_flag', '!=', 3)
                ->orderBy('stime', 'DESC')
                ->paginate();
        }
        return JsonResponse::create(['status' => 1, 'data' => $data]);
    }

    /**
     * 用户中心 礼物统计
     * @description 礼物统计 TODO 优化可能性
     * @author      D.C
     * @date        2015.2.6
     */
    public function count()
    {
        $uid = Auth::id();
        if (!$uid) {
            throw new NotFoundHttpException();
        }
        $type = $this->make('request')->get('type') ?: 'send';
        $mint = $this->make('request')->get('mintime') ?: date('Y-m-d', strtotime('-1 day'));
        $maxt = $this->make('request')->get('maxtime') ?: date('Y-m-d');

        $mintime = date('Y-m-d H:i:s', strtotime($mint));
        $maxtime = date('Y-m-d H:i:s', strtotime($maxt . '23:59:59'));

        $selectTypeName = $type == 'send' ? 'send_uid' : 'rec_uid';
        $uriParammeters = $this->make('request')->query->all();
        $var['uri'] = [];
        foreach ($uriParammeters as $p => $v) {
            if (strstr($p, '?')) continue;
            if (!empty($v)) {
                $var['uri'][$p] = $v;
            }
        }

        $all_data = MallList::where($selectTypeName, $uid)
            ->where('created', '>', $mintime)
            ->where('created', '<', $maxtime)
            ->where('gid', '>', 10)
            ->where('gid', '!=', 410001)
            ->paginate();

        $sum_Gift_mun = MallList::where($selectTypeName, $uid)
            ->where('created', '>', $mintime)
            ->where('created', '<', $maxtime)
            ->where('gid', '>', 10)
            ->where('gid', '!=', 410001)
            ->sum('gnum');
        $sum_Points_mun = MallList::where($selectTypeName, $uid)
            ->where('created', '>', $mintime)
            ->where('created', '<', $maxtime)
            ->where('gid', '!=', 410001)
            ->where('gid', '>', 10)
            ->sum('points');
        $sum_Gift_mun = $sum_Gift_mun ? $sum_Gift_mun : 0;
        $sum_Points_mun = $sum_Points_mun ? $sum_Points_mun : 0;
        $twig = clone $this->make('view');
        $twig->setLoader(new \Twig_Loader_String());

        $function = new \Twig_SimpleFunction('getUserName', function ($uid) {
            if (!$uid) return;
            $user = Users::find($uid);
            if ($user) {
                return $user['nickname'] ?: $user['username'];
            }
        });

        $twig->addFunction($function);

        $function = new \Twig_SimpleFunction('getGoods', function ($gid) {
            if (!$gid) return false;
            return $this->getGoods($gid);
        });
        $twig->addFunction($function);


        //todo author raby
        if ($type == "counttime") {
            $where_uid = ['send_uid' => Auth::id()];
            if ($this->userInfo['roled'] == 3) {
                $where_uid = ['rec_uid' => Auth::id()];
            }

            $timecost = (new TimecostMallList())->getList($where_uid, [$mintime, $maxtime]);
            $points_sum = $timecost->get()->sum("sum_points");
            //$uid_sum = $timecost->distinct('send_uid')->count('send_uid');
            $temp_uid_sum = $timecost->get()->toArray();
            $uid_sum = count(array_unique(array_column($temp_uid_sum, 'send_uid')));
            unset($temp_uid_sum);

            $all_data = $timecost->paginate();
            $all_data->appends(['type' => $type, 'mintime' => $mint, 'maxtime' => $maxt]);

            $sum_Gift_mun = $uid_sum;
            $sum_Points_mun = $points_sum;
            //$data['timecost_list'] = $live_list;
            //print_r($all_data); exit;
        }

        $all_data->appends(['type' => $type, 'mintime' => $mint, 'maxtime' => $maxt]);

        $var['type'] = $type;
        $var['data'] = $all_data;
        $var['mintime'] = $mint;
        $var['maxtime'] = $maxt;
        $var['sum_Gift_mun'] = $sum_Gift_mun;
        $var['sum_Points_mun'] = $sum_Points_mun;
        return $this->render('Member/count', $var);
    }

    /**
     * 用户中心 直播时间 TODO 尼玛
     *
     * @author TX
     * @date   2015.2.6
     */
    public function live()
    {

        $uid = Auth::id();

        /**
         * 查询的开始时间 用于获取数据
         * 默认是前一天到现在的
         */
        $start = $this->make('request')->get('start') ?: $this->_reqSession->get('live_start');
        if (!$start) {
            $start = date('Y-m-d', strtotime("-1 day"));
        } else {
            $this->_reqSession->set('live_start', $start);
        }

        $end = $this->make('request')->get('end') ?: $this->_reqSession->get('live_end');
        if (!$end) {
            $end = date('Y-m-d');
        } else {
            $this->_reqSession->set('live_end', $end);
        }
        $result = [];
        $result['end'] = $end;
        $result['start'] = $start;
        $end = date('Y-m-d' . ' 23:59:59', strtotime($end));
        $start = date('Y-m-d' . ' 00:00:00', strtotime($start));
        $result['user'] = $this->userInfo;
        $result['data'] = [];
        /**
         * 获取自播记录 运算时长
         */
        $iEnd = strtotime($end);
        $iStart = strtotime($start);
////        var_dump(['uid' => $uid, 'end' => $end, 'start' => $start, 'start2' => $start, 'iEnd' => $iEnd, 'iStart' => $iStart]);
////        $page = $this->make('request')->get('page')?$this->make('request')->get('page'):1;
//        $count = DB::select('select count(0) as c from video_live_list where uid=:uid and ((start_time < :end and start_time > :start) or (
//          start_time < :start2 and UNIX_TIMESTAMP(start_time)+duration < :iEnd and UNIX_TIMESTAMP(start_time)+duration > :iStart))
//          order by start_time desc', ['uid' => $uid, 'end' => $end, 'start' => $start, 'start2' => $start, 'iEnd' => $iEnd, 'iStart' => $iStart]);


        $data = (array)DB::select('SELECT * FROM video_live_list WHERE uid=:uid AND ((start_time < :end AND start_time > :start) OR (
          start_time < :start2 AND UNIX_TIMESTAMP(start_time)+duration < :iEnd AND UNIX_TIMESTAMP(start_time)+duration > :iStart))
          ORDER BY start_time DESC', ['uid' => $uid, 'end' => $end, 'start' => $start, 'start2' => $start, 'iEnd' => $iEnd, 'iStart' => $iStart]);
        $thispage = $this->make("request")->get('page') ?: 1;
        //我的预约推荐是从redis中取数据的，先全部取出数据，做排序
        $total_duration = $data;
        $Count = count($data);
        $data = array_slice($data, ($thispage - 1) * 15, 15);
        $result['pagination'] = [
            'page' => $thispage,
            'count' => $Count,
            'pages' => ceil($Count / 15),
        ];
        $total = 0;
        foreach ($total_duration as $key => $item) {
            /**
             * 如果出现了跨天的，就是只算当天的
             */
            $item = (array)$item;
            $endTime = strtotime($item['start_time']) + ($item['duration']);
            $duration = $item['duration'];
            if ($endTime > strtotime($end)) {
                $endTime = strtotime($end);
                $duration = strtotime($end) - strtotime($item['start_time']);
            }
            if ($item['start_time'] <= $start) {
                $duration = $endTime - strtotime($start);
            }
            $total += $duration;
        }
        unset($total_duration);

        // $total = 0;
        foreach ($data as $key => $item2) {
            $item = (array)$item2;
            $result['data'][$key] = $item;

            $result['data'][$key]['created'] = $item['start_time'];
            /**
             * 如果开始时间是在前一天的
             */
            if ($item['start_time'] <= $start) {
                $result['data'][$key]['startTime'] = $start;
            } else {
                $result['data'][$key]['startTime'] = $item['start_time'];
            }

            /**
             * 如果出现了跨天的，就是只算当天的
             */
            $endTime = strtotime($item['start_time']) + ($item['duration']);
            $duration = $item['duration'];
            if ($endTime > strtotime($end)) {
                $endTime = strtotime($end);
                $duration = strtotime($end) - strtotime($item['start_time']);
            }
            if ($item['start_time'] <= $start) {
                $duration = $endTime - strtotime($start);
            }

            //   $total += $duration;
            $result['data'][$key]['endTime'] = date('Y-m-d H:i:s', $endTime);//date('Y-m-d H:i:s',strtotime($time1)+30*60);//注意引号内的大小写,分钟是i不是m
            $result['data'][$key]['duration'] = $this->_sec2time($duration);
        }
        $result['data'] = new LengthAwarePaginator($result['data'], $Count, 15, '', ['path' => '/member/live', 'query' => ['start' => $result['start'], 'end' => $result['end']]]);
        //$result['totalTime'] = $this->getTotalTime($uid, $end, $start);
        if (empty($result['data'])) {
            $result['data'] = [];
        }
        $result['totalTime'] = $this->_sec2time($total);
        return $this->render('Member/live', $result);
    }

    /**
     *含时长房间修改
     * @author TX
     * @update 2015.4.27
     * @return Response
     */
    public function roomUpdateDuration()
    {
        $start_time = $this->make('request')->get('mintime');
        $durationid = $this->make('request')->get('durationid');
        $hour = $this->make('request')->get('hour');
        $minute = $this->make('request')->get('minute');
        $duration = $this->make('request')->get('duration');
        $points = $this->make('request')->get('points');
        if (empty($durationid) || empty($start_time) || empty($duration) || empty($points)) {
            return new JsonResponse(['code' => 10, 'msg' => '请求错误']);
        }
        if (!in_array($duration, [25, 55])) {
            return new JsonResponse(['code' => 11, 'msg' => '请求错误']);
        }
        /** @var  $durationRoom \Video\ProjectBundle\Entity\VideoRoomDuration */
        $durationRoom = RoomDuration::find($durationid);
        if ($durationRoom->reuid != 0) {
            return new JsonResponse(['code' => 8, 'msg' => '房间已经被预定，不能被修改']);
        }
        if (empty($durationRoom)) {
            return new JsonResponse(['code' => 12, 'msg' => '请求错误']);
        }
        $theday = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("d") + 7, date("Y")));
        $start_time = date("Y-m-d H:i:s", strtotime($start_time . ' ' . $hour . ':' . $minute . ':00'));
        if ($theday < date("Y-m-d H:i:s", strtotime($start_time))) {
            return new JsonResponse(['code' => 5, 'msg' => '只能设置未来七天以内']);
        }
        if (date("Y-m-d H:i:s") > date("Y-m-d H:i:s", strtotime($start_time))) {
            return new JsonResponse(['code' => 6, 'msg' => '不能设置过去的时间']);
        }
        $durationRoom->starttime = $start_time;
        $durationRoom->duration = $duration * 60;
        $durationRoom->points = $points;
        if ($this->checkRoomUnique($durationRoom)) {
            //$this->_data_model->updateByEntity('Video\ProjectBundle\Entity\VideoRoomDuration', array('id' => $durationid), array('starttime' => new \DateTime($start_time), 'duration' => $duration * 60, 'points' => $points));
            $durationRoom->save();
            $this->set_durationredis($durationRoom);
            return new JsonResponse(['code' => 1, 'msg' => '修改成功']);
        } else {
            return new JsonResponse(['code' => 9, 'msg' => '这一时段内有重复哦']);
        }

    }

    /**
     * [avatarupload 头像上传方法]
     *
     * @author  dc <dc#wisdominfo.my>
     * @version 2015-11-20
     * @return  JsonResponse
     */
    public function avatarUpload()
    {
        $user = $this->userInfo;
        $result = json_decode($this->make('systemServer')->upload($this->userInfo), true);

        if (!$result['ret']) return new JsonResponse($result);
        //更新用户头像
        Users::where('uid', $user['uid'])->update(['headimg' => $result['info']['md5']]);

        //更新用户redis
        resolve(UserService::class)->getUserReset($user['uid']);

        return new JsonResponse($result);
    }

    /**
     * [flashUpload 相册上传方法]
     *
     * @author  dc <dc#wisdominfo.my>
     * @version 2015-11-20
     * @return JsonResponse
     */
    public function flashUpload()
    {
        $user = $this->userInfo;
        $result = json_decode($this->make('systemServer')->upload($this->userInfo), true);

        if (!$result['ret']) return new JsonResponse($result);

        $anchor = Anchor::create(['uid' => Auth::id(), 'file' => $result['info']['md5'], 'size' => $result['info']['size'], 'jointime' => time()]);

        //更新用户空间
        Users::where('uid', $user['uid'])->update(['pic_used_size' => $user['pic_used_size'] + $result['info']['size']]);
        //更新用户redis
        resolve(UserService::class)->getUserReset($user['uid']);


        $result['info']['id'] = $anchor->id;
        return new JsonResponse($result);

    }

    /**
     * 取消装备道具
     * @return JsonResponse
     * @Author Orino
     */
    public function cancelScene()
    {
        Redis::del('user_car:' . Auth::id());//检查过期直接删除对应的缓存key
        return new JsonResponse(['status' => 1, 'msg' => '操作成功']);
    }

    /**
     * 开通贵族
     * TODO 提炼到服务中 现阶段过渡期
     * <p>
     *  code 0 成功
     *      1002 贵族不可用
     *      102 钻石不够《全局通用状态》
     *      1004 同样等级
     *      1005 已开通或重复
     *      1003 系统失败
     * </p>
     */
    public function buyVip()
    {
        $msg = [
            'status' => 1,
            'msg' => '',
        ];

        if (!Auth::user()) {
            $msg = [
                'status' => 101,
                'msg' => '亲，请先登录哦！',
            ];
            return (new JsonResponse($msg))->setCallback('cb');
        }
        // 取到开通的贵族的数据 判断价格
        $gid = $this->request()->get('gid');
        // 默认的天数
//        $day = $this->request()->get('day') ? $this->request()->get('day') : 30;
        $day = 30;
        // 如果在房间内 就会有roomid即为主播uid，默认为0不在房间开通， 用于佣金方面的问题
        $roomId = $this->request()->get('roomId') ? $this->request()->get('roomId') : 0;

        $user = DB::table('video_user')->where('uid', Auth::user()->uid)->first();
        // 用户组服务
        $userGroup = $this->make('userGroupServer')->getGroupById($gid);
        if (!$userGroup || $userGroup['dml_flag'] == 3) {
            $msg['status'] = 1002;
            $msg['msg'] = '该贵族状态异常,请联系客服！';
            return (new JsonResponse($msg))->setCallback('cb');
        }

        // 钱不够
        if ($userGroup['system']['open_money'] > $user->points) {
            $msg['status'] = 102;
            $msg['msg'] = '亲,你的钻石不够啦！赶快充值吧！';
            return (new JsonResponse($msg))->setCallback('cb');
        }

        // 已经开通了高等级的 不能再开通低等级的
        if ($userGroup['level_id'] == $user->vip) {
            $msg['status'] = 1004;
            $msg['msg'] = '你已开通过此贵族，你可以保级或者开通高级贵族！';
            return (new JsonResponse($msg))->setCallback('cb');
        }
        if ($userGroup['level_id'] < $user->vip) {
            $msg['status'] = 1005;
            $msg['msg'] = '请现有等级过期后再开通，或开通高等级的贵族！';
            return (new JsonResponse($msg))->setCallback('cb');
        }

        // 如果价格够了，就直接开通用户组
        try {


            /**
             * 开启事务 牵涉到多个表的操作
             */
            DB::beginTransaction();
            /**
             * 首开礼包 先判断是否已经开通过了此贵族的 TODO 为了解决mysqlnd_ms 的bug问题 强制读主库
             */
            $isBuyThisGroup = DB::select('SELECT * FROM video_user_buy_group WHERE gid=? AND uid=? LIMIT 1', [$gid, Auth::id()]);

            $userServer = resolve(UserService::class)->setUser(Users::find(Auth::id())); // 初始化用户服务
            $u['vip'] = $userGroup['level_id'];
            $exp = date('Y-m-d H:i:s', time() + $day * 24 * 60 * 60);
            // 用户的钻石 减去开通的价格
            $u['points'] = ($user->points - $userGroup['system']['open_money']); // 扣除的钻石
            $user->points = ($user->points - $userGroup['system']['open_money']); // 扣除的钻石
            $u['vip_end'] = $exp; // 过期时间
            $u['hidden'] = 0;
            DB::table('video_user')->where('uid', Auth::id())->update($u);
            // 首开礼包
            if (!$isBuyThisGroup && $userGroup['system']['gift_money']) {
                // 赠送 TODO 整合到用户的服务中去
                $us['points'] = ($user->points + $userGroup['system']['gift_money']);
                DB::table('video_user')->where('uid', Auth::id())->update($us);
                // 写入赠送日志
                $arr = [
                    'uid' => Auth::id(),
                    'created' => date('Y-m-d H:i:s'),
                    'points' => $userGroup['system']['gift_money'],
                    'paymoney' => round($userGroup['system']['gift_money'], 1),
                    'order_id' => time(),
                    'pay_type' => 5,//服务器送的钱pay_type=5
                    'pay_id' => null,
                    'nickname' => $user->nickname,
                ];
                DB::table('video_recharge')->insert($arr);
                // 赠送后 发送给用户通知消息
                $message = [
                    'mail_type' => 3,
                    'rec_uid' => $user->uid,
                    'content' => '您首次开通 ' . $userGroup['level_name'] . ' 贵族，获得了赠送礼包的' . $userGroup['system']['gift_money'] . '钻石',
                ];
                $this->make('messageServer')->sendSystemToUsersMessage($message);
            }

            // 写入购买用户组的记录 user_buy_group
            $buyGroup = [
                'uid' => Auth::id(),
                'gid' => $gid,
                'create_at' => date('Y-m-d H:i:s'),
                'rid' => $roomId,
                'end_time' => $exp,
                'status' => 1,
                'open_money' => $userGroup['system']['open_money'],
                'keep_level' => $userGroup['system']['keep_level'],
            ];
            DB::table('video_user_buy_group')->insertGetId($buyGroup);

            /**
             * 购买贵族后自动送坐骑
             * [添加用户背包判断再进行赠送]
             * @author  dc
             * @version 20151026
             */
            $userPack = Pack::whereUid(Auth::id())->whereGid($userGroup['mount'])->first();
            if (!$userPack) {
                DB::insert('INSERT INTO `video_pack` (uid, gid, expires, num) VALUES (?, ?, ?, ?)', [Auth::id(), $userGroup['mount'], strtotime($exp), 1]);
                /*@todo 待检查为何这个方法插入失败*/
                //Pack::create(array('uid'=>Auth::id(),'gid'=>$userGroup['mount'],'expires'=>strtotime($exp),'num'=>1));
            }

            // 赠送爵位
            if ($userGroup['system']['gift_level']) {
                $userServer->modLvRich($userGroup['system']['gift_level']);
            }

            // 开通成功后 发送给用户通知消息
            $message = [
                'mail_type' => 3,
                'rec_uid' => $user->uid,
                'content' => '贵族开通成功提醒：您已成功开通 ' . $userGroup['level_name'] . ' 贵族，到期日：' . $exp,
            ];
            $this->make('messageServer')->sendSystemToUsersMessage($message);

            // 如果设置了房间属性 就给主播返现
            $casheback = 0;
            if ($roomId && DB::table('video_user')->where('uid', $roomId)->first()) {
                $commission = [
                    'uid' => $roomId,
                    'r_uid' => Auth::id(),
                    'r_id' => $gid,
                    'type' => 'open_vip',
                    'title' => '房间内开通贵族返佣',
                    'points' => $userGroup['system']['host_money'],
                    'create_at' => date('Y-m-d H:i:s'),
                    'data' => serialize([
                        'gid' => $gid, 'vip_end' => $exp,
                    ]),
                    'content' => $this->userInfo['nickname'] . '在您的房间' . date('Y-m-d H:i:s') . '开通了什么' . $userGroup['level_name'] . '，您得到' . $userGroup['system']['host_money'] . '佣金！',
                    'status' => 0,
                    'dml_flag' => 1,
                ];
                DB::table('video_user_commission')->insertGetId($commission);
                $casheback = $userGroup['system']['host_money'];
            }
            $userinfo = DB::select('SELECT * FROM video_user WHERE uid=? LIMIT 1', [Auth::id()]);
            //赠送完坐骑立即装备
            $redis = $this->make('redis');
            // 更新用户redis中的信息
            $redis->hMset('huser_info:' . Auth::id(), (array)$userinfo[0]);
            $redis->del('user_car:' . Auth::id());
            $redis->hset('user_car:' . Auth::id(), $userGroup['mount'], strtotime($exp));

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            //记录下日志
            $logPath = base_path() . '/storage/logs/test_' . date('Y-m-d') . '.log';
            $loginfo = date('Y-m-d H:i:s') . ' uid' . Auth::id() . "\n 购买贵族 事务结果: \n" . $e->getMessage() . "\n";
            $this->logResult($loginfo, $logPath);

            $msg['status'] = 1003;
            $msg['msg'] = '可能由于网络原因，开通失败！';
            return (new JsonResponse($msg))->setCallback('cb');
        }
        $msg['msg'] = '开通成功';
        $msg['data'] = [
            'uid' => Auth::id(),
            'roomid' => $roomId,
            'vip' => $userGroup['level_id'],
            'cashback' => $casheback,
            'name' => $user->username,
        ];

        return (new JsonResponse($msg))->setCallback('cb');
    }

    /**
     * 获取贵族的坐骑
     */
    public function getVipMount()
    {
        // 获取vip坐骑的id
        $mid = $this->make('request')->get('mid');
        $msg = [
            'code' => 0,
            'msg' => '',
        ];

        // 判断是否已经领过了
        $pack = Pack::where('uid', Auth::id())->where('gid', $mid)->first();
        if ($pack) {
            $msg['code'] = 1002;
            $msg['msg'] = '你已经获取过了该坐骑！';
            return new JsonResponse($msg);
        }

        // 判断是否是对应的贵族
        $userGroup = UserGroup::where('type', 'special')->where('mount', $mid)->first();

        if (!$userGroup) {
            $msg['code'] = 1005;
            $msg['msg'] = '此坐骑专属贵族所有！';
            return new JsonResponse($msg);
        }

        if ($this->userInfo['vip'] < $userGroup['level_id']) {
            $msg['code'] = 1003;
            $msg['msg'] = '你还不够领取此级别的坐骑！';
            return new JsonResponse($msg);
        }

        // 领取成功
        $pack = new Pack();
        $pack->uid = Auth::id();
        $pack->gid = $mid;
        $pack->num = 1;
        $pack->expires = strtotime($this->userInfo['vip_end']);
        $res = $pack->save();

        if ($res !== false) {
            $msg['msg'] = '开通成功！';
            return new JsonResponse($msg);
        }

    }

    /**
     * 获取用户的钱 主要是用于在商城页面中购买商品的接口
     */
    public function getmoney()
    {
        return new JsonResponse(
            [
                'code' => 0,
                'info' => [
                    'nickname' => $this->userInfo['nickname'],
                    'money' => $this->userInfo['points'],
                ],
            ]
        );
    }

    /**
     * [hidden 用户在线、隐身接口]
     *
     * @author  dc <dc#wisdominfo.my>
     * @version 2015-11-11
     * @param   int $status 要设置的状态1=隐身，0=在线
     * @return  json
     */
    public function hidden($status)
    {
        if (!in_array($status, ['0', '1'])) return new JsonResponse(['status' => 0, 'message' => '参数错误']);

        $uid = Auth::id();
        if (!$uid) return new JsonResponse(['status' => 0, 'message' => '用户错误']);
        $user = Users::where('uid', $uid)->with('vipGroup')->first();

        //判断用户是否有隐身权限
        if (!resolve(UserService::class)->getUserHiddenPermission($user)) return new JsonResponse(['status' => 0, 'message' => '没有权限!']);

        //更新数据库隐身状态
        $hidden = Users::where('uid', $uid)->update(['hidden' => $status]);

        //更新用户redis
        resolve(UserService::class)->getUserReset($uid);

        return new JsonResponse(['status' => 1, 'message' => '操作成功']);
    }


    /**
     * ajax 请求获取信息 TODO 策略
     *
     * @return Response
     */
    public function ajax($act)
    {
//        $act = $this->request()->get('action');
        if (($act != 'getfidinfo') && Auth::guest()) {
            return new RedirectResponse('/', 301);
        }
//        $this->_initUser();
        $actions = [
            'userinfo' => 'editUserinfo',
            'attenionCancel' => 'attenionCancel',
            'getfidinfo' => 'getfidinfo',
            'delmsg' => 'delmsg',
            'equipHandle' => '_getEquipHandle',
        ];
        if ($act == 'userinfo') {
            $info = $this->$actions[$act]($this->request()->reques->all(), Auth::id());
            return new Response(json_encode($info));
        } elseif ($act == 'attenionCancel') {
            $this->$actions[$act](Auth::id(), $this->request()->get('tid'));
        } else if ($act == 'getfidinfo') {
            $onlineId = $this->request()->getSession()->get(self::SEVER_SESS_ID);
            $uid = intval($this->request()->get('uid'));
            if ($uid == 0) {
                return new Response(json_encode(['ret' => false]));
            }
            $data = $this->{$actions[$act]}($this->request()->get('uid'));
            if (!$data) {
                return new Response(json_encode(['ret' => false]));
            } else {
                $data = $this->getOutputUser($data, 80);
                unset($data['safemail']);
                if ($onlineId) {
                    //$data['checkatten'] = $this->_data_model->checkIsattenByuid($uid,$onlineId);
                    $data['checkatten'] = resolve(UserService::class)->checkFollow($onlineId, $uid) ? 1 : 0;
                } else {
                    $data['checkatten'] = 0;
                }
                if ($data['roled'] == 3) {
                    $data['live_status'] = $this->make('redis')->hget('hvediosKtv:' . $data['uid'], 'status');
                }
                return new Response(json_encode(['ret' => true, 'info' => $data]));
            }
        } else if ($act == 'delmsg') {
            $data = $this->$actions[$act](Auth::id());
            //  return;
        } else if ($act == 'equipHandle') {
            $this->$actions[$act]($this->request()->get('gid'));
        }
        return new Response(json_encode(['ret' => true, 'info' => '更新成功']));
    }

    /**
     * 付款
     */
    public function pay(Request $request)
    {
        $type = $request->get('type');
        $gid = $request->get('gid');
        $nums = $request->get('nums');
        if (!$this->buyGoods($type, $gid, $nums)) {
            $retData = [
                'msg' => '购买失败！可能钱不够',
                'status' => 0,
            ];
        } else {
            //  $this->_getEquipHandle($this->get('request')->get('gid'));//分布
            $retData = [
                'msg' => '购买成功！',
                'status' => 1,
            ];
        }
        return JsonResponse::create($retData);
    }

    /**
     * 删除一对一房间
     */
    public function delRoomDuration()
    {

        $rid = $this->request()->input('rid');


        if (!$rid) return JsonResponse::create(['status' => 0, 'msg' => '请求错误']);
        $roomservice = resolve(RoomService::class);
        $result = $roomservice->delOnetoOne($rid);



        return JsonResponse::create($result);
    }

    /**
     * 删除一对多房间
     * @return JsonResponse|Response|static
     */
    public function delRoomOne2Many()
    {
        $rid = $this->request()->input('rid');

        if (!$rid) return JsonResponse::create(['status' => 401, 'msg' => '请求错误']);
        $room = RoomOneToMore::find($rid);
        if (!$room) return new JsonResponse(['status' => 402, 'msg' => '房间不存在']);
        if ($room->uid != Auth::id()) return JsonResponse::create(['status' => 404, 'msg' => '非法操作']);//只能删除自己房间
        if ($room->status == 1) return new JsonResponse(['status' => 403, 'msg' => '房间已经删除']);
        if ($room->purchase()->exists()) {
            return new JsonResponse(['status' => 400, 'msg' => '房间已经被预定，不能删除！']);
        }
        $redis = $this->make('redis');
        $redis->sRem('hroom_whitelist_key:' . $room->uid, $room->id);
        $redis->delete('hroom_whitelist:' . $room->uid . ':' . $room->id);
        $room->update(['status' => 1]);
        return JsonResponse::create(['status' => 1, 'msg' => '删除成功']);
    }

    /**
     * 一对多补票接口
     */
    public function makeUpOneToMore()
    {
        $uid = Auth::id();
        $request = $this->request();
        $rid = intval($request->input('rid'));
        $origin = intval($request->input('origin')) ?: 12;
        if ($rid == $uid) return JsonResponse::create(['status' => 0, 'msg' => '不能购买自己房间亲']);
        $onetomany = intval($request->input('onetomore'));
        if (empty($onetomany) || empty($uid)) return JsonResponse::create(['status' => 0, 'msg' => '参数错误']);
        /** @var \Redis $redis */
        $redis = $this->make('redis');
        $room = $redis->hgetall("hroom_whitelist:$rid:$onetomany");
        if (empty($room)) return JsonResponse::create(['status' => 0, 'msg' => '房间不存在']);

        $points = $room['points'];
        if (isset($room['uids']) && in_array($uid, explode(',', $room['uids']))) return JsonResponse::create(['status' => 0, 'msg' => '您已有资格进入该房间，请从“我的预约”进入。']);
        if (isset($room['tickets']) && in_array($uid, explode(',', $room['tickets']))) return JsonResponse::create(['status' => 0, 'msg' => '您已有资格进入该房间，请从“我的预约”进入。']);
        /** 检查余额 */
        $user = resolve(UserService::class)->getUserByUid($uid);
        if ($user['points'] < $points) return JsonResponse::create(['status' => 0, 'msg' => '余额不足', 'cmd' => 'topupTip']);
        /** 通知java送礼*/
        $redis->publish('makeUpOneToMore',
            json_encode([
                'rid' => $rid,
                'uid' => $uid,
                'onetomore' => $onetomany,
                'origin' => $origin,
            ]));
        /** 检查购买状态 */
        $timeout = microtime(true) + 4;
        while (true) {
            if (microtime(true) > $timeout) break;
            $tickets = explode(',', $redis->hGet("hroom_whitelist:$rid:$onetomany", 'tickets'));
            if (in_array($uid, $tickets)) return JsonResponse::create(['status' => 1, 'msg' => '购买成功']);
            usleep(200000);
        }
        return JsonResponse::create(['status' => 0, 'msg' => '购买失败']);
    }

    public function buyModifyNickname()
    {
        $user = Auth::user();
        $uid = Auth::id();
        $price = SiteSer::config('nickname_price');
        $userService = resolve(UserService::class);
        if ($user['points'] < $price) return JsonResponse::create(['status' => 0, 'msg' => '余额不足' . $price . '钻']);
        $num = $userService->getModNickNameStatus()['num'];
        if ($num >= 1) return JsonResponse::create(['status' => 0, 'msg' => '已有修改昵称资格']);
        /** 扣钱给资格 */
        if (Users::where('uid', $uid)->where('points', '>=', $price)->decrement('points', $price)) {
            Redis::hIncrBy('huser_info:' . $uid, 'points', $price * -1);
            Redis::hIncrBy('modify.nickname', $uid, 1);
            return JsonResponse::create(['status' => 1, 'msg' => '购买成功']);
        } else {
            return JsonResponse::create(['status' => 0, 'msg' => '扣款失败']);
        }
    }

    /**添加用户到一对多
     * @param     $onetomany
     * @param     $uid
     * @param int $origin
     * @param int $points 0后台添加，-1使用房间价格全额
     * @return array
     */
    protected function addOneToManyRoomUser($rid, $onetomany, $uid, $origin = 13, $points = 0)
    {
        /** @var \Redis $redis */
        $redis = $this->make('redis');
        if (empty($onetomany) || empty($uid)) return [0, '参数错误'];
        $room = $redis->hgetall("hroom_whitelist:$rid:$onetomany");
        if (empty($room)) return [0, '房间不存在'];
        if ($rid == $uid) return [0, '主播不能购买自己的一对多'];//不能添加主播自己
        if (strtotime($room['endtime']) < time()) return [0, '房间已经结束'];//房间已经结束

        if (in_array($uid, explode(',', $room['uids']))) return [0, '您已有资格进入该房间，请从“我的预约”进入。'];
        if (UserBuyOneToMore::query()->where('onetomore', $onetomany)->where('uid', $uid)->first()) return [0, '您已有资格进入该房间，请从“我的预约”进入。'];//redis数据可能出错
        $redis->hIncrBy("hroom_whitelist:$rid:$onetomany", 'nums', 1);//房间人数+1
        $room['nums']++;
        RoomOneToMore::find($onetomany)->increment('tickets', 1);
        if ($points = -1) {//points -1 表示房间价格全额
            $points = $room['points'];
        }
        $buy_item = [
            'rid' => $room['uid'],
            'onetomore' => $onetomany,
            'uid' => $uid,
            'type' => 2,
            'created' => date('Y-m-d H:i:s'),
            'starttime' => $room['starttime'],
            'endtime' => $room['endtime'],
            'duration' => ($duration = strtotime($room['endtime']) - strtotime($room['starttime'])),
            'points' => $points,
            'origin' => $origin,
        ];
        $buy = UserBuyOneToMore::create($buy_item);
        $auto_id = $buy->id;
        $redis = $this->make('redis');
        $redis->hmset('hbuy_one_to_more:' . $rid . ':' . $auto_id, $buy->toArray());
        $redis->expire('hbuy_one_to_more:' . $rid . ':' . $auto_id, $duration + 86400);

        //添加白名单
        $uids = $redis->hget('hroom_whitelist:' . $rid . ':' . $onetomany, 'uids');
        if ($uids) {
            if (!in_array($uid, explode(',', $uids))) {
                $uids .= ',' . $uid;
            }
        } else {
            $uids = $uid;
        }
        $redis->hmset('hroom_whitelist:' . $rid . ':' . $onetomany
            , [
                'uids' => $uids,
            ]);
        return [
            1, '添加成功', 'data' =>
                ['points' => $points],
        ];
    }
}

