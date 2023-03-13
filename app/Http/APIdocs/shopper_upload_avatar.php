<?php

/**
 *     @OA\Post(
 *     path="/api/seller/profile/avatar/edit",
 *     summary="Upload avatar shopper",
 *     tags={"Shopper"},
 *     security={ {"bearer": {}} },
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     description="ID of shopper need to display",
 *                     property="shopper_id",
 *                     type="integer",
 *                     example="1",
 *                 ),
 *                 @OA\Property(
 *                     description="File to upload",
 *                     property="file",
 *                     type="file",
 *                     format="file",
 *                 ),
 *                 required={"file", "shopper_id"}
 *             )
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
