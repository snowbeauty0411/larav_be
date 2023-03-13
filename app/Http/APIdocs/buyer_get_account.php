<?php

/**
 *     @OA\Get(
 *     path="/api/buyer/account/{id}",
 *     summary="Get account buyer",
 *     tags={"Buyer"},
 *     security={ {"bearer": {}} },
 *  *     @OA\Parameter(
 *         description="ID of buyer need to display",
 *         in="path",
 *         name="id",
 *         required=true,
 *         example="1",
 *         @OA\Schema(
 *         type="integer"
 *        )
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
