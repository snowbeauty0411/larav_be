<?php

/**
 *     @OA\Patch(
 *     path="/api/signup/done/{id}",
 *     summary="register member step 2",
 *     tags={"new member"},
 *     security={ {"bearer": {}} },
 *      @OA\Parameter(
 *         description="ID of user need to input info",
 *         in="path",
 *         name="id",
 *         required=true,
 *         example="1",
 *         @OA\Schema(
 *         type="integer"
 *        )
 *     ),
 *      @OA\RequestBody(
 *        @OA\JsonContent(
 *             type="object",
 *                        @OA\Property(
 *                             property="name_first",
 *                             example="",
 *                             type="string"
 *                         ),
 *                         @OA\Property(
 *                             property="name_last",
 *                             example="",
 *                             type="string"
 *                         ),
 *                         @OA\Property(
 *                             property="name_first_kana",
 *                             type="string",
 *                             example="" 
 *                         ),
 *                         @OA\Property(
 *                             property="name_last_kana",
 *                             type="string",
 *                             example=""
 *                         ),
 *                         @OA\Property(
 *                             property="tel_home",
 *                             type="string",
 *                             example=""
 *                         ),
 *                         @OA\Property(
 *                             property="tel_mobile",
 *                             type="string",
 *                             example=""
 *                         ),
 *                         @OA\Property(
 *                             property="zipcode",
 *                             type="string",
 *                             example=""
 *                         ),
 *                         @OA\Property(
 *                             property="prefecture_id",
 *                             type="integer",
 *                             example=1
 *                         ),
 *                          @OA\Property(
 *                             property="class",
 *                             type="integer",
 *                             example=1
 *                         ),
 *                         @OA\Property(
 *                             property="city",
 *                             type="string",
 *                             example=""
 *                         ),
 *                         @OA\Property(
 *                             property="block",
 *                             type="string",
 *                             example=""
 *                         ),
 *                         @OA\Property(
 *                             property="birth_year",
 *                             type="string",
 *                             example="1995"
 *                         ),
 *                         @OA\Property(
 *                             property="sex_id",
 *                             type="integer",
 *                             example=1
 *                         ),
 *                         @OA\Property(
 *                             property="company_name",
 *                             type="string",
 *                             example=""
 *                         ),
 *                         @OA\Property(
 *                             property="corporate_number",
 *                             type="string",
 *                             example=""
 *                         ),
 *                         @OA\Property(
 *                             property="homebuilder_number",
 *                             type="string",
 *                             example=""
 *                         ),
 *                          @OA\Property(
 *                             property="hobby_ids",
 *                             type="string",
 *                             example="1,2,3"
 *                         ),
 * 
 *         )
 *     ),
 *     @OA\Response(
 *        response="200",
 *        description="register successful users",
 *     ),
 *     @OA\Response(
 *        response="400",
 *        description="Bad Request",
 *     ),
 * )
 *
 */
