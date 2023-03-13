<?php
/**
      *     @OA\Post(
      *     path="/api/signup/check-email",
      *     summary="check email registered member",
      *     tags={"new member"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="email",
     *                             type="string",
     *                             example="aaaa@gmail.com"
     *                         ),                       
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