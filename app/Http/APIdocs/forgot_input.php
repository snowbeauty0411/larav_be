<?php

/**
 *     @OA\Post(
 *     path="/api/forgot/input",
 *     summary="Send password reset email to user.",
 *     tags={"Reset user's password"},
 *      @OA\RequestBody(
 *        @OA\JsonContent(
 *             type="object",
 *                        required={"email"},
 *                        @OA\Property(
 *                             property="email",
 *                             example="example@example.org",
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
