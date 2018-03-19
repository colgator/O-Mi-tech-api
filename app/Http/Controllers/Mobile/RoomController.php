<?php
/**
 * Created by PhpStorm.
 * User: nicholas
 * Date: 2017/2/9
 * Time: 11:10
 */

namespace App\Http\Controllers\Mobile;


use App\Models\MallList;
use App\Models\RoomDuration;
use App\Models\RoomStatus;
use App\Models\UserBuyOneToMore;
use App\Models\Users;
use App\Service\Room\NoSocketChannelException;
use App\Service\Room\RoomService;
use App\Service\Room\SocketService;
use DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\RoomOneToMore;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\LiveList;

class RoomController extends Controller
{
    const FLAG_ONE_TO_ONE = 0b01;
    const FLAG_ONE_TO_MANY = 0b10;
    static $ORIGINS = [
        11,//网页房间内
        12,//网页房间外
        13,//网页后台
        21,//安卓房间内
        22,//安卓房间外
        31,//IOS房间内
        32,//IOS房间外
    ];

    public function buyOneToOne()
    {
        $request = $this->request();
        $origin = $request->input('origin');
        if (!$origin || !in_array($origin, static::$ORIGINS)) {
            return JsonResponse::create([
                'status' => 0,
                'msg' => '非法来源'
            ]);
        }
        $rid = $request->input('rid');
        $flag = $request->get('flag');
        if (empty($rid) || empty($flag)) {
            return new JsonResponse(array('status' => 408, 'msg' => L('MEMBER.DORESERVATION.ERROR')));
        }
        $duroom = RoomDuration::find($rid);
        if (empty($duroom)) return new JsonResponse(array('status' => 401, 'msg' => L('MEMBER.DORESERVATION.NO_EXIST')));
        if ($duroom['status'] == 1) return new JsonResponse(array('status' => 402, 'msg' => L('MEMBER.DORESERVATION.OFFLINE')));
        if ($duroom['reuid'] != '0') return new JsonResponse(array('status' => 403, 'msg' => L('MEMBER.DORESERVATION.BOOKED')));
        if ($duroom['uid'] == Auth::id()) return new JsonResponse(array('status' => 404, 'msg' => L('MEMBER.DORESERVATION.SELF')));
        if ($this->userInfo['points'] < $duroom['points']) return new JsonResponse(array('status' => 405, 'msg' => L('MEMBER.DORESERVATION.POINTS_NOT_ENOUGH')));
        //关键点，这个时段内有没有其他的房间重复，标志位为flag 默认值为false 当用户确认后传入的值为true
//        if ($flag == 'false' && !$this->notBuyRepeat($duroom['starttime'], $duroom['endtime'])) return new JsonResponse(array('status' => 407, 'msg' => L('MEMBER.DORESERVATION.CONFIRM')));

        $duroom['reuid'] = Auth::id();
        //$duroom->save();

        $logPath = BASEDIR . '/app/logs/test_' . date('Ym') . '.log';
        try {
            DB::beginTransaction();
//            DB::table('video_room_duration')
//                ->where('id', $rid)->update(['reuid' => Auth::id(), 'invitetime' => time(), 'origin' => $origin]);
            if (!DB::table('video_room_duration')
                ->where('id', $rid)->where('reuid', '0')->update(['reuid' => Auth::id(), 'invitetime' => time(), 'origin' => $origin])
            ) {
                DB::rollBack();
                return JsonResponse::create(['status' => 408, 'msg' => L('MEMBER.DORESERVATION.ERROR')]);
            }

            $keys = 'hroom_duration:' . $duroom['uid'] . ':' . $duroom['roomtid'];
            $duroom['invitetime'] = time();
            $arr = $duroom;
            $rs = $this->make('redis')->hSet($keys, $arr['id'], json_encode($arr));
            $this->logResult("redis hset表：" . $keys . " key:" . $arr['id'] . " 结果:" . $rs . "\n", $logPath);
            if ($rs !== false) {
                DB::commit();
            } else {
                DB::rollback();
                return new JsonResponse(array('status' => 408, 'msg' => L('MEMBER.DORESERVATION.ERROR')));
            }
        } catch (\Exception $e) {
            $this->logResult("事务异常：id" . $rid . " 房间号" . $duroom['uid'] . " 预约者" . $duroom['reuid'] . " 事务结果：" . $e->getMessage() . "\n", $logPath);
            DB::rollback();
            return new JsonResponse(array('status' => 409, 'msg' => L('MEMBER.DORESERVATION.ERROR')));
        }

        //$this->set_durationredis($duroom);
        //记录一个标志位，在我的预约列表查询中需要优先显示查询已经预约过的主播，已经预约过的主播的ID会写到这个redis中类似关注一样的
        if (!($this->checkUserAttensExists(Auth::id(), $duroom['uid'], true, true))) {
            $this->make('redis')->zadd('zuser_reservation:' . Auth::id(), time(), $duroom['uid']);
        }
        Users::where('uid', Auth::id())->update(array('points' => ($this->userInfo['points'] - $duroom['points']), 'rich' => ($this->userInfo['rich'] + $duroom['oints'])));
        $this->make('userServer')->getUserReset(Auth::id());// 更新redis TODO 好屌
//        RoomDuration::where('id', $duroom['id'])
//            ->update(array('reuid' => Auth::id(), 'invitetime' => time()));
        //增加消费记录查询
        MallList::create(array(
            'send_uid' => Auth::id(),
            'rec_uid' => $duroom['uid'],
            'gid' => $duroom['roomtid'],
            'gnum' => 1,
            'created' => date('Y-m-d H:i:s'),
            'rid' => $duroom['uid'],
            'points' => $duroom['points']
        ));
        // 用户增加预约排行榜的排名
        $this->make('redis')->zIncrBy('zrank_appoint_month' . date('Ym'), 1, $duroom['uid']);
        //修改用户日，周，月排行榜数据
        //zrank_rich_history: 用户历史消费    zrank_rich_week ：用户周消费   zrank_rich_day ：用户日消费  zrank_rich_month ：用户月消费
        $expire_day = strtotime(date('Y-m-d 00:00:00', strtotime('next day'))) - time();
        $expire_week = strtotime(date('Y-m-d 00:00:00', strtotime('next week'))) - time();
        $zrank_user = array('zrank_rich_history', 'zrank_rich_week', 'zrank_rich_day', 'zrank_rich_month:' . date('Ym'));
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
        $zrank_pop = array('zrank_pop_history', 'zrank_pop_month:' . date('Ym'), 'zrank_pop_week', 'zrank_pop_day');
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

        return new JsonResponse(array('status' => 1, 'msg' => L('MEMBER.DORESERVATION.OK')));

    }

    /**
     * 一对多补票接口
     */
    public function makeUpOneToMore()
    {
        $uid = Auth::id();
        $request = $this->request();
        $rid = intval($request->input('rid'));
        $origin = intval($request->input('origin'))?:12;
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
        $user = $this->make('userServer')->getUserByUid($uid);
        if ($user['points'] < $points) return JsonResponse::create(['status' => 0, 'msg' => '余额不足', 'cmd' => 'topupTip']);
        if ($redis->hGet("hvediosKtv:$rid", "status") == 0) return JsonResponse::create(['status' => 0, 'msg' => '主播不在播，不能购买！']);

        $logPath =  base_path() . '/storage/logs/one2more_' . date('Ym') . '.log';
        $this->logResult('makeUpOneToMore ' . json_encode(['rid' => $rid, 'uid' => $uid, 'onetomore' => $onetomany,]), $logPath);
        /** 通知java送礼*/
        $redis->publish('makeUpOneToMore',
            json_encode([
                'rid' => $rid,
                'uid' => $uid,
                'onetomore' => $onetomany,
                'origin' => $origin
            ]));
        /** 检查购买状态 */
        $timeout = microtime(true) + 4;
        while (true) {
            if (microtime(true) > $timeout) break;
            $tickets = explode(',', $redis->hGet("hroom_whitelist:$rid:$onetomany", 'tickets'));
            if (in_array($uid, $tickets)) return JsonResponse::create(['status' => 1, 'msg' => '购买成功']);
            usleep(20000);
        }
        return JsonResponse::create(['status' => 0, 'msg' => '购买失败']);
    }
    /**
     * @TODO copy from MemberController/doOneToMore()
     */
    /*
    public function buyOneToMany()
    {
        return false;
        $origin = $this->request()->input('origin');
        if (!$origin || !in_array($origin, static::$ORIGINS)) {
            return JsonResponse::create([
                'status' => 0,
                'msg' => '非法来源'
            ]);
        }
        $rid = $this->make('request')->input('rid');
        $flag = $this->request()->get('flag');
        if (empty($rid) || empty($flag)) return JsonResponse::create(['status' => 408, 'msg' => L('MEMBER.DORESERVATION.ERROR')]);
        $duroom = RoomOneToMore::find($rid);
        if (empty($duroom)) return new JsonResponse(array('status' => 410, 'msg' => L('MEMBER.DORESERVATION.ERROR')));
        if ($duroom['status'] == 1) return new JsonResponse(array('status' => 402, 'msg' => L('MEMBER.DORESERVATION.OFFLINE')));
        if ($duroom['uid'] == Auth::id()) return new JsonResponse(array('status' => 404, 'msg' => L('MEMBER.DORESERVATION.SELF')));
        if ($this->userInfo['points'] < $duroom['points']) return new JsonResponse(array('status' => 405, 'msg' => L('MEMBER.DORESERVATION.POINTS_NOT_ENOUGH')));
        if ($duroom['endtime'] < date('Y-m-d H:i:s')) return new JsonResponse(array('status' => 406, 'msg' => L('MEMBER.DOONETOMORE.END')));
        if (UserBuyOneToMore::where('onetomore', $rid)->where('uid', Auth::id())->first()) return new JsonResponse(array('status' => 407, 'msg' => L('MEMBER.DOONETOMORE.HAS_ROOM')));

        //关键点，这个时段内有没有其他的房间重复，标志位为flag 默认值为false 当用户确认后传入的值为true
        if ($flag == 'false' && !$this->notBuyRepeat($duroom['starttime'], $duroom['endtime'])) return new JsonResponse(array('status' => 408, 'msg' => L('MEMBER.DORESERVATION.CONFIRM')));

        //加入白名单
        $duroom->tickets += 1;
        $duroom->save();

        $buy_item = [
            'rid' => $duroom['uid'],
            'onetomore' => $rid,
            'uid' => Auth::id(),
            'type' => 2,
            'created' => date('Y-m-d H:i:s'),
            'starttime' => $duroom['starttime'],
            'endtime' => $duroom['endtime'],
            'duration' => $duroom['duration'],
            'points' => $duroom['points'],
            'origin' => $origin,
        ];
        $buy = UserBuyOneToMore::create($buy_item);
        $auto_id = $buy->id;
        $hbuy = $this->make('redis')->hmset('hbuy_one_to_more:' . $duroom['uid'] . ':' . $auto_id, $buy_item);
        $this->make('redis')->expire('hbuy_one_to_more:' . $duroom['uid'] . ':' . $auto_id, $duroom['duration'] + 86400);

        //添加白名单
        $uids = $this->make('redis')->hget('hroom_whitelist:' . $duroom['uid'] . ':' . $rid, 'uids');
        if ($uids) {
            if (!in_array(Auth::id(), explode(',', $uids))) {
                $uids .= ',' . Auth::id();
            }
        } else {
            $uids = Auth::id();
        }
        $temp = [
            'nums' => $duroom['tickets'],
            'uids' => $uids,
        ];
        $this->make('redis')->hmset('hroom_whitelist:' . $duroom['uid'] . ':' . $rid, $temp);

        //扣减钻石
        DB::table('video_user')->where('uid', Auth::id())->decrement('points', $duroom['points']);
        DB::table('video_user')->where('uid', Auth::id())->increment('rich', $duroom['points']);
        $this->make('userServer')->getUserReset(Auth::id());

        $logPath = BASEDIR . '/app/logs/one2more_' . date('Ym') . '.log';
        $one2moreLog = 'uid:' . Auth::id() . ' id:' . $rid . '用户钻石：' . $this->userInfo['points'] . '+';
        $one2moreLog .= $duroom['points'];
        $this->logResult('doOneToMore ' . $one2moreLog, $logPath);

        //更新排行榜
        //$this->updateRank($duroom['uid'],$duroom['points']);
        return JsonResponse::create(['status' => 1, 'msg' => L('MEMBER.DORESERVATION.OK')]);
    }*/

    public function listReservation($type = 0b11)
    {
        $lists = [];
        $flashVersion = $this->make('redis')->get('flash_version') ?: 'v201504092044';
        if ($type & static::FLAG_ONE_TO_ONE) {//一对一
            $lists['oneToOne'] = $this->getReserveOneToOneByUid(Auth::id(), $flashVersion);
        }
        if ($type & static::FLAG_ONE_TO_MANY) {//一对多
            $lists['oneToMany'] = $this->getReserveOneToManyByUid(Auth::id(), $flashVersion);
        }
        return JsonResponse::create(['status' => 1, 'data' => $lists]);
    }

    /**
     * @param $uid
     * @param $flashVersion
     * @return array|\Illuminate\Support\Collection
     */
    public function getReserveOneToOneByUid($uid, $flashVersion)
    {
        $list = collect();
        $myReservation = RoomDuration::where('reuid', $uid)
            ->whereRaw('starttime>(now()-duration)')
            ->orderBy('starttime', 'desc')
            ->get();
        if (!$myReservation->count()) return $list;
        // 从redis 获取一对一预约数据
        $ordRooms = $this->make('redis')->get('home_ord_' . $flashVersion);
        $ordRooms = str_replace(['cb(', ');'], ['', ''], $ordRooms);
        $ordRooms = json_decode($ordRooms, true);
        $rooms = $ordRooms['rooms'];
        foreach ($myReservation as $item) {
            foreach ($rooms as $room) {
                if ($item->uid == $room['uid'] && $item->id == $room['id']) {
                    $room['listType'] = 'myres';
                    $list[] = $room;
                }
            }
        }
        return $list;
    }

    /**
     * @param $uid
     * @param $flashVersion
     * @return array|\Illuminate\Support\Collection
     */
    public function getReserveOneToManyByUid($uid, $flashVersion)
    {
        $list = collect();

        $oneToMore = UserBuyOneToMore::where('uid', $uid)->whereRaw('starttime>DATE_SUB(now(),INTERVAL duration  SECOND)')->orderBy('starttime', 'desc')->get();
        if (!$oneToMore->count()) $list;
        // 从redis 获取一对多预约数据
        $oneManyRooms = $this->make('redis')->get('home_one_many_' . $flashVersion);
        $oneManyRooms = str_replace(['cb(', ');'], ['', ''], $oneManyRooms);
        $oneManyRooms = json_decode($oneManyRooms, true);
        $rooms = $oneManyRooms['rooms'];
        if ($rooms) {
            foreach ($oneToMore as $item) {
                foreach ($rooms as $room) {
                    if ($item->rid == $room['uid'] && $item->onetomore == $room['id']) {
                        $room['listType'] = 'myticket';
                        $list[] = $room;
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 检查密码房密码
     * @return JsonResponse
     */
    public function checkPwd()
    {
        $password = $this->request()->get('password');
        $rid = $this->request()->get('rid');
        $type = $this->getAnchorRoomType($rid);
        if ($type != 2) return new JsonResponse(array('status' => 0, 'msg' => '密码房异常,请联系运营重新开启一下密码房间的开关'));
        if (empty($rid)) return new JsonResponse(array('status' => 0, 'msg' => '房间号错误!'));
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
                return new JsonResponse(array('status' => 0, 'msg' => '请输入验证码!', 'times' => $times, 'cmd' => 'showCaptcha'));
            }
            if (!$this->make('captcha')->Verify($captcha)) return new JsonResponse(array('status' => 0, 'msg' => '验证码错误!', 'times' => $times));;
        }
        if (strlen($password) < 6 || strlen($password) > 22 || !preg_match('/^\w{6,22}$/', $password)) {
            $this->make('redis')->set($keys_room, $times + 1);
            $this->make('redis')->expire($keys_room, 3600);
            return new JsonResponse(array(
                'status' => 0,
                'msg' => "密码格式错误!",
                'times' => $times + 1
            ));
        }
        if ($password != $roomstatus['pwd']) {
            if (empty($times)) {
                $this->make('redis')->set($keys_room, 1);
                $this->make('redis')->expire($keys_room, 3600);
            } else {
                $this->make('redis')->set($keys_room, $times + 1);
                $this->make('redis')->expire($keys_room, 3600);
            }
            return new JsonResponse(array(
                'status' => 0,
                'msg' => "密码错误!",
                'times' => $times + 1
            ));
        }
        $this->make('redis')->hset('keys_room_passwd:' . $rid . ':' . $sessionid, 'status', 1);
        return new JsonResponse(array('status' => 1, 'msg' => '登陆成功'));
    }

    /**
     *房间密码错误次数请求
     * @author TX
     * updata 2015.4.16
     * @return JsonResponse
     */
    public function geterrorsAction()
    {
        $rid = $this->request()->get('roomid');
        if (empty($rid)) return new JsonResponse(array('status' => 0, 'msg' => '房间号错误!'));
//        $this->get('session')->start();
        $session_name = $this->request()->getSession()->getName();
        if (isset($_POST[$session_name])) {
            $this->request()->getSession()->setId($_POST[$session_name]);
        }
        $sessionid = $this->request()->getSession()->getId();
        $keys_room = 'keys_room_errorpasswd:' . $sessionid . ':' . $rid;
        $times = $this->make('redis')->hget($keys_room, 'times');
        if (empty($times)) $times = 0;
        return new JsonResponse(array('status' => 1, 'times' => $times));
    }

    /**
     * @description 获取房间权限
     * @author TX
     * @date 2015.4.20
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

    public function getConf()
    {
        $config = $this->make('config');
        $redis = $this->make('redis');
        $conf = [
            'OPEN_WEB' => $config['config.OPEN_WEB'],
            'IMG_HOST' => $config['config.REMOTE_PIC_URL'],
            'PIC_CDN_STATIC' => $config['config.PIC_CDN_STATIC'],
            'flash_version' => $redis->get('flash_version') ?: 'v201504092044',
            'publish_version' => $redis->get('publish_version') ?: 'v2017090701', //young添加
            'in_limit_points' => $redis->hget('hconf', 'in_limit_points') ?: 0,
            'in_limit_safemail' => $redis->hget('hconf', 'in_limit_safemail') ?: 0,   //1开，0关
        ];
        return JsonResponse::create($conf);
    }

    public function getRoomConf()
    {
        $rid = $this->request()->get('rid');
        $redis = $this->make('redis');
        /** @var RoomService $roomService */
        $roomService = $this->make('roomService');

        $room = $roomService->getRoom($rid, Auth::id());
        /** @var SocketService $socketService */
        $socketService = $this->make('socketService');
        $chatServer = [];
        if (!empty($room) && !empty($room['channel_id'])) {
            $chatServer = $socketService->getServer($room['channel_id']);
        }
        if (empty($room) || empty($chatServer)) {
            //创建房间
            if ($this->userInfo['rid'] == $rid) {   //最近一次与当前一致
                try {
                    $room = $roomService->addRoom($rid, $rid, Auth::id());
                    $chatServer = $socketService->getServer($room['channel_id']);
                } catch (NoSocketChannelException $e) {
                    $chatServer['error'] = $e->getMessage();
                }
            }
        }
        return JsonResponse::create([
            'new_user' => $this->userInfo['created'] > $this->container->config['config.USER_TIME_DIVISION'] ? 1 : 0,
            'rid' => $rid,
//            'room'=>$room,
            'chatServer' => $chatServer,
            'in_limit_points' => $redis->hget('hconf', 'in_limit_points') ?: 0,
            'in_limit_safemail' => $redis->hget('hconf', 'in_limit_safemail') ?: 0,   //1开，0关
//            'flash_version' => $redis->get('flash_version') ?: 'v201504092044',
//            'flash_ver_h5' => $redis->get('flash_ver_h5') ?: 'v201504092044',
            'certificate' => $this->make('safeService')->getLcertificate(),
//            'publish_version' => $redis->get('publish_version') ?: 'v2017090701' //young添加
        ]);
    }

    public function getRoomAccess($rid)
    {
        $return = [];
        $redis = $this->make('redis');
        $now = time();
        /** 判断房间一对多 */
        if ($redis->exists("hroom_status:" . $rid . ":7") && $redis->hget("hroom_status:" . $rid . ":7", "status") == '1') {
            $onetomores = $redis->sMembers('hroom_whitelist_key:' . $rid);
            foreach ($onetomores as $onetomore) {
                $room = $redis->hGetAll("hroom_whitelist:$rid:$onetomore");
                $starttime = strtotime($room['starttime']);
                $endtime = strtotime($room['endtime']);
                if ($now >= $starttime && $now < $endtime) {//正在一对多
                    $return['onetomore'] = $room;
                    $return['onetomore']['id'] = $onetomore;
                    $return['onetomore']['access'] = 0;
                    if (Auth::id()) {
                        /** 判断用户是否购买 */
                        $uids = array_merge(explode(',', $room['uids']), explode(',', isset($room['tickets']) ? $room['tickets'] : ''));
                        $uids = array_filter($uids);
                        if (in_array(Auth::id(), $uids)) {
                            $return['onetomore']['access'] = 1;
                        }
                    }
                    break;
                }
            }
        }

        /** 一对一 */
        if ($redis->hget("hroom_status:" . $rid . ":4", "status") == 1) {
            $ordMap = $redis->hgetall("hroom_duration:" . $rid . ":4");
            if ($ordMap) {
                foreach ($ordMap as $k => $v) {
                    $ord = json_decode($v, true);
                    if (!$ord || $ord['status'] != 0) continue;

                    $starttime = strtotime($ord['starttime']);
                    $endtime = $starttime + $ord['duration'];
                    if ($now >= $starttime && $now <= $endtime) {
                        $return['ord'] = $ord;
                        $return['ord']['access'] = Auth::id() == $ord['reuid'] ? 1 : 0;
                    }
                }
            }
        }
        /** 时长房 */
        if ($redis->exists("hroom_status:" . $rid . ":6") && $redis->hget("hroom_status:" . $rid . ":6", "status") == '1') {
            if ($redis->hget("htimecost:" . $rid, "timecost_status")) {
                $timecost = $redis->hget("hroom_status:" . $rid . ":6", "timecost") ?: 0;
                $discount = $redis->hget('hgroups:special:' . $this->userInfo['vip'], 'discount') ?: 10;
                $return['timecost'] = [
                    'price' => $timecost,
                    'discount' => $discount,
                    'discountValue' => ceil($timecost * $discount / 10)
                ];
            }
        }
        return JsonResponse::create($return);
    }

    /*
     * app创建一对多房间接口 by desmond
     */
    public function createOne2More()
    {

        $start_time = $this->make('request')->get('date');
        $hour = $this->make('request')->get('hour');
        $minute = $this->make('request')->get('minute');
        //   $tid = $this->make('request')->get('tid');
        $duration = $this->make('request')->get('duration');
        $points = intval($this->make('request')->get('points'));
        $origin = $this->make('request')->get('origin', 11);

        if (!in_array($duration, array(20,25,30,35,40,45,50,55,60))) return new JsonResponse(array('status' => 9, 'msg' => '请求错误'));
        if ($points>99999 || $points<=0) return new JsonResponse(array('status' => 3, 'msg' => '金额超出范围'));

        if (empty($start_time) || empty($duration)) return new JsonResponse(array('status' => 4, 'msg' => '请求错误'));
        $start_time = date("Y-m-d H:i:s", strtotime($start_time . ' ' . $hour . ':' . $minute . ':00'));

        if (date("Y-m-d H:i:s") > date("Y-m-d H:i:s", strtotime($start_time))) return new JsonResponse(array('status' => 6, 'msg' =>'不能设置过去的时间'));

        //$room_config = $this->getRoomStatus(Auth::id(),7);
        $endtime = date('Y-m-d H:i:s',strtotime($start_time)+$duration * 60);

        if(!$this->notSetRepeat($start_time,$endtime)) return new JsonResponse(array('status' => 2, 'msg' => '你这段时间和一对一或一对多有重复的房间'));

        //添加
        /** @var \Redis $redis */
        $redis = $this->make('redis');

        $uids = '';
        $tickets = 0;

        //如果结束时间在记录之前并且未结速，则处理。否则忽略
        $now = date('Y-m-d H:i:s');
        $lastRoom = RoomOneToMore::where('uid',Auth::id())->where('endtime','>',$now)->where('status',0)->orderBy('endtime','asc')->first();
        //$preRoom = RoomOneToMore::where('starttime','>',$endtime)->where('uid',Auth::id())->where('endtime','>',$now)->where('status',0)->first();
        if(!$lastRoom || strtotime($lastRoom->starttime)>strtotime($endtime)){
            //当天消费,并且只能向后设置，固不用判断时间大于开始时间情况
            $macro_starttime = strtotime($start_time);
            $h = date('H');
            $etime='';
            if($h>6){
                $etime = strtotime(date('Y-m-d'))+30*3600;
            }else{
                $etime = strtotime(date('Y-m-d'))+6*3600;
            }
            if($macro_starttime<$etime){
                $user_send_gite = $redis->hGetAll('one2many_statistic:'.Auth::id());
                if($user_send_gite){
                    foreach ($user_send_gite as $k=>$v){
                        if($v>=$points){
                            $tickets +=1;   $uids .= $k.",";
                        }
                    }
                    $uids = substr($uids, 0, -1);
                }
            }
        }

        if(empty($uids)){
            return new JsonResponse(array('status' => 2, 'msg' => '没有用户满足送礼数，不允许创建房间'));
        }

        //$points = $room_config['timecost'];
        $oneToMoreRoom = new RoomOneToMore();
        $oneToMoreRoom->created = date('Y-m-d H:i:s');
        //  $oneToMoreRoom->uid = Auth::id();
        $oneToMoreRoom->uid = Auth::id();
        /*    $oneToMoreRoom->roomtid = $tid;*/
        $oneToMoreRoom->starttime = $start_time;
        $oneToMoreRoom->duration = $duration * 60;
        $oneToMoreRoom->endtime = $endtime;
        $oneToMoreRoom->status = 0;
        $oneToMoreRoom->tickets = $tickets;
        $oneToMoreRoom->points = $points;
        $oneToMoreRoom->origin = $origin;
        $oneToMoreRoom->save();

        if($uids){
            $uidArr = explode(',',$uids);
            $insertArr = [];
            foreach ($uidArr as $k=>$v){
                $temp = [];
                $temp['onetomore']=$oneToMoreRoom->id;
                $temp['rid']=Auth::id();
                $temp['type']=2;
                $temp['starttime']=$start_time;
                $temp['endtime']=$endtime;
                $temp['duration']=$duration*60;
                $temp['points']=$points;
                $temp['uid']=$v;
                $temp['origin']=12;
                array_push($insertArr,$temp);
            }
            DB::table('video_user_buy_one_to_more')->insert($insertArr);
        }

        //
        $duroom = $oneToMoreRoom;
        $redis->sAdd("hroom_whitelist_key:".$duroom['uid'],$duroom->id);

        $temp = [
            'starttime'=>$duroom['starttime'],
            'endtime'=>$duroom['endtime'],
            'uid'=>$duroom['uid'],
            'nums'=>$tickets,
            'uids'=>$uids,
            'points'=>$points,
        ];
        $rs = $this->make('redis')->hmset('hroom_whitelist:'.$duroom['uid'].':'.$duroom->id,$temp);

        $logPath = base_path() . '/storage/logs/one2more_' . date('Ym') . '.log';
        $one2moreLog = 'hroom_whitelist:'.$duroom['uid'].':'.$duroom->id.' '.json_encode($temp)."\n";
        $this->logResult('roomOneToMore  '.$one2moreLog,$logPath);

        return new JsonResponse(array('status' =>1, 'msg' =>'添加成功'));
    }

    /**
     * 删除一对多房间 by desmond
     * @return JsonResponse
     */
    public function delRoomOne2More()
    {

        $rid = $this->request()->input('rid');

        if (!$rid) return JsonResponse::create(['status' => 0, 'msg' => '请求错误']);
        $room = RoomOneToMore::find($rid);
        if (!$room) return new JsonResponse(array('status' => 0, 'msg' => '房间不存在'));

        if ($room->uid != Auth::id()) return JsonResponse::create(['status' => 0, 'msg' => '非法操作']);//只能删除自己房间
        if ($room->status == 1) return new JsonResponse(['status' => 0, 'msg' => '房间已经删除']);
        if ($room->purchase()->exists()) {
            return new JsonResponse(array('status' => 0, 'msg' => '房间已经被预定，不能删除！'));
        }

        $redis = $this->make('redis');
        $redis->sRem('hroom_whitelist_key:' . $room->uid, $room->id);
        $redis->delete('hroom_whitelist:' . $room->uid . ':' . $room->id);
        $room->update(['status' => 1]);
        return JsonResponse::create(['status' => 1, 'msg' => '删除成功']);
    }

    /*
    *  一对多房间记录接口by desmond
    */
    public function  listOneToMoreByHost(){

        //$this->isLogin() or die;

        $start_date = $this->request()->input('starttime') ? $this->request()->input('starttime') . ' 00:00:00' : date('0000-00-00 00:00:00');
        $end_date = $this->request()->input('endtime') ? $this->request()->input('endtime') . ' 23:59:59' : date('Y-m-d 23:59:59');
        $uid        =  Auth::id();

        if(isset($_GET['page'])){
            $pages = $_GET['page'];
        }else{
            $pages = 1;
        }
        $result['data'] = RoomOneToMore::where('uid','=',$uid)
            ->where('status','=', 0)
            ->where('starttime','>=',$start_date)
            ->where('starttime','<=',$end_date)
            ->orderBy('starttime','=', 'DESC')
            ->paginate(15)
            ->appends($_GET);

        return JsonResponse::create($result);
    }

    /*
   * 判断登录的主播是否开通一对多
   */
    public function competence(){
        $uid = $this->request()->input('uid')?:'';
        $key = 'hroom_status:'.$uid.':7';
        $keys = 'hroom_status:'.$uid.':4';
        $listonetomany = $this->make('redis')->hGetAll($key);
        $listonetoone = $this->make('redis')->hGetAll($keys);
        $result[0] = $listonetomany;
        $result[1] = $listonetoone;
        if($listonetoone && $listonetoone){
            return JsonResponse::create(['status' => 1, 'data' => $result]);
        }else{
            return JsonResponse::create(['status' => 0, 'data' => '']);
        }

    }

    /*
    *  直播记录接口 by desmond
    */
    public function showlist(){

        $start_time =  $this->request()->input('starttime') ? strtotime($this->request()->input('starttime') . ' 00:00:00') : strtotime('-1 month');
        $end_time   =  $this->request()->input('endtime') ? strtotime($this->request()->input('endtime') . ' 23:59:59') : strtotime('tomorrow') - 1;
        $uid        =  Auth::id();

        $result = LiveList::where('uid','=',$uid)
            ->where('start_time','>=', date("Y-m-d H:i:s",$start_time))
            ->where('start_time','<=', date("Y-m-d H:i:s",$end_time))
            ->select('id','created','start_time','rid','duration')
            ->get();



        $liveinfo = array();
        $duration_total = 0;
        foreach($result as $key=>$value){
            $liveinfo[$key]['id']      = $value['id'];
            $liveinfo[$key]['start_time']  = $value['start_time'];
            $liveinfo[$key]['end_time'] = date("Y-m-d H:i:s",strtotime($value['start_time'] )+ $value['duration']);
            $liveinfo[$key]['duration']  = $value['duration'];
            $duration_total = $duration_total+$value['duration'];
        }
        $getinfo['list'] = $liveinfo;
        $getinfo['duration_total'] = $duration_total;
        return JsonResponse::create($getinfo);


    }

}