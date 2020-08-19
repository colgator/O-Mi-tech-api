<?php

return [
    'success' => '成功',
    'fail' => '失敗',
    'apiError' => 'API操作错误',
    'page_not_found' => '找不到该页面!',
    'successfully_obtained' => '获取成功',
    'failed_to_obtained' => '获取失败',
    'unknown_error' => '操作出现未知错误',
    'permission_denied' => '没有权限!',
    'unknown_user' => '用户错误',
    'captcha_error' => '验证码错误',
    'must_login_on_platform' => '请由平台网站登入',
    'is_vip' => '目前已是贵族身份，无法使用喔！',
    'exchanged_failed' => '兑换失败',
    'exchanged_successful' => '兑换成功',
    'sent_successful' => '发送成功',
    'sent_failed' => '发送失败',
    'successfully_opened' => '开通成功',
    'opened_failed' => '开通失敗',
    'modified_successfully' => '修改成功',
    'illegal_operation' => '非法操作',
    'set_successfully' => '设置成功',
    'request_error' => '请求错误',
    'out_of_money' => '对不起！您的钻石余额不足!',
    'not_logged_in' => '未登录!',
    'failed_to_get_userInfo' => '用户信息获取失败!',
    'Guardian.getSetting.setting_is_empty' => '设定為空',
    'Guardian.buy.class_not_active' => '守护系统 :day 天方案未啟用',
    'Guardian.buy.user_point_not_enough' => '用戶鑽石不足,無法開通',
    'Guardian.buy.level_is_high' => '用戶现在的级别已大於要開通/續費的等级',
    'Guardian.buy.only_renewal' => '用戶已開通該級別守護，故僅能續費该等級守護',
    'Guardian.buy.only_active' => '用戶尚未開通該級別守護，故無法續費',
    'Api.reg.ip_block' => '来自您当前 IP 的注册数量过多，已暂停注册功能，请联系客服处理。',
    'Api.reg.invalid_request' => '无效的请求',
    'Api.reg.mobile_is_used' => '对不起, 该手机号已被使用!',
    'Api.reg.captcha_error' => '验证码错误',
    'Api.reg.username_wrong_format' => '注册邮箱不符合格式！(5-30位的邮箱)',
    'Api.reg.nickname_wrong_format' => '注册昵称不能使用/:;\空格,换行等符号！(2-11位的昵称)',
    'Api.reg.nickname_is_lawbreaking' => '昵称中含有非法字符，请修改后再提交!',
    'Api.reg.password_is_not_the_same' => '两次密码输入不一致!',
    'Api.reg.password_wrong_format' => '注册密码不符合格式!',
    'Api.reg.username_is_used' => '对不起, 该帐号已被使用!',
    'Api.reg.nickname_is_used' => '对不起, 该昵称已被使用!',
    'Api.reg.nickname_repeat' => '昵称已被注册或注册失败',
    'Api.reg.redis_token_error' => 'token 寫入redis失敗，請重新登錄',
    'Api.reg.please_login' => '请重新登陆!',
    'Activity.detailtype.wrong_type' => '配置的链接错误或者type类型错误',
    'Api.getUserByDes.invalid_user' => '无效用户',
    'Api.platExchange.processing' => '已送出，请耐心等待审核',
    'Api.platExchange.Already_exist' => '已存在审核中的订单',
    'Api.getTimeCountRoomDiscountInfo.not_vip' => '非贵族',
    'Api.getTimeCountRoomDiscountInfo.permission_denied' => '无权限组',
    'Api.aa.login_permission_denied' => '您的账号已经被禁止登录，请联系客服！',
    'Api.platform.validate_failed' => '您的账号已经被禁止登录，请联系客服！',
    'Api.platform.wrong_param' => ':num 接入方提供参数不对',
    'Api.platform.closed' => '接入已关闭',
    'Api.platform.wrong_sign' => '接入方校验失败',
    'Api.platform.data_acquisition_failed' => '接入方数据获取失败  :url  :data  返回: :res',
    'Api.platform.uuid_does_not_exist' => '接入方uuid不存在',
    'Api.platform.empty_nickename' => '接入方用户名为空',
    'Api.platform.user_does_not_exist' => '用户不存在 :user  :uid  :res',
    'Api.platform.failed_to_get_userInfo' => '获取用户信息失败 :user  :uid  :res',
    'Api.platform.wrong_username_or_pwd' => '用户名密码错误',
    'Api.get_lcertificate.out_of_ticket' => '票据用完或频率过快',
    'Api.Follow.search_following' => '关注查詢',
    'Api.Follow.can_not_follow_yourself' => '请勿关注自己',
    'Api.Follow.already_followed' => '已关注',
    'Api.Follow.not_followed' => '未关注',
    'Api.Follow.limit' => '您已经关注了1000人了，已达上限，请清理一下后再关注其他人吧',
    'Api.Follow.followed_success' => '关注成功',
    'Api.Follow.can_not_repeat_follow' => '请勿重复关注',
    'Api.Follow.unfollowed_success' => '取消关注成功',
    'Api.Follow.unfollowed_failed' => '取消关注失败',
    'Api.letter.word_limit' => '内容不能为空且字符长度限制200字符以内!',
    'Api.letter.lv_rich_limit' => '财富等级达到二富才能发送私信哦，请先去给心爱的主播送礼物提升财富等级吧.',
    'Api.letter.mail_amount_limit' => '本日发送私信数量已达上限，请明天再试！',
    'Api.coverUpload.empty_img' => '封面图不能为空',
    'Api.coverUpload.zimg_upload_failed' => 'zimg上传失败',
    'Api.coverUpload.upload_success' => '上传成功',
    'Api.coverUpload.upload_failed' => '上传失败',
    'Api.rankListGift.empty_uid' => '请输入会员id',
    'Api.rankListGift.empty_data' => '数据为空',
    'Api.coverUpload.upload_service_error' => '获取图片服务器失败',
    'Api.coverUpload.upload_error' => '上传图片错误',
    'Api.coverUpload.wrong_format' => '图片格式错误',
    'Api.coverUpload.size_limit' => '图片上传超过限制大小',
    'Api.coverUpload.out_of_space' => '你的个人相册空间不足!',
    'Api.coverUpload.system_error' => '系统错误,不支持上传功能!',
    'BackPack.use_item_failed' => '使用失敗',
    'BackPack.useItem.is_vip' => '目前已是贵族身份，无法使用喔！',
    'Mobile.userInfo.invalid_user' => '无效的用户',
    'Mobile._getEquipHandle.use_in_room' => '该道具限房间内使用,不能装备！',
    'Mobile.login.password_required' => '用户名密码不能为空',
    'Mobile.login.account_block_30days_no_show' => '您超过30天未开播，账号已被冻结，请联系客服QQ: :S_qq',
    'Mobile.login.password_error' => '用户名密码错误',
    'Mobile.login.token_error' => 'token写入redis失败，请重新登录!',
    'Mobile.statistic.param_error' => '请求参数错误',
    'Mobile.getFans.host_id_not_exist' => '该主播id不存在！',
    'Mobile.passwordChange.old_password_required' => '原始密码不能为空！',
    'Mobile.passwordChange.more_or_equal_than_six_char_length' => '请输入大于或等于6位字符串长度',
    'Mobile.passwordChange.new_password_is_not_the_same' => '新密码两次输入不一致!',
    'Mobile.passwordChange.old_password_is_wrong' => '原始密码错误!',
    'Mobile.passwordChange.new_and_old_is_the_same' => '新密码和原密码相同',
    'Mobile.passwordChange.modify_failed' => '修改失败!',
    'Mobile.loginmsg.no_data' => '無資料',
    'Charge.block_msg' => '尊敬的用户，您好，您今日的充值申请已达上限，请点击在线客服，让我们协助您，感谢您的支持与理解！',
    'Charge.order.charge_error' => '需要充值请联系客服！！！',
    'Charge.del.the_record_is_not_yours' => '这条纪录不是你的',
    'Charge.del.success' => '删除成功',
    'Charge.pay.please_enter_right_price' => '请输入正确的金额!',
    'Charge.pay.please_select_top_up_way' => '请选择充值渠道',
    'Charge.pay.the_top_up_channel_is_not_open' => '充值渠道未开放',
    'Charge.pay.one_pay_error' => '请联系客服，错误代码  :onePayError',
    'Charge.exchange.orderID_is_empty' => '没有订单号！',
    'Charge.exchange.order_is_not_exist' => '该订单号不存在！',
    'Charge.exchange.empty_status' => '状态不正确！',
    'Charge.processGD.empty_name' => '请输入名称',
    'Charge.processGD.limit' => '1小时内，不能提同一金额，同一姓名的订单',
    'Charge.back2Charge.accept' => ' 已接受!',
    'Charge.back2Charge.dealing_with_it' => ' 处理中!',
    'Charge.back2Charge.success' => ' 处理成功！',
    'Charge.back2Charge.failed' => ' 处理失败！',
    'Charge.callFailOrder.failed' => '传入的数据存在问题',
    'Charge.notice.sign_wrong' => '订单号： :tradeno 签名没有通过！',
    'Charge.orderHandler.already_done' => ':tradeno 订单号： 数据已处理完毕，请查看"充值记录"！',
    'Charge.checkCharge.success' => '该订单号已经成功支付,请返回会员中心的"充值记录"查看！',
    'Charge.checkCharge.failed' => '该订单号支付已经失败,请返回会员中心的"充值记录"查看！',
    'Charge.checkCharge.error' => '充提查询接口出问题：  :errstr',
    'Charge.checkCharge.top_up_failed' => '订单未成功支付!',
    'Charge.checkCharge.result' => '该订单充值 :msg ！',
    'Game.entry.connect_failed' => '服务器连接失败',
    'Game.deposit.status_down' => '設置狀態為關閉',
    'Game.deposit.amount_required' => '無儲值金額',
    'Game.deposit.failed' => '儲值失敗',
    'Game.gameList.maintained' => '小遊戲目前維修中',
    'Login.solveMobileLogin.account_block_30days_no_show' => '您超过30天未开播，账号已被冻结，请联系客服QQ: :S_qq',
    'Login.solveMobileLogin.password_modify' => '密码修改',
    'Login.solveMobileLogin.is_logout' => '您已退出登录',
    'Login.solveUserLogin.account_password_required' => '用户名或密码不能为空',
    'Login.solveUserLogin.captcha_wrong' => '验证码错误，请重新输入！',
    'Login.solveUserLogin.account_block_30days_no_show' => '您超过30天未开播，账号已被冻结，请联系客服QQ: :S_qq',
    'Login.solveUserLogin.password_modify' => '密码修改',
    'Login.solveUserLogin.account_password_wrong' => '用户名密码错误！',
    'Login.solveUserLogin.success' => '登录成功',
    'Reg.nickname.the_same_ip_too_many' => '来自相同 IP 的注册数量过多，已暂停注册功能，请联系客服处理。',
    'Sms.send.invalid_request' => '无效的请求',
    'Sms.send.the_phone_has_been_use' => '对不起, 该手机号已被使用!',
    'Sms.send.not_registered' => '手机号尚未注册!',
    'Sms.send.send_success' => '成功发送!',
    'Task.billTask.receive_success' => '领取成功！',
    'Task.billTask.receive_failed' => '领取失败！请查看任务是否完成或已经领取过了！',
    'Room.index.the_room_is_not_exist' => '房间不存在',
    'Room.index.has_been_kick' => '您被踢出房间，请等待 :time 分钟后重试',
    'Room.index.has_been_other_buy' => '已被其他用户购买',
    'Room.index.must_buy' => '未购买该房间',
    'Room.index.password_is_wrong' => '密码不正确',
    'Room.roommid.param_is_wrong' => '参数错误',
    'Room.roommid.password_room_is_not_exist' => '密码房不存在',
    'Room.roommid.one2one_lack_id' => '一对一缺少场次id信息',
    'Room.roommid.one2one_is_not_exist' => '此一对一房间场次不存在',
    'Room.roommid.one2one_had_been_end' => '该场次已经结束',
    'Room.roommid.one2one_you_had_been_reservation' => '您已经预约过该场次',
    'Room.roommid.one2one_had_been_reservation' => '该场次已被预约',
    'Room.roommid.one2many_lack_id' => '一对多缺少场次id信息',
    'Room.roommid.one2many_is_not_open' => '此一对多房间没有开任何场次',
    'Room.roommid.one2many_had_been_end' => '该场次已经结束',
    'Room.roommid.one2many_you_had_been_reservation' => '您已经预约过该场次',
    'Room.roommid.wrong_type' => '房间类型错误',
    'Password.sendVerifyMail.already_validation' => '你已验证过安全邮箱,不用再次验证！',
    'Password.sendVerifyMail.mail_invalid' => '安全邮箱地址格式不正确',
    'Password.sendVerifyMail.mail_is_been_use' => '此安全邮件已被使用',
    'Password.sendVerifyMail.send_failed' => '发送失败！',
    'Password.sendVerifyMail.send_success' => '发送成功！',
    'Password.VerifySafeMail.validate_link_invalid' => '验证链接已过期！',
    'Password.VerifySafeMail.already_validation' => '你已验证过安全邮箱！',
    'Password.VerifySafeMail.mail_is_been_link_account' => '对不起！该邮箱已绑定其他帐号！',
    'Password.VerifySafeMail.update_failed' => '更新安全邮箱失败！',
    'Password.VerifySafeMail.update_success' => '更新安全邮箱成功！',
    'Password.pwdreset.send_success' => '邮件发送成功',
    'Password.pwdResetByMobile.invalid_request' => '无效的请求',
    'Password.pwdResetByMobile.err_invalid_format' => '手机号格式错误',
    'Password.pwdResetByMobile.err_verify_failed' => '验证号码错误',
    'Password.pwdResetSendFromMobile.mail_wrong_format' => '邮箱格式不正确',
    'Password.pwdResetSendFromMobile.mail_not_validate' => '该邮箱没有通过安全邮箱验证, 验证安全邮箱才能使用此功能。',
    'Password.pwdResetSendFromMobile.validate_code_send_success' => '邮箱验证码发送成功',
    'Password.pwdResetConfirmFromMobile.mail_wrong_format' => '邮箱格式不正确',
    'Password.pwdResetConfirmFromMobile.validate_code_is_wrong' => '邮箱验证码错误',
    'Password.pwdResetConfirmFromMobile.mail_not_validate' => '该邮箱没有通过安全邮箱验证, 验证安全邮箱才能使用此功能。',
    'Password.pwdResetConfirmFromMobile.send_success' => '邮件发送成功',
    'Password.pwdResetSubmit.mail_wrong_format' => '邮箱格式不正确',
    'Password.pwdResetSubmit.mail_not_validate' => '该邮箱没有通过安全邮箱验证, 验证安全邮箱才能使用此功能。',
    'Password.pwdResetSubmit.send_failed' => '发送失败！',
    'Password.pwdResetSubmit.validate_link_invalid' => '链接已过期',
    'Password.pwdResetSubmit.password_format_invalid' => '密码格式无效！',
    'Password.pwdResetSubmit.twice_enter_not_the_same' => '两次输入的密码不一致',
    'Password.pwdResetSubmit.modify_success' => '密码修改成功',
    'MobileRoom.checkPwd.unknown_error' => '密码房异常,请联系运营重新开启一下密码房间的开关',
    'MobileRoom.checkPwd.room_id_is_wrong' => '房间号错误!',
    'MobileRoom.checkPwd.captcha_required' => '请输入验证码!',
    'MobileRoom.checkPwd.captcha_error' => '验证码错误!',
    'MobileRoom.checkPwd.password_format_wrong' => '密码格式错误!',
    'MobileRoom.checkPwd.password_is_wrong' => '密码错误!',
    'MobileRoom.checkPwd.validation_success' => '验证成功',
    'MobileRoom.geterrorsAction.room_id_wrong' => '房间号错误!',
    'MobileRoom.getRoomConf.room_is_not_exist' => '房间不存在',
    'MobileRoom.roomSetDuration.more_than_2000_points' => '手动设置的钻石数必须大于2000钻石',
    'Member.transfer.wrong_pwd' => '交易密码错误',
    'Member.transfer.transfer_to_owner' => '不能转给自己!',
    'Member.transfer.wrong_points' => '转帐金额错误!',
    'Member.transfer.wrong_user' => '对不起！该用户不存在',
    'Member.transfer.permission_denied' => '对不起！您没有该权限！',
    'Member.transfer.transfer_failed' => '对不起！转帐失败!',
    'Member.transfer.transfer_success' => '您成功转出:points钻石',
    'Member._getEquipHandle.equipment_room_only' => '对不起！该道具限房间内使用,不能装备！',
    'Member._getEquipHandle.equip_success' => '装备成功',
    'Member.passwordChange.successfully_modified' => '修改成功!请重新登录',
    'Member.passwordChange.can_not_setting' => '尚有未结束的一对多房间时，无法设定',
    'Member.roomSetTimecost.timecost_wrong' => '金额设置有误',
    'Member.roomSetTimecost.can_not_setting' => '时长房直播中,不能设置',
    'Member.roomSetDuration.max_setting' => '金额超出范围,不能大于99999钻石',
    'Member.roomSetDuration.min_setting' => '手动设置的钻石数必须大于2000钻石',
    'Member.roomSetPwd.pwd_empty' => '密码不能为空',
    'Member.roomSetPwd.wrong_pwd_format' => '密码格式不对',
    'Member.roomSetPwd.close_pwd_success' => '密码关闭成功',
    'Member.checkroompwd.pwd_room_error' => '密码房异常,请联系运营重新开启一下密码房间的开关',
    'Member.checkroompwd.wrong_roomID' => '房间号错误!',
    'Member.checkroompwd.please_enter_verify_pwd' => '请输入验证码!',
    'Member.checkroompwd.wrong_pwd_format' => '密码格式错误!',
    'Member.checkroompwd.wrong_pwd' => '密码错误!',
    'Member.checkroompwd.verified_successfully' => '验证成功',
    'Member.doReservation.reserved_room_not_exist' => '您预约的房间不存在',
    'Member.doReservation.room_offline' => '当前的房间已经下线了，请选择其他房间。',
    'Member.doReservation.room_already_reserved' => '当前的房间已经被预定了，请选择其他房间。',
    'Member.doReservation.room_forbidden_yourself' => '自己不能预约自己的房间',
    'Member.doReservation.room_reservation_repeat' => '您这个时间段有房间预约了，您确定要预约么',
    'Member.doReservation.reserve_successfully' => '预约成功',
    'Member.domsg.can_not_send_to_yourself' => '不能给自己发私信!',
    'Member.domsg.receiver_not_exist' => '接受者用户不存在！',
    'Member.domsg.msg_length_limit' => '输入为空或者输入内容过长，字符长度请限制200以内！',
    'Member.domsg.lv_rich_limit' => '财富等级达到二富才能发送私信哦，请先去给心爱的主播送礼物提升财富等级吧。',
    'Member.domsg.out_of_msg' => '本日发送私信数量已达上限，请明天再试！',
    'Member.domsg.send_msg_successfully' => '私信发送成功!',
    'Member.domsg.send_msg_failed' => '私信发送失败!',
    'Member.withdraw.withdraw_min_limit' => '每次提现不能少于200!',
    'Member.withdraw.withdraw_max_limit' => '提现金额不能大于可用余额!',
    'Member.withdraw.withdraw_successfully' => '申请成功！请等待审核',
    'Member.roomUpdateDuration.room_already_been_reserved' => '房间已经被预定，不能被修改',
    'Member.roomUpdateDuration.set_max_limit' => '只能设置未来七天以内',
    'Member.roomUpdateDuration.set_min_limit' => '不能设置过去的时间',
    'Member.roomUpdateDuration.time_repeat' => '这一时段内有重复哦',
    'Member.buyVip.vip_status_error' => '该贵族状态异常,请联系客服！',
    'Member.buyVip.same_vip_limit' => '你已开通过此贵族，你可以保级或者开通高级贵族！',
    'Member.buyVip.buy_vip_limit' => '请现有等级过期后再开通，或开通高等级的贵族！',
    'Member.buyVip.buy_vip_failed' => '可能由于网络原因，开通失败！',
    'Member.getVipMount.already_have_this_ride' => '你已经获取过了该坐骑！',
    'Member.getVipMount.vip_only_ride' => '此坐骑专属贵族所有！',
    'Member.getVipMount.not_qualified_to_ride' => '你还不够领取此级别的坐骑！',
    'Member.ajax.uid_empty' => 'uid为空',
    'Member.ajax.data_empty' => '获取数据为空',
    'Member.ajax.success' => '更新成功',
    'Member.pay.failed_to_buy' => '购买失败！可能钱不够',
    'Member.pay.buy_successfully' => '购买成功',
    'Member.delRoomDuration.del_yourself_only' => '只能删除自己房间',
    'Member.delRoomOne2Many.room_deleted' => '房间已经删除',
    'Member.delRoomOne2Many.room_already_reserved' => '房间已经被预定，不能删除！',
    'Member.makeUpOneToMore.limit' => '不能购买自己房间亲',
    'Member.makeUpOneToMore.buy_ticket' => '您已有资格进入该房间，请从“我的预约”进入。',
    'Member.makeUpOneToMore.anchor_offline' => '主播不在播，不能购买！',
    'Member.makeUpOneToMore.failed_to_buy' => '购买失敗',
    'Member.buyModifyNickname.out_of_money' => '余额不足:price钻',
    'Member.buyModifyNickname.qualified' => '已有修改昵称资格',
    'Member.buyModifyNickname.failed' => '扣款失败',
    'Member.addOneToManyRoomUser.purchase_limit' => '主播不能购买自己的一对多',
    'Member.addOneToManyRoomUser.end' => '房间已经结束',
    'Member.addOneToManyRoomUser.success' => '添加成功',
    'Member.password.already_set_trade_pwd' => '你已设置交易密码,需要修改请联系客服进行重置!',
    'Member.password.incorrect_pwd' => '登录密码不正确!',
    'Member.password.setting_pwd_success' => '交易密码设置成功',
    'Member.password.setting_pwd_failed' => '交易密码设置失败',
    'Member.password.pwd_empty' => '交易密码不能为空',
    'Member.roomInfo.anchor_only' => '限主播使用!',
    'Member.roomInfo.string_length_limit' => '最多10个字',
    'Business.index.request_error' => '请求方法错误',
    'Business.index.failed' => '申请失败',
    'Business.signup.is_guest' => '请登录',
    'Business.signup.failed' => '申请失败',
    'Business._ajaxSigninHandle.unauthorized' => '对不起，你未登录，请登录后申请主播功能！',
    'Business._ajaxSigninHandle.has_been_apply' => '对不起，你已申请了主播功能！',
    'Business._ajaxSigninHandle.apply_data_empty' => '请把资料填写完整!',
    'Business._ajaxSigninHandle.has_been_apply_for_wait' => '对不起，你已申请了主播功能,请等待审核！!',
    'Business._ajaxSigninHandle.before_cancel_wait_pass' => '你之前的主播身份已被取消，现在重新提交申请成功，请等待审核',
    'Business._ajaxSigninHandle.before_reject_wait_pass' => '你之前的申请已被驳回，现在重新提交申请，请等待审核！',
    'Business._ajaxSigninHandle.apply_success_wait_pass' => '提交申请成功，请耐心等待审核。',
    'Controller.doChangePwd.account_password_required' => '用户名或密码不能为空',
    'Controller.doChangePwd.invalid_password' => '密码不合法！',
    'Controller.doChangePwd.new_and_old_is_the_same' => '新旧密码不能相同',
    'Controller.doChangePwd.new_password_not_equal' => '新密码两次输入不一致',
    'Controller.doChangePwd.account_not_exist' => '用户名不存在',
    'Controller.doChangePwd.old_password_invalid' => '旧密码验证失败',
    'Controller.editUserInfo.invalid_submit' => '非法提交',
    'Controller.editUserInfo.nickname_format_error' => '注册昵称不能使用/:;\空格,换行等符号！(2-11位的昵称)',
    'Controller.editUserInfo.nickname_contain_invalid_char' => '昵称中含有非法字符，请修改后再提交!',
    'Controller.editUserInfo.nickname_repeat' => '昵称重复！',
    'Controller.editUserInfo.unable_modify' => '你已经不能修改昵称了！',
    'Controller.editUserInfo.update_success' => '更新成功！',
    'Member.roomOneToMore.room_min_limit' => '钻石数不能少于399钻石',
    'Member.roomOneToMore.setting_limit' => '只能设置基于当前时间 3小时内的一对多房间',
    'Member.roomOneToMore.time_repeat' => '你这段时间和一对一或一对多有重复的房间',
    'Member.signin.close' => '签到功能已关闭',
    'Member.signin.already_sign' => '今日已签到',
    'Member.signin.check_date' => '日期错误，请检查您的系统日期',
    'Shop.getPropInfo.not_login' => '请前往首页登录!',
    'Shop.getgroup.get_data_successfully' => '数据获取成功',
    'Shop.getgroup.get_data_failed' => '数据获取失败',
    'Room.roomSetDuration.room_limit' => '当前时间还有未开播或者正在开播的一对一',
    'Room.roomSetDuration.time_repeat' => '你这段时间有重复的房间',
    'Room.roomSetDuration.no_audience' => '没有用户满足送礼数，不允许创建房间',
];
