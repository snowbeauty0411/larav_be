<?php

/**
 *     @OA\Post(
 *     path="/api/admin/user/identity/reject",
 *     summary="Admin reject identification file",
 *     tags={"Verify Identity"},
 *     security={ {"bearer": {}} },
 *     @OA\RequestBody(
 *        @OA\JsonContent(
 *             type="object",
 *             required={"id","type"},
 *                 @OA\Property(
 *                      description="ID of buyer or shopper need to display",
 *                      property="id",
 *                      example="1",
 *                      type="integer",
 *                  ),
 *                  @OA\Property(
 *                      property="type",
 *                      example="buyers",
 *                      type="string",
 *                      description="type is buyer or shopper",
 *                  ),
 *         )
 *     ),
 *     @OA\Response(
 *        response="200",
 *        description="本人確認を拒否しました",
 *     ),
 *     @OA\Response(
 *        response="400",
 *        description="Bad Request",
 *     ),
 *     @OA\Response(
 *        response="401",
 *        description="許可がありません。",
 *     ),
 *    @OA\Response(
 *        response="500",
 *        description="Internal Server Error",
 *     ),
 * )
 *
 */
