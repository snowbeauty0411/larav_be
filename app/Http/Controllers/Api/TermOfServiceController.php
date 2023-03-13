<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\TermOfService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TermOfServiceController extends BaseController
{
    protected $termOfService;

    public function __construct(TermOfService $termOfService)
    {
        $this->termOfService = $termOfService;
    }


    public function listTermOfServices(){
        try{
            $terms = $this->termOfService->listTermOfServices();
            $last_update =$terms[0]->updated_at;
            foreach($terms as $term){
                if($term['updated_at']>$last_update){
                    $last_update=$term['updated_at'];
                }
            }
           $data = [];
           $data=array_merge([
               'terms'=>$term->all(),
               'last_update'=>Carbon::parse($last_update)->toDateString()
           ]);
            return $this->sendSuccessResponse($data);
        }catch(Exception $e){
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
