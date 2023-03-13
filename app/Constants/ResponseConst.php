<?php

namespace App\Constants;

class ResponseConst
{
    public static function const()
    {
        return [
            // 成功
            'success' => [
                'ok' => true,
                'message' => null,
            ],

            // システムエラー
            'system_error' => [
                'ok' => false,
                'message' => 'システムエラーが発生しています',
            ],

            // 認証エラー
            'token_error' => [
                'ok' => false,
                'message' => '認証トークンが指定されていないか、無効です',
            ],

            // 権限不足
            'authority_error' => [
                'ok' => false,
                'message' => '権限が不足しています',
            ],

            // ログインに成功
            'login_success' => [
                'ok' => true,
                'message' => 'ログインに成功しました',
            ],

            // ログインに失敗
            'login_error' => [
                'ok' => false,
                'message' => 'ログインに失敗しました。ID・パスワードを確認して再試行するか、時間を置いてから再試行してください',
            ],

            // メールアドレス重複エラー
            'email_unique_error' => [
                'ok' => false,
                'message' => 'そのメールアドレスはすでに使われています',
            ],

            '403' => [
                'ok' => false,
                'message' => 'Forbidden',
            ],

            '404' => [
                'ok' => false,
                'message' => 'Not Found',
            ],

            '405' => [
                'ok' => false,
                'message' => 'Method Not Allowed',
            ],

            '429' => [
                'ok' => false,
                'message' => 'Too Many Requests',
            ],

            '500' => [
                'ok' => false,
                'message' => 'Internal Server Error',
            ],
        ];
    }

    /**
     * メッセージ一覧を返す
     *
     * @param String $name
     * @return array
     */
    public static function message(String $name)
    {
        return static::const()[$name];
    }
}
