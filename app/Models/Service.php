<?php

namespace App\Models;

use App\Constants\ServiceConst;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    public $incrementing = false;

    protected $fillable = [
        'seller_id',
        'name',
        'price',
        'service_type_id',
        'caption',
        'service_cat_id',
        'service_content',
        'address',
        'zipcode',
        'lat',
        'lng',
        'max',
        'private',
        // 'age_confirm',
        'url_website',
        'location',
        'target_area'
    ];

    public function categories()
    {
        return $this->hasOne(ServiceCategory::class, 'id', 'service_cat_id')->select(['id', 'name']);
    }

    public function type()
    {
        return $this->hasOne(ServiceType::class, 'id', 'service_type_id')->select(['id', 'name']);
    }

    public function serviceImages()
    {
        return $this->hasMany(ServiceImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(ServiceReview::class);
    }

    // public function favoriteTags()
    // {
    //     return $this->hasManyThrough(FavoriteTag::class, ServiceFavoriteTag::class, 'service_id', 'id', 'id', 'favorite_tag_id');
    // }

    public function tags()
    {
        return $this->hasManyThrough(Tag::class, ServiceTag::class, 'service_id', 'id', 'id', 'tag_id');
    }


    public function buyerServiceReserves()
    {
        return $this->hasMany(BuyerServiceReserve::class);
    }

    public function serviceHours()
    {
        return $this->hasMany(ServiceHour::class)->orderBy('day_of_week')->select(['service_id', 'day_of_week', 'work_hour', 'status']);
    }

    public function ServiceHoursTemps()
    {
        return $this->hasMany(ServiceHoursTemp::class)->select(['service_id', 'date', 'work_hour', 'status']);
    }

    public function serviceReserveSetting()
    {
        return $this->hasOne(ServiceReserveSetting::class);
    }

    public function serviceDelivery()
    {
        return $this->hasOne(ServiceDelivery::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'account_id')
            ->join('accounts', 'accounts.id', '=', 'sellers.account_id')
            ->leftJoin('businesses', 'businesses.business_id', '=', 'sellers.business_id')
            ->leftJoin('url_officials', 'url_officials.id', '=', 'sellers.url_official_id')
            ->select(
                'sellers.account_id',
                'accounts.email',
                'sellers.account_name',
                'sellers.first_name',
                'sellers.last_name',
                'sellers.gender',
                'sellers.profile_image_url_sell',
                'businesses.business_name',
                'url_officials.url_official',
                'url_officials.url_facebook',
                'url_officials.url_instagram',
                'url_officials.url_twitter',
                'url_officials.url_sns_1',
                'url_officials.url_official',
                'url_officials.url_sns_2'
            );
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'seller_id', 'id');
    }

    public function steps()
    {
        return $this->hasMany(ServiceStep::class);
    }

    public function links()
    {
        return $this->hasOne(ServiceLink::class, 'id', 'id');
    }

    public function serviceStoreBuyers()
    {
        return $this->hasManyThrough(ServiceStoreBuyer::class, ServiceCourse::class, 'service_id', 'course_id', 'id', 'course_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function courses()
    {
        return $this->hasMany(ServiceCourse::class);
    }

    public function serviceAreas()
    {
        return $this->hasMany(ServiceArea::class, 'service_id')
            ->join('areas', 'areas.id', '=', 'service_areas.area_id')
            ->leftjoin('prefectures', 'prefectures.id', '=', 'service_areas.pref_id')
            ->select([
                'service_areas.service_id',
                'service_areas.area_id',
                'service_areas.pref_id',
                'areas.name as area',
                'prefectures.name as prefecture_name'
            ]);
    }

    public function numberAccessListServicePage()
    {
        return $this->hasMany(NumberAccessListServicePage::class);
    }

    public function numberAccessServiceDetailPage()
    {
        return $this->hasMany(NumberAccessServiceDetailPage::class);
    }

    public function recommendService()
    {
        return $this->hasMany(RecommendService::class);
    }

    public function serviceBrowsingHistory()
    {
        return $this->belongsTo(ServiceBrowsingHistory::class, 'id', 'service_id');
    }

    public function getDayJapan($day_of_week)
    {
        $data = [
            '0' => '日曜日',
            '1' => '月曜日',
            '2' => '火曜日',
            '3' => '水曜日',
            '4' => '木曜日',
            '5' => '金曜日',
            '6' => '土曜日',
        ];
        return $data[$day_of_week];
    }

    public function getAll($buyer_id = null)
    {
        $services = $this->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'tags', 'favorites')
            ->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->whereHas('courses')
            ->orderBy('created_at', 'desc')
            ->get();
        foreach ($services as $service) {
            $this->convertService($service, $buyer_id);
        }
        return $services;
    }

    public function getServiceById($hash_id, $buyer_id = null)
    {
        $service = $this->where('hash_id', $hash_id)
            ->with(['categories', 'type', 'serviceImages', 'reviews', 'steps', 'links', 'seller', 'favorites', 'tags', 'serviceDelivery', 'serviceReserveSetting', 'serviceHours', 'serviceAreas', 'serviceStoreBuyers'])
            ->withCount('favorites', 'reviews')            
            ->first();
        if ($service) {
            $this->convertServiceDetail($service, $buyer_id);
            unset($service->serviceStoreBuyers);
        }

        return  $service;
    }

    public function getServiceSellingById($id)
    {
        $service = $this->where('id', $id)
            ->with(['categories', 'type', 'serviceImages', 'serviceDelivery', 'seller'])
            ->first();
        if ($service) {
            $this->convertServiceSellingDetail($service);
        }

        return  $service;
    }

    public function getCurrentQuantityByID($service_id)
    {
        $service = $this->where('id', $service_id)
            ->withCount('serviceStoreBuyers')
            ->first();
        if ($service['max']) {
            return $service['max'] - $service['service_store_buyers_count'];
        }
        return null;
    }

    public function getAllServiceBySellerId($per_page, $condition)
    {
        $query = $this->where('seller_id', $condition->seller_id)
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'seller', 'favorites', 'tags');
        //is_draft
        if (isset($condition->is_draft)) {
            $query->where('is_draft', $condition->is_draft);
        }

        //sort
        if (isset($condition->sort_type)) {
            $type = 'ASC';
            if ($condition->sort_type == 2) {
                $type = 'DESC';
            }

            $query->orderBy('updated_at', $type);
        }

        if (!$per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }
        foreach ($results->items() as $service) {
            $this->convertService($service);
        }
        return $results;
    }

    public function getAllServiceApprovedBySellerId($condition)
    {
        $results = $this->where('seller_id', $condition->seller_id)
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'seller', 'favorites', 'tags')
            ->where('enabled', 1)
            ->orderBy('created_at', 'DESC')->get();

        foreach ($results as $service) {
            $this->convertService($service);
        }
        return $results;
    }

    public function updateService($id, $service)
    {
        $this->where('id', $id)->update($service);
    }

    public function getServiceBySortType($page, $type_sort, $condition)
    {
        $query = $this
            ->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->where('sort_type', 'like', '%' . $type_sort . '%')
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null);

        if ($condition->sort) {
            $query = $this->sortService($query, $condition->sort);
        } else {
            $query->orderBy('updated_at', 'DESC');
        }
        
        if (!$page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service, $condition->buyer_id);
        }
        return $results;
    }

    
    public function getServiceRecommend($page, $condition)
    {
        $query = $this
            ->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->withSum('numberAccessListServicePage', 'count_by_month')
            ->withSum('numberAccessServiceDetailPage', 'count_by_month')
            ->withCount('serviceStoreBuyers')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null)
            ->orderBy('number_access_service_detail_page_sum_count_by_month', 'DESC')
            ->orderBy('service_store_buyers_count', 'DESC')
            ->orderBy('number_access_list_service_page_sum_count_by_month', 'DESC');
        
        if (!$page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service, $condition->buyer_id);
        }
        return $results;
    }

    public function getServiceRecommendBuyer($per_page, $condition)
    {
        $buyer_id = $condition->buyer_id;
        $query = $this
            ->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->withSum( ['recommendService' => function($query) use ($buyer_id) {
                    $query->where('buyer_id', $buyer_id);
                    }], 'count')
            ->withCount('serviceStoreBuyers')
            ->withSum('numberAccessListServicePage', 'count_by_month')
            ->withSum('numberAccessServiceDetailPage', 'count_by_month')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null)
            ->orderBy('number_access_service_detail_page_sum_count_by_month', 'DESC')
            ->orderBy('service_store_buyers_count', 'DESC')
            ->orderBy('recommend_service_sum_count', 'DESC')
            ->orderBy('number_access_list_service_page_sum_count_by_month', 'DESC');;
        
        if (!$per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service, $condition->buyer_id);
        }
        return $results;
    }

    public function serviceOverview($service_id, $buyer_id)
    {
        $result = $this->with(['seller', 'serviceImages', 'serviceDelivery'])
            ->with('serviceStoreBuyers', function ($result) use ($buyer_id) {
                $result->with('serviceCourses')
                    ->where(
                        [
                            'buyer_id' => $buyer_id,
                            'status' => 1
                        ]
                    )
                    ->orWhere(function ($q) use ($buyer_id) {
                        $q->where([
                            'buyer_id' => $buyer_id,
                            'status' => 2
                        ])->whereDate('end', '>', Carbon::now());
                    });
            })
            ->where('id', $service_id)
            ->first();
        if ($result) {
            $date_now = Carbon::now();

            $buyer_reserves = BuyerServiceReserve::where([
                'buyer_id' => $buyer_id,
                'service_id' => $service_id
            ])->where(function ($query) use ($date_now) {
                $query->whereDate('reserve_start', '=', $date_now)
                    ->WhereTime('reserve_start', '>', $date_now)
                    ->orWhereDate('reserve_start', '>', $date_now);
            })
                ->get();

            if ($buyer_reserves) {
                $result->reserves_count = count($buyer_reserves);
            } else {
                $result->reserves_count = null;
            }
            $this->convertServiceDetail($result);
        }

        return $result;
    }

    public function serviceDeleted($service_id, $buyer_id)
    {
        $result = $this->with(['seller', 'serviceImages', 'serviceDelivery'])
            ->with('serviceStoreBuyers', function ($result) use ($buyer_id) {
                $result->with('serviceCourses')
                    ->where(function ($q) use ($buyer_id) {
                        $q->where([
                            'buyer_id' => $buyer_id,
                            'status' => 2
                        ]);
                    });
            })
            ->where('id', $service_id)
            ->first();
        if ($result) {
            $date_now = Carbon::now();

            $buyer_reserves = BuyerServiceReserve::where([
                'buyer_id' => $buyer_id,
                'service_id' => $service_id
            ])->where(function ($query) use ($date_now) {
                $query->whereDate('reserve_start', '=', $date_now)
                    ->WhereTime('reserve_start', '>', $date_now)
                    ->orWhereDate('reserve_start', '>', $date_now);
            })
                ->get();

            if ($buyer_reserves) {
                $result->reserves_count = count($buyer_reserves);
            } else {
                $result->reserves_count = null;
            }
            $this->convertServiceDetail($result);
        }

        return $result;
    }

    public function getServiceByBuyerId($per_page, $condition)
    {
        $query = $this->with(['categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags'])
                    ->join('service_courses', 'service_courses.service_id' ,'services.id')
                    ->join('service_store_buyers', 'service_store_buyers.course_id', '=', 'service_courses.course_id')
                    ->where(function ($q) use ($condition) {
                        $q->where('service_store_buyers.buyer_id', '=', $condition->buyer_id)
                        ->where('service_store_buyers.status', '=', 1);
                    })->orWhere(function ($q) use ($condition){ 
                        $q->where('service_store_buyers.buyer_id', '=', $condition->buyer_id)
                        ->where('service_store_buyers.status', '=', 2)
                        ->whereDate('service_store_buyers.end', '>', Carbon::now());
                    })
                    ->select([
                        'services.*',
                        'service_store_buyers.created_at as service_store_buyer_created_at'
                    ])->orderBy('service_store_buyer_created_at', 'desc')
                    ;

        if (!$per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service, $condition->buyer_id);
        }
        return $results;
    }

    public function getAllServiceStopByBuyer($buyer_id, $condition)
    {
        $query = $this->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags', 'serviceStoreBuyers')
            ->whereHas('courses')
            ->whereHas('serviceStoreBuyers', function ($q) use ($buyer_id) {
                $q->where(['buyer_id' => $buyer_id, 'status' => 2])
                ->whereDate('end', '<=', Carbon::now());
            })
            ->orderBy('updated_at', 'DESC');

        if (!$condition->per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($condition->per_page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service, $buyer_id);
            unset($service->serviceStoreBuyers);
        }
        return $results;
    }

    public function favoriteRegisteredService($per_page, $condition)
    {
        $query = $this->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null)
            ->whereRelation('favorites', 'favorites.buyer_id', '=', $condition->buyer_id);

        $query = $this->sortService($query, $condition->sort);

        if (!$per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service, $condition->buyer_id);
        }
        return $results;
    }

    public function getOtherServices($service_id, $seller_id, $condition)
    {
        $query = $this->where('seller_id', $seller_id)
            ->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->where('id', '<>', $service_id)
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereHas('courses')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null)
            ->orderBy('created_at', 'DESC');

        if (!$condition->limit) {
            $results = $query->paginate(50);
        } else {
            $results = $query->paginate($condition->limit);
        }
        foreach ($results as $service) {
            $this->convertService($service,  $condition->buyer_id);
        }
        return $results;
    }

    public function getServicesByCategory($service_id, $service_cat_id, $condition)
    {
        $query = $this->where('service_cat_id', $service_cat_id)
            ->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->where('id', '<>', $service_id)         
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereHas('courses')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null)
            ->inRandomOrder();
        if (!$condition->limit) {
            $results = $query->paginate(50);
        } else {
            $results = $query->paginate($condition->limit);
        }
        foreach ($results as $service) {
            $this->convertService($service,  $condition->buyer_id);
        }
        return $results;
    }

    public function getServicesFavoriteByBuyer($service_id, $condition)
    {
        $query = $this->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->where('id', '<>', $service_id)
            ->whereHas('courses')
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null)
            ->whereRelation('favorites', 'favorites.buyer_id', '=',  $condition->buyer_id)
            ->inRandomOrder();

        if (!$condition->limit) {
            $results = $query->paginate(50);
        } else {
            $results = $query->paginate($condition->limit);
        }
        foreach ($results as $service) {
            $this->convertService($service, $condition->buyer_id);
        }
        return $results;
    }

    public function adminGetAllService($per_page, $condition)
    {
        $query = $this->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'tags','seller')
            ->where(['is_draft' => 0]);

        if (isset($condition->id)) {
            $query->where('id', 'like', '%' . $condition->id . '%');
        }
        if (isset($condition->seller_id)) {
            $query->where('seller_id', $condition->seller_id);
        }
        if (isset($condition->name)) {
            $query->where('name', 'like', '%' . $condition->name . '%');
        }
        if (isset($condition->caption)) {
            $query->where('caption', 'like', '%' . $condition->caption . '%');
        }
        if (isset($condition->service_content)) {
            $query->where('service_content', 'like', '%' . $condition->service_content . '%');
        }
        // if (isset($condition->age_confirm)) {
        //     $query->where('age_confirm', 'like', '%' . $condition->age_confirm . '%');
        // }
        if (isset($condition->private)) {
            $query->where('private', $condition->private);
        }
        if (isset($condition->sort_type)) {
            $type = 'ASC';
            if ($condition->sort_type === 2) {
                $type = 'DESC';
            }
            $query->orderBy('created_at', $type);
        } else {
            $query->orderBy('created_at', 'DESC');
        }
        if (!$per_page) {
            $results = $query->paginate(50);
        } else {
            $results = $query->paginate($per_page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service);
        }
        return $results;
    }

    public function checkFavoritedService($buyer_id, $service_id)
    {
        return  Favorite::where('service_id', $service_id)
            ->where('buyer_id', $buyer_id)
            ->first();
    }

    public function registerFavoriteService($id, $buyer_id)
    {
        $service = $this->find($id);
        $service->favorites()->create(['service_id' => $id, 'buyer_id' => $buyer_id]);
    }

    public function cancelFavoriteService($id, $buyer_id)
    {
        $favorites = Favorite::where('service_id', $id)
            ->where('buyer_id', $buyer_id)
            ->first();
        $favorites->delete();
    }

    public function ServicesAreasByServiceId($id)
    {
        return $this->find($id)->serviceAreas;
    }

    public function getServiceByArea($page, $service_id)
    {
        $results = $this->whereIn('id', $service_id)
            ->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null)
            // ->whereHas('courses')
            ->orderBy('updated_at', 'DESC')->paginate($page);
        foreach ($results->items() as $service) {
            $service->avg_reviews = number_format($service->reviews->avg('rating'), 1);
            // $service->price = $service->courses->min('price');
            unset($service->reviews);
            unset($service->courses);
        }
        return $results;
    }

    public function getServiceNew($per_page, $condition)
    {
        $query = $this->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->whereHas('courses')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null)
            ->with('serviceAreas');

        $query = $this->sortService($query, $condition->sort);
        if (!$per_page) {
            $results = $query->orderBy('created_at', 'desc')->paginate(50);
        } else {
            $results = $query->orderBy('created_at', 'desc')->paginate($per_page);
        }
        foreach ($results->items() as $service) {
            $this->convertService($service, $condition->buyer_id);
        }
        return $results;
    }

    public function convertService($service, $buyer_id = null)
    {
        $service->is_favorite = 0;
        if ($service->favorites->where('buyer_id', $buyer_id)->first()) $service->is_favorite = 1;
        unset($service->favorites);

        $service->avg_reviews = number_format($service->reviews->avg('rating'), 1);
        $service->price = $service->courses()->whereNotNull(['price','name', 'content'])->where('price', '!=', 0)->min('price');
        $serviceDelivery = $service->serviceDelivery;

        if ($service->service_type_id == 1 && $serviceDelivery) {
            $service->cycle = $serviceDelivery->interval ? ($serviceDelivery->month_delivery == 1 ? '月' : $serviceDelivery->month_delivery . 'ヶ月') : '週';
        }elseif ($service->service_type_id == 1) {
            $service->cycle = null;
        } else {
            $cycle = $service->courses()->where('price', $service->price)->min('cycle');
            $service->cycle = $cycle == 1 ? '月' : $cycle. 'ヶ月';
        }

        unset($service->reviews);
        unset($service->courses);
        $list_img = array();
        if (isset($service['serviceImages']) && !empty($service['serviceImages'])) {
            foreach ($service['serviceImages'] as $img) {
                if (!empty($img['image_url'])) {
                    $path = config('app.app_resource_path') . $img['image_url'];
                } else {
                    $path = null;
                }
                array_push($list_img, $path);
            }
        }
        $service['images'] = $list_img;
        unset($service->serviceImages);
        return $service;
    }

    public function convertServiceDetail($service, $buyer_id = null)
    {
        $service->is_favorite = 0;
        if ($service->favorites->where('buyer_id', $buyer_id)->first()) $service->is_favorite = 1;
        unset($service->favorites);

        $service->avg_reviews = number_format($service->reviews->where('is_active', 1)->avg('rating'), 1);
        $service->price = $service->courses()->whereNotNull(['price','name', 'content'])->where('price', '!=', 0)->min('price');
        
        if (!is_null($service->max) && $service->max > 0) {
            $service->current_quantity = $service->max - $service->serviceStoreBuyers->count();
        } else {
            $service->current_quantity = null;
        }
        
        unset($service->reviews);

        $list_img = array();
        if (isset($service['serviceImages']) && !empty($service['serviceImages'])) {
            foreach ($service['serviceImages'] as $img) {
                if (!empty($img['image_url'])) {
                    $path = config('app.app_resource_path') . $img['image_url'];
                } else {
                    $path = null;
                }
                array_push($list_img, ['id' => $img['id'], 'path' => $path]);
            }
        }

        $service->images = $list_img;
        unset($service->serviceImages);
        unset($service->courses);

        if (isset($service->seller->profile_image_url_sell)) {
            $service->seller->profile_image_url_sell = config('app.app_resource_path') . 'avatar/' . $service->seller->profile_image_url_sell;
        }

        if (isset($service['serviceHours']) && count($service['serviceHours'])) {
            $serviceHours =  $service['serviceHours']->toArray();
            $el_first = array_shift($serviceHours);
            array_push($serviceHours, $el_first);
            $service['service_hours'] = $serviceHours;
            unset($service->serviceHours);
        }

        if (isset($service['serviceStoreBuyers']) && isset($service['serviceStoreBuyers'][0]['serviceCourses'])) {
            $serviceCourse = $service['serviceStoreBuyers'][0]['serviceCourses'];
            $serviceDelivery = $service->serviceDelivery;

            if ($service->service_type_id == 1 && $serviceDelivery) {
                $serviceCourse->cycle = $serviceDelivery->interval == 1 ? ($serviceDelivery->month_delivery == 1 ? '月' : $serviceDelivery->month_delivery . 'ヶ月') : '週';
            } elseif ($service->service_type_id == 1) {
                $serviceCourse->cycle = null;
            } else {
                $cycle = $serviceCourse->cycle;
                $serviceCourse->cycle = $cycle == 1 ? '月' : $cycle. 'ヶ月';
            }
            
            $service['serviceStoreBuyers'][0]['service_courses'] = $serviceCourse;
        }


        return $service;
    }

    public function convertServiceSellingDetail($service)
    {
        $list_img = array();
        if (isset($service['serviceImages']) && !empty($service['serviceImages'])) {
            foreach ($service['serviceImages'] as $img) {
                if (!empty($img['image_url'])) {
                    $path = config('app.app_resource_path') . $img['image_url'];
                } else {
                    $path = null;
                }
                array_push($list_img, ['id' => $img['id'], 'path' => $path]);
            }
        }
        $service->images = $list_img;
        unset($service->serviceImages);

        if (isset($service->seller->profile_image_url_sell)) {
            $service->seller->profile_image_url_sell = config('app.app_resource_path') . 'avatar/' . $service->seller->profile_image_url_sell;
        }
        
        return $service;
    }

    public function searchServiceByKeyword($per_page, $condition)
    {
        $query = $this->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->where(function ($q) use ($condition) {
                $q->where('name', 'like', '%' . $condition->keyword . '%')
                    ->orWhereRelation('tags', 'tags.name', 'like', '%' . $condition->keyword . '%');
            })
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereHas('courses')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null);
        $query = $this->sortService($query, $condition->sort);

        if (!$per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service,  $condition->buyer_id);
        }
        return $results;
    }

    public function searchServiceByCategory($per_page, $condition)
    {
        $query = $this->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->where('service_cat_id', $condition->category_id)
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereHas('courses')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null);

        $query = $this->sortService($query, $condition->sort);

        if (!$per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service, $condition->buyer_id);
        }
        return $results;
    }

    public function sortService($query, $sort)
    {
        if ($sort == 1) {
            $query->orderBy('created_at', 'desc');
        } else if ($sort == 2) {
            $query->orderBy(
                ServiceReview::selectRaw('avg(rating) as average_rating')
                    ->whereColumn('services.id', 'service_reviews.service_id'),
                'desc'
            );
        } else if ($sort == 3) {
            $query->orderBy(
                ServiceCourse::select('price')
                    ->whereColumn('services.id', 'service_courses.service_id')
                    ->orderBy('price', 'asc')
                    ->limit(1),
                'desc'
            );
        } else if ($sort == 4) {
            $query->orderBy(
                ServiceCourse::select('price')
                    ->whereColumn('services.id', 'service_courses.service_id')
                    ->orderBy('price', 'asc')
                    ->limit(1),
                'asc'
            );
        } else if ($sort == 5) {
            $query->orderBy(
                Favorite::selectRaw('count(service_id) as counts')
                    ->whereColumn('services.id', 'favorites.service_id'),
                'desc'
            );
        } else {
            $query->orderBy('updated_at', 'DESC');
        }
        return $query;
    }

    public function getBusinessScheduleByServiceId($service_id)
    {
        $service = $this->where('id', $service_id)->first();
        $reserveSetting =  $service->serviceReserveSetting;

        $serviceHours =   $service->serviceHours->toArray();

        $el_first = array_shift($serviceHours);
        array_push($serviceHours, $el_first);

        $serviceHoursTemps = $service->ServiceHoursTemps;

        return
            [
                'reserve_setting' => $reserveSetting,
                'service_hours' => $serviceHours
            ];
    }

    public function getBusinessScheduleTempByServiceId($service_id, $date = null)
    {
        $service = $this->where('id', $service_id)->first();

        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        }

        $serviceHoursTemps = $service->ServiceHoursTemps()->whereDate('date', '=', $date)->first();

        if ($serviceHoursTemps) {
            $date = Carbon::createFromFormat('Y-m-d',$serviceHoursTemps->date);
            $date_format = $date->month . '月' . $date->day . '日 ' . $this->getDayJapan($date->dayOfWeek);
            $serviceHoursTemps->date_format = $date_format;
        } else {
            $date = Carbon::createFromFormat('Y-m-d', $date);
            $date_format = $date->month . '月' . $date->day . '日 ' . $this->getDayJapan($date->dayOfWeek);
            $day_of_week =  $date->dayOfWeek;
            $serviceHoursTemps = $service->serviceHours()->where('day_of_week', $day_of_week)->first();
            $serviceHoursTemps->date = $date->format('Y-m-d');
            $serviceHoursTemps->date_format = $date_format;
        }

        return $serviceHoursTemps;
    }

    public function searchServiceByArea($per_page, $condition)
    {
        $area = Area::where('name', $condition->area)->select('id')->first();
        $area_id = isset($area) ? $area['id'] : null;
        $ids = ServiceArea::where('area_id', $area_id)->select('service_id')->get()->pluck('service_id');

        $query = $this->whereIn('id', $ids)
            ->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereHas('courses')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null);
        $query = $this->sortService($query, $condition->sort);
        if (!$per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service, $condition->buyer_id);
        }
        return $results;
    }

    public function searchServiceByTag($per_page, $condition)
    {
        $tag = Tag::where('name', $condition->tag_name)->select('id')->first();
        $tag_id = isset($tag) ? $tag['id'] : null;
        $ids = ServiceTag::where('tag_id', $tag_id)->select('service_id')->get()->pluck('service_id');

        $query = $this->whereIn('id', $ids)
            ->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereHas('courses')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null);

        $query = $this->sortService($query, $condition->sort);
        
        if (!$per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service, $condition->buyer_id);
        }

        return $results;
    }

    /**
     * ID発行
     */
    private static function generateId()
    {
        // 9桁のランダムな数字（0始まり禁止）
        $random = strval(mt_rand(100000000, 999999999));

        while (!is_null(Service::find($random))) {
            $random = strval(mt_rand(100000000, 999999999));
        }

        return $random;
    }

    public function createService()
    {
        // 新規登録時、IDを自動採番
        if (!isset($this->id)) {
            $this->id = self::generateId();
        }
        return $this->save();
    }

    public function getLastServiceId()
    {
        $service = $this->orderBy('id', 'desc')->first();
        if (!isset($service)) {
            return 1;
        }
        return $service->id + 1;
    }

    public function findHashId($hash_id)
    {
        return $this->where('hash_id', $hash_id)->first();
    }

    public function getLatLngByAddress($request)
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'key' => config('map.key'),
            'address' => $request->address,
        ]);
        $data = json_decode($response->body());
        $data = $data->results;
        if ($data) {
            $data = $data[0]->geometry;
            $location = $data->location;
            return $location;
        }
        return null;
    }

    public function findByHashId($hash_id)
    {
        $result = $this->where('hash_id', $hash_id)->first();
        return $result;
    }

    public function getAllServiceBySeller($seller_id, $condition)
    {
        $query = $this->where('seller_id', $seller_id)->select('id','name as service_name');
        if (isset($condition->per_page)) {
            $result =  $query->paginate($condition->per_page);
        } else {
            $result = $query->get();
        }
        return $result;
    }

    public function getAllBySellerId($seller_id)
    {
        $result = $this->where('seller_id', $seller_id)->select(
            'id',
            'hash_id',
            'name'
        )->get();
        return $result;
    }
    
    public function getAllServiceSellingBySellerId($seller_id)
    {
        $result = $this->where('seller_id', $seller_id)
                ->where(['is_draft' => 0, 'enabled' => 1])
                ->select(
                    'id',
                    'hash_id',
                    'name'
                )->get();
        return $result;
    }

    public function getBrowsingHistoryServices($service_id, $ip_address, $condition)
    {
        $query = $this->whereHas('serviceBrowsingHistory', function ($q) use($ip_address) {
                $q->where('ip_address', $ip_address);
                })
                ->where('services.id', '<>', $service_id)
                ->join('service_browsing_histories', function($join) use($ip_address) {
                    $join->on('service_browsing_histories.service_id', '=', 'services.id')
                    ->where('service_browsing_histories.ip_address', $ip_address);
                })
                ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
                ->whereRelation('account', 'accounts.date_withdrawal' , '=', null)
                ->select('services.*', 'service_browsing_histories.created_at as service_browsing_history_created_at')
                ->orderBy('service_browsing_history_created_at', 'DESC');

        if ($condition->per_page ) {
            $results = $query->paginate($condition->per_page);
        } else {
            $results = $query->paginate(10);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service,  $condition->buyer_id);
        }
        
        return $results;
    }

    public function searchServiceByStation($per_page, $condition)
    {
        $query = $this->where(['private' => 0, 'is_draft' => 0, 'enabled' => 1])
            ->where(function ($q) use ($condition) {
                $q->where('name', 'like', '%' . $condition->keyword . '%')
                    ->orWhereRelation('tags', 'tags.name', 'like', '%' . $condition->keyword . '%');
            })
            ->select('services.*')
            // ->selectRaw("
            //     111.111 *
            //     DEGREES(ACOS(LEAST(1.0, COS(RADIANS(services.lat))
            //         * COS(RADIANS('$condition->lat'))
            //         * COS(RADIANS(services.lng - '$condition->lng'))
            //         + SIN(RADIANS(services.lat))
            //         * SIN(RADIANS('$condition->lat'))))) AS distance_in_km
            // ")
            ->selectRaw("
                6378 * 
                ACOS(
                    (SIN(RADIANS('$condition->lng')) * SIN(RADIANS(services.lng))) 
                    + COS(RADIANS('$condition->lng')) 
                    * COS(RADIANS(services.lng)) 
                    * COS(RADIANS(ABS(services.lat - '$condition->lat')))
                ) AS distance_in_km
            ")
            ->having('distance_in_km', '<=', 1)
            ->with('categories', 'type', 'serviceImages', 'reviews', 'courses', 'favorites', 'tags')
            ->whereHas('courses')
            ->whereRelation('account', 'accounts.date_withdrawal' , '=', null);

        $sort = $condition->sort;

        if ($sort == 1) {
            $query->orderBy('created_at', 'desc');
        } else if ($sort == 2) {
            $query->orderBy(
                ServiceReview::selectRaw('avg(rating) as average_rating')
                    ->whereColumn('services.id', 'service_reviews.service_id'),
                'desc'
            );
        } else if ($sort == 3) {
            $query->orderBy(
                ServiceCourse::select('price')
                    ->whereColumn('services.id', 'service_courses.service_id')
                    ->orderBy('price', 'asc')
                    ->limit(1),
                'desc'
            );
        } else if ($sort == 4) {
            $query->orderBy(
                ServiceCourse::select('price')
                    ->whereColumn('services.id', 'service_courses.service_id')
                    ->orderBy('price', 'asc')
                    ->limit(1),
                'asc'
            );
        } else if ($sort == 5) {
            $query->orderBy(
                Favorite::selectRaw('count(service_id) as counts')
                    ->whereColumn('services.id', 'favorites.service_id'),
                'desc'
            );
        } else {
            $query->orderBy('distance_in_km', 'asc');
        }
        
        if (!$per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }

        foreach ($results->items() as $service) {
            $this->convertService($service,  $condition->buyer_id);
        }

        return $results;
    }
}
