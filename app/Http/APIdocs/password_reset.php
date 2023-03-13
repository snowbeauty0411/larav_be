<?php

/**
 *     @OA\Post(
 *     path="/api/password/reset",
 *     summary="Password reset",
 *     tags={"Reset user's password"},
 *      @OA\RequestBody(
 *        @OA\JsonContent(
 *             type="object",
 *                        required={"password", "password_confirmation", "token"},
 *                        @OA\Property(
 *                             property="password",
 *                             example="password",
 *                             type="string",
 *                         ),
 *                         @OA\Property(
 *                             property="password_confirmation",
 *                             example="password",
 *                             type="string",
 *                         ),
 *                         @OA\Property(
 *                             property="token",
 *                             example="vxas4xDWy4WpI9UrSbDIUZgKintAXYn5lQSS3W4qOkZRus31MlMxXKr18Xyl",
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
