<?php

/**
 *     @OA\Post(
 *     path="/api/service/management/list",
 *     summary="Admin get user",
 *     tags={"Service"},
 *     security={ {"bearer": {}} },
 *     @OA\RequestBody(
 *        @OA\JsonContent(
 *             type="object",
 *             required={"shopper_id"},
 *                 @OA\Property(
 *                      description="ID of shopper need to display",
 *                      property="shopper_id",
 *                      example="1",
 *                      type="integer",
 *                  ),
 *
 *                 @OA\Property(
 *                      description="0 is draft, 1 is public",
 *                      property="is_draft",
 *                      example="1",
 *                      type="integer",
 *                  ),
 *                 @OA\Property(
 *                      description="1=> id, 2=> first_name or last_name, 3=> name, 4=> email, 5=> phone",
 *                      property="sort_type",
 *                      example="1",
 *                      type="integer",
 *                  ),
 *                 @OA\Property(
 *                      property="per_page",
 *                      example="10",
 *                      type="integer",
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
