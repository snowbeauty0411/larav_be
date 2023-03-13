<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NumberAccessServiceDetailPage extends Model
{
    use HasFactory;

    protected $table = 'number_access_service_detail_pages';

    protected $fillable = [
        'service_id',
        'count_by_month'
    ];

    public function findLastByService($service_id)
    {
        $result = $this->where('service_id', $service_id)->orderBy('created_at', 'DESC')->first();
        return $result;
    }

    public function statisticalByServiceMonth($service_id)
    {
        $current_month_name = Carbon::now()->format('M');
        $curr_month = date("m", strtotime($current_month_name));
        $result = [];
        $result_current_month = $this
            ->where('service_id', $service_id)
            ->whereMonth('created_at', $curr_month)
            ->whereYear('created_at', '=', Carbon::now()->year)
            ->select('count_by_month')->first();
        $result_last_month = $this
            ->where('service_id', $service_id)
            ->whereMonth('created_at', $curr_month - 1)
            ->whereYear('created_at', '=', Carbon::now()->year)
            ->select('count_by_month')->first();

        $current_access_detail_pages = 0;
        $last_month_access_detail_pages = 0;
        if ($result_current_month) {
            $current_access_detail_pages = $result_current_month->count_by_month;
        }

        if ($result_last_month) {
            $last_month_access_detail_pages = $result_last_month->count_by_month;
        }
        $increase_number = $current_access_detail_pages - $last_month_access_detail_pages;
        $result['current_access_detail_pages'] = $current_access_detail_pages;
        $result['increase_number'] = $increase_number;
        return $result;
    }

    public function graphByService($service_id, $condition)
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
                DB::raw('SUM(count_by_month) as count_views'),
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
                        $item['value'] = $graph_item['count_views'];
                        $results[$key] = $item;
                    }
                }
            }

        } else {
            
            $start = Carbon::now()->subMonths($month)->toDateString();
            $end = Carbon::now()->toDateString();
            
            $graph =  $this->where('service_id', $service_id)->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(count_by_month) as count_views'),
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
                        $item['value'] = $graph_item['count_views'];
                        $results[$key] = $item;
                    }
                }
            }

        }

        return $results;
    }

    public function statisticalByServiceYear($service_id, $condition)
    {
        $current_year = Carbon::now()->year;
        if (isset($condition->last_year_number)) {
            $last_year = $current_year - $condition->last_year_number;
        } else {
            $last_year = $current_year - 1;
        }

        $result = [];

        $result_current_year = $this
            ->where('service_id', $service_id)
            ->whereYear('created_at', '=', $current_year)->sum('count_by_month');


        $result_last_year = $this
            ->where('service_id', $service_id)
            ->whereYear('created_at', '=', $last_year)->sum('count_by_month');


        $increase_number = $result_current_year - $result_last_year;
        $result['current_access_detail_pages'] = $result_current_year;
        $result['increase_number'] = $increase_number;

        return $result;
    }

    public function countNumberAccess($number_access, $service_id){
        if (!$number_access) {
            $this->create([
                'service_id' => $service_id,
                'count_by_month' => 1
            ]);
        } else {
            $created_date = Carbon::parse($number_access->created_at);
            if ($created_date->isCurrentDay()) {
                $number_access->update([
                    'count_by_month' => $number_access->count_by_month + 1
                ]);
            } else {
                $this->create([
                    'service_id' => $service_id,
                    'count_by_month' => 1
                ]);
            }
        }
    }
}
