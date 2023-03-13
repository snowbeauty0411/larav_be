<?php
/**
 *     @OA\Post(
 *     path="/api/signup/create",
 *     summary="register member step 1",
 *     tags={"new member"},
 *      @OA\RequestBody(
 *        @OA\JsonContent(
 *             type="object",
 *                       
 *                         @OA\Property(
 *                             property="name",
 *                             type="string",
 *                             example=""
 *                         ),
 *                         @OA\Property(
 *                             property="password",
 *                             type="string",
 *                             example=""
 *                         ),
 *                         @OA\Property(
 *                             property="password_confirmation",
 *                             type="string",
 *                             example=""
 *                         ),
 *                          @OA\Property(
 *                             property="token",
 *                             type="string",
 *                             example=""
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

