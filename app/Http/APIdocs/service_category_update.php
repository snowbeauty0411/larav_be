<?php

/**
 * *   @OA\Put(
 *     path="/api/service-category/edit/{id}",
 *     summary="Edit service category",
 *     tags={"Service Category"},
 *     security={ {"bearer": {}} },
*      @OA\Parameter(
 *         description="ID of service category need to input info",
 *         in="path",
 *         name="id",
 *         required=true,
 *         example="1",
 *         @OA\Schema(
 *         type="integer"
 *        )
 *      ),
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
