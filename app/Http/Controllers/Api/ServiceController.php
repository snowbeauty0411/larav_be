<?php

namespace App\Http\Controllers\Api;

use App\Constants\ServiceConst;
use App\Http\Controllers\Api\BaseController;
use App\Models\Area;
use App\Models\Buyer;
use App\Models\BuyerServiceReserve;
use App\Models\Favorite;
use App\Models\NumberAccessListServicePage;
use App\Models\NumberAccessServiceDetailPage;
use App\Models\Payment;
use App\Models\Prefecture;
use App\Models\Seller;
use App\Models\Service;
use App\Models\ServiceCourse;
use App\Models\ServiceCourseImage;
use App\Models\ServiceHour;
use App\Models\ServiceImage;
use App\Models\ServiceLink;
use App\Models\ServiceReserveSetting;
use App\Models\ServiceStep;
use App\Models\ServiceReview;
use App\Models\ServiceStoreBuyer;
use App\Models\ServiceArea;
use App\Models\ServiceDelivery;
use App\Models\ServiceTag;
use App\Models\Tag;
use App\Models\ActionPayment;
use App\Constants\UserConst;
use App\Models\Delivery;
use App\Models\RecommendHashTag;
use App\Models\RecommendService;
use App\Models\ServiceBrowsingHistory;
use Carbon\Carbon;
use App\Models\ServiceHoursTemp;
use App\Models\ShippingInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use function PHPUnit\Framework\isEmpty;

class ServiceController extends BaseController
{
    protected $service;
    protected $serviceImage;
    protected $serviceLink;
    protected $serviceCourse;
    protected $serviceCourseImage;
    protected $serviceStep;
    protected $favorite;
    protected $buyer;
    protected $serviceReview;
    protected $serviceStoreBuyer;
    protected $buyerServiceReserve;
    protected $serviceReserveSetting;
    protected $serviceHour;
    protected $tag;
    protected $serviceTag;
    protected $seller;
    protected $serviceDelivery;
    protected $prefecture;
    protected $numberAccessListServicePage;
    protected $recommendService;
    protected $recommendHashTag;
    protected $numberAccessServiceDetailPage;
    protected $serviceHoursTemp;
    protected $delivery;
    protected $shippingInfo;
    protected $serviceBrowsingHistory;
    protected $actionPayment;

    public function __construct(
        Service $service,
        ServiceImage $serviceImage,
        ServiceLink $serviceLink,
        ServiceCourse $serviceCourse,
        ServiceCourseImage $serviceCourseImage,
        ServiceStep $serviceStep,
        Favorite $favorite,
        Buyer $buyer,
        ServiceReview $serviceReview,
        ServiceStoreBuyer $serviceStoreBuyer,
        Payment $payment,
        BuyerServiceReserve $buyerServiceReserve,
        ServiceReserveSetting $serviceReserveSetting,
        ServiceHour $serviceHour,
        ServiceArea $serviceArea,
        ServiceTag $serviceTag,
        Tag $tag,
        Seller $seller,
        ServiceDelivery $serviceDelivery,
        Prefecture $prefecture,
        NumberAccessListServicePage $numberAccessListServicePage,
        NumberAccessServiceDetailPage $numberAccessServiceDetailPage,
        ServiceHoursTemp $serviceHoursTemp,
        Delivery $delivery,
        RecommendService $recommendService,
        RecommendHashTag $recommendHashTag,
        ShippingInfo $shippingInfo,
        ServiceBrowsingHistory $serviceBrowsingHistory,
        ActionPayment $actionPayment

    ) {
        $this->service = $service;
        $this->serviceImage = $serviceImage;
        $this->serviceLink = $serviceLink;
        $this->serviceCourse = $serviceCourse;
        $this->serviceCourseImage = $serviceCourseImage;
        $this->serviceStep = $serviceStep;
        $this->favorite = $favorite;
        $this->buyer = $buyer;
        $this->serviceReview = $serviceReview;
        $this->serviceStoreBuyer = $serviceStoreBuyer;
        $this->payment = $payment;
        $this->buyerServiceReserve = $buyerServiceReserve;
        $this->serviceReserveSetting = $serviceReserveSetting;
        $this->serviceHour = $serviceHour;
        $this->serviceArea = $serviceArea;
        $this->serviceTag = $serviceTag;
        $this->tag = $tag;
        $this->seller = $seller;
        $this->serviceDelivery = $serviceDelivery;
        $this->prefecture = $prefecture;
        $this->numberAccessListServicePage = $numberAccessListServicePage;
        $this->numberAccessServiceDetailPage = $numberAccessServiceDetailPage;
        $this->serviceHoursTemp = $serviceHoursTemp;
        $this->delivery = $delivery;
        $this->recommendService = $recommendService;
        $this->recommendHashTag = $recommendHashTag;
        $this->shippingInfo = $shippingInfo;
        $this->serviceBrowsingHistory = $serviceBrowsingHistory;
        $this->actionPayment = $actionPayment;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/service/list",
     *     summary="Get all service",
     *     tags={"Service"},
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Parameter(
     *         description="Buyer ID to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function index(Request $request)
    {
        try {

            if ($request->buyer_id && !$this->buyer->findByAccountId($request->buyer_id)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]), Response::HTTP_OK);
            return $this->sendSuccessResponse($this->service->getAll($request->buyer_id));
        } catch (Exception $e) {
            $this->log("getAllService", null, $request->all, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/service/detail/{hash_id}",
     *     summary="Get detail service",
     *     tags={"Service"},
     *      @OA\Parameter(
     *         description="Hash ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="Buyer ID to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="is_guest to input info",
     *         in="query",
     *         name="is_guest",
     *         example="1",
     *         @OA\Schema(
     *         type="boolean"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function show($hash_id, Request $request)
    {
        try {
            $ip_address = $request->getClientIp();

            if ($request->buyer_id && !$this->buyer->findByAccountId($request->buyer_id)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]), Response::HTTP_OK);

            if (!$this->service->findHashId($hash_id)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $service = $this->service->getServiceById($hash_id, $request->buyer_id);
            
            if ($request->is_guest) {
                $number_access_detail_page = $this->numberAccessServiceDetailPage->findLastByService($service->id);
                
                $this->serviceBrowsingHistory->updateOrCreate(
                    [
                        'service_id' => $service->id,
                        'ip_address' => $ip_address,
                    ],
                    [
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                );

                if (!auth(UserConst::USER_GUARD)->user()) {
                    $this->numberAccessServiceDetailPage->countNumberAccess($number_access_detail_page, $service->id);
                } elseif (auth(UserConst::USER_GUARD)->user() && auth(UserConst::USER_GUARD)->user()->id != $service->seller_id) {
                    $this->numberAccessServiceDetailPage->countNumberAccess($number_access_detail_page, $service->id);
                }
            }
            
            $service->is_use = 0;
            if ($service->serviceStoreBuyers->where('status', 1)->count() > 0) $service->is_use = 1;
            
            return $this->sendSuccessResponse($service);
        } catch (Exception $e) {
            error_log($e);
            $this->log("ServiceDetail", null, ["request" => $request->all(), 'hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function filterRules()
    {
        return [
            'seller_id' => 'required|nullable|integer',
            'sort_type' => 'integer|nullable',
            'private' => 'integer|nullable',
            "per_page" => 'integer|nullable'
        ];
    }

    public function rules()
    {
        return [
            'seller_id' => 'required|integer',
        ];
    }

    /**
     *     @OA\Post(
     *     path="/api/seller/service/creating-or-selling",
     *     summary="Get list service creating or selling",
     *     tags={"Seller"},
     *     security={ {"bearer": {}} },
     *     description="is_draft: 0 is creating, 1 is selling and sort_type: 1 order by ASC, 2 order by DESC",
     *     @OA\Parameter(
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
     *             required={"seller_id"},
     *              @OA\Property(
     *                property="seller_id",
     *                example="1",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="is_draft",
     *                example="1",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="sort_type",
     *                example="1",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="per_page",
     *                example="10",
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
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
    public function getAllServiceByCreatingOrSelling(Request $request)
    {
        try {
            // $credentials = $request->all();
            // //valid credential
            // $validator = Validator::make($credentials, $this->filterRules());
            // var_dump($validator);
            // $errors = $validator->errors();
            // if ($errors->first()) return $this->sendError($errors->first());
            $service = $this->service->getAllServiceBySellerId($request->per_page, $request);
            return $this->sendSuccessResponse($service);
        } catch (Exception $e) {
            $this->log("get_all_service_seller_id", null, ["request" => $request->all(), 'seller_id' => $request->seller_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     *     @OA\Post(
     *     path="/api/seller/service/getAllService",
     *     summary="Get list service creating or selling",
     *     tags={"Seller"},
     *     description="is_draft: 0 is creating, 1 is selling and sort_type: 1 order by ASC, 2 order by DESC",
     *     @OA\Parameter(
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
     *             required={"seller_id"},
     *              @OA\Property(
     *                property="seller_id",
     *                example="1",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="is_draft",
     *                example="1",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="sort_type",
     *                example="1",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="per_page",
     *                example="10",
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
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
    public function getAllSerivceOfSeller(Request $request)
    {
        try {
            // $credentials = $request->all();
            // //valid credential
            // $validator = Validator::make($credentials, $this->filterRules());
            // $errors = $validator->errors();
            // if ($errors->first()) return $this->sendError($errors->first());
            $service = $this->service->getAllServiceBySellerId($request->per_page, $request);
            return $this->sendSuccessResponse($service);
        } catch (Exception $e) {
            $this->log("get_all_service_seller_id", null, ["request" => $request->all(), 'seller_id' => $request->seller_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/seller/service/approved",
     *     summary="Get list service approved",
     *     tags={"Seller"},
     *     security={ {"bearer": {}} },
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *             required={"seller_id"},
     *              @OA\Property(
     *                property="seller_id",
     *                example="1",
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
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
    public function getAllServiceByApproved(Request $request)
    {
        try {
            $credentials = $request->all();
            //valid credential
            $validator = Validator::make($credentials, $this->rules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());
            $service = $this->service->getAllServiceApprovedBySellerId($request);
            return $this->sendSuccessResponse($service);
        } catch (Exception $e) {
            $this->log("get_all_service_seller_id", null, ["request" => $request->all(), 'seller_id' => $request->seller_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function serviceRules($is_draft = false)
    {
        if ($is_draft) {
            return [
                'service_id' => 'integer|min:1',
                'service_hash_id' => 'string|min:1',
                'seller_id' => 'required|integer|exists:sellers,account_id',
                'service_type_id' => 'integer|nullable|exists:service_types,id',
                'service_cat_id' => 'integer|exists:service_categories,id',
                'name' => 'string',
                'area' => 'nullable|string',
                'max' => 'nullable|integer',
                'caption' => 'string',
                'service_content' => 'string',
                'private' => 'nullable|boolean',
                'is_reserves' => 'boolean',
                // 'age_confirm' => 'required|string',
                'url_private' => 'nullable|string',
                'is_draft' => 'boolean',
            ];
        }
        return [
            'service_id' => 'integer|min:1',
            'service_hash_id' => 'string|min:1',
            'seller_id' => 'integer|required|exists:sellers,account_id',
            'service_type_id' => 'integer|nullable|exists:service_types,id',
            'service_cat_id' => 'required|integer|exists:service_categories,id',
            'name' => 'required|string',
            'area' => 'nullable|string',
            'max' => 'nullable|integer',
            'caption' => 'required|string',
            'service_content' => 'required|string',
            'private' => 'required|boolean',
            'is_reserves' => 'boolean',
            // 'age_confirm' => 'required|string',
            'url_private' => 'nullable|string',
            'is_draft' => 'required|boolean',
        ];
    }

    public function servicePrivateRules($id, $hash_id, $url_private)
    {
        return [
            'private' => [
                'boolean',
                function ($attr, $value, $fail) use ($id, $hash_id, $url_private) {
                    if ($value == 1) {
                        if (empty($id)) $fail(__('app.invalid', ['attribute' => __('app.service_id')]));
                        if (empty($hash_id)) $fail(__('app.invalid', ['attribute' => __('app.service_hash_id')]));
                        if (empty($url_private)) $fail(__('app.exits', ['attribute' => __('app.url_private')]));

                        $service = Service::where('id', $id)->first();
                        if ($service) $fail(__('app.invalid', ['attribute' => __('app.service_id')]));

                        $serviceByHash = Service::where('hash_id', $hash_id)->first();
                        if ($serviceByHash) $fail(__('app.invalid', ['attribute' =>  __('app.service_hash_id')]));
                        
                        $url_private_check = substr($url_private, strrpos($url_private,"service"));
                        $url_check = 'service/detail/' . $hash_id;

                        if ($url_check != $url_private_check) $fail(__('app.invalid', ['attribute' => __('app.url_private')]));
                    }
                }
            ]
        ];
    }

    public function servicePrivateUpdateRules($id, $hash_id, $url_private)
    {
        return [
            'private' => [
                'boolean',
                function ($attr, $value, $fail) use ($id, $hash_id, $url_private) {
                    if ($value == 1) {
                        if (empty($url_private)) $fail(__('app.exits', ['attribute' => __('app.url_private')]));

                        $service = Service::where('id', $id)->first();
                        if (!$service) $fail(__('app.invalid', ['attribute' => __('app.service_id')]));

                        $serviceByHash = Service::where('hash_id', $hash_id)->first();
                        if (!$serviceByHash) $fail(__('app.invalid', ['attribute' =>  __('app.service_hash_id')]));

                        $url_private_check = substr($url_private, strrpos($url_private,"service"));
                        $url_check = 'service/detail/' . $hash_id;
                        
                        if ($url_check != $url_private_check) $fail(__('app.invalid', ['attribute' => __('app.url_private')]));
                    }
                }
            ]
        ];
    }

    public function serviceImageRules($is_draft = false)
    {
        if ($is_draft) {
            return [
                'service_images' => 'nullable|array|max:5',
                'service_images.*' => 'nullable|mimes:jpeg,jpg,png|max:10240',
            ];
        }
        return [
            'service_images' => 'required|array|max:5',
            'service_images.*' => 'nullable|mimes:jpeg,jpg,png|max:10240',
        ];
    }

    public function serviceUpdateImageRules($count_image = null)
    {
        if ($count_image > 0) {
            return [
                'service_images' => 'required|array|max:5',
                'service_images.*.id' => 'required|integer|exists:service_images,id',
                'service_images.*.status' => 'required|boolean',
                'service_images.*.file' => 'nullable|mimes:jpeg,jpg,png|max:10240',
            ];
        }
        return [
            'service_images' => 'nullable|array|max:5',
            'service_images.*.id' => 'integer|exists:service_images,id',
            'service_images.*.status' => 'required|boolean',
            'service_images.*.file' => 'nullable|mimes:jpeg,jpg,png|max:10240',
        ];

    }

    public function serviceLinkRules($type_id = null, $is_draft = false)
    {
        if ($is_draft || $type_id == 4) {
            return [
                'jump_count' => 'integer',
                'url' => 'nullable|string',
                'address' => 'nullable|string',
                'zipcode' => 'nullable|string',
            ];
        } elseif ($type_id == 2) {
            return [
                'jump_count' => 'integer',
                'url' => 'nullable|string',
                'address' => 'nullable|string',
                'zipcode' => 'nullable|string',
            ];
        }
        return [
            'jump_count' => 'integer',
            'url' => 'required|string',
            'address' => 'required|string',
            'zipcode' => 'required|string',
        ];
    }

    public function serviceCourseRules($is_draft = false, $is_delivery = false)
    {
        if ($is_draft) {
            return [
                'service_courses' => 'nullable|array',
                'service_courses.*.course_id' => 'string|exists:service_courses,course_id',
                'service_courses.*.name' => 'nullable|string',
                'service_courses.*.price' => 'nullable|integer',
                // 'service_courses.*.max' => 'required|integer',
                'service_courses.*.content' => 'nullable|string',
                'service_courses.*.cycle' => 'nullable|integer',
                'service_courses.*.firstPr' => 'nullable|integer',
                'service_courses.*.image' => 'nullable|mimes:jpeg,jpg,png|max:10240',
                'service_courses.*.age_confirm' => 'nullable|integer',
                'service_courses.*.gender_restrictions' => 'nullable|integer'
            ];
        } elseif ($is_delivery) {
            return [
                'service_courses' => 'required|array',
                'service_courses.*.course_id' => 'string|exists:service_courses,course_id',
                'service_courses.*.name' => 'required|nullable|string',
                'service_courses.*.price' => 'required|integer',
                // 'service_courses.*.max' => 'required|integer',
                'service_courses.*.content' => 'required|nullable|string',
                'service_courses.*.cycle' => 'nullable|integer',
                'service_courses.*.firstPr' => 'required|integer',
                'service_courses.*.image' => 'mimes:jpeg,jpg,png|max:10240',
                'service_courses.*.age_confirm' => 'required|integer',
                'service_courses.*.gender_restrictions' => 'required|integer'
            ];
        } else {
            return [
                'service_courses' => 'required|array',
                'service_courses.*.course_id' => 'string|exists:service_courses,course_id',
                'service_courses.*.name' => 'required|string',
                'service_courses.*.price' => 'required|integer',
                // 'service_courses.*.max' => 'required|integer',
                'service_courses.*.content' => 'required|string',
                'service_courses.*.cycle' => 'integer',
                'service_courses.*.firstPr' => 'required|integer',
                'service_courses.*.image' => 'mimes:jpeg,jpg,png|max:10240',
                'service_courses.*.age_confirm' => 'required|integer',
                'service_courses.*.gender_restrictions' => 'required|integer'
            ];
        }
    }

    public function serviceStepRules($is_draft = false)
    {
        if ($is_draft) {
            return [
                'service_steps' => 'nullable|array|max:5',
                'service_steps.*.id' => 'nullable|integer|exists:service_steps,id',
                'service_steps.*.number' => 'nullable|integer|distinct',
                'service_steps.*.title' => 'nullable|string',
                'service_steps.*.content' => 'nullable|string'
            ];
        }
        return [
            'service_steps' => 'nullable|array|max:5',
            'service_steps.*.id' => 'nullable|integer|exists:service_steps,id',
            'service_steps.*.number' => 'required|distinct|integer',
            'service_steps.*.title' => 'required|string',
            'service_steps.*.content' => 'nullable|string'
        ];
    }

    public function serviceAreaRules($is_draft = false)
    {
        if ($is_draft) {
            return [
                'service_areas' => 'array',
                'service_areas.*.area_id' => 'integer|distinct|exists:areas,id',
                'service_areas.*.pref_id' => 'string|regex:/^\d+(?:,\d+)*$/',
                'service_areas.*' => [
                    function ($attr, $value, $fail) {
                        $area_id = $value['area_id'];
                        $area = Area::where('id', $area_id)->first();
                        if (!$area) $fail(__('validation.exists', ['attribute' => 'ID' . $area_id . 'エリア']));
                        $pref_id = $value['pref_id'];
                        $parts = explode(',', $pref_id);
                        foreach ($parts as $part) {
                            $pref = Prefecture::where(['area_id' => $area_id, 'id' => (int)$part])->first();
                            if (!$pref) $fail(__('validation.exists', ['attribute' => 'ID' . $part . '都道府県']));
                        }
                    }
                ],
            ];
        }
        return [
            'service_areas' => 'required|array',
            'service_areas.*.area_id' => 'integer|required|distinct|exists:areas,id',
            'service_areas.*.pref_id' => 'required|string|regex:/^\d+(?:,\d+)*$/',
            'service_areas.*' => [
                function ($attr, $value, $fail) {
                    $area_id = $value['area_id'];
                    $area = Area::where('id', $area_id)->first();
                    if (!$area) $fail(__('validation.exists', ['attribute' => 'ID' . $area_id . 'エリア']));
                    $pref_id = $value['pref_id'];
                    $parts = explode(',', $pref_id);
                    foreach ($parts as $part) {
                        $pref = Prefecture::where(['area_id' => $area_id, 'id' => (int)$part])->first();
                        if (!$pref) $fail(__('validation.exists', ['attribute' => 'ID' . $part . '都道府県']));
                    }
                }
            ],
        ];
    }

    public function favoriteServiceRules()
    {
        return [
            'buyer_id' => 'required|integer|exists:buyers,account_id',
        ];
    }

    public function reservesRulesSeller()
    {
        return [
            'year' => 'required|integer',
            'month' => 'required|integer',
        ];
    }

    public function reservesRulesBuyer()
    {
        return [
            'year' => 'required|integer',
            'month' => 'required|integer',
            'buyer_id' => 'required|integer|exists:buyers,account_id'
        ];
    }

    public function businessScheduleRules($type_id = null, $is_draft = false, $is_reserves = false)
    {
        if ($is_draft || !$is_reserves || ($type_id != 2 && $type_id != 3)) {
            return [
                // 'service_reserve_setting' => 'nullable|array',
                // 'service_reserve_setting.max' => 'nullable|integer|min:1',
                // // 'service_reserve_setting.time_distance' => 'nullable|date_format:"G:i"|time_distance',
                // 'service_reserve_setting.duration_before' => 'nullable|integer|min:1',
                // 'service_reserve_setting.duration_after' => 'nullable|integer|min:1',
                // 'service_reserve_setting.type_duration_after' => 'nullable|integer|min:1',
                // 'service_hours' => 'nullable|array|min:7',
                // 'service_hours.*.day_of_week' => 'nullable|integer|distinct|min:0|max:6',
                // 'service_hours.*.work_hour' => 'nullable|array|work_hours',
                // 'service_hours.*.work_hour.*.start' => 'nullable|date_format:"G:i"|time_schedule',
                // 'service_hours.*.work_hour.*.end' => 'nullable|date_format:"G:i"|after:service_hours.*.work_hour.*.start|time_schedule',
                // 'service_hours.*.status' => 'nullable|boolean',
            ];
        }
        return [
            'service_reserve_setting' => 'required|array',
            'service_reserve_setting.is_enable' => 'required|boolean',
            'service_reserve_setting.max' => 'nullable|integer|min:1',
            'service_reserve_setting.time_distance' => 'required|date_format:"G:i"|time_distance',
            'service_reserve_setting.duration_before' => 'required|integer|min:1',
            'service_reserve_setting.duration_after' => 'required|integer|min:1',
            'service_reserve_setting.type_duration_after' => 'required|integer|min:1',
            'service_hours' => 'required|array|min:7',
            'service_hours.*.day_of_week' => 'required|integer|distinct|min:0|max:6',
            'service_hours.*.work_hour' => 'required|array|work_hours',
            'service_hours.*.work_hour.*.start' => 'required|date_format:"G:i"|time_schedule',
            'service_hours.*.work_hour.*.end' => 'required|date_format:"G:i"|after:service_hours.*.work_hour.*.start|time_schedule',
            'service_hours.*.status' => 'required|boolean',
        ];
    }

    public function businessScheduleTempRules()
    {
        return [
            'date' => 'required|date_format:Y-m-d|after:'. Carbon::now()->format('Y-m-d'),
            'work_hour' => 'required|array|work_hours',
            'work_hour.*.start' => 'required|date_format:"G:i"|time_schedule',
            'work_hour.*.end' => 'required|date_format:"G:i"|after:work_hour.*.start|time_schedule',
            'status' => 'required|boolean',
        ];
    }

    public function tagRules($service_id)
    {
        return [
            'tags.*' => [
                'string',
                'distinct',
                function ($attr, $value, $fail) use ($service_id) {
                    $tag = Tag::where(['name' => $value])->first();
                    if ($tag) {
                        $service_tag = ServiceTag::where(['service_id' => $service_id, 'tag_id' => $tag['id']])->first();
                        if ($service_tag) $fail(__('validation.tag_exists', ['attribute' => $value]));
                    }
                }
            ]
        ];
    }

    public function tagsDeleteRules($service_id, $user_id)
    {
        return [
            'tag_deletes.*' => [
                'string',
                'distinct',
                function ($attr, $value, $fail) use ($service_id, $user_id) {
                    $tag = Tag::where(['name' => $value])->first();

                    if ($tag) {
                        $service_tag = ServiceTag::where(['service_id' => $service_id, 'tag_id' => $tag['id']])->first();
                        if (!$service_tag) $fail(__('app.invalid', ['attribute' => $value]));
                    }
                }
            ]
        ];
    }

    public function stepDeleteRules()
    {
        return [
            'id_step_deletes' => 'array',
            'id_step_deletes.*' => 'integer|exists:service_steps,id',
        ];
    }

    public function serviceCourseDeleteRules()
    {
        return [
            'id_service_courses_deletes' => 'array',
            'id_service_courses_deletes.*' => 'string|exists:service_courses,course_id',
        ];
    }

    public function serviceImageDeleteRules()
    {
        return [
            'id_service_image_deletes' => 'array',
            'id_service_image_deletes.*' => 'integer|exists:service_images,id',
        ];
    }

    public function serviceDeliveryRules($type_id = null, $is_draft = null)
    {
        if ($type_id != 1 || $is_draft) {
            return [
                'service_delivery' => 'array|nullable',
                'service_delivery.interval' => 'nullable|boolean',
                'service_delivery.month_delivery' => 'nullable|integer',
                'service_delivery.skip' => 'nullable|boolean',
                'service_delivery.with_skip' => 'nullable|integer'
            ];
        }
        return [
            'service_delivery' => 'required|array',
            'service_delivery.interval' => 'required|boolean',
            'service_delivery.month_delivery' => 'nullable|integer',
            'service_delivery.skip' => 'required|boolean',
            'service_delivery.with_skip' => 'nullable|integer'
        ];
    }

    public function serviceUrlWebsiteRules($type_id = null, $is_draft = null)
    {
        if ($type_id != 4 || $type_id == 2 || $is_draft) {
            return [
                'url_website' => 'nullable|string',
            ];
        }
        return [
            'url_website' => 'required|string',
        ];
    }

    public function adminSettingServiceRules()
    {
        return [
            'sort_type' => 'nullable|string|regex:/^\d{1}+(?:,\d{1}+)*$/',
        ];
    }

    public function validator($data, $rules)
    {
        $validator = Validator::make($data, $rules);
        $errors = $validator->errors();
        return $errors->first();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/service/create",
     *     summary="Service a newly created",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *               @OA\Property(
     *                     description="ID need to display",
     *                     property="id",
     *                     type="integer",
     *                     example="123456789",
     *               ),
     *               @OA\Property(
     *                     description="Hash ID content need to display",
     *                     property="hash_id",
     *                     type="string",
     *                     example="hash_id",
     *              ),
     *               @OA\Property(
     *                     description="Seller ID need to display",
     *                     property="seller_id",
     *                     type="integer",
     *                     example="1",
     *              ),
     *              @OA\Property(
     *                     description="Categories ID need to display",
     *                     property="service_cat_id",
     *                     type="integer",
     *                     example="1",
     *              ),
     *              @OA\Property(
     *                     description="Type ID need to display",
     *                     property="service_type_id",
     *                     type="integer",
     *                     example="1",
     *              ),
     *              @OA\Property(
     *                     description="Name need to display",
     *                     property="name",
     *                     type="string",
     *                     example="Name",
     *              ),
     *              @OA\Property(
     *                     description="Address need to display",
     *                     property="address",
     *                     type="string",
     *                     example="address",
     *              ),
     *              @OA\Property(
     *                     description="Zipcode need to display",
     *                     property="zipcode",
     *                     type="string",
     *                     example="1200015",
     *              ),
     *              @OA\Property(
     *                     description="Area need to display",
     *                     property="area",
     *                     type="string",
     *                     example="area",
     *              ),
     *              @OA\Property(
     *                     description="caption need to display",
     *                     property="caption",
     *                     type="string",
     *                     example="caption",
     *              ),
     *              @OA\Property(
     *                     description="Service content need to display",
     *                     property="service_content",
     *                     type="string",
     *                     example="service_content",
     *              ),
     *              @OA\Property(
     *                     description="Private content need to display",
     *                     property="private",
     *                     type="boolean",
     *                     example="0",
     *              ),
     *               @OA\Property(
     *                     description="is_reserves need to display",
     *                     property="is_reserves",
     *                     type="boolean",
     *                     example="0",
     *              ),
     *              @OA\Property(
     *                     description="Max content need to display",
     *                     property="max",
     *                     type="integer",
     *                     example="100",
     *              ),
     *              @OA\Property(
     *                     description="URL content need to display",
     *                     property="url",
     *                     type="string",
     *                     example="url",
     *              ),
     *              @OA\Property(
     *                     description="URL website need to display",
     *                     property="url_website",
     *                     type="string",
     *                     example="url_website",
     *              ),
     *              @OA\Property(property="service_images", description="Array image need to display", type="array",
     *                     @OA\Items(type="file")
     *              ),
     *              @OA\Property(property="tags", description="Array tags need to display", type="array",
     *                     @OA\Items(type="string")
     *              ),
     *              @OA\Property(property="service_areas", description="Array tags need to display", type="array",
     *                     @OA\Items(type="object",
     *                              @OA\Property(property="area_id", type="integer",example="1"),
     *                              @OA\Property(property="pref_id", type="string",example="1,2,3"),
     *                              ),
     *              ),
     *              @OA\Property(property="service_courses", description="Array tags need to display", type="array",
     *                     @OA\Items(type="object",
     *                              @OA\Property(property="name", type="string",example="course_name"),
     *                              @OA\Property(property="price", type="integer",example="5000"),
     *                              @OA\Property(property="cycle", type="integer",example="100"),
     *                              @OA\Property(property="content", type="integer",example="content"),
     *                              @OA\Property(property="firstPr", type="integer",example="0"),
     *                              @OA\Property(property="image", type="file"),
     *                              @OA\Property(property="gender_restrictions", type="integer", example="null"),
     *                              @OA\Property(property="age_confirm", type="integer", example="20"),
     *                              ),
     *              ),
     *              @OA\Property(property="service_steps", description="Array tags need to display", type="array",
     *                     @OA\Items(type="object",
     *                              @OA\Property(property="number", type="integer",example="1"),
     *                              @OA\Property(property="title", type="string",example="title"),
     *                              @OA\Property(property="content", type="integer",example="content"),
     *                              ),
     *              ),
     *              @OA\Property(property="service_delivery", description="Array service_delivery need to display", type="object",
     *                              @OA\Property(property="interval", type="boolean",example="0"),
     *                              @OA\Property(property="month_delivery", type="integer",example="null"),
     *                              @OA\Property(property="skip", type="boolean",example="0"),
     *                              @OA\Property(property="with_skip", type="integer",example="null"),
     *                              ),
     *              @OA\Property(property="service_reserve_setting", description="Array service_reserve_setting need to display", type="object",
     *                              @OA\Property(property="is_enable", type="integer",example="1"),
     *                              @OA\Property(property="max", type="integer",example="100"),
     *                              @OA\Property(property="time_distance", type="string",example="1:00"),
     *                              @OA\Property(property="duration_before", type="integer",example="2"),
     *                              @OA\Property(property="duration_after", type="integer",example="2"),
     *                              @OA\Property(property="type_duration_after", type="integer",example="1"),
     *                              ),
     *             @OA\Property(property="service_hours", description="Array service_hours need to display from sunday to saturday", type="array",
     *                              @OA\Items(type="object",
     *                              @OA\Property(property="day_of_week", type="integer",example="0"),
     *                              @OA\Property(property="work_hour", type="array",
     *                                @OA\Items(type="object",
     *                                @OA\Property(property="start", type="string",example="9:00"),
     *                                @OA\Property(property="end", type="string",example="22:00"),
     *                                ),
     *                              ),
     *                              @OA\Property(property="status", type="boolean",example="0"),
     *                              ),
     *             ),
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

            $data = $request->all();

            $user = Auth::guard('users')->user();
            //check permission seller
            if (!$this->seller->findByAccountId($user->id)) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            //check seller_id login and seller_id data
            if (isset($data['seller_id']) && $user->id != $data['seller_id']) return $this->sendError(__('app.invalid', ['attribute' => __('app.seller')]), Response::HTTP_OK);

            DB::beginTransaction();

            $service_id = isset($data['service_id']) ? $data['service_id'] : null;
            $hash_id = isset($data['hash_id']) ? $data['hash_id'] : null;
            $url_private = isset($data['url_private']) ? $data['url_private'] : null;
            $is_draft = isset($data['is_draft']) && $data['is_draft'] == true ? true : false;
            $is_delivery = (isset($data['service_type_id']) && $data['service_type_id'] == 1) ? true : false;
            $type = isset($data['service_type_id']) ? $data['service_type_id'] : null;
            $is_reserves = isset($data['is_reserves']) && $data['is_reserves'] == true ? true : false;

            $errors = $this->validator($data, $this->serviceRules($is_draft));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            // $errors = $this->validator($data, $this->serviceUrlWebsiteRules($type, $is_draft));
            // if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->servicePrivateRules($service_id,  $hash_id, $url_private));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->serviceImageRules($is_draft));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->serviceCourseRules($is_draft, $is_delivery));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->serviceStepRules($is_draft));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->serviceAreaRules($is_draft));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->serviceDeliveryRules($type, $is_draft));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->businessScheduleRules($type, $is_draft, $is_reserves));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->serviceLinkRules($type, $is_draft));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $new_service = new Service();
            $new_service['id'] = is_null($service_id) ? $this->generateServiceId() : $service_id;
            $new_service['hash_id'] = isset($data['hash_id']) ? $data['hash_id'] : md5($new_service['id']);

            if (isset($data['name']))  $new_service['name'] = $data['name'];
            if (isset($data['service_cat_id']))  $new_service['service_cat_id'] = $data['service_cat_id'];
            if (isset($data['seller_id']))  $new_service['seller_id'] = $data['seller_id'];
            if (isset($data['service_type_id']))  $new_service['service_type_id'] = $data['service_type_id'];
            if (isset($data['caption']))  $new_service['caption'] = $data['caption'];
            if (isset($data['service_content']))  $new_service['service_content'] = $data['service_content'];
            if (isset($data['area']))  $new_service['area'] = $data['area'];
            if (isset($data['address']))  $new_service['address'] = $data['address'];
            if (isset($data['max'])) $new_service["max"] = $data["max"];
            if (isset($data['zipcode']))  $new_service['zipcode'] = $data['zipcode'];
            if (isset($data['private']))  $new_service['private'] = $data['private'];
            if (isset($data['is_draft']))  $new_service['is_draft'] = $data['is_draft'];
            if (isset($data['url_private']) &&  $data['private'] == 1) $new_service['url_private'] = $data['url_private'];
            if (isset($data['is_reserves']))  $new_service['is_reserves'] = $data['is_reserves'];
            if (isset($data['url_website']) && isset($data['service_type_id']) && $data['service_type_id'] == 4)  $new_service['url_website'] = $data['url_website'];
            
            if (!empty($data['address'])) {
                $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'key' => config('map.key'),
                    'address' =>  $data['address'],
                ]);

                $results = json_decode($response->body())->results;

                if ($results) {
                    $geometry = $results[0]->geometry;
                    $location = $geometry->location;
                    $new_service['lat'] = $location->lat;
                    $new_service['lng'] = $location->lng;
                }
            }

            $new_service->save();

            $id = $new_service['id'];

            if (isset($data['service_areas'])) {
                $service_areas = $data['service_areas'];
                foreach ($service_areas as $area) {
                    $parts = explode(',', $area['pref_id']);
                    foreach ($parts as $part) {
                        $service_area = new ServiceArea();
                        $service_area['service_id'] = $id;
                        $service_area['area_id'] = $area['area_id'];
                        $service_area['pref_id'] = $part;
                        $service_area->save();
                    }
                }
            }
            // create images service
            if (isset($data['service_images'])) {
                $file_service_images = $data['service_images'];
                $count_image_upload = 0;
                foreach ($file_service_images as $file) {
                    $file_saved = new ServiceImage();
                    if ($file) {
                        $extension = $file->getClientOriginalExtension();
                        $fileName = $id . '-' . date('mdHis') . uniqid('-') . '.' . $extension;
                        $file->move(public_path('storage/images/services/' . $id), $fileName);
                        $file_saved["service_id"] = $id;
                        $file_saved["image_url"] = 'images/services/' . $id . '/' . $fileName;
                        $count_image_upload++;
                        $file_saved = $file_saved->save();
                    }
                }
                for ($i = 1; $i <= 5 - $count_image_upload; $i++) {
                    $file_saved = new ServiceImage();
                    $file_saved["service_id"] = $id;
                    $file_saved["image_url"] = null;
                    $file_saved = $file_saved->save();
                }
            }

            // create link service
            if (isset($data['url'])) {
                $new_link = new ServiceLink();
                $new_link['id'] = $id;
                $new_link['url'] = $data['url'];
                $new_link['jump_count'] = 0;
                $new_link->save();
            }

            //add tag
            if (isset($data['tags'])) {
                foreach ($data['tags'] as $tag_name) {
                    $tag = $this->tag->getByName($tag_name, $user->id);
                    if ($tag) {
                        $this->serviceTag->create(['service_id' => $id, 'tag_id' => $tag['id']]);
                    } else {
                        $tag = $this->tag->create(['name' => $tag_name]);
                        $this->serviceTag->create(['service_id' => $id, 'tag_id' => $tag['id']]);
                    }
                    $this->recommendHashTag->redisCountRecommendHashTag($tag->id);
                }
            }

            //service courses
            if (isset($data['service_courses'])) {
                foreach ($data['service_courses'] as $courses) {
                    //create
                    $courses['service_id'] = $id;
                    $courses['course_id'] = $this->generateCourseId();
                    $courses_data = $this->serviceCourse->create($courses);
                    if (isset($courses['image'])) {
                        $file = $courses['image'];
                        $extension = $file->getClientOriginalExtension();
                        $fileName = $courses_data['course_id'] . '-' . date('mdHis') . uniqid('-') . '.' . $extension;
                        $file->move(public_path('storage/images/services/' . $id . '/courses'), $fileName);
                        $file_saved = new ServiceCourseImage();
                        $file_saved["course_id"] = $courses_data['course_id'];
                        $file_saved["image_url"] = 'images/services/' . $id . '/courses/' . $fileName;
                        $file_saved = $file_saved->save();
                    }
                }
            }

            //service step
            if (isset($data['service_steps'])) {
                foreach ($data['service_steps'] as $step) {
                    $step['service_id'] = $id;
                    $this->serviceStep->create($step);
                }
            }

            if (isset($data['service_delivery'])) {
                $service_delivery = $data['service_delivery'];
                $service_delivery['service_id'] = $id;
                $this->serviceDelivery->create($service_delivery);
            }

            // create service reserve setting
            if (isset($data['service_reserve_setting'])) {
                $reservesSetting = $data['service_reserve_setting'];
                $reservesSetting['service_id'] = $id;
                $this->serviceReserveSetting->create($reservesSetting);
            }

            // create service hours
            if (isset($data['service_hours'])) {
                foreach ($data['service_hours'] as $item) {
                    $json_work_hour = json_encode($item['work_hour']);
                    $item['work_hour'] = $json_work_hour;
                    $item['service_id'] = $id;
                    $this->serviceHour->create($item);
                }
            }

            DB::commit();
            if ($is_draft) {
                return $this->sendSuccess(__('app.save_draft_success'));
            } else {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.service')]));
            }
        } catch (Exception $e) {
            DB::rollback();
            $this->log("createService", null, ["request" => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly updated resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/service/edit/{id}",
     *     summary="Service a newly updated",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          description="ID of service need to display",
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
     *               @OA\Property(
     *                     description="Seller ID need to display",
     *                     property="seller_id",
     *                     type="integer",
     *                     example="1",
     *              ),
     *              @OA\Property(
     *                     description="Categories ID need to display",
     *                     property="service_cat_id",
     *                     type="integer",
     *                     example="1",
     *              ),
     *              @OA\Property(
     *                     description="Type ID need to display",
     *                     property="service_type_id",
     *                     type="integer",
     *                     example="1",
     *              ),
     *              @OA\Property(
     *                     description="Name need to display",
     *                     property="name",
     *                     type="string",
     *                     example="Name",
     *              ),
     *              @OA\Property(
     *                     description="Address need to display",
     *                     property="address",
     *                     type="string",
     *                     example="address",
     *              ),
     *              @OA\Property(
     *                     description="Zipcode need to display",
     *                     property="zipcode",
     *                     type="string",
     *                     example="1200015",
     *              ),
     *              @OA\Property(
     *                     description="Area need to display",
     *                     property="area",
     *                     type="string",
     *                     example="area",
     *              ),
     *              @OA\Property(
     *                     description="caption need to display",
     *                     property="caption",
     *                     type="string",
     *                     example="caption",
     *              ),
     *              @OA\Property(
     *                     description="Service content need to display",
     *                     property="service_content",
     *                     type="string",
     *                     example="service_content",
     *              ),
     *              @OA\Property(
     *                     description="Private content need to display",
     *                     property="private",
     *                     type="boolean",
     *                     example="0",
     *              ),
     *              @OA\Property(
     *                     description="is_reserves need to display",
     *                     property="is_reserves",
     *                     type="boolean",
     *                     example="0",
     *              ),
     *              @OA\Property(
     *                     description="Max content need to display",
     *                     property="max",
     *                     type="integer",
     *                     example="100",
     *              ),
     *              @OA\Property(
     *                     description="URL content need to display",
     *                     property="url",
     *                     type="string",
     *                     example="url",
     *              ),
     *              @OA\Property(
     *                     description="URL website need to display",
     *                     property="url_website",
     *                     type="string",
     *                     example="url_website",
     *              ),
     *              @OA\Property(property="service_images", description="Array image need to display", type="array",
     *                      @OA\Items(type="object",
     *                              @OA\Property(property="id", type="integer",example="1"),
     *                              @OA\Property(property="status", type="boolean",example="0"),
     *                              @OA\Property(property="file", type="file"),
     *                              ),
     *              ),
     *              @OA\Property(property="tags", description="Array tags need to display", type="array",
     *                     @OA\Items(type="string")
     *              ),
     *              @OA\Property(property="service_areas", description="Array tags need to display", type="array",
     *                     @OA\Items(type="object",
     *                              @OA\Property(property="area_id", type="integer",example="1"),
     *                              @OA\Property(property="pref_id", type="string",example="1,2,3"),
     *                              ),
     *              ),
     *              @OA\Property(property="service_courses", description="Array tags need to display", type="array",
     *                     @OA\Items(type="object",
     *                              @OA\Property(property="course_id", type="string",example="course_id"),
     *                              @OA\Property(property="name", type="string",example="course_name"),
     *                              @OA\Property(property="price", type="integer",example="5000"),
     *                              @OA\Property(property="cycle", type="integer",example="100"),
     *                              @OA\Property(property="content", type="integer",example="content"),
     *                              @OA\Property(property="firstPr", type="integer",example="0"),
     *                              @OA\Property(property="image", type="file"),
     *                              @OA\Property(property="gender_restrictions", type="integer", example="null"),
     *                              @OA\Property(property="age_confirm", type="integer", example="20"),
     *                              ),
     *              ),
     *              @OA\Property(property="service_steps", description="Array tags need to display", type="array",
     *                     @OA\Items(type="object",
     *                              @OA\Property(property="number", type="integer",example="1"),
     *                              @OA\Property(property="title", type="string",example="title"),
     *                              @OA\Property(property="content", type="integer",example="content"),
     *                              ),
     *              ),
     *              @OA\Property(property="tag_deletes", description="Array tags need to display", type="array",
     *                     @OA\Items(type="string"),
     *              ),
     *              @OA\Property(property="id_service_courses_deletes", description="Array ID courses of service deletes", type="array",
     *                     @OA\Items(type="string"),
     *              ),
     *              @OA\Property(property="id_step_deletes", description="Array ID step of service deletes", type="array",
     *                     @OA\Items(type="integer"),
     *              ),
     *              @OA\Property(property="service_reserve_setting", description="Array service_reserve_setting need to display", type="object",
     *                              @OA\Property(property="is_enable", type="integer",example="1"),
     *                              @OA\Property(property="max", type="integer",example="100"),
     *                              @OA\Property(property="time_distance", type="string",example="1:00"),
     *                              @OA\Property(property="duration_before", type="integer",example="2"),
     *                              @OA\Property(property="duration_after", type="integer",example="2"),
     *                              @OA\Property(property="type_duration_after", type="integer",example="1"),
     *                              ),
     *             @OA\Property(property="service_hours", description="Array service_hours need to display from sunday to saturday", type="array",
     *                              @OA\Items(type="object",
     *                              @OA\Property(property="day_of_week", type="integer",example="0"),
     *                              @OA\Property(property="work_hour", type="array",
     *                                @OA\Items(type="object",
     *                                @OA\Property(property="start", type="string",example="9:00"),
     *                                @OA\Property(property="end", type="string",example="22:00"),
     *                                ),
     *                              ),
     *                              @OA\Property(property="status", type="boolean",example="0"),
     *                              ),
     *             ),
     *             @OA\Property(property="service_delivery", description="Array service_delivery need to display", type="object",
     *                              @OA\Property(property="interval", type="boolean",example="0"),
     *                              @OA\Property(property="month_delivery", type="integer",example="null"),
     *                              @OA\Property(property="skip", type="boolean",example="0"),
     *                              @OA\Property(property="with_skip", type="integer",example="null"),
     *                              ),
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
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function update($id, Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();

            $user = Auth::guard('users')->user();

            //check permission seller
            if (!$this->seller->findByAccountId($user->id)) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            //check seller_id login and seller_id data
            if (isset($data['seller_id']) && $user->id != $data['seller_id']) return $this->sendError(__('app.invalid', ['attribute' => __('app.seller')]), Response::HTTP_OK);

            //check service exists
            $service = $this->service->find($id);
            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $hash_id = $service['hash_id'];
            $url_private = isset($data['url_private']) ? $data['url_private'] : null;
            $is_draft = isset($data['is_draft']) && $data['is_draft'] == true ? true : false;
            $is_delivery = ($service->service_type_id == 1) ? true : false;
            $type = isset($data['service_type_id']) ? $data['service_type_id'] : null;
            $service_images = $service->serviceImages;
            $image_current = isset($service_images) ? $service_images->count() : null;

            if (isset($data['service_images'])) {
                $service_images_req = $data['service_images'];
                $check_image_delete = 0;
                $check_image_upload = 0;
                $count_image_null = $this->serviceImage->countImageNullByServiceId($id);
                $count_image_current = 5 - $count_image_null;
    
                foreach ($service_images_req as $service_image) {
                    if (isset($service_image['id'])) {
                        // image change or delete
                        if ($service_image['status'] == 1) {
                            // change image
                            if (!isset($service_image['file']) && empty($service_image['file'])) {
                                $check_image_delete++;
                            }
                        }
                    } else {
                        // image upload new
                        if (isset($service_image['file']) && !empty($service_image['file'])) $check_image_upload++;
                    }   
                }
                
                if ($check_image_delete >= $count_image_current && $check_image_upload == 0) return $this->sendError(__('app.image_service_required'));

                // return $this->sendSuccess($count_image_null);
                //サービス画像フィールドは必須です。
            }
            
            $errors = $this->validator($data, $this->serviceRules($is_draft));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            // $errors = $this->validator($data, $this->serviceUrlWebsiteRules($type, $is_draft));
            // if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->servicePrivateUpdateRules($id, $hash_id, $url_private));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            // $errors = $this->validator($data, $this->serviceUpdateImageRules($image_current));
            // if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->serviceCourseRules($is_draft, $is_delivery));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->serviceStepRules($is_draft));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->tagRules($id, $user->id));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            // $errors = $this->validator($data, $this->favoriteTagRules($id));
            // if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->tagsDeleteRules($id, $user->id));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->stepDeleteRules());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->serviceCourseDeleteRules());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            // $errors = $this->validator($data, $this->serviceImageDeleteRules());
            // if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->serviceAreaRules($is_draft));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            // $errors = $this->validator($data, $this->serviceDeliveryRules($type, $is_draft));
            // if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $errors = $this->validator($data, $this->businessScheduleRules($type, $is_draft));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $service_update = [];

            //update property service
            if (isset($data['name'])) $service_update["name"] = $data["name"];
            if (isset($data['service_cat_id'])) $service_update["service_cat_id"] = $data["service_cat_id"];
            if (isset($data['service_type_id'])) $service_update["service_type_id"] = $data["service_type_id"];
            if (isset($data['area'])) $service_update["area"] = $data["area"];
            if (isset($data['address'])) $service_update["address"] = $data["address"];
            if (isset($data['zipcode'])) $service_update["zipcode"] = $data["zipcode"];
            if (isset($data['caption'])) $service_update["caption"] = $data["caption"];
            if (isset($data['service_content'])) $service_update["service_content"] = $data["service_content"];
            if (isset($data['private'])) $service_update["private"] = $data["private"];
            if (isset($data['is_draft'])) $service_update["is_draft"] = $data["is_draft"];
            if (isset($data['max'])) $service_update["max"] = $data["max"];
            // if (isset($data['age_confirm'])) $service_update["age_confirm"] = $data["age_confirm"];
            if (isset($data['url_private']) && $data['private'] == 1) $service_update["url_private"] = $data["url_private"];
            if (isset($data['url_website']) && isset($data['service_type_id']) && $data['service_type_id'] == 4) $service_update["url_website"] = $data["url_website"];
            if (isset($data['is_reserves'])) $service_update['is_reserves'] = $data['is_reserves'];

            if (!empty($data['address']) && $service->address != $data['address']) {
                $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'key' => config('map.key'),
                    'address' =>  $data['address'],
                ]);

                $results = json_decode($response->body())->results;

                if ($results) {
                    $geometry = $results[0]->geometry;
                    $location = $geometry->location;
                    $service_update['lat'] = $location->lat;
                    $service_update['lng'] = $location->lng;
                }
            }
            
            //update service
            $this->service->updateService($id, $service_update);

            //delete service area
            $this->serviceArea->removeByServiceId($id);

            //update service area
            if (isset($data['service_areas'])) {
                $service_areas = $data['service_areas'];
                foreach ($service_areas as $area) {
                    $parts = explode(',', $area['pref_id']);
                    foreach ($parts as $part) {
                        $service_area = new ServiceArea();
                        $service_area['service_id'] = $id;
                        $service_area['area_id'] = $area['area_id'];
                        $service_area['pref_id'] = $part;
                        $service_area->save();
                    }
                }
            }

            // update images service
            if (isset($data['service_images'])) {
                $service_images = $data['service_images'];
                $count_image_upload = 0;
                foreach ($service_images as $service_image) {
                    if (!isset($service_image['id'])) {
                        if (isset($service_image['file']) && !empty($service_image['file']) && is_file($service_image['file']) && $service_image['status'] == 1) {
                            if ($image_current > 0) {
                                $rules_image = [
                                    'status' => 'required|boolean',
                                    'file' => 'nullable|mimes:jpeg,jpg,png|max:10240',
                                ];
                            } else {
                                $rules_image = [
                                    'status' => 'required|boolean',
                                    'file' => 'nullable|mimes:jpeg,jpg,png|max:10240',
                                ];
                            }
                            $errors = $this->validator($service_image, $rules_image);
                            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

                            $file_saved = new ServiceImage();
                            $file = $service_image['file'];
                            $extension = $file->getClientOriginalExtension();
                            $fileName = $id . '-' . date('mdHis') . uniqid('-') . '.' . $extension;
                            $file->move(public_path('storage/images/services/' . $id), $fileName);
                            $file_saved["service_id"] = $id;
                            $file_saved["image_url"] = 'images/services/' . $id . '/' . $fileName;
                            $count_image_upload++;
                            $file_saved = $file_saved->save();
                        }
                    } else {
                        $service_image_update = $this->serviceImage->find($service_image['id']);
                        if ($service_image['status'] == 1) {
                            if (isset($service_image['file']) && !empty($service_image['file']) && is_file($service_image['file'])) {
                                if ($image_current > 0) {
                                    $rules_image = [
                                        'id' => 'required|integer|exists:service_images,id',
                                        'status' => 'required|boolean',
                                        'file' => 'nullable|mimes:jpeg,jpg,png|max:10240',
                                    ];
                                } else {
                                    $rules_image = [
                                        'id' => 'integer|exists:service_images,id',
                                        'status' => 'required|boolean',
                                        'file' => 'nullable|mimes:jpeg,jpg,png|max:10240',
                                    ];
                                }
                                $errors = $this->validator($service_image, $rules_image);
                                if ($errors) return $this->sendError($errors, Response::HTTP_OK);
                                $file = $service_image['file'];
                                //remove images
                                $path = $service_image_update['image_url'];
                                if (Storage::disk('public')->exists($path)) {
                                    Storage::disk('public')->delete($path);
                                }
                                //update image
                                $extension = $file->getClientOriginalExtension();
                                $fileName = $id . '-' . date('mdHis') . uniqid('-') . '.' . $extension;
                                $file->move(public_path('storage/images/services/' . $id), $fileName);
                                $service_image_update["image_url"] = 'images/services/' . $id . '/' . $fileName;
                            } 
                        } else {
                            $service_image_update->delete();
                        }
                        $this->serviceImage->updateServiceImage($service_image['id'], $service_image_update->toArray());
                    }
                }
                // if ($count_image_upload > 0) {
                //     for ($i = 1; $i <= 5 - $count_image_upload; $i++) {
                //         $file_saved = new ServiceImage();
                //         $file_saved["service_id"] = $id;
                //         $file_saved["image_url"] = null;
                //         $file_saved = $file_saved->save();
                //     }
                // }
            }

            //update link service
            if (isset($data['url'])) {
                $service_link = $this->serviceLink->find($id);
                if (!$service_link) {
                    $new_link = new ServiceLink();
                    $new_link['id'] = $id;
                    $new_link['url'] = $data['url'];
                    $new_link['jump_count'] = 0;
                    $new_link->save();
                } else {
                    $service_link['url'] = $data['url'];
                    $this->serviceLink->updateServiceLink($id, $service_link->toArray());
                }
            }

            //remove tag
            if (isset($data['tag_deletes'])) {
                $tag_names = $data['tag_deletes'];
                foreach ($tag_names as $tag_name) {
                    $tag = $this->tag->getByName($tag_name);
                    if ($tag) {
                        if ($this->serviceTag->findByServiceIdAndTagId($id, $tag->id)) {
                            $this->serviceTag->deleteByServiceIdAndTagId($id, $tag->id);
                        }
                    }
                }
            }

            // add tag
            if (isset($data['tags'])) {
                foreach ($data['tags'] as $tag_name) {
                    $tag = $this->tag->getByName($tag_name);
                    if ($tag) {
                        $this->serviceTag->create(['service_id' => $id, 'tag_id' => $tag['id']]);
                    } else {
                        $tag = $this->tag->create(['name' => $tag_name]);
                        $this->serviceTag->create(['service_id' => $id, 'tag_id' => $tag['id']]);
                    }
                }
            }

            //service courses
            if (isset($data['service_courses'])) {
                foreach ($data['service_courses'] as $courses) {
                    if (isset($courses['course_id'])) {
                        $courses_data = $this->serviceCourse->findByCourseId($courses['course_id']);
                        if (isset($courses_data)) {
                            //update
                            if (isset($courses['name'])) $courses_data["name"] = $courses["name"];
                            if (isset($courses['price'])) $courses_data["price"] = $courses["price"];
                            if (isset($courses['cycle'])) $courses_data["cycle"] = $courses["cycle"];
                            if (isset($courses['content'])) $courses_data["content"] = $courses["content"];
                            // if (isset($courses['max'])) $courses_data["max"] = $courses["max"];
                            if (isset($courses['firstPr'])) $courses_data["firstPr"] = $courses["firstPr"];
                            if (isset($courses['age_confirm'])) $courses_data["age_confirm"] = $courses["age_confirm"];
                            if (isset($courses['gender_restrictions'])) $courses_data["gender_restrictions"] = $courses["gender_restrictions"];
                            $courses_data->save();
                        } else {
                            $courses['service_id'] = $id;
                            $courses['course_id'] = $this->generateCourseId();
                            $courses_data = $this->serviceCourse->create($courses);
                        }
                    } else {
                        //create
                        $courses['service_id'] = $id;
                        $courses['course_id'] = $this->generateCourseId();
                        $courses_data = $this->serviceCourse->create($courses);
                    }
                    if (isset($courses['image'])) {
                        $img = $this->serviceCourseImage->findByCourseId($courses_data['course_id']);
                        if ($img) {
                            //delete courses image
                            $path = $img['image_url'];
                            if (Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            }
                            $img->delete();
                        }
                        $file = $courses['image'];
                        $extension = $file->getClientOriginalExtension();
                        $fileName = $courses_data['course_id'] . '-' . date('mdHis') . uniqid('-') . '.' . $extension;
                        $file->move(public_path('storage/images/services/' . $id . '/courses'), $fileName);
                        $file_saved = new ServiceCourseImage();
                        $file_saved["course_id"] = $courses_data['course_id'];
                        $file_saved["image_url"] = 'images/services/' . $id . '/courses/' . $fileName;
                        $file_saved = $file_saved->save();
                    }
                }
            }

            //remove courses
            if (isset($data['id_service_courses_deletes'])) {
                foreach ($data['id_service_courses_deletes'] as $course_id) {
                    $course = $this->serviceCourse->findByCourseId($course_id);
                    if ($course) {
                        $img = $this->serviceCourseImage->findByCourseId($course_id);
                        $path = $img['image_url'];
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                        $img->delete();
                        //delete courses
                        $course->delete();
                    }
                }
            }

            //service step
            if (isset($data['service_steps']) || !empty($data['service_steps'])) {
                foreach ($data['service_steps'] as $step) {
                    if (isset($step['id'])) {
                        $step_update = $this->serviceStep->find($step['id']);
                        if (isset($step_update)) {
                            if (isset($step['title'])) $step_update["title"] = $step["title"];
                            if (isset($step['number'])) $step_update["number"] = $step["number"];
                            if (isset($step['content'])) $step_update["content"] = $step["content"];
                            $step_update->save();
                        } else {
                            $step['service_id'] = $id;
                            $this->serviceStep->create($step);
                        }
                    } else {
                        $step['service_id'] = $id;
                        $this->serviceStep->create($step);
                    }
                }
            }

            // update service reserve setting
            if (isset($data['service_reserve_setting'])) {
                $this->serviceReserveSetting->updateServiceReservesSetting($id, $data['service_reserve_setting']);
            }

            // update service hours
            if (isset($data['service_hours'])) {
                foreach ($data['service_hours'] as $item) {

                    $json_work_hour = json_encode($item['work_hour']);
                    $item['work_hour'] = $json_work_hour;
                    $item['service_id'] = $id;

                    $this->serviceHour->updateByServiceID($id, $item['day_of_week'], $item);
                }
            }
            //update service delivery
            if (isset($data['service_delivery'])) {
                $service_delivery = $data['service_delivery'];
                $this->serviceDelivery->createOrUpdateByServiceID($id, $service_delivery);
            }

            //remove courses
            if (isset($data['id_step_deletes'])) {
                foreach ($data['id_step_deletes'] as $step_id) {
                    $service_step = $this->serviceStep->find($step_id);
                    if ($service_step) $service_step->delete();
                }
            }

            DB::commit();
            if ($is_draft) {
                return $this->sendSuccess(__('app.save_draft_success'));
            } else {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.service')]));
            }
        } catch (Exception $e) {
            DB::rollback();
            $this->log("updateService", null, ["request" => $request->all(), 'id' => $id], $e->getFile() . "-" . $e->getLine() ."-". $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the listing review of service.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/service/detail/{hash_id}/list-review",
     *     summary="Get all review of service",
     *     tags={"Service"},
     *      @OA\Parameter(
     *         description="ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="per_page of need to input info",
     *         in="query",
     *         name="per_page",
     *         example="3",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="page of need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getReviewsById($hash_id, Request $request)
    {
        try {
            $service = $this->service->findHashId($hash_id);

            if (!$service) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $reviews = $this->serviceReview->getAllByServiceId($service->id, $request->per_page);

            foreach ($reviews as $review) {
                if (!$review->seller_reply || $review->is_active_seller == 0) {
                    unset($review->seller_reply);
                }
            }

            return $this->sendSuccessResponse($reviews);
        } catch (Exception $e) {
            $this->log("getReviewByServiceId", null, ["request" => $request->all(), 'hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function generateCourseId()
    {
        $random = strval(mt_rand(100000000, 999999999));
        while (!is_null($this->serviceCourse->findByCourseId('A' . $random))) {
            $random = strval(mt_rand(100000000, 999999999));
        }
        return 'A' . $random;
    }

    private function generateServiceId()
    {
        $random = strval(mt_rand(100000000, 999999999));
        while (!is_null($this->service->find($random))) {
            $random = strval(mt_rand(100000000, 999999999));
        }
        return $random;
    }


    /**
     *   @OA\Get(
     *     path="/api/service/recommend-list/{page}",
     *     summary="Get service recommend",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         description="size number need to input info",
     *         in="path",
     *         name="page",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="page number need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="Buyer ID number need to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function getServiceRecommend($page = 10, Request $request)
    {
        try {
            
            if ($request->buyer_id && $this->buyer->findByAccountId($request->buyer_id)) {
                $services = $this->service->getServiceRecommendBuyer($page, $request);
            } else {
                $services = $this->service->getServiceRecommend($page, $request);
            }
            
            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("getServiceRecommend", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Get(
     *     path="/api/service/special/{page}",
     *     summary="Get service special",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         description="size number need to input info",
     *         in="path",
     *         name="page",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="page number need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="Buyer ID need to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function getServiceSpecial($page = 10, Request $request)
    {
        try {
            $services = $this->service->getServiceBySortType($page, ServiceConst::SERVICE_SPECIAL, $request);
            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("getServiceSpecial", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Get(
     *     path="/api/buyer/service/{hash_id}",
     *     summary="buyer get service overview",
     *     tags={"Buyer"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="hash id service need to input info",
     *         in="path",
     *         name="hash_id",
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function serviceOverview($hash_id)
    {
        try {
            $user = Auth::guard('users')->user();

            if (!$user || !$this->buyer->findByAccountId($user->id)) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $service = $this->service->findHashId($hash_id);

            if (!$service) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $service = $this->service->serviceOverview($service->id, $user->id);

            if (!empty($service)) {
                return $this->sendSuccessResponse($service);
            } else {
                return $this->sendSuccess(__('app.not_have_permission'));
            }
        } catch (Exception $e) {
            $this->log("serviceOverview", null, ["service_hash_id" => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Get(
     *     path="/api/buyer/service/deleted/{hash_id}",
     *     summary="buyer get service deleted overview",
     *     tags={"Buyer"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="hash id service need to input info",
     *         in="path",
     *         name="hash_id",
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function serviceDeleted($hash_id)
    {
        try {
            $user = Auth::guard('users')->user();

            if (!$user || !$this->buyer->findByAccountId($user->id)) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $service = $this->service->findHashId($hash_id);

            if (!$service) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $service = $this->service->serviceDeleted($service->id, $user->id);

            if (!empty($service)) {
                return $this->sendSuccessResponse($service);
            } else {
                return $this->sendSuccess(__('app.not_have_permission'));
            }
        } catch (Exception $e) {
            $this->log("serviceOverview", null, ["service_hash_id" => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


        /**
     *   @OA\Get(
     *     path="/api/seller/service/{hash_id}",
     *     summary="seller get service selling",
     *     tags={"Seller"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="hash id service need to input info",
     *         in="path",
     *         name="hash_id",
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function serviceSelling($hash_id)
    {
        try {
            $user = Auth::guard('users')->user();

            if (!$user || !$this->seller->findByAccountId($user->id)) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $service = $this->service->findHashId($hash_id);
            
            if ($user->id != $service->seller_id) return $this->sendSuccess(__('app.not_have_permission'));

            if (!$service) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $service = $this->service->getServiceSellingById($service->id);

            if (!empty($service)) {
                return $this->sendSuccessResponse($service);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->log("serviceOverview", null, ["service_hash_id" => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Get(
     *     path="/api/buyer/service/stop/{id}",
     *     summary="buyer stop service",
     *     tags={"Buyer"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="id service store buyer need to input info",
     *         in="path",
     *         name="id",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function stopService($id)
    {
        try {
            $serviceStoreBuyer = $this->serviceStoreBuyer->find($id);
            $action_payment = $this->actionPayment->getByStoreBuyerId($id);
            // return $this->sendSuccessResponse($serviceStoreBuyer);
            if (isset($serviceStoreBuyer) && isset($action_payment)) {
                $payment = $serviceStoreBuyer->payments->where('payment_status', 0)->whereNull('stripe_charge_id')->first();
                $payment2 = $serviceStoreBuyer->payments->where('payment_status', 1)->first();
                $payment2->update(['payment_status' => 2]);
                if ($payment) {
                    $delivery = $payment->delivery;
                    if ($delivery) {
                        $delivery->delete();
                    }
                    $payment->delete();
                }
                $serviceStoreBuyer['status'] = 2;
                // $serviceStoreBuyer['end'] = $action_payment['charge_at'];
                $serviceStoreBuyer['cancel_at'] = Carbon::now();
                $serviceStoreBuyer->save();

                $this->buyerServiceReserve->deleteAllByBuyerAndCourseId($serviceStoreBuyer['buyer_id'], $serviceStoreBuyer['course_id']);

                return $this->sendSuccess(__('app.stop_service_success'));
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.serviceStoreBuyer')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->log("stopService", null, ["service_id" => $id], $e->getFile() . " " . $e->getLine() . " "  . $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the listing other service of seller.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/service/detail/{hash_id}/other-list",
     *     summary="Get all other service of seller",
     *     tags={"Service"},
     *      @OA\Parameter(
     *         description="Hash ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="Buyer ID need to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="limit need to input info",
     *         in="query",
     *         name="limit",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="page need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getOtherServices($hash_id, Request $request)
    {
        try {
            $service = $this->service->findHashId($hash_id);

            if (!$service)  return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $services = $this->service->getOtherServices($service->id, $service->seller_id, $request);

            $this->numberAccessListServicePage->redisCountNumberAccessServiceList($services);

            if ($request->buyer_id && $this->buyer->findByAccountId($request->buyer_id)) {
                $this->recommendService->redisCountRecommendService($services, $request->buyer_id);
            }

            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("getOtherServices", null, ["request" => $request->all(), 'hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/buyer/service/buying-or-bought",
     *     summary="Get list service buying or bought",
     *     tags={"Buyer"},
     *     security={ {"bearer": {}} },
     *     description="status: 0 is buying, 1 is bought",
     *     @OA\Parameter(
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
     *             required={"buyer_id"},
     *              @OA\Property(
     *                property="buyer_id",
     *                example="1",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="per_page",
     *                example="10",
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
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
    public function getServiceByBuyerId(Request $request)
    {
        try {
            $user = Auth::guard(UserConst::USER_GUARD)->user();

            $buyer = $this->buyer->findByAccountId($request->buyer_id);

            if ($buyer && $user->id == $buyer->account_id) {
                $service = $this->service->getServiceByBuyerId($request->per_page, $request);
                return $this->sendSuccessResponse($service);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->log("getServiceByBuyerId", null, ["request" => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the all service by category.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/service/detail/{hash_id}/same-category-services",
     *     summary="Get all service by category",
     *     tags={"Service"},
     *      @OA\Parameter(
     *         description="Hash ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="Buyer ID need to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="limit need to input info",
     *         in="query",
     *         name="limit",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="page need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getServicesByCategory($hash_id, Request $request)
    {
        try {
            $service = $this->service->findHashId($hash_id);
            if (!$service) return $this->sendError(__('app.not_exist', ['attribute'  => __('app.service')]));

            $services = $this->service->getServicesByCategory($service->id, $service->service_cat_id, $request);

            $this->numberAccessListServicePage->redisCountNumberAccessServiceList($services);

            if ($request->buyer_id && $this->buyer->findByAccountId($request->buyer_id)) {
                $this->recommendService->redisCountRecommendService($services, $request->buyer_id);
            }

            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("getServicesByCategory", null, ["service_cat_id" => $service->service_cat_id, 'hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/buyer/service/favorite",
     *     summary="Get list service favorite",
     *     tags={"Buyer"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
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
     *             required={"buyer_id"},
     *              @OA\Property(
     *                property="buyer_id",
     *                example="1",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="per_page",
     *                example="10",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="sort",
     *                example="1",
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
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
    public function favoriteRegisteredService(Request $request)
    {
        try {
            if ($this->buyer->findByAccountId($request->buyer_id)) {
                $service = $this->service->favoriteRegisteredService($request->per_page, $request);
                return $this->sendSuccessResponse($service);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->log("favoriteRegisteredService", null, ["request" => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the all favorite service of buyer.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/service/detail/{hash_id}/random-services",
     *     summary="Get all favorite service of buyer",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="Hash ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="Buyer ID need to input info",
     *         in="query",
     *         name="buyer_id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="limit need to input info",
     *         in="query",
     *         name="limit",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="page need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Not Found",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getServicesFavoriteByBuyer($hash_id, Request $request)
    {
        try {
            $service = $this->service->findHashId($hash_id);
            if (!$service) return $this->sendError(__('app.not_exist', ['attribute'  => __('app.service')]));
            if ($this->buyer->findByAccountId($request->buyer_id)) {
                $services = [];
                $services = $this->service->getServicesFavoriteByBuyer($service->id, $request);
                return $this->sendSuccessResponse($services);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->log("getServicesFavoriteByBuyer", null, ['hash_id' => $hash_id, "request" => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Register favorite service of buyer.
     *
     *  @OA\Post(
     *     path="/api/service/{id}/favorite",
     *     summary="register favorite service of buyer",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="ID of service need to input info",
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
     *            required={"buyer_id"},
     *            @OA\Property(
     *              property="buyer_id",
     *              example="1",
     *              type="integer",
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
     */
    public function registerFavoriteService($id, Request $request)
    {
        try {
            // validate buyer
            $data = $request->all();
            $errors = $this->validator($data, $this->favoriteServiceRules());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);
            // check service exist
            $service = $this->service->find($id);
            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);
            // check favorited
            if ($this->service->checkFavoritedService($data['buyer_id'], $id)) {
                $this->service->cancelFavoriteService($id, $data['buyer_id']);
                return $this->sendSuccess(__('app.cancel_favorite'));
            } else {
                $this->service->registerFavoriteService($id, $data['buyer_id']);
                $buyer = $this->buyer->findByAccountId($data['buyer_id']);
                $email = $buyer->account->email;
                if ($buyer->account->favorite_service_mail_flg) {
                    $title = "【subsQ】サービスお気に入り登録のお知らせ";
                    $data = [
                        'buyer_name' => $buyer->account_name,
                        'service_name' => $service->name,
                    ];
                    $this->sendEmail('email.email-register-favorite-user', $email, $data, $title);
                }
                return $this->sendSuccess(__('app.register_favorite'));
            }
        } catch (Exception $e) {
            $this->log("registerFavoriteService", null, ['service_id' => $id, "request" => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/admin/service-manage",
     *     summary="Get list",
     *     tags={"Admin"},
     *     security={ {"bearer": {}} },
     *     description="status: 0 is buying, 1 is bought",
     *     @OA\Parameter(
     *         description="page number need to input info",
     *         in="path",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                property="id",
     *                example="",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="seller_id",
     *                example="",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="name",
     *                example="",
     *                type="string",
     *              ),
     *              @OA\Property(
     *                property="service_content",
     *                example="",
     *                type="string",
     *              ),
     *              @OA\Property(
     *                property="private",
     *                example="",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="sort_type",
     *                example="1",
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="per_page",
     *                example="50",
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
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
    public function adminGetAllService(Request $request)
    {
        try {
            $service = $this->service->adminGetAllService($request->per_page, $request);
            return $this->sendSuccessResponse($service);
        } catch (Exception $e) {
            $this->log("adminGetAllService", null, ["request" => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Patch(
     *     path="/api/admin/service/{id}/approve",
     *     summary="Admin approve service",
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
    public function approveService($id)
    {
        try {
            $service = $this->service->find($id);
            if (!$service) return $this->sendError(__('app.not_exist', ['attribute'  => __('app.service')]));
            $service['enabled'] = 1;
            $service->save();
            return $this->sendSuccess(__('app.success'));
        } catch (Exception $e) {
            $this->log("approveService", null, ["account_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display all Buyer use Service.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Post(
     *     path="/api/seller/service/{hash_id}/list-customer",
     *     summary="Get all Buyer use Service",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *     description="",
     *      @OA\Parameter(
     *         description="Hash ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getAllBuyerUseService($hash_id, Request $request)
    {
        try {
            $service = $this->service->where('hash_id', $hash_id)->first();
            if ($service) {
                $listBuyer = $this->serviceStoreBuyer->getAllBuyerUseService($service->id, $request);
                return $this->sendSuccessResponse($listBuyer);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->log("getAllBuyerUseService", null, ["hash_id" => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display all data select of list-customer.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/seller/service/{hash_id}/list-customer/data-select",
     *     summary="Get data select of list-customer",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="type_select:0 get all course name, 1 get all date created_at",
     *         in="query",
     *         name="type_select",
     *         example="0",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getAllCourseAndDateUseService($hash_id, Request $request)
    {
        try {
            $service = $this->service->where('hash_id', $hash_id)->first();

            if (!$service) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $results = $this->serviceStoreBuyer->getAllCourseAndDateUseService($service->id, $request);

            return $this->sendSuccessResponse($results);
        } catch (Exception $e) {
            $this->log("getAllCourseAndDateUseService", null, ["hash_id" => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display invoice of buyer by id.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/buyer/service/invoice/{id}",
     *     summary="Payment invoice  of buyer by id",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="ID of service need to input info",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function showInvoiceServiceByBuyer($payment_id)
    {
        try {
            $payment = $this->payment->showInvoiceServiceByBuyer($payment_id);
            $service_store_buyer = $this->serviceStoreBuyer->where('id', $payment->service_store_buyer_id)->first();
            $course_id = $service_store_buyer->course_id;
            $service_course = $this->serviceCourse->where('course_id', $course_id)->first();
            $service = $service_course->service;
            
            $delivery_address = null;
            $buyer_name = null;
            if($payment->delivery_id != null){
                $delivery = $this->delivery->find($payment->delivery_id);
                $delivery_address = $delivery->delivery_address;
                $buyer_name = $delivery->buyer_full_name;
            } else {
                $shipping_info = $this->shippingInfo->findDefaultByBuyerId($service_store_buyer->buyer_id);
                if ($shipping_info) {
                    $post_code = $shipping_info['post_code'];
                    $delivery_address = $shipping_info['address'];
                    $buyer_name =  $shipping_info['last_name'] . ' ' . $shipping_info['first_name'];
                }
            }

            $payment->course_name = $service_course->name;
            $payment->service_address = $service->address;
            $payment->buyer_name = $buyer_name;
            $payment->post_code = $post_code;
            $payment->delivery_address = $delivery_address;
            $payment->service_name = $service_course->service->name;
            $payment->bill_id = substr($payment->id, 0, 4) . Carbon::parse($payment->pay_expire_at_date)->format('dmY');

            if ($payment) {
                return $this->sendSuccessResponse($payment);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.payment')]), Response::HTTP_BAD_REQUEST);
            }
            
        } catch (Exception $e) {
            error_log($e);
            $this->log("showInvoiceServiceByBuyer", null, ["payment_id" => $payment_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a listing of the resource.
     *   @OA\Get(
     *     path="/api/hash-tag",
     *     summary="Get all favorite tag",
     *     tags={"Hashtag"},
     *     @OA\Parameter(
     *         description="tag name",
     *         in="query",
     *         name="name",
     *         example="1",
     *         @OA\Schema(
     *         type="string"
     *        )
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
     * @return \Illuminate\Http\Response
     */
    public function topTag(Request $request)
    {
        try {
            $tags = $this->recommendHashTag->getTopTag($request);
            return $this->sendSuccessResponse($tags);
        } catch (Exception $e) {
            $this->log('favoriteTag_index', null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Post(
     *     path="/api/service/new-list/{page}",
     *     summary="Get new service",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         description="page number need to input info",
     *         in="path",
     *         required=false,
     *         name="page",
     *         example=9,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                description="0=>並び替え, 1=>新着, 2=>評価が高い, 3=>価格が高い, 4=>価格が安い, 5=>登録者が多い",
     *                property="sort",
     *                example=1,
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                description="Buyer ID to input",
     *                property="buyer_id",
     *                example=2,
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function getServiceNew($page = null, Request $request)
    {
        try {
            $services = $this->service->getServiceNew($page, $request);
            // $this->numberAccessListServicePage->redisCountNumberAccessServiceList($services);
            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("getServiceNew", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Post(
     *     path="/api/service/featured-list/{page}",
     *     summary="Get Featured service",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         description="size number need to input info",
     *         in="path",
     *         required=false,
     *         name="page",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                description="0=>並び替え, 1=>新着, 2=>評価が高い, 3=>価格が高い, 4=>価格が安い, 5=>登録者が多い",
     *                property="sort",
     *                example=1,
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                description="Buyer ID to input",
     *                property="buyer_id",
     *                example=2,
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function getServiceFeatured($page = null, Request $request)
    {
        try {
            $services = $this->service->getServiceBySortType($page, ServiceConst::SERVICE_FEATURED, $request);
            $this->numberAccessListServicePage->redisCountNumberAccessServiceList($services);
            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("getServiceFeatured", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Manager service reservations by Seller.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/seller/service/{hash_id}/reservations",
     *     summary="Service reservations manager",
     *     tags={"Reservations Seller"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="Hash_ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="CourseID of service need to input info",
     *         in="query",
     *         name="course_id",
     *         example="course_id",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="ID of service need to input info",
     *         in="query",
     *         name="year",
     *         required=true,
     *         example="2021",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="ID of service need to input info",
     *         in="query",
     *         name="month",
     *         required=true,
     *         example="11",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="ID of service need to input info",
     *         in="query",
     *         name="week",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function serviceReservesManagerBySeller($hash_id, Request $request)
    {
        try {
            // validate
            $data = $request->all();
            $errors = $this->validator($data, $this->reservesRulesSeller());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            // check service exist
            $service = $this->service->findHashId($hash_id);
            $user = Auth::guard('users')->user();
            $seller = $this->seller->findByAccountId($service->seller_id);

            if ($user->id != $seller->account_id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            // check service reserves setting exist
            if (!$service->serviceReserveSetting) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service_reserves_setting')]), Response::HTTP_OK);

            //check week exist of month
            if (isset($data['week']) && !$this->buyerServiceReserve->checkWeekOfMonth($request)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.week')]), Response::HTTP_OK);

            return $this->sendSuccessResponse($this->buyerServiceReserve->serviceReservesManagerBySeller($service->id, $request));
        } catch (Exception $e) {
            $this->log("serviceReservesManagerBySeller", null, ["request" => $request->all(), 'service_hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Manager service reservations by Buyer.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/buyer/service/{hash_id}/reservations",
     *     summary="Service reservations manager",
     *     tags={"Reservations Buyer"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="Hash_ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="buyer_id of need to input info",
     *         in="query",
     *         name="buyer_id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="year of need to input info",
     *         in="query",
     *         name="year",
     *         required=true,
     *         example="2021",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="month of need to input info",
     *         in="query",
     *         name="month",
     *         required=true,
     *         example="11",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="week of need to input info",
     *         in="query",
     *         name="week",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function serviceReservesManagerByBuyer($hash_id, Request $request)
    {
        try {
            // validate
            $data = $request->all();
            $errors = $this->validator($data, $this->reservesRulesBuyer());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $user = Auth::guard('users')->user();
            $buyer = $this->buyer->findByAccountId($request->buyer_id);
            if ($user->id != $buyer->account_id)
                return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            // check service exist
            $service = $this->service->findHashId($hash_id);
            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            // check service reserves setting exist
            if (!$service->serviceReserveSetting) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service_reserves_setting')]), Response::HTTP_OK);

            //check week exist of month
            if (isset($data['week']) && !$this->buyerServiceReserve->checkWeekOfMonth($request)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.week')]), Response::HTTP_OK);

            $serviceStoreBuyer = $this->serviceStoreBuyer->findServiceUseByBuyer($service->id, $buyer->account_id);
            if (!isset($serviceStoreBuyer))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service_store_buyer')]));
            $service_course = $serviceStoreBuyer->serviceCourses;
            if (!isset($service_course))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.course')]));

            return $this->sendSuccessResponse($this->buyerServiceReserve->serviceReservesManagerByBuyer($service->id, $service_course->course_id, $buyer->account_id, $request));
        } catch (Exception $e) {
            $this->log("serviceReservesManagerByBuyer", null, ["request" => $request->all(), 'service_hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display service business schedule
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/seller/service/{hash_id}/business-schedule",
     *     summary="Display service business-schedule",
     *     tags={"Reservations Seller"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="Hash_ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getBusinessSchedule($hash_id)
    {
        try {

            $service = $this->service->findHashId($hash_id);

            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $results = $this->service->getBusinessScheduleByServiceId($service->id);
            if ($results) {
                return $this->sendSuccessResponse($results);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service_reserves_setting')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->log("getBusinessSchedule", null, ['service_hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Display service business schedule
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/seller/service/{hash_id}/service-hours-temp",
     *     summary="Display service-hours-temp",
     *     tags={"Reservations Seller"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="Hash_ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="Date of service need to input info",
     *         in="query",
     *         name="date",
     *         required=true,
     *         example="Y-m-d",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getBusinessScheduleTemp($hash_id, Request $request)
    {
        try {
            $service = $this->service->findHashId($hash_id);

            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);
            $results = $this->service->getBusinessScheduleTempByServiceId($service->id, $request->date);

            if(!isset($results))  $results = [];

            return $this->sendSuccessResponse($results);
        } catch (Exception $e) {
            $this->log("getBusinessScheduleTemp", null, ['service_hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Get(
     *     path="/api/service/search-keyword/{page}",
     *     summary="Search service by keyword",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         description="size number need to input info",
     *         in="path",
     *         name="page",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="page number need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="keyword need to input info",
     *         in="query",
     *         name="keyword",
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="0=>並び替え, 1=>新着, 2=>評価が高い, 3=>価格が高い, 4=>価格が安い, 5=>登録者が多い",
     *         in="query",
     *         name="sort",
     *         example="1",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *  @OA\Parameter(
     *         description="Buyer ID need to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function searchServiceByKeyword($page = 10, Request $request)
    {
        try {
            $services = $this->service->searchServiceByKeyword($page, $request);

            $this->numberAccessListServicePage->redisCountNumberAccessServiceList($services);

            if ($request->buyer_id && $this->buyer->findByAccountId($request->buyer_id)) {
                $this->recommendService->redisCountRecommendService($services, $request->buyer_id);
            }

            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("searchServiceByKeyword", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/seller/service/{hash_id}/status-reserves",
     *     summary="Update status reserves",
     *     tags={"Reservations Seller"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="hash_id of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
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
    public function updateStatusReserves($hash_id)
    {
        try {

            $service = $this->service->findHashId($hash_id);

            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            if (isset($service->buyerServiceReserves) && $service->buyerServiceReserves->count() > 0)
                return $this->sendError(__('app.not_update_setting'));

            $service['is_reserves'] =  !$service['is_reserves'];
            $service->save();

            if ($service['is_reserves']) {
                return $this->sendSuccess(__('app.reserves_on'));
            } else {
                return $this->sendSuccess(__('app.reserves_off'));
            }
        } catch (Exception $e) {
            $this->log("updateStatusReserves", null, ['service_hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Get(
     *     path="/api/service/search-category/{page}",
     *     summary="Search service by category",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="size number need to input info",
     *         in="path",
     *         name="page",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="page number need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="category id need to input info",
     *         in="query",
     *         name="category_id",
     *         example="",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="0=>並び替え, 1=>新着, 2=>評価が高い, 3=>価格が高い, 4=>価格が安い, 5=>登録者が多い",
     *         in="query",
     *         name="sort",
     *         example="1",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *  @OA\Parameter(
     *         description="Buyer Id need to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function searchServiceByCategory($page = 10, Request $request)
    {
        try {
            $services = $this->service->searchServiceByCategory($page, $request);

            $this->numberAccessListServicePage->redisCountNumberAccessServiceList($services);

            if ($request->buyer_id && $this->buyer->findByAccountId($request->buyer_id)) {
                $this->recommendService->redisCountRecommendService($services, $request->buyer_id);
            }

            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("searchServiceByCategory", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     Update service business schedule
     *
     *     @OA\Post(
     *     path="/api/seller/service/{hash_id}/business-schedule/update",
     *     summary="Update service business schedule",
     *     tags={"Reservations Seller"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="Hash_ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *              @OA\Property(property="is_reserves", type="boolean",example="1"),
     *              @OA\Property(property="service_reserve_setting", type="object",
     *                             required={"is_enable", "max", "time_distance", "duration_before", "duration_after", "type_duration_after"},
     *                             @OA\Property(
     *                             property="is_enable",
     *                             example="0",
     *                             type="integer",
     *                             ),
     *                             @OA\Property(
     *                             property="max",
     *                             example="50",
     *                             type="integer",
     *                             ),
     *                             @OA\Property(
     *                             property="time_distance",
     *                             example="1:00",
     *                             type="integer",
     *                             ),
     *                             @OA\Property(
     *                             property="duration_before",
     *                             example="60",
     *                             type="integer",
     *                             ),
     *                             @OA\Property(
     *                             property="duration_after",
     *                             example="2",
     *                             type="integer",
     *                             ),
     *                             @OA\Property(
     *                             property="type_duration_after",
     *                             example="2",
     *                             type="integer",
     *                             ),
     *                          ),
     *              @OA\Property(property="service_hours", type="array",
     *                             @OA\Items(type="object",
     *                              @OA\Property(property="day_of_week", type="integer",example = 0),
     *                              @OA\Property(property="work_hour", type="array",
     *                               @OA\Items(type="object",
     *                                  @OA\Property(property="start", type="time", example ="9:00"),
     *                                  @OA\Property(property="end", type="time", example ="22:00"),
     *                              ),
     *                              ),
     *                            ),
     *                          ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
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
    public function updateBusinessSchedule($hash_id, Request $request)
    {
        try {

            DB::beginTransaction();

            $data = $request->all();

            $is_reserves = isset($data['is_reserves']) ? $data['is_reserves'] : false;

            $errors = $this->validator($data, $this->businessScheduleRules(null, false, $is_reserves));
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $service = $this->service->findHashId($hash_id);
            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            if (isset($service->buyerServiceReserves) && $service->buyerServiceReserves->count() > 0)
                return $this->sendError(__('app.not_update_setting'));

            $time_distance = (strtotime($data['service_reserve_setting']['time_distance']) - strtotime('00:00'))/(60*60);
            if (isset($data['service_hours'])) {
                foreach ($data['service_hours'] as $item) {
                    foreach ($item['work_hour'] as $work_hour) {
                        $sub_hours = (strtotime($work_hour['end']) - strtotime($work_hour['start'])) / (60 * 60);
                        if ($sub_hours < $time_distance) return $this->sendError(__('app.work_hours', ['attribute' => __('app.work_hour')]));
                    }
                }
            }

            if (isset($data['is_reserves'])) {
                $service['is_reserves'] =  $data['is_reserves'];
                $service->save();
            }

            // update service reserve setting
            if (isset($data['service_reserve_setting'])) {
                $this->serviceReserveSetting->updateServiceReservesSetting($service->id, $data['service_reserve_setting']);
            }

            // update service hours
            if (isset($data['service_hours'])) {
                foreach ($data['service_hours'] as $item) {
                    $json_work_hour = json_encode($item['work_hour']);
                    $item['work_hour'] = $json_work_hour;
                    $item['service_id'] = $service->id;
                    $this->serviceHour->updateByServiceID($service->id, $item['day_of_week'], $item);
                }
            }
            DB::commit();
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.service')]));
        } catch (Exception $e) {
            DB::rollBack();
            $this->log("updateBusinessSchedule", null, ['request' => $request->all(), 'service_hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     Update service business schedule
     *
     *     @OA\Post(
     *     path="/api/seller/service/{hash_id}/service-hours-temp/update",
     *     summary="Update service business schedule temp",
     *     tags={"Reservations Seller"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="Hash_ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *              @OA\Property(property="date", type="string", example="date"),
     *              @OA\Property(property="work_hour", type="array",
     *                             @OA\Items(type="object",
     *                                  @OA\Property(property="start", type="time", example ="9:00"),
     *                                  @OA\Property(property="end", type="time", example ="22:00"),
     *                              ),
     *                          ),
     *              @OA\Property(property="status", type="boolean", example="1"),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
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
    public function updateBusinessScheduleTemp($hash_id, Request $request)
    {
        try {

            DB::beginTransaction();

            $data = $request->all();

            $errors = $this->validator($data, $this->businessScheduleTempRules());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $service = $this->service->findHashId($hash_id);
            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $buyerReserves = $service->buyerServiceReserves()->whereDate('reserve_start', $request->date)->get();

            if (isset($buyerReserves) && $buyerReserves->count() > 0)
                return $this->sendError(__('app.not_update_setting'));

            $service_reserves_setting = $service->serviceReserveSetting;
            $time_distance = (strtotime($service_reserves_setting['time_distance']) - strtotime('00:00')) / (60 * 60);

            if (isset($data['work_hour'])) {
                foreach ($data['work_hour'] as $work_hour) {
                    $sub_hours = (strtotime($work_hour['end']) - strtotime($work_hour['start'])) / (60 * 60);
                    if ($sub_hours < $time_distance) return $this->sendError(__('app.work_hours', ['attribute' => __('app.work_hour')]));
                }
            }

            // update service hours temps
            $data_update = [];
            $data_update['date'] = $data['date'];
            $json_work_hour = json_encode($data['work_hour']);
            $data_update['work_hour'] = $json_work_hour;
            $data_update['service_id'] = $service->id;
            $data_update['status'] = $data['status'];

            $service_hours_temp =  $this->serviceHoursTemp->findByServiceIdAndDate($service->id, $data['date']);

            if ($service_hours_temp) {
                $this->serviceHoursTemp->updateByServiceID($service->id,  $data['date'], $data_update);
            } else {
                $this->serviceHoursTemp->create($data_update);
            }

            DB::commit();
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.service_hours')]));
        } catch (Exception $e) {
            DB::rollBack();
            $this->log("updateBusinessScheduleTemp", null, ['request' => $request->all(), 'service_hash_id' => $hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Get(
     *     path="/api/service/search-area/{page}",
     *     summary="Search service by area",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         description="size number need to input info",
     *         in="path",
     *         name="page",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="page number need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="area id need to input info",
     *         in="query",
     *         name="area",
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="0=>並び替え, 1=>新着, 2=>評価が高い, 3=>価格が高い, 4=>価格が安い, 5=>登録者が多い",
     *         in="query",
     *         name="sort",
     *         example="1",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="Buyer Id need to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function searchServiceByArea($page = 10, Request $request)
    {
        try {
            $services = $this->service->searchServiceByArea($page, $request);

            $this->numberAccessListServicePage->redisCountNumberAccessServiceList($services);

            if ($request->buyer_id && $this->buyer->findByAccountId($request->buyer_id)) {
                $this->recommendService->redisCountRecommendService($services, $request->buyer_id);
            }

            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("searchServiceByArea", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Get(
     *     path="/api/service/search-tag/{per_page}",
     *     summary="Search service by tag",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         description="size number need to input info",
     *         in="path",
     *         name="per_page",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="page number need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="tag name need to input info",
     *         in="query",
     *         name="tag_name",
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="0=>並び替え, 1=>新着, 2=>評価が高い, 3=>価格が高い, 4=>価格が安い, 5=>登録者が多い",
     *         in="query",
     *         name="sort",
     *         example="1",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="Buyer Id need to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function searchServiceByTag($per_page = 10, Request $request)
    {
        try {
            $services = $this->service->searchServiceByTag($per_page, $request);
            $tag = Tag::where('name', $request->tag_name)->select('id')->first();
            $tag_id = isset($tag) ? $tag['id'] : null;
            
            $this->numberAccessListServicePage->redisCountNumberAccessServiceList($services);

            if ($request->buyer_id && $this->buyer->findByAccountId($request->buyer_id)) {
                $this->recommendService->redisCountRecommendService($services, $request->buyer_id);
            }

            if ($tag_id) {
                $this->recommendHashTag->redisCountRecommendHashTag($tag_id);
            }
            
            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("searchServiceByArea", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Post(
     *     path="/api/buyer/{id}/service/list-stop",
     *     summary="Get all service stop of buyer",
     *     tags={"Buyer"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="id of buyer need to input info",
     *         in="path",
     *         name="id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                description="Number record display on current page",
     *                property="per_page",
     *                example=10,
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function getAllServiceStopByBuyer($id, Request $request)
    {
        try {
            if (!$this->buyer->findByAccountId($id)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]), Response::HTTP_OK);
            return $this->sendSuccessResponse($this->service->getAllServiceStopByBuyer($id, $request));
        } catch (Exception $e) {
            $this->log("getAllServiceStopByBuyer", null, ["service_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Get(
     *     path="/api/service/hash-id",
     *     summary="Get id and hash-id of service",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     )
     * )
     */
    public function createHashServiceId()
    {
        $results = [];
        $random = strval(mt_rand(100000000, 999999999));
        $hash_id = md5($random);
        while (!is_null($this->service->find($random)) && !is_null($this->service->findHashId($random))) {
            $random = strval(mt_rand(100000000, 999999999));
            $hash_id = md5($random);
        }
        $results = [
            'id' => $random,
            'hash_id' => $hash_id
        ];
        return $results;
    }

    /**
     *   @OA\Get(
     *     path="/api/service/prefectures/{zipcode}",
     *     summary="Get prefectures by zipcode",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="id of buyer need to input info",
     *         in="path",
     *         name="zipcode",
     *         example="4720054",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     )
     * )
     */
    public function getPrefecturesByZipCode($zipcode)
    {
        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'key' => config('map.key'),
                'address' => $zipcode,
                'sensor' => true,
                'region' => 'JP',
                'language' => 'ja',
            ]);
            $results = [];
            $data = json_decode($response->body());
            $data = $data->results;
            if ($data) {
                $data = $data[0]->address_components;
                $results['address'] = '';
                $pref = '';
                $post_code = '';
                foreach ($data as $area) {
                    $type = $area->types;
                    if (in_array('postal_code', $type)) {
                        $post_code = '〒' . $area->short_name . ' ';
                        continue;
                    }
                    if (in_array('country', $type)) continue;
                    if (in_array('administrative_area_level_1', $type)) {
                        $pref = $area->short_name;
                    }
                    $results['address'] = $area->short_name . $results['address'];
                }
                $results['address_2'] = $results['address'];
                $results['address'] = $post_code .  $results['address'];
                $prefectures = $this->prefecture->getAreaByPrefectureName($pref);
                if (isset($prefectures)) {
                    $results['area'] = $prefectures->area->name;
                } else {
                    $pref = mb_strcut($pref, 0, strlen($pref) - 1, "UTF-8");
                    $prefectures = $this->prefecture->getAreaByPrefectureName($pref);
                    if (isset($prefectures)) {
                        $results['area'] = $prefectures->area->name;
                    } else {
                        $results['area'] = null;
                    }
                }
                return $this->sendSuccessResponse($results);
            }
            return $this->sendError(__('app.not_exist', ['attribute' => __('app.zip_code')]), Response::HTTP_OK);;
        } catch (Exception $e) {
            $this->log("getPrefecturesByZipCode", null, ["zipcode" => $zipcode], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  Remove the specified resource from storage.
     *  @OA\Delete(
     *     path="/api/service/delete/{id}",
     *     summary="delete service by id",
     *     tags={"Service"},
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
            $service = $this->service->find($id);
            if (!$service)
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);
            if ($service->serviceStoreBuyers->count() > 0  || $service->buyerServiceReserves->count() > 0)
                return $this->sendError(__('app.delete_service_failed'), Response::HTTP_OK);
            DB::beginTransaction();
            // delete service course
            $this->serviceCourse->deleteAllByServiceId($service->id);

            // delete service image
            $this->serviceImage->deleteServiceImageByServiceId($service->id);

            // delete service areas
            $service->serviceAreas()->delete();

            // delete favorite
            $service->favorites()->delete();

            // delete links
            $service->links()->delete();

            // delete steps
            $service->steps()->delete();

            // delete service Delivery
            $service->serviceDelivery()->delete();

            // delete tags
            $this->serviceTag->deleteByServiceId($service->id);


            //delete service hours
            $service->serviceHours()->delete();

            //delete service reserves setting
            $service->serviceReserveSetting()->delete();

            //delete service reviews
            $this->serviceReview->deleteAllByServiceId($service->id);

            // delete service browsing history
            $service->serviceBrowsingHistory()->delete();

            $serviced = $service->delete();
            DB::commit();

            if ($serviced) {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.delete'), 'attribute' => __('app.service')]));
            } else {
                DB::rollBack();
                return $this->sendError(__('app.action_failed', ['action' => __('app.delete'), 'attribute' => __('app.service')]));
            }
        } catch (Exception $e) {
            DB::rollBack();
            $this->log("deleteService", null, ['service_id' => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Patch(
     *     path="/api/admin/service-manage/{id}/setting",
     *     summary="Admin setting service",
     *     tags={"Admin"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *          name="id",
     *          description="serviceID need to input",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="sort_type",
     *                             type="string",
     *                             example="1,2,3"
     *                         ),
     *
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
     * )
     *
     */
    public function adminSettingService($service_id, Request $request)
    {
        try {
            $data = $request->all();
            $errors = $this->validator($data, $this->adminSettingServiceRules());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $service = $this->service->find($service_id);
            if (!$service)
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);
            
            $this->service->updateService($service_id, $data);

            return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.service')]));
            } catch (Exception $e) {
            $this->log("AdminSettingService", null, ['service_id' => $service_id, 'request' => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
        /**
     * Display the listing other service of seller.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/service/{hash_id}/browsing-history",
     *     summary="Get all service browsing history by ip_address",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         description="Hash ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="c4ca4238a0b923820dcc509a6f75849b",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="Buyer ID need to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="per_page need to input info",
     *         in="query",
     *         name="per_page",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="page need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getBrowsingHistoryServices($hash_id, Request $request)
    {
        try {
            $ip_address = $request->getClientIp();
            
            $service = $this->service->findHashId($hash_id);

            if (!$service)  return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $services = $this->service->getBrowsingHistoryServices($service->id, $ip_address, $request);

            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            $this->log("getBrowsingHistoryServices", null, ["request" => $request->all(), 'ip_address' => $ip_address], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Post(
     *     path="/api/seller/service/start-date/update",
     *     summary="update start date use service",
     *     security={ {"bearer": {}} },
     *     tags={"Seller"},
     *     @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="user_id",
     *                             type="integer",
     *                             example="1"
     *                         ),
     *                         @OA\Property(
     *                             property="hash_id",
     *                             type="string",
     *                             example=""
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function updateStartDate(Request $request)
    {
        try {
            $user = Auth::guard(UserConst::USER_GUARD)->user();

            $service = $this->service->findHashId($request->hash_id);

            // return $this->sendSuccessResponse($service->seller_id);
            //check permission seller
            if ($user->id !== $service->seller_id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED);

            if (!$service) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $serviceStoreBuyer = $this->serviceStoreBuyer->findServiceUseByBuyer($service->id, $request->user_id);

            if (!empty($serviceStoreBuyer)) 
            {   
                if ($serviceStoreBuyer->flagQR == 1) 
                {
                    return $this->sendSuccess(__('app.updated_start_date'));
                }
                $update = $serviceStoreBuyer->update([
                    "flagQR" => 1,
                    'start' => Carbon::now()->toDateTimeString()
                ]);
                if (!$update) 
                {
                    return $this->sendError(__('app.action_failed', ['action' => __('app.update'), 'attribute' => __('app.used_start_date')]));
                }
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.used_start_date')]));
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]));
            }
        } catch (Exception $e) {
            $this->log("updateStartDate", null, ["service_hash_id" => $request->hash_id, "user_id" => $request->user_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *   @OA\Get(
     *     path="/api/service/update-location",
     *     summary="Update location by address service",
     *     tags={"Service"},
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     )
     * )
     */
    public function updateAllLocationService()
    {
        try {
            $services = $this->service->all();

            foreach ($services as $service){
                $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'key' => config('map.key'),
                    'address' =>  $service->address,
                ]);
                $data = json_decode($response->body());
                $data = $data->results;
                if ($data) {
                    $data = $data[0]->geometry;
                    $location = $data->location;
                    $service['lat'] = $location->lat;
                    $service['lng'] = $location->lng;
                    $service->save();
                }
            }
            return $this->sendSuccess('update all location success');
        } catch (Exception $e) {
            $this->log("updateAllLocationService", null, [null], $e->getMessage());
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
     *     path="/api/service/create-edit-course",
     *     summary="create edit course by service id",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *               @OA\Property(
     *                     description="ID need to display",
     *                     property="service_id",
     *                     type="integer",
     *                     example="123456789",
     *               ),
     *               @OA\Property(
     *                     description="Hash ID content need to display",
     *                     property="hash_id",
     *                     type="string",
     *                     example="hash_id",
     *              ),
     *               @OA\Property(
     *                     property="seller_id",
     *                     type="integer",
     *                     example="1",
     *              ),
     *               @OA\Property(
     *                     property="course_id",
     *                     type="string",
     *                     example="A274519209",
     *              ),
     *              @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="course_name",
     *              ),
     *              @OA\Property(
     *                     property="price",
     *                     type="integer",
     *                     example="5000",
     *              ),
     *              @OA\Property(
     *                     property="cycle",
     *                     type="integer",
     *                     example="100",
     *              ),
     *              @OA\Property(
     *                     property="content",
     *                     type="integer",
     *                     example="content",
     *              ),
     *              @OA\Property(
     *                     property="firstPr",
     *                     type="integer",
     *                     example="0",
     *              ),
     *              @OA\Property(
     *                     property="image",
     *                     type="file",
     *              ),
     *              @OA\Property(
     *                     property="gender_restrictions",
     *                     type="integer",
     *                     example="null",
     *              ),
     *              @OA\Property(
     *                     property="age_confirm",
     *                     type="integer",
     *                     example="20",
     *              ),
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
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function createEditCourse(Request $request)
    {
        try {
            $courses = $request->all();

            $user = Auth::guard('users')->user();

            if (!$this->seller->findByAccountId($user->id)) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            if (isset($courses['seller_id']) && $user->id != $courses['seller_id']) return $this->sendError(__('app.invalid', ['attribute' => __('app.seller')]), Response::HTTP_OK);

            DB::beginTransaction();

            $service = $this->service->find($courses['service_id']);
            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            if (isset($courses['course_id'])) {
                $courses_data = $this->serviceCourse->findByCourseId($courses['course_id']);
                if (isset($courses_data)) {
                    //update
                    if (isset($courses['name'])) $courses_data["name"] = $courses["name"];
                    if (isset($courses['price'])) $courses_data["price"] = $courses["price"];
                    if (isset($courses['cycle'])) $courses_data["cycle"] = $courses["cycle"];
                    if (isset($courses['content'])) $courses_data["content"] = $courses["content"];
                    if (isset($courses['firstPr'])) $courses_data["firstPr"] = $courses["firstPr"];
                    if (isset($courses['age_confirm'])) $courses_data["age_confirm"] = $courses["age_confirm"];
                    if (isset($courses['gender_restrictions'])) $courses_data["gender_restrictions"] = $courses["gender_restrictions"];

                    $serviceCourse = $this->serviceCourse->find($courses_data->id);
                    $serviceCourse->update($courses_data->toArray());
                } else {
                    $courses['course_id'] = $this->generateCourseId();
                    $courses_data = $this->serviceCourse->create($courses);
                }
            } else {
                //create
                $courses['course_id'] = $this->generateCourseId();
                $courses_data = $this->serviceCourse->create($courses);
            }
            if (isset($courses['image'])) {
                $img = $this->serviceCourseImage->findByCourseId($courses_data['course_id']);
                if ($img) {
                    //delete courses image
                    $path = $img['image_url'];
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                    $img->delete();
                }
                $file = $courses['image'];
                $extension = $file->getClientOriginalExtension();
                $fileName = $courses_data['course_id'] . '-' . date('mdHis') . uniqid('-') . '.' . $extension;
                $file->move(public_path('storage/images/services/' . $courses['service_id'] . '/courses'), $fileName);
                $file_saved = new ServiceCourseImage();
                $file_saved["course_id"] = $courses_data['course_id'];
                $file_saved["image_url"] = 'images/services/' . $courses['service_id'] . '/courses/' . $fileName;
                $file_saved = $file_saved->save();
            }

            DB::commit();
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.service')]));
        } catch (Exception $e) {
            DB::rollback();
            $this->log("createEditCourse", null, ["request" => $request->all()], $e->getFile() . "-" . $e->getLine() . "-" . $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/service/delete-course",
     *     summary="delete course",
     *     tags={"Service"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *               @OA\Property(
     *                     description="ID need to display",
     *                     property="service_id",
     *                     type="integer",
     *                     example="123456789",
     *               ),
     *               @OA\Property(
     *                     description="Hash ID content need to display",
     *                     property="hash_id",
     *                     type="string",
     *                     example="hash_id",
     *              ),
     *               @OA\Property(
     *                     property="seller_id",
     *                     type="integer",
     *                     example="1",
     *              ),
     *               @OA\Property(
     *                     property="course_id",
     *                     type="string",
     *                     example="A274519209",
     *              ),
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
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function deleteCourse(Request $request)
    {
        try {
            $courses = $request->all();

            $user = Auth::guard('users')->user();

            if (!$this->seller->findByAccountId($user->id)) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            if (isset($courses['seller_id']) && $user->id != $courses['seller_id']) return $this->sendError(__('app.invalid', ['attribute' => __('app.seller')]), Response::HTTP_OK);

            DB::beginTransaction();

            $service = $this->service->find($courses['service_id']);
            if (!isset($service)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            $img = $this->serviceCourseImage->findByCourseId($courses['course_id']);
            if ($img) {
                //delete courses image
                $path = $img['image_url'];
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
                $img->delete();
            }

            $this->serviceCourse->deleteByCourseId($courses['course_id']);

            DB::commit();
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.service')]));
        } catch (Exception $e) {
            DB::rollback();
            $this->log("deleteCourse", null, ["request" => $request->all()], $e->getFile() . "-" . $e->getLine() . "-" . $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/seller/service/lat-lng-from-address",
     *     summary="Get lat lng service",
     *     tags={"Seller"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                description="Lat lng",
     *                property="address",
     *                example="大阪府大阪市西淀川区御幣島２",
     *                type="string",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
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
    public function getLatLngByAddress(Request $request)
    {
        try {
            $service = $this->service->getLatLngByAddress($request);
            return $this->sendSuccessResponse($service);
        } catch (Exception $e) {
            $this->log("get_lat_lng_by_address", null, ["request" => $request->all(), 'seller_id' => $request->seller_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
