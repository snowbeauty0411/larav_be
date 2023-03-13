<?php

/**
 *     @OA\Put(
 *     path="/api/buyer/profile/edit/{id}",
 *     summary="Edit buyer",
 *     tags={"Buyer"},
 *      security={ {"bearer": {}} },
 *      @OA\Parameter(
 *         description="ID of user need to input info",
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
 *             type="object",
 *                        required={"email","phone"},
 *                         @OA\Property(
 *                             property="name",
 *                             example="name",
 *                             type="string",
 *                         ),
 *                          @OA\Property(
 *                             property="first_name",
 *                             example="",
 *                             type="string",
 *                         ),
 *                          @OA\Property(
 *                             property="last_name",
 *                             example="",
 *                             type="string",
 *                         ),
 *                        @OA\Property(
 *                             property="email",
 *                             example="example@example.org",
 *                             type="string",
 *                         ),
 *                         @OA\Property(
 *                             property="phone",
 *                             example="012345678",
 *                             type="string",
 *                         ),
 *                          @OA\Property(
 *                             property="profile",
 *                             example="",
 *                             type="string",
 *                         ),
 *                          @OA\Property(
 *                             property="gender",
 *                             example="",
 *                             type="string",
 *                         ),
 *                          @OA\Property(
 *                             property="person",
 *                             example="",
 *                             type="string",
 *                         ),
 *                          @OA\Property(
 *                             property="business_cart",
 *                             example="",
 *                             type="string",
 *                         ),
 *                          @OA\Property(
 *                             property="id_certificate",
 *                             example=0,
 *                             type="boolean",
 *                         ),
 *                          @OA\Property(
 *                             property="nda_certificate",
 *                             example=0,
 *                             type="boolean",
 *                         ),
 *         )
 *     ),
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
