<?php

/**
 *     @OA\Post(
 *     path="/api/admin/user/list",
 *     summary="Admin get user",
 *     tags={"Admin"},
 *     security={ {"bearer": {}} },
 *     @OA\RequestBody(
 *        @OA\JsonContent(
 *             type="object",
 *             required={"type"},
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
 *                      description="type is buyers or shoppers",
 *                  ),
 *                   @OA\Property(
 *                      property="phone",
 *                      example="",
 *                      type="string",
 *                  ),
 *                  @OA\Property(
 *                      property="name",
 *                      example="",
 *                      type="string",
 *                  ),
 *                  @OA\Property(
 *                      property="full_name",
 *                      example="",
 *                      type="string",
 *                  ),
 *                  @OA\Property(
 *                      property="email",
 *                      example="",
 *                      type="string",
 *                  ),
 *                 @OA\Property(
 *                      description="1=> ASC, 2=> DESC",
 *                      property="sort_type",
 *                      example="1",
 *                      type="integer",
 *                  ),
 *                 @OA\Property(
 *                      description="1=> id, 2=> first_name or last_name, 3=> name, 4=> email, 5=> phone",
 *                      property="sort",
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
