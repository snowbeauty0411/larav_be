<?php

namespace App\Models;

use App\Constants\ServiceConst;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class ServiceCourse extends Model
{
    use HasFactory;

    protected $table = 'service_courses';

    protected $fillable = [
        'course_id',
        'service_id',
        'name',
        'price',
        'cycle',
        'max',
        'age_confirm',
        'gender_restrictions',
        'firstPr',
        'content'
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function courseImages()
    {
        return $this->hasMany(ServiceCourseImage::class, 'course_id', 'course_id');
    }

    public function serviceStoreBuyer()
    {
        return $this->hasMany(ServiceStoreBuyer::class, 'course_id', 'course_id');
    }

    public function deleteByCourseId($course_id)
    {
        return $this->where('course_id', $course_id)->delete();
    }

    public function findByCourseId($course_id)
    {
        return $this->where('course_id', $course_id)->first();
    }

    public function getServiceCourseByServiceId($service, $condition)
    {
        $service_id = $service->id;

        $query = $this->where('service_id', $service_id)->with('courseImages')->withCount('serviceStoreBuyer')->orderBy('service_store_buyer_count', 'DESC');

        // if (!$condition->per_page) {
        //     $results = $query->paginate(5);
        // } else {
        //     $results = $query->paginate($condition->per_page);
        // }

        $listServiceCourse = $query->get();
        foreach($listServiceCourse as $key => $serviceCourse){
            if(isset($serviceCourse['courseImages']) && count($serviceCourse['courseImages']) > 0 ){
                $course_images = $serviceCourse['courseImages'];
                $img =  $course_images[0];
                $path = config('app.app_resource_path') . $img['image_url'];
                $serviceCourse['course_images'] = $path;
            }else{
                $serviceCourse['course_images'] = null;
            }

            $serviceCourse->is_use_course = 0;
            if ($serviceCourse->serviceStoreBuyer->where('status', 1)->count() > 0) $serviceCourse->is_use_course = 1;

            $serviceCourse['is_draft'] = $service->is_draft;
            
            // if (($service->service_type_id != 1 && is_null($serviceCourse->cycle)) || is_null($serviceCourse->price) || is_null($serviceCourse->age_confirm) || is_null($serviceCourse->gender_restrictions) || is_null($serviceCourse->content) || is_null($serviceCourse->firstPr) || is_null($serviceCourse['course_images'])) {
            //     $serviceCourse['is_draft'] = 1;
            // } else {
            //     $serviceCourse['is_draft'] = 0;
            // }

            unset($serviceCourse->courseImages);
            unset($serviceCourse->service_store_buyer_count);

            $serviceDelivery = $service->serviceDelivery;

            if ($service->service_type_id == 1 && $serviceDelivery) {
                $serviceCourse['cycle_format'] = $serviceDelivery->interval ? ($serviceDelivery->month_delivery == 1 ? '月' : $serviceDelivery->month_delivery . 'ヶ月') : '週';
                $serviceCourse['cycle'] = null;
            } elseif($service->service_type_id == 1) {
                $serviceCourse['cycle_format'] = null;
                $serviceCourse['cycle'] = null;
            } else {
                $serviceCourse['cycle_format'] = $serviceCourse['cycle'] == 1 ? '月' : $serviceCourse['cycle']. 'ヶ月';
            }

            $serviceCourse['course_draft'] = false;
            if (
                empty($serviceCourse['name']) ||
                empty($serviceCourse['price']) ||
                $serviceCourse['price'] == 0 ||
                empty($serviceCourse['content']) ||
                empty($serviceCourse['course_images'])
            ) {
                $serviceCourse['course_draft'] = true;
            }

            if ($condition->user_type == 'BUYER' && $condition->user_id) {
                $user = Account::with('buyers:account_id,gender')->where('id', $condition->user_id)->first();
                $age = Carbon::parse($user->birth_day)->diff(Carbon::now())->format('%y');
                $gender = $user->buyers['gender'];

                if (
                    $age < $serviceCourse['age_confirm']
                    && $serviceCourse['age_confirm'] > 0
                    && ($serviceCourse['gender_restrictions'] == 0 || empty($serviceCourse['gender_restrictions']))
                ) {
                    $serviceCourse['message'] = $serviceCourse['age_confirm'] . '歳以上　が対象のコースです。';
                } elseif (
                    $age < $serviceCourse['age_confirm']
                    && $serviceCourse['age_confirm'] > 0
                    && ($serviceCourse['gender_restrictions'] != 0 || !empty($serviceCourse['gender_restrictions']))
                ) {
                    if ($gender != $serviceCourse['gender_restrictions']) {
                        if ($serviceCourse['gender_restrictions'] == 1) {
                            $serviceCourse['message'] = $serviceCourse['age_confirm'] . '歳以上　女性のみ　が対象のコースです。';
                        } else {
                            $serviceCourse['message'] = $serviceCourse['age_confirm'] . '歳以上　男性のみ　が対象のコースです。';
                        }
                    } else {
                        $serviceCourse['message'] = $serviceCourse['age_confirm'] . '歳以上　が対象のコースです。';
                    }
                } else if ($serviceCourse['age_confirm'] == 0 && ($serviceCourse['gender_restrictions'] != 0 || !empty($serviceCourse['gender_restrictions']))) {
                    if ($gender != $serviceCourse['gender_restrictions']) {
                        if ($serviceCourse['gender_restrictions'] == 1) {
                            $serviceCourse['message'] = '女性のみ　が対象のコースです';
                        } else {
                            $serviceCourse['message'] = '男性のみ　が対象のコースです';
                        }
                    }
                }
            }
        }

        $page = Paginator::resolveCurrentPage() ?: 1;
        $listServiceCourse = $listServiceCourse instanceof Collection ? $listServiceCourse : Collection::make($listServiceCourse);
        $results = new LengthAwarePaginator($listServiceCourse->forPage($page, $condition->per_page ?? 5)->values(), $listServiceCourse->count(), $condition->per_page ?? 5, $page, []);
        
        return $results;
    }

    public function deleteAllByServiceId($service_id)
    {
        $results = $this->where('service_id',$service_id)->get();
        foreach ($results as $item) {
            if (!empty($item->courseImages[0]->image_url)) {
                $path = $item->courseImages[0]->image_url;
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
            $item->courseImages()->delete();
            $item->delete();
        }
        $dir = 'images/services/' . $service_id.'/courses';
        if (Storage::disk('public')->exists($dir)) Storage::disk('public')->deleteDirectory($dir);
    }

    public function getCourseByCourseId($course_id)
    {
        $paymentCourseInfo = [];

        $serviceCourse = $this->where('course_id', $course_id)->with('service', 'courseImages')->first();

        if(isset($serviceCourse->courseImages) && count($serviceCourse->courseImages) > 0 ){
            $course_images = $serviceCourse->courseImages;
            $img =  $course_images[0];
            $path = config('app.app_resource_path') . $img['image_url'];
            $serviceCourse['course_images'] = $path;
        }else{
            $serviceCourse['course_images'] = null;
        }
        unset($serviceCourse->courseImages);

        if ($serviceCourse['firstPr']) {
            $paymentCourseInfo['charge_at'] = Carbon::now()->addMonth()->format('Y-m-d');
        } else {
            $paymentCourseInfo['charge_at'] = Carbon::now()->format('Y-m-d');
        }

        $paymentCourseInfo['price'] = $serviceCourse['price'];
        $paymentCourseInfo['service_fee'] = round($serviceCourse['price'] * ServiceConst::SERVICE_FEE) / 100;
        $paymentCourseInfo['amount'] = $serviceCourse['price'] +  $paymentCourseInfo['service_fee'];

        $paymentCourseInfo['start_date'] = Carbon::now()->format('Y-m-d');
        $paymentCourseInfo['start_date_format'] = Carbon::now()->month . '月' . Carbon::now()->day . '日';
        $serviceCourse['payment_info'] =  $paymentCourseInfo;

        $service = $serviceCourse->service;
        $serviceDelivery = $service->serviceDelivery;
        
        if ($service->service_type_id == 1 && $serviceDelivery) {
            $serviceCourse['cycle_format'] = $serviceDelivery->interval ? ($serviceDelivery->month_delivery == 1 ? '月' : $serviceDelivery->month_delivery . 'ヶ月') : '週';
            $serviceCourse['cycle'] = null;
        } elseif($service->service_type_id == 1) {
            $serviceCourse['cycle_format'] = null;
            $serviceCourse['cycle'] = null;
        } else {
            $serviceCourse['cycle_format'] = $serviceCourse['cycle'] == 1 ? '月' : $serviceCourse['cycle']. 'ヶ月';
        }   

        return $serviceCourse;
    }
}
