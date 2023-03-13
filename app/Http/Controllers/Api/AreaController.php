<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AreaController extends BaseController
{
    protected $area;
    public function __construct(Area $area)
    {
        $this->area = $area;
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/area/list",
     *     summary="Get All Area",
     *     tags={"Area"},
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $areas = $this->area->getAll();
            return $this->sendSuccessResponse($areas);
        } catch (Exception $e) {
            $this->log("getAllArea", null, null, $e->getMessage());
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
