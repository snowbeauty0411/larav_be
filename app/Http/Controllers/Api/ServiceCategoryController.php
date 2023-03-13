<?php

namespace App\Http\Controllers\Api;

use App\Models\ServiceCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ServiceCategoryController extends BaseController
{
    protected $serviceCategory;

    public function __construct(ServiceCategory $serviceCategory)
    {
        $this->serviceCategory = $serviceCategory;
    }

    public function rules()
    {
        return [
            'name' => 'required|string'
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/service-category/list",
     *     summary="Get all service category",
     *     tags={"Service Category"},
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
    public function index()
    {
        try {
            $serviceCategorys = $this->serviceCategory->getAllServiceCategory();
            return $this->sendSuccessResponse($serviceCategorys);
        } catch (Exception $e) {
            $this->log("getAllServiceCategory", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * *   @OA\Post(
     *     path="/api/service-category/create",
     *     summary="Create service category",
     *     tags={"Service Category"},
     *     security={ {"bearer": {}} },
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *            type="object",
     *            required={"name"},
     *            @OA\Property(
     *              property="name",
     *              example="name",
     *              type="string",
     *            ),
     *         )
     *     ),
     *
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
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->rules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first(), Response::HTTP_OK);
            $check_name = $this->serviceCategory->findServiceCategoryByName($request->name);
            if (isset($check_name)) return $this->sendError(__('app.exist', ['attribute' => __('app.name')]));
            $service_category = $this->serviceCategory->create($request->all());

            if (isset($service_category)) {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.category')]));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.create'), 'attribute' => __('app.category')]));
            }
        } catch (Exception $e) {
            $this->log("createServiceCategory", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/service-category/detail/{id}",
     *     summary="Get detail service category",
     *     tags={"Service Category"},
     *       @OA\Parameter(
     *         description="ID of service category need to display",
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

    public function show($id)
    {
        try {
            $service_category = $this->serviceCategory->getServiceCategoryById($id);
            if (isset($service_category)) {
                return $this->sendSuccessResponse($service_category);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.category')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->log("getDetailServiceCategory", null, $id, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * *   @OA\Put(
     *     path="/api/service-category/edit/{id}",
     *     summary="Edit service category",
     *     tags={"Service Category"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="ID of service category need to input info",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *            type="object",
     *            required={"name"},
     *            @OA\Property(
     *              property="name",
     *              example="name",
     *              type="string",
     *            ),
     *         )
     *     ),
     *
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
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), $this->rules());
            $errors = $validator->errors();
            if ($errors->first())
                return $this->sendError($errors->first(), Response::HTTP_OK);

            $service_category = $this->serviceCategory->getServiceCategoryById($id);
            if (!isset($service_category))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.category')]), Response::HTTP_OK);

            $updated = $service_category->update($request->all());
            if ($updated) {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.category')]));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.update'), 'attribute' => __('app.category')]));
            }
        } catch (Exception $e) {
            $this->log("getDetailServiceCategory", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     @OA\Delete(
     *     path="/api/service-category/delete/{id}",
     *     summary="Delete service category",
     *     tags={"Service Category"},
     *     security={ {"bearer": {}} },
     *  *     @OA\Parameter(
     *         description="ID of service category need to display",
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
    public function destroy($id)
    {
        try {
            // check exist
            $service_category = $this->serviceCategory->getServiceCategoryById($id);

            if (!isset($service_category))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.category')]), Response::HTTP_OK);

            $deleted = $service_category->delete();
            if ($deleted) {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.delete'), 'attribute' => __('app.category')]));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.delete'), 'attribute' => __('app.category')]));
            }
        } catch (Exception $e) {
            $this->log("deleteServiceCategory", null, $id, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
