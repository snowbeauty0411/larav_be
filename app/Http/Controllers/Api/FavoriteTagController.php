<?php

namespace App\Http\Controllers\Api;

use App\Models\FavoriteTag;
use App\Models\Favorite;
use App\Models\ServiceFavoriteTag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FavoriteTagController extends BaseController
{
    protected $favotiteTag;
    protected $favorite;
    protected $serviceFavoriteTag;

    public function __construct(
        FavoriteTag $favoriteTag,
        Favorite $favorite,
        ServiceFavoriteTag $serviceFavoriteTag
    )
    {
        $this->favoriteTag = $favoriteTag;
        $this->favorite = $favorite;
        $this->serviceFavoriteTag = $serviceFavoriteTag;
    }

    public function rules()
    {
        return [
            'name' => 'required|string'
        ];
    }

    public function filterAdminGetTagRules()
    {
        return [
            'id' => 'nullable|integer',
            'name' => 'nullable|string',
            'amount_registed' => 'nullable|integer',
            'sort' => 'integer|nullable',
            'sort_type' => 'integer|nullable',
        ];
    }

    /**
     *     @OA\Post(
     *     path="/api/admin/favorite-tag/list",
     *     summary="Get all favorite tag",
     *     tags={"favorite tag"},
     *      security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="page number need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *               @OA\Property(
     *               property="id",
     *               example="1",
     *               type="integer",
     *              ),
     *              @OA\Property(
     *              property="name",
     *              example="",
     *              type="string",
     *              ),
     *              @OA\Property(
     *              property="amount_registed",
     *              example="",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="sort",
     *              example="1",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="sort_type",
     *              example="1",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="per_page",
     *              example="10",
     *              type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful favorite tag",
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
    public function listTagForAdmin(Request $request)
    {
        try {
            $credentials = $request->all();
            $validator = Validator::make($credentials, $this->filterAdminGetTagRules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());
            // $results = $this->account->getAllAccount($request->per_page, $request);
            $favorite_tags = $this->favoriteTag->getAllTagForAdmin($request->per_page, $request);
            return $this->sendSuccessResponse($favorite_tags);
        } catch (Exception $e) {
            $this->log('favoriteTag_index', null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *  @OA\Post(
     *     path="/api/admin/favorite-tag",
     *     summary="Create favorite tag",
     *     tags={"favorite tag"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *        required = true,
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="name",
     *                             type="string",
     *                             example="favorite 1"
     *                         ),
     *        ),
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful response",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *  )
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->rules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first(), Response::HTTP_OK);

            $favorite_tag = $this->favoriteTag->getByName($request->name);
            if (isset($favorite_tag)) {
                return $this->sendError(__('app.exist', ['attribute' => __('app.name')]));
            }

            $favorite_tag = $this->favoriteTag->create($request->all());

            if (isset($favorite_tag)) {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.favorite_tag')]));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.create'), 'attribute' => __('app.favorite_tag')]));
            }
        } catch (Exception $e) {
            $this->log("favoriteTag_store", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     * @OA\Get(
     *     path="/api/admin/favorite-tag/{id}",
     *     summary="get favorite tag by id",
     *     tags={"favorite tag"},
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
     *        description="Get successful favorite tag",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="favorite tag not found",
     *     )
     * )
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $favorite_tag = $this->favoriteTag->find($id);
            if (isset($favorite_tag)) {
                return $this->sendSuccessResponse($favorite_tag);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.favorite_tag')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @OA\Patch(
     *     path="/api/admin/favorite-tag/{id}",
     *     summary="edit favorite tag",
     *     tags={"favorite tag"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *     ),
     *     @OA\RequestBody(
     *        required = true,
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  example="favorite 1"
     *             ),
     *        ),
     *     ),
     *
     *     @OA\Response(
     *        response="200",
     *        description="Successful response",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), $this->rules());
            $errors = $validator->errors();
            if ($errors->first())
                return $this->sendError($errors->first(), Response::HTTP_OK);

            $favorite_tag = $this->favoriteTag->find($id);
            if (!isset($favorite_tag))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.favorite_tag')]), Response::HTTP_OK);

            $data = $this->favoriteTag->getByName($request->name);
            if (isset($data) && $data->id != $id) {
                return $this->sendError(__('app.exist', ['attribute' => __('app.name')]));
            }
            $favorite_tag->name = $request->name;
            $updated = $favorite_tag->save();

            if ($updated) {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.favorite_tag')]));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.update'), 'attribute' => __('app.favorite_tag')]));
            }
        } catch (Exception $e) {
            $this->log("favoriteTag_update", null, ["request"=>$request->all(), 'id'=>$id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @OA\Delete(
     *     path="/api/admin/favorite-tag/{id}",
     *     summary="delete favorite tag by id",
     *     tags={"favorite tag"},
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
     *        description="Get successful users",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="User not found",
     *     )
     * )
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $favorite_tag = $this->favoriteTag->find($id);
            if (!isset($favorite_tag))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.favorite_tag')]), Response::HTTP_OK);

            DB::beginTransaction();
            $serviceFavoriteTag = $this->serviceFavoriteTag->getByFavoriteTagId($id);
            if (count($serviceFavoriteTag) > 0) {
                foreach ($serviceFavoriteTag as $item) {
                    $deleted_service_tag = $item->delete();
                    if (!$deleted_service_tag) {
                        DB::rollBack();
                        return $this->sendError(__('app.action_failed', ['action' => __('app.delete'), 'attribute' => __('app.favorite_tag')]));
                    }
                }
            }

            $deleted = $favorite_tag->delete();
            if ($deleted) {
                DB::commit();
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.delete'), 'attribute' => __('app.favorite_tag')]));
            } else {
                DB::rollBack();
                return $this->sendError(__('app.action_failed', ['action' => __('app.delete'), 'attribute' => __('app.favorite_tag')]));
            }
        } catch (Exception $e) {
            DB::rollBack();
            $this->log("favoriteTag_delete", null, ['id'=>$id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
