<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCourse;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class ServiceCourseController extends BaseController
{
    protected $serviceCourse;
    protected $service;

    public function __construct(
        ServiceCourse $serviceCourse,
        Service $service
        )
    {
        $this->serviceCourse = $serviceCourse;
        $this->service = $service;
    }

    /**
     * Display the listing course of service.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/service/detail/{hash_id}/list-course",
     *     summary="Get all review of service",
     *     tags={"Course"},
     *      @OA\Parameter(
     *         description="HashID of service need to input info",
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
    public function getCourseByServiceHashId($service_hash_id, Request $request)
    {
        try {
            $service = $this->service->findHashId($service_hash_id);
            if (!$service) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);

            return $this->sendSuccessResponse($this->serviceCourse->getServiceCourseByServiceId($service, $request));

        } catch (Exception $e) {
            $this->log("listCourseByServiceID", null, ['hash_id' => $service_hash_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the listing course of service.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/course/detail/{course_id}",
     *     summary="Get detail course of service",
     *     tags={"Course"},
     *      @OA\Parameter(
     *         description="CourseID of service need to input info",
     *         in="path",
     *         name="course_id",
     *         required=true,
     *         example="A1",
     *         @OA\Schema(
     *         type="string"
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
    public function show($course_id)
    {
        try {
            $service_course = $this->serviceCourse->findByCourseId($course_id);
            if(!$service_course) return $this->sendError(__('app.not_exist', ['attribute' => __('app.course')]), Response::HTTP_OK);

            return $this->sendSuccessResponse($this->serviceCourse->getCourseByCourseId($course_id));
        } catch (Exception $e) {
            $this->log("getCourseDetail", null, ['course_id' => $course_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
