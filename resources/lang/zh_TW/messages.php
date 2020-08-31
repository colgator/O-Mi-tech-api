<?php

return [
    'success' => '成功',
    'fail' => '失敗',
    'apiError' => 'API操作錯誤',
    'page_not_found' => '找不到該頁面!',
    'successfully_obtained' => '獲取成功',
    'failed_to_obtained' => '獲取失敗',
    'unknown_error' => '操作出現未知錯誤',
    'permission_denied' => '沒有許可權!',
    'unknown_user' => '使用者錯誤',
    'captcha_error' => '驗證碼錯誤',
    'must_login_on_platform' => '請由平臺網站登入',
    'is_vip' => '目前已是貴族身份，無法使用喔！',
    'exchanged_failed' => '兌換失敗',
    'exchanged_successful' => '兌換成功',
    'sent_successful' => '傳送成功',
    'sent_failed' => '傳送失敗',
    'successfully_opened' => '開通成功',
    'opened_failed' => '開通失敗',
    'modified_successfully' => '修改成功',
    'illegal_operation' => '非法操作',
    'set_successfully' => '設定成功',
    'request_error' => '請求錯誤',
    'out_of_money' => '對不起！您的鑽石餘額不足!',
    'not_logged_in' => '未登入!',
    'return_format_error' => '返回格式錯誤',
    'failed_to_get_userInfo' => '使用者資訊獲取失敗!',
    'Guardian.name.1' => '黃色守護',
    'Guardian.name.2' => '紫色守護',
    'Guardian.name.3' => '黑色守護',
    'Guardian.getSetting.setting_is_empty' => '設定為空',
    'Guardian.buy.class_not_active' => '守護系統 :day 天方案未啟用',
    'Guardian.buy.user_point_not_enough' => '使用者鑽石不足,無法開通',
    'Guardian.buy.level_is_high' => '使用者現在的級別已大於要開通/續費的等級',
    'Guardian.buy.only_renewal' => '使用者已開通該級別守護，故僅能續費該等級守護',
    'Guardian.buy.only_active' => '使用者尚未開通該級別守護，故無法續費',
    'Guardian_buy_request.type_error' => '參數類型不正確',
    'Guardian_buy_request.required' => '需必填',
    'Api.reg.ip_block' => '來自您當前 IP 的註冊數量過多，已暫停註冊功能，請聯絡客服處理。',
    'Api.reg.invalid_request' => '無效的請求',
    'Api.reg.mobile_is_used' => '對不起, 該手機號已被使用!',
    'Api.reg.captcha_error' => '驗證碼錯誤',
    'Api.reg.username_wrong_format' => '註冊郵箱不符合格式！(5-30位的郵箱)',
    'Api.reg.nickname_wrong_format' => '註冊暱稱不能使用/:;\空格,換行等符號！(2-11位的暱稱)',
    'Api.reg.nickname_is_lawbreaking' => '暱稱中含有非法字元，請修改後再提交!',
    'Api.reg.password_is_not_the_same' => '兩次密碼輸入不一致!',
    'Api.reg.password_wrong_format' => '註冊密碼不符合格式!',
    'Api.reg.username_is_used' => '對不起, 該帳號已被使用!',
    'Api.reg.nickname_is_used' => '對不起, 該暱稱已被使用!',
    'Api.reg.nickname_repeat' => '暱稱已被註冊或註冊失敗',
    'Api.reg.redis_token_error' => 'token 寫入redis失敗，請重新登入',
    'Api.reg.please_login' => '請重新登陸!',
    'Activity.detailtype.wrong_type' => '配置的連結錯誤或者type類型錯誤',
    'Api.getUserByDes.invalid_user' => '無效使用者',
    'Api.platExchange.processing' => '已送出，請耐心等待稽覈',
    'Api.platExchange.Already_exist' => '已存在稽覈中的訂單',
    'Api.getTimeCountRoomDiscountInfo.not_vip' => '非貴族',
    'Api.getTimeCountRoomDiscountInfo.permission_denied' => '無許可權組',
    'Api.aa.login_permission_denied' => '您的賬號已經被禁止登入，請聯絡客服！',
    'Api.platform.wrong_param' => ':num 接入方提供參數不對',
    'Api.platform.closed' => '接入已關閉',
    'Api.platform.wrong_sign' => '接入方校驗失敗',
    'Api.platform.data_acquisition_failed' => '接入方資料獲取失敗  :url  :data  返回: :res',
    'Api.platform.uuid_does_not_exist' => '接入方uuid不存在',
    'Api.platform.empty_nickname' => '接入方使用者名稱為空',
    'Api.platform.user_does_not_exist' => '使用者不存在 :user  :uid  :res',
    'Api.platform.failed_to_get_userInfo' => '獲取使用者資訊失敗 :user  :uid  :res',
    'Api.platform.wrong_username_or_pwd' => '使用者名稱密碼錯誤',
    'Api.platform.room_does_not_exist' => '房間不存在',
    'Api.get_lcertificate.out_of_ticket' => '票據用完或頻率過快',
    'Api.Follow.search_following' => '關注查詢',
    'Api.Follow.can_not_follow_yourself' => '請勿關注自己',
    'Api.Follow.already_followed' => '已關注',
    'Api.Follow.not_followed' => '未關注',
    'Api.Follow.limit' => '您已經關注了1000人了，已達上限，請清理一下後再關注其他人吧',
    'Api.Follow.followed_success' => '關注成功',
    'Api.Follow.can_not_repeat_follow' => '請勿重複關注',
    'Api.Follow.unfollowed_success' => '取消關注成功',
    'Api.Follow.unfollowed_failed' => '取消關注失敗',
    'Api.letter.word_limit' => '內容不能為空且字元長度限制200字元以內!',
    'Api.letter.lv_rich_limit' => '財富等級達到二富才能傳送私信哦，請先去給心愛的主播送禮物提升財富等級吧.',
    'Api.letter.mail_amount_limit' => '本日傳送私信數量已達上限，請明天再試！',
    'Api.coverUpload.empty_img' => '封面圖不能為空',
    'Api.coverUpload.zimg_upload_failed' => 'zimg上傳失敗',
    'Api.coverUpload.upload_success' => '上傳成功',
    'Api.coverUpload.upload_failed' => '上傳失敗',
    'Api.rankListGift.empty_uid' => '請輸入會員id',
    'Api.rankListGift.empty_data' => '資料為空',
    'Api.coverUpload.upload_service_error' => '獲取圖片伺服器失敗',
    'Api.coverUpload.upload_error' => '上傳圖片錯誤',
    'Api.coverUpload.wrong_format' => '圖片格式錯誤',
    'Api.coverUpload.size_limit' => '圖片上傳超過限制大小',
    'Api.coverUpload.out_of_space' => '你的個人相簿空間不足!',
    'Api.coverUpload.system_error' => '系統錯誤,不支援上傳功能!',
    'BackPack.use_item_failed' => '使用失敗',
    'BackPack.useItem.is_vip' => '目前已是貴族身份，無法使用喔！',
    'backpack_request.wrong_type' => '參數類型不正確',
    'Mobile._getEquipHandle.use_in_room' => '該道具限房間內使用,不能裝備！',
    'Mobile.login.password_required' => '使用者名稱密碼不能為空',
    'Mobile.login.account_block_30days_no_show' => '您超過30天未開播，賬號已被凍結，請聯絡客服QQ: :S_qq',
    'Mobile.login.password_error' => '使用者名稱密碼錯誤',
    'Mobile.login.token_error' => 'token寫入redis失敗，請重新登入!',
    'Mobile.statistic.param_error' => '請求參數錯誤',
    'Mobile.getFans.host_id_not_exist' => '該主播id不存在！',
    'Mobile.passwordChange.old_password_required' => '原始密碼不能為空！',
    'Mobile.passwordChange.more_or_equal_than_six_char_length' => '請輸入大於或等於6位字元串長度',
    'Mobile.passwordChange.new_password_is_not_the_same' => '新密碼兩次輸入不一致!',
    'Mobile.passwordChange.old_password_is_wrong' => '原始密碼錯誤!',
    'Mobile.passwordChange.new_and_old_is_the_same' => '新密碼和原密碼相同',
    'Mobile.passwordChange.modify_failed' => '修改失敗!',
    'Mobile.loginmsg.no_data' => '無資料',
    'Mobile.userInfo.invalid_user' => '無效的使用者',
    'Charge.block_msg' => '尊敬的使用者，您好，您今日的充值申請已達上限，請點選線上客服，讓我們協助您，感謝您的支援與理解！',
    'Charge.order.charge_error' => '需要充值請聯絡客服！！！',
    'Charge.del.the_record_is_not_yours' => '這條紀錄不是你的',
    'Charge.del.success' => '刪除成功',
    'Charge.pay.please_enter_right_price' => '請輸入正確的金額!',
    'Charge.pay.please_select_top_up_way' => '請選擇充值渠道',
    'Charge.pay.the_top_up_channel_is_not_open' => '充值渠道未開放',
    'Charge.pay.one_pay_error' => '請聯絡客服，錯誤程式碼  :onePayError',
    'Charge.pay.pay_system_error' => '金流伺服器錯誤，請聯絡客服',
    'Charge_pay_request.wrong_price' => '請輸入正確的金額!',
    'Charge_pay_request.wrong_vip_level' => '渠道輸入不正確',
    'Charge_pay_request.wrong_mode_type' => '支付類型輸入不正確',
    'Charge_pay_request.wrong_name' => '名稱輸入不正確',
    'Charge.exchange.orderID_is_empty' => '沒有訂單號！',
    'Charge.exchange.order_is_not_exist' => '該訂單號不存在！',
    'Charge.exchange.empty_status' => '狀態不正確！',
    'Charge.processGD.empty_name' => '請輸入名稱',
    'Charge.processGD.limit' => '1小時內，不能提同一金額，同一姓名的訂單',
    'Charge.back2Charge.accept' => ' 已接受!',
    'Charge.back2Charge.dealing_with_it' => ' 處理中!',
    'Charge.back2Charge.success' => ' 處理成功！',
    'Charge.back2Charge.failed' => ' 處理失敗！',
    'Charge.callFailOrder.failed' => '傳入的資料存在問題',
    'Charge.notice.sign_wrong' => '訂單號： :tradeno 簽名沒有通過！',
    'Charge.orderHandler.already_done' => ':tradeno 訂單號： 資料已處理完畢，請檢視"充值記錄"！',
    'Charge.checkCharge.success' => '該訂單號已經成功支付,請返回會員中心的"充值記錄"檢視！',
    'Charge.checkCharge.failed' => '該訂單號支付已經失敗,請返回會員中心的"充值記錄"檢視！',
    'Charge.checkCharge.error' => '充提查詢介面出問題：  :errstr',
    'Charge.checkCharge.top_up_failed' => '訂單未成功支付!',
    'Charge.checkCharge.result' => '該訂單充值 :msg ！',
    'Game.entry.connect_failed' => '伺服器連線失敗',
    'Game.deposit.status_down' => '設定狀態為關閉',
    'Game.deposit.amount_required' => '無儲值金額',
    'Game.deposit.failed' => '儲值失敗',
    'Game.gameList.maintained' => '小遊戲目前維修中',
    'Login.solveMobileLogin.account_block_30days_no_show' => '您超過30天未開播，賬號已被凍結，請聯絡客服QQ: :S_qq',
    'Login.solveMobileLogin.password_modify' => '密碼修改',
    'Login.solveMobileLogin.is_logout' => '您已退出登入',
    'Login.solveUserLogin.account_password_required' => '使用者名稱或密碼不能為空',
    'Login.solveUserLogin.captcha_wrong' => '驗證碼錯誤，請重新輸入！',
    'Login.solveUserLogin.account_block_30days_no_show' => '您超過30天未開播，賬號已被凍結，請聯絡客服QQ: :S_qq',
    'Login.solveUserLogin.password_modify' => '密碼修改',
    'Login.solveUserLogin.account_password_wrong' => '使用者名稱密碼錯誤！',
    'Login.solveUserLogin.success' => '登入成功',
    'Reg.nickname.the_same_ip_too_many' => '來自相同 IP 的註冊數量過多，已暫停註冊功能，請聯絡客服處理。',
    'Sms.send.invalid_request' => '無效的請求',
    'Sms.send.the_phone_has_been_use' => '對不起, 該手機號已被使用!',
    'Sms.send.not_registered' => '手機號尚未註冊!',
    'Sms.send.send_success' => '成功傳送!',
    'Task.billTask.receive_success' => '領取成功！',
    'Task.billTask.receive_failed' => '領取失敗！請檢視任務是否完成或已經領取過了！',
    'Room.index.the_room_is_not_exist' => '房間不存在',
    'Room.index.has_been_kick' => '您被踢出房間，請等待 :time 分鐘後重試',
    'Room.index.has_been_other_buy' => '已被其他使用者購買',
    'Room.index.must_buy' => '未購買該房間',
    'Room.index.password_is_wrong' => '密碼不正確',
    'Room.roommid.param_is_wrong' => '參數錯誤',
    'Room.roommid.password_room_is_not_exist' => '密碼房不存在',
    'Room.roommid.one2one_lack_id' => '一對一缺少場次id資訊',
    'Room.roommid.one2one_is_not_exist' => '此一對一房間場次不存在',
    'Room.roommid.one2one_had_been_end' => '該場次已經結束',
    'Room.roommid.one2one_you_had_been_reservation' => '您已經預約過該場次',
    'Room.roommid.one2one_had_been_reservation' => '該場次已被預約',
    'Room.roommid.one2many_lack_id' => '一對多缺少場次id資訊',
    'Room.roommid.one2many_is_not_open' => '此一對多房間沒有開任何場次',
    'Room.roommid.one2many_had_been_end' => '該場次已經結束',
    'Room.roommid.one2many_you_had_been_reservation' => '您已經預約過該場次',
    'Room.roommid.wrong_type' => '房間類型錯誤',
    'Password.sendVerifyMail.already_validation' => '你已驗證過安全郵箱,不用再次驗證！',
    'Password.sendVerifyMail.mail_invalid' => '安全郵箱地址格式不正確',
    'Password.sendVerifyMail.mail_is_been_use' => '此安全郵件已被使用',
    'Password.sendVerifyMail.send_failed' => '傳送失敗！',
    'Password.sendVerifyMail.send_success' => '傳送成功！',
    'Password.VerifySafeMail.validate_link_invalid' => '驗證連結已過期！',
    'Password.VerifySafeMail.already_validation' => '你已驗證過安全郵箱！',
    'Password.VerifySafeMail.mail_is_been_link_account' => '對不起！該郵箱已繫結其他帳號！',
    'Password.VerifySafeMail.update_failed' => '更新安全郵箱失敗！',
    'Password.VerifySafeMail.update_success' => '更新安全郵箱成功！',
    'Password.pwdreset.send_success' => '郵件傳送成功',
    'Password.pwdResetByMobile.invalid_request' => '無效的請求',
    'Password.pwdResetByMobile.err_invalid_format' => '手機號格式錯誤',
    'Password.pwdResetByMobile.err_verify_failed' => '驗證號碼錯誤',
    'Password.pwdResetSendFromMobile.mail_wrong_format' => '郵箱格式不正確',
    'Password.pwdResetSendFromMobile.mail_not_validate' => '該郵箱沒有通過安全郵箱驗證, 驗證安全郵箱才能使用此功能。',
    'Password.pwdResetSendFromMobile.validate_code_send_success' => '郵箱驗證碼傳送成功',
    'Password.pwdResetConfirmFromMobile.mail_wrong_format' => '郵箱格式不正確',
    'Password.pwdResetConfirmFromMobile.validate_code_is_wrong' => '郵箱驗證碼錯誤',
    'Password.pwdResetConfirmFromMobile.mail_not_validate' => '該郵箱沒有通過安全郵箱驗證, 驗證安全郵箱才能使用此功能。',
    'Password.pwdResetConfirmFromMobile.send_success' => '郵件傳送成功',
    'Password.pwdResetSubmit.mail_wrong_format' => '郵箱格式不正確',
    'Password.pwdResetSubmit.mail_not_validate' => '該郵箱沒有通過安全郵箱驗證, 驗證安全郵箱才能使用此功能。',
    'Password.pwdResetSubmit.send_failed' => '傳送失敗！',
    'Password.pwdResetSubmit.validate_link_invalid' => '連結已過期',
    'Password.pwdResetSubmit.password_format_invalid' => '密碼格式無效！',
    'Password.pwdResetSubmit.twice_enter_not_the_same' => '兩次輸入的密碼不一致',
    'Password.pwdResetSubmit.modify_success' => '密碼修改成功',
    'Password.changePwd.illegal_username' => '使用者名稱不合法',
    'MobileRoom.checkPwd.unknown_error' => '密碼房異常,請聯絡運營重新開啟一下密碼房間的開關',
    'MobileRoom.checkPwd.room_id_is_wrong' => '房間號錯誤!',
    'MobileRoom.checkPwd.captcha_required' => '請輸入驗證碼!',
    'MobileRoom.checkPwd.captcha_error' => '驗證碼錯誤!',
    'MobileRoom.checkPwd.password_format_wrong' => '密碼格式錯誤!',
    'MobileRoom.checkPwd.password_is_wrong' => '密碼錯誤!',
    'MobileRoom.checkPwd.validation_success' => '驗證成功',
    'MobileRoom.geterrorsAction.room_id_wrong' => '房間號錯誤!',
    'MobileRoom.getRoomConf.room_is_not_exist' => '房間不存在',
    'MobileRoom.roomSetDuration.more_than_2000_points' => '手動設定的鑽石數必須大於2000鑽石',
    'Member.signin.close' => '簽到功能已關閉',
    'Member.signin.already_sign' => '今日已簽到',
    'Member.signin.check_date' => '日期錯誤，請檢查您的系統日期',
    'Member.transfer.wrong_pwd' => '交易密碼錯誤',
    'Member.transfer.transfer_to_owner' => '不能轉給自己!',
    'Member.transfer.wrong_points' => '轉賬金額錯誤!',
    'Member.transfer.wrong_user' => '對不起！該使用者不存在',
    'Member.transfer.permission_denied' => '對不起！您沒有該許可權！',
    'Member.transfer.transfer_failed' => '對不起！轉賬失敗!',
    'Member.transfer.transfer_success' => '您成功轉出:points鑽石',
    'Member._getEquipHandle.equipment_room_only' => '對不起！該道具限房間內使用,不能裝備！',
    'Member._getEquipHandle.equip_success' => '裝備成功',
    'Member.passwordChange.successfully_modified' => '修改成功!請重新登入',
    'Member.passwordChange.can_not_setting' => '尚有未結束的一對多房間時，無法設定',
    'Member.roomSetTimecost.timecost_wrong' => '金額設定有誤',
    'Member.roomSetTimecost.can_not_setting' => '時長房直播中,不能設定',
    'Member.roomSetDuration.max_setting' => '金額超出範圍,不能大於99999鑽石',
    'Member.roomSetDuration.min_setting' => '手動設定的鑽石數必須大於2000鑽石',
    'Member.roomSetPwd.pwd_empty' => '密碼不能為空',
    'Member.roomSetPwd.wrong_pwd_format' => '密碼格式不對',
    'Member.roomSetPwd.close_pwd_success' => '密碼關閉成功',
    'Member.checkroompwd.pwd_room_error' => '密碼房異常,請聯絡運營重新開啟一下密碼房間的開關',
    'Member.checkroompwd.wrong_roomID' => '房間號錯誤!',
    'Member.checkroompwd.please_enter_verify_pwd' => '請輸入驗證碼!',
    'Member.checkroompwd.wrong_pwd_format' => '密碼格式錯誤!',
    'Member.checkroompwd.wrong_pwd' => '密碼錯誤!',
    'Member.checkroompwd.verified_successfully' => '驗證成功',
    'Member.doReservation.reserved_room_not_exist' => '您預約的房間不存在',
    'Member.doReservation.room_offline' => '當前的房間已經下線了，請選擇其他房間。',
    'Member.doReservation.room_already_reserved' => '當前的房間已經被預定了，請選擇其他房間。',
    'Member.doReservation.room_forbidden_yourself' => '自己不能預約自己的房間',
    'Member.doReservation.room_reservation_repeat' => '您這個時間段有房間預約了，您確定要預約麼',
    'Member.doReservation.reserve_successfully' => '預約成功',
    'Member.domsg.can_not_send_to_yourself' => '不能給自己發私信!',
    'Member.domsg.receiver_not_exist' => '接受者使用者不存在！',
    'Member.domsg.msg_length_limit' => '輸入為空或者輸入內容過長，字元長度請限制200以內！',
    'Member.domsg.lv_rich_limit' => '財富等級達到二富才能傳送私信哦，請先去給心愛的主播送禮物提升財富等級吧。',
    'Member.domsg.out_of_msg' => '本日傳送私信數量已達上限，請明天再試！',
    'Member.domsg.send_msg_successfully' => '私信傳送成功!',
    'Member.domsg.send_msg_failed' => '私信傳送失敗!',
    'Member.withdraw.withdraw_min_limit' => '每次提現不能少於200!',
    'Member.withdraw.withdraw_max_limit' => '提現金額不能大於可用餘額!',
    'Member.withdraw.withdraw_successfully' => '申請成功！請等待稽覈',
    'Member.roomUpdateDuration.room_already_been_reserved' => '房間已經被預定，不能被修改',
    'Member.roomUpdateDuration.set_max_limit' => '只能設定未來七天以內',
    'Member.roomUpdateDuration.set_min_limit' => '不能設定過去的時間',
    'Member.roomUpdateDuration.time_repeat' => '這一時段內有重複哦',
    'Member.buyVip.vip_status_error' => '該貴族狀態異常,請聯絡客服！',
    'Member.buyVip.same_vip_limit' => '你已開通過此貴族，你可以保級或者開通高階貴族！',
    'Member.buyVip.buy_vip_limit' => '請現有等級過期後再開通，或開通高等級的貴族！',
    'Member.buyVip.buy_vip_failed' => '可能由於網路原因，開通失敗！',
    'Member.buyVip.first' => '您首次開通 :level_name 貴族，獲得了贈送禮包的 :gift_money 鑽石',
    'Member.buyVip.pass' => '貴族開通成功提醒：您已成功開通 :level_name 貴族，到期日： :exp ',
    'Member.getVipMount.already_have_this_ride' => '你已經獲取過了該坐騎！',
    'Member.getVipMount.vip_only_ride' => '此坐騎專屬貴族所有！',
    'Member.getVipMount.not_qualified_to_ride' => '你還不夠領取此級別的坐騎！',
    'Member.ajax.uid_empty' => 'uid為空',
    'Member.ajax.data_empty' => '獲取資料為空',
    'Member.ajax.success' => '更新成功',
    'Member.pay.failed_to_buy' => '購買失敗！可能錢不夠',
    'Member.pay.buy_successfully' => '購買成功',
    'Member.delRoomDuration.del_yourself_only' => '只能刪除自己房間',
    'Member.delRoomOne2Many.room_deleted' => '房間已經刪除',
    'Member.delRoomOne2Many.room_already_reserved' => '房間已經被預定，不能刪除！',
    'Member.makeUpOneToMore.limit' => '不能購買自己房間親',
    'Member.makeUpOneToMore.buy_ticket' => '您已有資格進入該房間，請從“我的預約”進入。',
    'Member.makeUpOneToMore.anchor_offline' => '主播不在播，不能購買！',
    'Member.makeUpOneToMore.failed_to_buy' => '購買失敗',
    'Member.buyModifyNickname.out_of_money' => '餘額不足:price鑽',
    'Member.buyModifyNickname.qualified' => '已有修改暱稱資格',
    'Member.buyModifyNickname.failed' => '扣款失敗',
    'Member.addOneToManyRoomUser.purchase_limit' => '主播不能購買自己的一對多',
    'Member.addOneToManyRoomUser.end' => '房間已經結束',
    'Member.addOneToManyRoomUser.success' => '新增成功',
    'Member.password.already_set_trade_pwd' => '你已設定交易密碼,需要修改請聯絡客服進行重置!',
    'Member.password.incorrect_pwd' => '登入密碼不正確!',
    'Member.password.setting_pwd_success' => '交易密碼設定成功',
    'Member.password.setting_pwd_failed' => '交易密碼設定失敗',
    'Member.password.pwd_empty' => '交易密碼不能為空',
    'Member.roomInfo.anchor_only' => '限主播使用!',
    'Member.roomInfo.string_length_limit' => '最多10個字',
    'Member.roomOneToMore.room_min_limit' => '鑽石數不能少於399鑽石',
    'Member.roomOneToMore.setting_limit' => '只能設定基於當前時間 3小時內的一對多房間',
    'Member.roomOneToMore.time_repeat' => '你這段時間和一段一或一對多有重複的房間',
    'Shop.getPropInfo.not_login' => '請前往首頁登入!',
    'Shop.getgroup.get_data_successfully' => '資料獲取成功',
    'Shop.getgroup.get_data_failed' => '資料獲取失敗',
    'Business.index.request_error' => '請求方法錯誤',
    'Business.index.failed' => '申請失敗',
    'Business.signup.is_guest' => '請登入',
    'Business.signup.failed' => '申請失敗',
    'Business._ajaxSigninHandle.unauthorized' => '對不起，你未登入，請登入後申請主播功能！',
    'Business._ajaxSigninHandle.has_been_apply' => '對不起，你已申請了主播功能！',
    'Business._ajaxSigninHandle.apply_data_empty' => '請把資料填寫完整!',
    'Business._ajaxSigninHandle.has_been_apply_for_wait' => '對不起，你已申請了主播功能,請等待稽覈！!',
    'Business._ajaxSigninHandle.before_cancel_wait_pass' => '你之前的主播身份已被取消，現在重新提交申請成功，請等待稽覈',
    'Business._ajaxSigninHandle.before_reject_wait_pass' => '你之前的申請已被駁回，現在重新提交申請，請等待稽覈！',
    'Business._ajaxSigninHandle.apply_success_wait_pass' => '提交申請成功，請耐心等待稽覈。',
    'Controller.doChangePwd.account_password_required' => '使用者名稱或密碼不能為空',
    'Controller.doChangePwd.invalid_password' => '密碼不合法！',
    'Controller.doChangePwd.new_and_old_is_the_same' => '新舊密碼不能相同',
    'Controller.doChangePwd.new_password_not_equal' => '新密碼兩次輸入不一致',
    'Controller.doChangePwd.account_not_exist' => '使用者名稱不存在',
    'Controller.doChangePwd.old_password_invalid' => '舊密碼驗證失敗',
    'Controller.editUserInfo.invalid_submit' => '非法提交',
    'Controller.editUserInfo.nickname_format_error' => '註冊暱稱不能使用/:;\空格,換行等符號！(2-11位的暱稱)',
    'Controller.editUserInfo.nickname_contain_invalid_char' => '暱稱中含有非法字元，請修改後再提交!',
    'Controller.editUserInfo.nickname_repeat' => '暱稱重複！',
    'Controller.editUserInfo.unable_modify' => '你已經不能修改暱稱了！',
    'Controller.editUserInfo.update_success' => '更新成功！',
    'Index.setInRoomStat.invalid_param' => '無效的參數',
    'Index.setInRoomStat.not_time_room' => '不是時長房間',
    'Index.setInRoomStat.set_success' => '設定狀態成功',
    'Index.checkUniqueName.invalid_param' => '傳遞的參數非法！',
    'Index.checkUniqueName.invalid_email' => '註冊郵箱不符合格式！(5-30位的郵箱)',
    'Index.checkUniqueName.email_had_been_use' => '該郵箱已被使用，請換一個試試！',
    'Index.checkUniqueName.email_can_be_use' => '恭喜該郵箱可以使用。',
    'Index.checkUniqueName.nickname_format_error' => '註冊暱稱不能使用/:;\空格,換行等符號！(2-11位的暱稱',
    'Index.checkUniqueName.nickname_had_been_use' => '該暱稱已被使用，請換一個試試！',
    'Index.checkUniqueName.nickname_can_be_use' => '恭喜該暱稱可以使用。',
    'Index.getIndexInfo.success' => '獲取成功',
    'Index.complaints.success' => '處理成功！',
    'Index.complaints.content_required' => '缺少投訴內容',
    'Index.anchor_join.failed' => '獲取失敗',
    'RoomService.addOnetomore.request_error' => '請求錯誤',
    'RoomService.addOnetomore.amount_too_many' => '金額超出範圍,不能大於99999鑽石',
    'RoomService.addOnetomore.unable_set_past_time' => '不能設定過去的時間',
    'RoomService.addOnetomore.only_set_in_3hours' => '只能設定基於當前時間 3小時內的一對多房間',
    'RoomService.addOnetomore.room_repeat' => '你這段時間和一對一或一對多有重複的房間',
    'RoomService.addOnetomore.unable_create' => '沒有使用者滿足送禮數，不允許建立房間',
    'RoomService.addOnetomore.success' => '新增成功',
    'RoomService.addOnetoOne.request_error' => '請求錯誤',
    'RoomService.addOnetoOne.only_set_in_7days' => '只能設定未來七天以內',
    'RoomService.addOnetoOne.unable_set_past_time' => '不能設定過去的時間',
    'RoomService.addOnetoOne.room_repeat' => '當前時間還有未開播或者正在開播的一對一',
    'RoomService.addOnetoOne.success' => '新增成功',
    'RoomService.addOnetoOne.room_repeat_in_time' => '你這段時間有重複的房間',
    'Other.createHomeOneToManyList.create_room_successfully' => '建立直播間一對多資料成功',
    'SiteService.check_domain' =>'域名配置錯誤，請聯絡客服！',
    'SiteService.check_site_config' =>'站點配置缺失，請聯絡客服！',
    'SocketService.out_of_channel' =>'沒有可用channel',
    'SocketService.failed_tp_get_socket' =>'獲取Socket Channel失敗',
    'SmsService.try_again_later' =>'剛剛已傳送過，請稍後再試',
    'SmsService.curlPost_error' =>'請求狀態錯誤，HTTP error:  ',
    'Captcha.HTTP_ERROR_401' => '未授權的請求，請重啟應用後再嘗試',
    'Captcha.HTTP_ERROR_403' => '拒絕存取',
    'Captcha.HTTP_ERROR_410' => '驗證碼已逾期，請重啟應用後再嘗試',
    'GameEntryRequest.gp_id' => '輸入遊戲商ID不正確',
    'GameEntryRequest.game_code' => '輸入遊戲程式碼不正確',
    'ShareInstallLogRequest.origin' => '輸入來源不正確',
    'ShareInstallLogRequest.site_id' => '輸入站點ID不正確',
    'UserSetLocaleRequest.loc' => '輸入語系不正確',
    'Api.platform.not_empty' => '不能為空',
    'Api.platform.wrong_length' => '長度（數值）不對',
    'goods.category_id.1' => '熱門',
    'goods.category_id.2' => '推薦',
    'goods.category_id.3' => '高階',
    'goods.category_id.4' => '奢華',
    'goods.category_id.5' => '貴族',
];
