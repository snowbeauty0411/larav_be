<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ServiceStoreBuyer extends Model
{
    use HasFactory;

    protected $table = 'service_store_buyers';

    protected $fillable = [
        'course_id',
        'buyer_id',
        'qrUrl',
        'flagQR',
        'start',
        'end',
        'status',
        'buy_at',
        'cancel_at',
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id', 'account_id')
            ->join('accounts', 'accounts.id', '=', 'buyers.account_id')
            ->select(
                'buyers.account_id',
                'buyers.stripe_customer_id',
                'buyers.account_name',
                'buyers.first_name',
                'buyers.last_name',
                'buyers.gender',
                'accounts.postcode',
                'accounts.email',
                'buyers.profile_image_url_buy',
                'buyers.profile_text_buy',
            );
    }

    public function serviceCourses()
    {
        return $this->belongsTo(ServiceCourse::class, 'course_id', 'course_id');
    }

    public function services()
    {
        return $this->belongsTo(ServiceCourse::class, 'course_id', 'course_id')
            ->leftJoin('services', 'services.id', '=', 'service_courses.service_id')
            ->select(
                'service_courses.course_id',
                'service_courses.service_id',
                'services.id',
                'services.name',
                'services.caption',
            );
    }

    public function delivery()
    {
        return $this->hasMany(Delivery::class, 'service_store_buyer_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'service_store_buyer_id');
    }

    public function getAllBuyerUseService($service_id, $request)
    {
        $query = $this->with('buyer', 'buyer.shipping_info_default', 'serviceCourses')
            ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->select('service_store_buyers.*');
        if (isset($request->course_id)) {
            $query->where('course_id', $request->course_id);
        }
        if (isset($request->created_at)) {
            $query->whereDate('created_at', $request->created_at);
        }
        $limit = $request->per_page ? $request->per_page : 10;
        $results = $query->orderBy('status')->orderByDesc('created_at')->paginate($limit);
        return $results;
    }

    public function getAllCourseAndDateUseService($service_id, $condition)
    {
        $results = [];
        if ($condition->type_select == 0) {
            $service_store_buyers = $this->with('serviceCourses:service_id,course_id,name')
                ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
                ->select('service_store_buyers.course_id')
                ->get()
                ->toArray();
            $index = 0;
            $course = [];
            if (count($service_store_buyers) > 0) {
                foreach ($service_store_buyers as $item) {
                    $course_id = $item['service_courses']['course_id'];
                    $course_name = $item['service_courses']['name'];
                    if (!in_array($course_id, $course)) {
                        $results[$index] = [
                            'value' => $course_id,
                            'text' => $course_name
                        ];
                        array_push($course, $course_id);
                        $index++;
                    }
                }
            }
        } else {
            $dateList = $this->with('serviceCourses')
                ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
                ->pluck('service_store_buyers.created_at');
            $index = 0;
            $date = [];
            if (count($dateList) > 0) {
                foreach ($dateList as $item) {
                    if (!in_array(date('Y-m-d', strtotime($item)), $date)) {
                        $results[$index] = [
                            'value' => date('Y-m-d', strtotime($item)),
                            'text' => date('Y/m/d', strtotime($item))
                        ];
                        $index++;
                        array_push($date, date('Y-m-d', strtotime($item)));
                    }
                }
            }
        }

        return $results;
    }

    public function countByServiceIdMonth($service_id)
    {
        $result = array();
        $now = Carbon::now();
        $current = $this->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->whereYear('buy_at', '=', $now->year)
            ->whereMonth('buy_at', '=', $now->month)
            ->count();
        $last = $this->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->whereYear('buy_at', '=', $now->year)
            ->whereMonth('buy_at', '=', $now->month - 1)
            ->count();
        $result['current_contracts'] = $current;
        $result['increase_number'] = $current - $last;
        return $result;
    }

    public function countByServiceIdYear($service_id, $condition)
    {
        $current_year = Carbon::now()->year;
        if (isset($condition->last_year_number)) {
            $last_year = $current_year - $condition->last_year_number;
        } else {
            $last_year = $current_year - 1;
        }

        $current = $this->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->whereYear('buy_at', '=', $current_year)
            ->count();

        $last = $this->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->whereYear('buy_at', '=', $last_year)
            ->count();

        $result['current_contracts'] = $current;
        $result['increase_number'] = $current - $last;
        return $result;
    }

    public function countBuyerByServiceId($service_id)
    {
        $result = array();
        $now = Carbon::now();
        $curent = $this->select('buyer_id')
            ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->whereYear('created_at', '=', $now->year)
            ->whereMonth('created_at', '=', $now->month)
            ->groupBy('buyer_id')->get();
        $last = $this->select('buyer_id')
            ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->whereYear('created_at', '=', $now->year)
            ->whereMonth('created_at', '=', $now->month - 1)
            ->groupBy('buyer_id')->get();
        $result['curent_count'] = count($curent);
        $result['last_count'] =  count($curent) -  count($last);
        return $result;
    }

    public function getAllBuyerUseServiceInMonth($service_id, $condition)
    {
        $query = $this::query()->from('service_store_buyers')
            ->leftjoin('buyers as buyer', 'buyer.account_id', '=', 'service_store_buyers.buyer_id')
            ->leftjoin('service_courses as course', 'course.course_id', '=', 'service_store_buyers.course_id')
            ->select(
                'service_store_buyers.buyer_id',
                'service_store_buyers.course_id',
                'service_store_buyers.start',
                'buyer.stripe_customer_id',
                'buyer.profile_image_url_buy',
                'buyer.account_id',
                'course.price as course_price',
                'course.name',
                'course.course_id'
            )->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id);
        $curent_month_name = Carbon::now()->format('M');
        $curr_month = date("m", strtotime($curent_month_name));
        if (isset($condition->month)) {
            $curent_year = Carbon::now()->format('Y');
            $query->whereYear('start', $curent_year)->whereMonth('start', '=', $condition->month);
        } else {
            $curent_year = Carbon::now()->format('Y');
            $query->whereYear('start', $curent_year)->whereMonth('start', '=', $curr_month);
        }
        $result = $query->orderBy('service_store_buyers.start', 'DESC')->get();
        return $result;
    }

    public function getAllWithPaymentBySeller($service_id, $condition)
    {
        $query = $this::from('service_store_buyers')
            ->rightjoin('payments', 'payments.service_store_buyer_id', '=', 'service_store_buyers.id')
            ->leftjoin('buyers as buyer', 'buyer.account_id', '=', 'service_store_buyers.buyer_id')
            ->leftjoin('service_courses as course', 'course.course_id', '=', 'service_store_buyers.course_id')
            ->select(
                'service_store_buyers.id',
                'service_store_buyers.buyer_id',
                'payments.total',
                'payments.sub_total',
                'payments.service_fee',
                'payments.pay_expire_at_date as pay_at_date',
                'payments.payment_status',
                'course.course_id',
                'course.name as course_name',
                'buyer.profile_image_url_buy',
                'buyer.account_id',
                'payments.stripe_charge_id as pay_id',
            )->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id);
        //filter
        $curent_month_name = Carbon::now()->format('M');
        $curr_month = date("m", strtotime($curent_month_name));
        if (isset($condition->month)) {
            $curent_year = Carbon::now()->format('Y');
            $query->whereYear('payments.pay_expire_at_date', $curent_year)->whereMonth('payments.pay_expire_at_date', '=', $condition->month);
        } else {
            $curent_year = Carbon::now()->format('Y');
            $query->whereYear('payments.pay_expire_at_date', $curent_year)->whereMonth('payments.pay_expire_at_date', '=', $curr_month);
        }

        if (!isset($condition->per_page)) {
            $result = $query->paginate(10);
        } else {
            $result = $query->paginate($condition->per_page);
        }
        return $result;
    }

    public function findByServiceAndUser($service_id, $buyer_id)
    {
        $result = $this->where('buyer_id', $buyer_id)->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)->first();
        return $result;
    }

    public function graphByService($service_id, $condition)
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
            $graph =  $this->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                DB::raw('COUNT(buyer_id) as count_contact')
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
                        $item['value'] = $graph_item['count_contact'];
                        $results[$key] = $item;
                    }
                }
            }

        } else {
            $graph =  $this->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(buyer_id) as count_contact')
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
                        $item['value'] = $graph_item['count_contact'];
                        $results[$key] = $item;
                    }
                }
            }

        }

        return $results;
    }

    public function findServiceUseByBuyer($service_id, $buyer_id)
    {
        $serviceStoreBuyer = $this->where([
                ['service_store_buyers.buyer_id', '=', $buyer_id],
            ])->where(function($q){
                $q->where([
                    'service_store_buyers.status' => 1
                ])
                ->orWhere(function ($q) {
                    $q->where([
                        'service_store_buyers.status' => 2
                    ])->whereDate('service_store_buyers.end', '>', Carbon::now());
                });
            })
            ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->first();
        return $serviceStoreBuyer;
    }

    public function findByBuyer($buyer_id)
    {
        return $this->where([
            ['service_store_buyers.buyer_id', '=', $buyer_id],
        ])->where(function($q){
            $q->where([
                'service_store_buyers.status' => 1
            ])
            ->orWhere(function ($q) {
                $q->where([
                    'service_store_buyers.status' => 2
                ])->whereDate('service_store_buyers.end', '>', Carbon::now());
            });
        })
        ->first();
    }

    public function getRevenueOfServiceInMonth($service_id)
    {
        $now = Carbon::now();
        $result1 = $this->with('serviceCourses')
            ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->whereYear('buy_at', $now->year)
            ->whereMonth('buy_at', $now->month)
            ->get();

        $result2 = $this->with('serviceCourses')
            ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->whereYear('buy_at', $now->year)
            ->whereMonth('buy_at', $now->month - 1)
            ->get();

        $revenue_current = 0;
        foreach ($result1 as $value) {
            $revenue_current += $value->serviceCourses->price;
        }

        $revenue_last = 0;
        foreach ($result2 as $value) {
            $revenue_last += $value->serviceCourses->price;
        }

        $result['current_revenues'] = $revenue_current;
        $result['increase_number'] = $revenue_current - $revenue_last;
        return $result;
    }

    public function graphRevenue($service_id, $condition)
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

            $graph =  $this->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->select(
                DB::raw('YEAR(service_store_buyers.buy_at) as year'),
                DB::raw('MONTH(service_store_buyers.buy_at) as month'),
                DB::raw('DAY(service_store_buyers.buy_at) as day'),
                DB::raw('SUM(service_courses.price) as sum_price'),
                'service_store_buyers.id',
                'service_store_buyers.course_id',
            )
            ->whereDate('service_store_buyers.buy_at', '>=', $start)
            ->whereDate('service_store_buyers.buy_at', '<=', $end)
            ->groupBy([ 'year', 'month', 'day'])
            ->orderBy('service_store_buyers.buy_at', 'asc')
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
                        $item['value'] = $graph_item['sum_price'];
                        $results[$key] = $item;
                    }
                }
            }

        } else {
            
            $start = Carbon::now()->subMonths($month - 1)->toDateString();
            $end = Carbon::now()->toDateString();

            $graph = $this->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
            ->select(
                DB::raw('YEAR(service_store_buyers.buy_at) as year'),
                DB::raw('MONTH(service_store_buyers.buy_at) as month'),
                DB::raw('SUM(service_courses.price) as sum_price'),
                'service_store_buyers.id',
                'service_store_buyers.course_id',
            )
            ->whereDate('service_store_buyers.buy_at', '>=', $start)
            ->whereDate('service_store_buyers.buy_at', '<=', $end)
            ->groupBy(['year', 'month'])
            ->orderBy('service_store_buyers.buy_at')
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
                        $item['value'] = $graph_item['sum_price'];
                        $results[$key] = $item;
                    }
                }
            }

        }

        return $results;
    }

    public function revenueOfServiceByYear($service_id,$condition){
        $current_year = Carbon::now()->year;
        if (isset($condition->last_year_number)) {
            $last_year = $current_year - $condition->last_year_number;
        } else {
            $last_year = $current_year - 1;
        }
        
        $current_result = $this->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
        ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
        ->whereYear('buy_at', $current_year)
        ->sum('service_courses.price');

        $last_result = $this->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
        ->whereRelation('serviceCourses', 'service_courses.service_id', '=', $service_id)
        ->whereYear('buy_at', $last_year)
        ->sum('service_courses.price');

        $result['current_revenue'] = $current_result;
        $result['increase_number'] = $current_result - $last_result;
        return $result;
    }

    public function findBySeller($seller_id)
    {
        return $this->where([
                        ['status', '=' , 1]
                    ])->join('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
                    ->join('services', 'services.id', '=', 'service_courses.service_id')
                    ->where([
                        ['services.seller_id', '=' , $seller_id]
                    ])
                    ->first();
    }
}
