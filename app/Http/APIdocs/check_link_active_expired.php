<?php

/**
 *     @OA\Get(
 *     path="/api/signup/check/active-link/{token}",
 *     summary="Member information",
 *     tags={"new member"},
 *       @OA\Parameter(
 *         description="Token used to check the link",
 *         in="path",
 *         name="token",
 *         required=true,
 *         example="eyJpdiI6ImRBcEtuRzhBc2xoUnJTM3F0RmY1amc9PSIsInZhbHVlIjoiY1...",
 *         @OA\Schema(
 *         type="string"
 *        )
 *     ),
 *     @OA\Response(
 *        response="200",
 *        description="メンバー登録の有効なリンク",
 *     ),
 *     @OA\Response(
 *        response="400",
 *        description="会員登録リンクの有効期限が切れています。もう一度登録を行ってください",
 *     ),
 *    @OA\Response(
 *        response="500",
 *        description="Internal Server Error",
 *     ),
 * )
 *
 */
