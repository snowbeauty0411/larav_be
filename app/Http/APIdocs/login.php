<?php

/**
 *     @OA\Post(
 *     path="/api/login",
 *     summary="Members login",
 *     tags={"Auth User"},
 *      @OA\RequestBody(
 *        @OA\JsonContent(
 *             type="object",
 *                        required={"email","password"},
 *                        @OA\Property(
 *                             property="email",
 *                             example="example@example.org",
 *                             type="string",
 *                         ),
 *                         @OA\Property(
 *                             property="password",
 *                             example="password",
 *                             type="string",
 *                         ),
 *         )
 *     ),
 *     @OA\Response(
 *        response="200",
 *        description="User login successful",
 *     ),
 *     @OA\Response(
 *        response="400",
 *        description="Bad Request",
 *     ),
 *  *     @OA\Response(
 *        response="500",
 *        description="Internal Server Error",
 *     ),
 * )
 *
 */
