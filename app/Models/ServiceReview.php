<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceReview extends Model
{
    use HasFactory;
    protected $table = 'service_reviews';

    protected $fillable = [
        'buyer_id',
        'service_id',
        'description',
        'rating',
        'seller_reply',
        'is_active',
        'is_active_seller',
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i',
    //     'updated_at' => 'datetime:Y-m-d H:i',
    // ];


    public function serviceReviewImages()
    {
        return $this->hasMany(ServiceReviewImage::class, 'reviews_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id')->select(['id', 'hash_id', 'name', 'service_content', 'address', 'seller_id']);
    }

    public function serviceImages()
    {
        return $this->hasManyThrough(ServiceImage::class, Service::class, 'id', 'service_id', 'service_id', 'id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id', 'account_id')
            ->join('accounts', 'accounts.id', '=', 'buyers.account_id')
            ->select(
                'buyers.account_id',
                'accounts.email',
                'buyers.account_name',
                'buyers.first_name',
                'buyers.last_name',
                'buyers.gender',
                'buyers.profile_image_url_buy',
            );
    }

    public function seller()
    {
        return $this->hasOneThrough(Seller::class, Service::class, 'id', 'account_id', 'service_id', 'seller_id')
            ->join('accounts', 'accounts.id', '=', 'sellers.account_id')
            ->select(
                'sellers.account_id',
                'accounts.email',
                'sellers.account_name',
                'sellers.first_name',
                'sellers.last_name',
                'sellers.gender',
                'sellers.profile_image_url_sell',
            );
    }

    public function findByServiceId($service_id)
    {
        return $this->where('service_id', $service_id)->first();
    }

    public function findByServiceAndBuyer($service_id, $buyer_id)
    {
        return $this->where(['service_id' => $service_id, 'buyer_id' => $buyer_id])->first();
    }

    public function convertReviews($reviews)
    {
        $reviews->service->avg_reviews = number_format($reviews->service->reviews->avg('rating'), 1);
        unset($reviews->service->reviews);
        if (isset($reviews['serviceReviewImages']) && !empty($reviews['serviceReviewImages'])) {
            $review_images = [];
            foreach ($reviews['serviceReviewImages'] as $img) {
                $path = config('app.app_resource_path') . $img['image_url'];
                array_push($review_images, ['id' => $img['id'], 'path' => $path]);
            }
            unset($reviews->serviceReviewImages);
            $reviews->images = $review_images;
        } else {
            $reviews->images = [];
        }
        if (isset($reviews['serviceImages']) && !empty($reviews['serviceImages'])) {
            $service_images = [];
            foreach ($reviews['serviceImages'] as $img) {
                $path = config('app.app_resource_path') . $img->image_url;
                array_push($service_images, $path);
            }
            unset($reviews->serviceImages);
            $reviews->service->images = $service_images;
        } else {
            $reviews->service->images = [];
        }
        if (isset($reviews->seller->profile_image_url_sell)) {
            $reviews->seller->profile_image_url_seller = config('app.app_resource_path') . 'avatar/' . $reviews->seller->profile_image_url_sell;
        }

        if (isset($reviews->buyer->profile_image_url_buy)) {
            $reviews->buyer->profile_image_url_buyer = config('app.app_resource_path') . 'avatar/' . $reviews->buyer->profile_image_url_buy;
        }
        return $reviews;
    }

    public function getAllByServiceId($service_id, $per_page)
    {
        $query =  $this->where('service_id',  $service_id)->where('is_active', 1)->with('buyer', 'seller', 'serviceReviewImages')->orderBy('updated_at', 'DESC');;

        if (!$per_page) {
            $listReviews = $query->paginate(50);
        } else {
            $listReviews = $query->paginate($per_page);
        }

        foreach ($listReviews as $reviews) {
            $this->convertReviews($reviews);
        }
        return $listReviews;
    }

    public function getAll($per_page, $request)
    {
        $query =  $this->with('serviceReviewImages', 'service')->orderBy('updated_at', 'DESC');
        if (isset($request->id)) {
            $query->where('id', 'like', '%' . $request->id . '%');
        }
        if (isset($request->buyer_id)) {
            $query->where('buyer_id', $request->buyer_id);
        }
        if (isset($request->name)) {
            $query->whereRelation('service', 'name', 'like', '%' . $request->name . '%');
        }
        if (isset($request->rating)) {
            $query->where('rating', $request->rating);
        }
        if (isset($request->description)) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }
        if (isset($request->is_active)) {
            $query->where('is_active', $request->is_active);
        }
        if (isset($request->sort_type)) {
            $type = 'ASC';
            if ($request->sort_type === 2) {
                $type = 'DESC';
            }
            $query->orderBy('created_at', $type);
        } else {
            $query->orderBy('created_at', 'DESC');
        }
        if (!$per_page) {
            $listReviews = $query->paginate(50);
        } else {
            $listReviews = $query->paginate($per_page);
        }
        foreach ($listReviews as $review) {
            $this->convertReviews($review);
        }
        return $listReviews;
    }

    public function getAllByBuyer($buyer_id, $per_page)
    {
        $query =  $this->where('buyer_id', $buyer_id)->with('serviceReviewImages', 'seller', 'service', 'serviceImages')->orderBy('updated_at', 'DESC');

        if (!$per_page) {
            $listReviews = $query->paginate(50);
        } else {
            $listReviews = $query->paginate($per_page);
        }
        foreach ($listReviews as $review) {
            $this->convertReviews($review);
        }
        return $listReviews;
    }

    public function getAllByBuyerAndService($service_id, $buyer_id, $per_page)
    {
        $query =  $this->with('serviceReviewImages', 'seller', 'service', 'serviceImages')
        ->where(['buyer_id' => $buyer_id, 'service_id' => $service_id]);
        if (!$per_page) {
            $listReviews = $query->paginate(50);
        } else {
            $listReviews = $query->paginate($per_page);
        }
        foreach ($listReviews as $review) {
            $this->convertReviews($review);
        }
        return $listReviews;
    }

    public function getAllBySeller($seller_id, $per_page)
    {
        $query =  $this->with('serviceReviewImages', 'seller', 'service', 'serviceImages')
            ->where('is_active', 1)
            ->whereRelation('service', 'services.seller_id', '=', $seller_id)
            ->orderBy('updated_at', 'DESC');

        if (!$per_page) {
            $listReviews = $query->paginate(50);
        } else {
            $listReviews = $query->paginate($per_page);
        }
        foreach ($listReviews as $review) {
            $this->convertReviews($review);
        }
        return $listReviews;
    }

    public function getReviewsById($id)
    {
        $review = $this->where('id', $id)->with('serviceReviewImages', 'seller', 'buyer', 'service', 'serviceImages')->first();

        return $this->convertReviews($review);
    }

    public function countByServiceIdMonth($service_id)
    {
        $result = array();
        $now = Carbon::now();
        $current = $this->where('service_id', $service_id)
            ->whereYear('created_at', '=', $now->year)
            ->whereMonth('created_at', '=', $now->month)
            ->count();
        $last = $this->where('service_id', $service_id)
            ->whereYear('created_at', '=', $now->year)
            ->whereMonth('created_at', '=', $now->month - 1)
            ->count();
        $result['current_reviews'] = $current;
        $result['increase_number'] = $current - $last;
        return $result;
    }

    public function countRantingByServiceIdMonth($service_id)
    {
        $result = array();
        $now = Carbon::now();
        $current = $this->where('service_id', $service_id)
            ->whereYear('created_at', '=', $now->year)
            ->whereMonth('created_at', '=', $now->month)
            ->avg('rating');
        $last = $this->where('service_id', $service_id)
            ->whereYear('created_at', '=', $now->year)
            ->whereMonth('created_at', '=', $now->month - 1)
            ->avg('rating');
        $result['current_ratings'] = number_format($current, 1);
        $result['increase_number'] = number_format(($current - $last), 1);
        return $result;
    }

    public function sellerReply($id, $request)
    {
        $this->where('id', $id)->update(['seller_reply' => $request->seller_reply]);
    }

    public function graphByReviewService($service_id, $condition)
    {
        if (!empty($condition->last_month_number)) {
            $month = $condition->last_month_number;
        } else {
            $month = 1;
        }
        
        $results = [];

        if ($month == 1) {
            
            $start = Carbon::now()->subMonths($month)->toDateString();
            $end = Carbon::now()->toDateString();

            $graph =  $this->where('service_id', $service_id)->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                DB::raw('COUNT(buyer_id) as count_reviews')
            )
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy([ 'year', 'month', 'day'])
            ->orderBy('created_at', 'asc')
            ->get();

            while ($start <= $end) {
                $item = [
                    'year' => Carbon::parse($start)->year,
                    'month' => Carbon::parse($start)->month,
                    'day' => Carbon::parse($start)->day,
                    'value' => 0,
                ];

                array_push($results, $item);
                
                $start = Carbon::parse($start)->addDays(1)->toDateString();
            }

            foreach ($results as $key => $item) {
                foreach ($graph as $graph_item) {
                    if ($item['year'] == $graph_item['year'] && $item['month'] == $graph_item['month'] && $item['day'] == $graph_item['day']) {
                        $item['value'] = $graph_item['count_reviews'];
                        $results[$key] = $item;
                    }
                }
            }

        } else {
            
            $start = Carbon::now()->subMonths($month - 1)->toDateString();
            $end = Carbon::now()->toDateString();

            $graph =  $this->where('service_id', $service_id)->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(buyer_id) as count_reviews')
            )
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy(['year', 'month'])
            ->orderBy('created_at')
            ->get();

            $start_year = Carbon::now()->subMonths($month - 1)->year;
            $start_month = Carbon::now()->subMonths($month - 1)->month;
            
            $end_year = Carbon::now()->year;
            $end_month = Carbon::now()->month;

            while (($start_month <= $end_month && $start_year == $end_year) || $start_year < $end_year ) {
                $item = [
                    'year' => Carbon::parse($start)->year,
                    'month' => Carbon::parse($start)->month,
                    'value' => 0,
                ];

                array_push($results, $item);
                
                $start = Carbon::parse($start)->addMonth();
                $start_year = $start->year;
                $start_month = $start->month;
            }

            foreach ($results as $key => $item) {
                foreach ($graph as $graph_item) {
                    if ($item['year'] == $graph_item['year'] && $item['month'] == $graph_item['month']) {
                        $item['value'] = $graph_item['count_reviews'];
                        $results[$key] = $item;
                    }
                }
            }

        }

        return $results;
    }

    public function graphRatingByService($service_id, $condition)
    {
        if (!empty($condition->last_month_number)) {
            $month = $condition->last_month_number;
        } else {
            $month = 1;
        }

        $start = Carbon::now()->subMonths($month)->toDateString();
        $end = Carbon::now()->toDateString();
        
        $results = [];

        if ($month == 1) {
            $graph =  $this->where('service_id', $service_id)->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                DB::raw('AVG(rating) as avg_rating')
            )
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy([ 'year', 'month', 'day'])
            ->orderBy('created_at', 'asc')
            ->get();

            while ($start <= $end) {
                $item = [
                    'year' => Carbon::parse($start)->year,
                    'month' => Carbon::parse($start)->month,
                    'day' => Carbon::parse($start)->day,
                    'value' => 0,
                ];

                array_push($results, $item);
                
                $start = Carbon::parse($start)->addDays(1)->toDateString();
            }

            foreach ($results as $key => $item) {
                foreach ($graph as $graph_item) {
                    if ($item['year'] == $graph_item['year'] && $item['month'] == $graph_item['month'] && $item['day'] == $graph_item['day']) {
                        $item['value'] = number_format($graph_item['avg_rating'], 1);
                        $results[$key] = $item;
                    }
                }
            }

        } else {
            $graph =  $this->where('service_id', $service_id)->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('AVG(rating) as avg_rating')
            )
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy(['year', 'month'])
            ->orderBy('created_at')
            ->get();

            while ($start <= $end) {
                $item = [
                    'year' => Carbon::parse($start)->year,
                    'month' => Carbon::parse($start)->month,
                    'value' => 0,
                ];

                array_push($results, $item);
                
                $start = Carbon::parse($start)->addMonths(1);
            }

            foreach ($results as $key => $item) {
                foreach ($graph as $graph_item) {
                    if ($item['year'] == $graph_item['year'] && $item['month'] == $graph_item['month']) {
                        $item['value'] =  number_format($graph_item['avg_rating'], 1);
                        $results[$key] = $item;
                    }
                }
            }

        }

        return $results;

    }

    public function countByServiceIdYear($service_id, $condition)
    {
        $current_year = Carbon::now()->year;
        if (isset($condition->last_year_number)) {
            $last_year = $current_year - $condition->last_year_number;
        } else {
            $last_year = $current_year - 1;
        }
        $result = array();

        $current = $this->where('service_id', $service_id)
            ->whereYear('created_at', '=', $current_year)
            ->count();

        $last = $this->where('service_id', $service_id)
            ->whereYear('created_at', '=', $last_year)
            ->count();

        $result['current_reviews'] = $current;
        $result['increase_number'] = $current - $last;
        return $result;
    }

    public function countRantingByServiceIdYear($service_id, $condition)
    {
        $current_year = Carbon::now()->year;
        if (isset($condition->last_year_number)) {
            $last_year = $current_year - $condition->last_year_number;
        } else {
            $last_year = $current_year - 1;
        }
        $result = array();

        $current = $this->where('service_id', $service_id)
            ->whereYear('created_at', '=', $current_year)
            ->avg('rating');

        $last = $this->where('service_id', $service_id)
            ->whereYear('created_at', '=', $last_year)
            ->avg('rating');

        $result['current_ratings'] = number_format($current, 1);
        $result['increase_number'] = number_format(($current - $last), 1);
        return $result;
    }

    public function deleteAllByServiceId($service_id)
    {
        $results = $this->where('service_id', $service_id)->get();
        foreach ($results as $item) {
            foreach ($item->serviceReviewImages as $image_reviews) {
                if ($image_reviews['image_url']) {
                    $path = $image_reviews['image_url'];
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            }
            $item->serviceReviewImages()->delete();
            $dir = 'images/reviews/' . $item->id;
            if (Storage::disk('public')->exists($dir)) Storage::disk('public')->deleteDirectory($dir);
            $item->delete();
        }
    }
}
