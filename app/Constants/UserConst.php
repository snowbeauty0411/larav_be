<?php

namespace App\Constants;

class UserConst
{
    const ACTIVATION_ACCOUNT_PATH = '/signup/create/';
    const DEFAULT_PASSWORD = 'password_1234';
    const TIME_ACTIVATE = 1 ;// hour
    const USER_GUARD = 'users';
    const ADMIN_GUARD = 'admins';
    const BUYER_GUARD = 'buyers';
    const SELLER_GUARD = 'sellers';
    const RESET_PASSWORD_PATH = '/password/reset/';
    const RESET_MAIL_PATH = '/mail/reset/';
    const TIME_EXPIRE_OTP = 5;//minute
    const LOGIN_FAILED_LIMIT = 5;
    const LOGIN_BLOCK_TIME = 10; // minute
    const TO_DO_LIST = array(
        '0'=>'本人確認資料に不備がありました。再提出をお願いします。',
        '1'=>'の取引チャットメッセージが届いています。返信をお願いします。',
        '2'=>'の契約に合意しました。売主合意をお願いします。');
    const VERYFI_INDENTITY_TYPE = 0;
    const RECIVER_MESSAGE_TYPE = 1;
    // account status
    const USING = 1;
    const REGISTER = 2;
    const UNUSED = 3;
    const UNSUBSCRIBED = 4;
    const BLOCKED = 1;
    const ADMIN_EMAIL='cheat.test.info.123@gmail.com';
    const CHEAT_TEST_EMAIL='subsq.test@gmail.com';
    public static function const()
    {
        return [
            // 利用状況
            'accountStatusId' => [
                1 => '利用中',
                2 => '登録中',
                3 => '利用停止中',
                4 => '退会済み',
            ],

            // 性別
            'sex' => [
                1 => '男性',
                2 => '女性',
                3 => '無回答',
            ],
        ];
    }

    /**
     * 利用状況一覧を返す
     *
     * @param  bool
     * @return array
     */
    public static function accountStatusId($isSearch = false)
    {
        if ($isSearch) {
            return ['all' => 'すべて'] + static::const()['accountStatusId'];;
        }
        return static::const()['accountStatusId'];
    }

    /**
     * 性別一覧を返す
     *
     * @return array
     */
    public static function sex()
    {
        return static::const()['sex'];
    }
}
