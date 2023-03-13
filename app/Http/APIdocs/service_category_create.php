<?php

/**
 * *   @OA\Post(
 *     path="/api/service-category/create",
 *     summary="Create service category",
 *     tags={"Service Category"},
 *     security={ {"bearer": {}} },
 *      @OA\RequestBody(
 *        @OA\JsonContent(
 *            type="object",
 *            required={"name"},
 *            @OA\Property(
 *              property="name",
 *              example="name",
 *              type="string",
 *            ),
 *         )
 *     ),
 *
 *     @OA\Response(
 *        response="200",
 *        description="Successful",
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
