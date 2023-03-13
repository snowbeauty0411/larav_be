<?php

namespace App\Http\Controllers\Api;

use App\Models\Buyer;
use App\Models\Seller;
use App\Models\Service;
use App\Models\ServiceReview;
use App\Models\ServiceReviewImage;
use Exception;
use Hamcrest\Core\IsNull;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ServiceReviewController extends BaseController
{

    public function __construct(
        ServiceReview $serviceReview,
        Buyer $buyer,
        ServiceReviewImage $serviceReviewImage,
        Seller $seller,
        Service $service
    ) {
        $this->serviceReview = $serviceReview;
        $this->serviceReviewImage = $serviceReviewImage;
        $this->buyer = $buyer;
        $this->seller = $seller;
        $this->service = $service;
    }

    public function rules()
    {
        return [
            'buyer_id' => 'required|integer|exists:buyers,account_id',
            'service_id' => 'required|integer|exists:services,id',
            'description' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'images' => 'array|max:4',
            'images.*' => 'mimes:jpg,jpeg,png,gif|max:10240',
        ];
    }

    public function detailRules()
    {

        return [
            'buyer_id' => 'required|integer|exists:buyers,account_id',
        ];
    }

    public function updateRules()
    {
        return [
            'description' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'images' => 'array|max:4',
            'images.*' => 'mimes:jpg,jpeg,png,gif|max:10240',
            'id_review_image_deletes' => 'array',
            'id_review_image_deletes.*' => 'distinct|exists:service_reviews_images,id',
        ];
    }

    public function sellerReplyRules()
    {
        return [
            'seller_reply' => 'required|string',
        ];
    }

    public function validator($data, $rules)
    {
        $validator = Validator::make($data, $rules);
        $errors = $validator->errors();
        return $errors->first();
    }

    /**
     * Display a listing of the resource.
     *
     *  @OA\Get(
     *     path="/api/admin/comment/list",
     *     summary="get all comment",
     *     tags={"Comment"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="10",
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1",
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     *  )
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $reviews = $this->serviceReview->getAll($request->per_page, $request);
            return $this->sendSuccessResponse($reviews);
        } catch (Exception $e) {
            error_log($e);
            $this->log("getAllComment", null, ['request' => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/comment/create",
     *     summary="Store a newly created Comment",
     *     tags={"Comment"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="Buyer ID need to display",
     *                     property="buyer_id",
     *                     type="integer",
     *                     example="2",
     *                 ),
     *                 @OA\Property(
     *                     description="Service ID need to display",
     *                     property="service_id",
     *                     type="string",
     *                     example="service_id",
     *                 ),
     *                  @OA\Property(
     *                     description="description need to display",
     *                     property="description",
     *                     type="string",
     *                     example="",
     *                 ),
     *                  @OA\Property(
     *                     description="rating need to display",
     *                     property="rating",
     *                     type="integer",
     *                     example="5",
     *                 ),
     *                 @OA\Property(property="images", description="Array Images to upload", type="array",
     *                      @OA\Items(type="file")
     *                 ),
     *                 required={"file", "account_id", "buyer_id", "service_id", "description", "rating"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function store(Request $request)
    {
        try {
            //validate data
            $data = $request->all();
            $errors = $this->validator($data, $this->rules());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            if ($this->serviceReview->findByServiceAndBuyer($data['service_id'], $data['buyer_id'])) return  $this->sendError(__('app.exist', ['attribute' => __('app.reviews')]), Response::HTTP_OK);;

            //create reviews
            $serviceReview = $this->serviceReview->create($data);

            // check create success and create images
            if (!isset($serviceReview)) {
                return $this->sendError(__('app.action_failed', ['action' => __('app.create'), 'attribute' => __('app.reviews')]));
            } else {
                $id = $serviceReview->id;
                if ($request->hasFile('images')) {
                    $images = $request->file('images');
                    foreach ($images as $file) {
                        $extension = $file->getClientOriginalExtension();
                        $fileName = $id . '-' . date('mdHis') . uniqid('-') . '.' . $extension;
                        $file->move(public_path('storage/images/reviews/' . $id), $fileName);
                        $file_saved = new ServiceReviewImage();
                        $file_saved["reviews_id"] = $id;
                        $file_saved["image_url"] = 'images/reviews/' . $id . '/' . $fileName;
                        $file_saved = $file_saved->save();
                    }
                }
            }
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.reviews')]));
        } catch (Exception $e) {
            $this->log("createComment", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     *  @OA\Get(
     *     path="/api/comment/detail/{service_hash_id}",
     *     summary="get Comment by id",
     *     tags={"Comment"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="service_hash_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          example="service_hash_id",
     *      ),
     *     @OA\Parameter(
     *          name="buyer_id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="2",
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     *  )
     * @return \Illuminate\Http\Response
     */
    public function show($service_hash_id, Request $request)
    {
        try {
            $data = $request->all();
            $errors = $this->validator($data, $this->detailRules());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $service = $this->service->findHashId($service_hash_id);
            if (!$service) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $reviews = $this->serviceReview->findByServiceAndBuyer($service->id, $request->buyer_id);
            if (!$reviews) {
                return $this->sendSuccessResponse(__('app.not_exist', ['attribute' => __('app.review')]));
            } else {
                $data = $this->serviceReview->getReviewsById($reviews->id);
                return $this->sendSuccessResponse($data);
            }
            // if(!$reviews) return $this->sens
            // // if (empty($reviews)) $this->sendError(__('app.not_exist', ['attribute' => __('app.review')]));
            // return $this->sendSuccessResponse($this->serviceReview->getReviewsById($reviews->id));
        } catch (Exception $e) {
            error_log($e);
            $this->log("getCommentById", null, ['service_hash_id' => $service_hash_id, 'request' => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/comment/edit/{id}",
     *     summary="Update Comment",
     *     tags={"Comment"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1"
     *      ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  @OA\Property(
     *                     description="description need to display",
     *                     property="description",
     *                     type="string",
     *                     example="",
     *                 ),
     *                  @OA\Property(
     *                     description="rating need to display",
     *                     property="rating",
     *                     type="integer",
     *                     example="5",
     *                 ),
     *                 @OA\Property(property="images", description="Array Images to upload", type="array",
     *                      @OA\Items(type="file")
     *                 ),
     *                  @OA\Property(property="id_review_image_deletes", description="Array of image ids to delete", type="array",
     *                          @OA\Items(type="integer")
     *                  ),
     *                 required={"description", "rating"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     *
     */
    public function update($id, Request $request)
    {
        try {
            //validate data
            $data = $request->all();
            $errors = $this->validator($data, $this->updateRules());

            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $reviews = $this->serviceReview->find($id);
            if (!$reviews) return $this->sendError(__('app.not_exist', ['attribute' => __('app.reviews')]), Response::HTTP_OK);

            if (isset($data['id_review_image_deletes']) && is_array($data['id_review_image_deletes'])) {
                $id_image_check = $data['id_review_image_deletes'];
                foreach ($id_image_check as $id_img) {
                    $review_image_check = $this->serviceReviewImage->find($id_img);
                    if ($review_image_check->reviews_id != $id) return $this->sendError(__('app.invalid', ['attribute' => __('app.id_image')]), Response::HTTP_OK);
                }
            }

            $count_image_current = count($this->serviceReviewImage->findByReviewsId($reviews->id));

            $count_image = $request->hasFile('images') ? count($request->file('images')) : 0;
            $image_deletes = isset($data['id_review_image_deletes']) ? count($data['id_review_image_deletes']) : 0;

            if ((($count_image_current - $image_deletes) + $count_image) > 4) return $this->sendError(__('app.image_max', ['attribute' => __('app.images'), 'max' => 4]), Response::HTTP_OK);

            //update reviews
            $reviews->update($data);

            //remove images
            if (isset($data['id_review_image_deletes'])) {
                $id_imgs = $data['id_review_image_deletes'];
                foreach ($id_imgs as $id_img) {
                    $review_image = $this->serviceReviewImage->find($id_img);
                    if (isset($review_image)) {
                        $path = $review_image['image_url'];
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                        $review_image->delete();
                    }
                }
            }

            //update images
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                foreach ($images as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $id . '-' . date('mdHis') . uniqid('-') . '.' . $extension;
                    $file->move(public_path('storage/images/reviews/' . $id), $fileName);
                    $file_saved = new ServiceReviewImage();
                    $file_saved["reviews_id"] = $id;
                    $file_saved["image_url"] = 'images/reviews/' . $id . '/' . $fileName;
                    $file_saved = $file_saved->save();
                }
            }

            return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.reviews')]));
        } catch (Exception $e) {
            $this->log("updateCommentById", null, ['id' => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  Remove the specified resource from storage.
     *  @OA\Delete(
     *     path="/api/comment/delete/{id}",
     *     summary="delete comment by id",
     *     tags={"Comment"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     *  )
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $reviews = $this->serviceReview->find($id);
            if ($reviews) {
                $reviews_images = $this->serviceReviewImage->findByReviewsId($id);
                foreach ($reviews_images as $image) {
                    $path = $image['image_url'];
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                    $image->delete();
                }
                $dir = 'images/reviews/' . $id;
                if (Storage::disk('public')->exists($dir)) Storage::disk('public')->deleteDirectory($dir);

                $deleted = $reviews->delete();

                if ($deleted) {
                    return $this->sendSuccess(__('app.action_success', ['action' => __('app.delete'), 'attribute' => __('app.reviews')]));
                } else {
                    return $this->sendError(__('app.action_failed', ['action' => __('app.delete'), 'attribute' => __('app.reviews')]));
                }
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.reviews')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->log("deleteComment", null, ['id' => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     *  @OA\Get(
     *     path="/api/comment/buyer/{buyer_id}",
     *     summary="get Comment by buyer_id",
     *     tags={"Comment"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="buyer_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="2",
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="10",
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1",
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     *  )
     * @return \Illuminate\Http\Response
     */
    public function getAllByBuyer($buyer_id, Request $request)
    {
        try {
            if (!$this->buyer->findByAccountId($buyer_id)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]), Response::HTTP_OK);

            $list_reviews = $this->serviceReview->getAllByBuyer($buyer_id, $request->limit);

            return $this->sendSuccessResponse($list_reviews);
        } catch (Exception $e) {
            $this->log("getAllReviewsByBuyer", null, ['buyer_id' => $buyer_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Display the specified resource.
     *
     *  @OA\Get(
     *     path="/api/comment/buyer/{service_id}/list/{buyer_id}",
     *     summary="get Comment by buyer_id and service_id",
     *     tags={"Comment"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="buyer_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="2",
     *      ),
     *     @OA\Parameter(
     *          name="service_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1",
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="10",
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1",
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     *  )
     * @return \Illuminate\Http\Response
     */
    public function getAllByBuyerAndService($service_id, $buyer_id, Request $request)
    {
        try {
            if (!$this->buyer->findByAccountId($buyer_id)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]), Response::HTTP_OK);

            $list_reviews = $this->serviceReview->getAllByBuyerAndService($service_id, $buyer_id, $request->limit);

            return $this->sendSuccessResponse($list_reviews);
        } catch (Exception $e) {
            $this->log("getAllReviewsByBuyer", null, ['buyer_id' => $buyer_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     *  @OA\Get(
     *     path="/api/comment/seller/{seller_id}",
     *     summary="get Comment by Seller",
     *     tags={"Comment"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="seller_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1",
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="10",
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1",
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     *  )
     * @return \Illuminate\Http\Response
     */
    public function getAllBySeller($seller_id, Request $request)
    {
        try {
            if (!$this->seller->findByAccountId($seller_id)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.seller')]), Response::HTTP_OK);

            $list_reviews = $this->serviceReview->getAllBySeller($seller_id, $request->per_page);

            return $this->sendSuccessResponse($list_reviews);
        } catch (Exception $e) {
            error_log($e);
            $this->log("getAllReviewsBySelle", null, ['seller_id' => $seller_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/comment/{id}/reply",
     *     summary="Update Comment",
     *     tags={"Comment"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1"
     *      ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  @OA\Property(
     *                     description="description need to display",
     *                     property="seller_reply",
     *                     type="string",
     *                     example="売り手の返信",
     *                 ),
     *                 required={"seller_reply"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     *
     */
    public function sellerReply($id, Request $request)
    {
        try {
            $data = $request->all();
            $errors = $this->validator($data, $this->sellerReplyRules());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            if (!$this->serviceReview->find($id)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.reviews')]), Response::HTTP_OK);

            $this->serviceReview->sellerReply($id, $request);

            return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.reviews')]));
        } catch (Exception $e) {
            $this->log("getAllReviewsBySelle", null, ['id' => $id, "request" => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Patch(
     *     path="/api/admin/comment/{id}/approve",
     *     summary="Admin approve comment",
     *     tags={"Admin"},
     *     security={ {"bearer": {}} },
     *       @OA\Parameter(
     *         description="ID of service need to display",
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
     *        description="success",
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
    public function approveReview($id)
    {
        try {
            $review = $this->serviceReview->find($id);
            if (!$review) return $this->sendError(__('app.not_exist', ['attribute'  => __('app.reviews')]));
            $review['is_active'] = 1;
            $review->save();
            return $this->sendSuccess(__('app.success'));
        } catch (Exception $e) {
            $this->log("approveReview", null, ["review_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Patch(
     *     path="/api/admin/comment/seller/{id}/approve",
     *     summary="Admin approve comment",
     *     tags={"Admin"},
     *     security={ {"bearer": {}} },
     *       @OA\Parameter(
     *         description="ID of service need to display",
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
     *        description="success",
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
    public function approveReviewSeller($id)
    {
        try {
            $review = $this->serviceReview->find($id);
            if (!$review) return $this->sendError(__('app.not_exist', ['attribute'  => __('app.reviews')]));
            if ($review->is_active == 1 && !empty($review->seller_reply)) {
                $review['is_active_seller'] = 1;
                $review->save();
                return $this->sendSuccess(__('app.success'));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.update'), 'attribute' => __('app.reviews')]));
            }
        } catch (Exception $e) {
            $this->log("approveReviewSeller", null, ["review_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
