<?php

/**
 *     @OA\Get(
 *     path="/api/logout",
 *     summary="Members logout",
 *     tags={"Auth User"},
 *     security={ {"bearer": {}} },
 *     @OA\Response(
 *        response="200",
 *        description="User login successful",
 *     ),
 *     @OA\Response(
 *        response="401",
 *        description="許可がありません。",
 *     ),
 *     @OA\Response(
 *        response="500",
 *        description="Internal Server Error",
 *     ),
 * )
 *
 */
