<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Favorite extends Model
{
    use HasFactory;

    protected $table = 'favorites';

    protected $fillable = [
        'buyer_id',
        'service_id',
        'favorite_tag_id'
    ];

    public function getByFavoriteTagId($favorite_tag_id)
    {
        return $this->where('favorite_tag_id', $favorite_tag_id)->first();
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
        $result['current_favorites'] = $current;
        $result['increase_number'] = $current - $last;
        return $result;
    }

    public function countBuyerByServiceId($service_id)
    {
        $result = array();
        $now = Carbon::now();
        $curent = $this->select('buyer_id')->where('service_id', $service_id)
            ->whereYear('created_at', '=', $now->year)
            ->whereMonth('created_at', '=', $now->month)
            ->groupBy('buyer_id')->get();
        $last = $this->select('buyer_id')->where('service_id', $service_id)
            ->whereYear('created_at', '=', $now->year)
            ->whereMonth('created_at', '=', $now->month - 1)
            ->groupBy('buyer_id')->get();
        $result['curent_count'] = count($curent);
        $result['last_count'] =  count($curent) -  count($last);
        return $result;
    }

    public function favoriteGraph($service_id)
    {
        $favorites = $this->where('service_id', $service_id)
            ->select('id', 'created_at')
            ->get()->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $count = [];
        $favoriteArr = [];

        foreach ($favorites as $key => $value) {
            $count[(int)$key] = count($value);
        }

        for ($i = 1; $i <= 12; $i++) {
            if (!empty($count[$i])) {
                $favoriteArr[$i] = $count[$i];
            } else {
                $favoriteArr[$i] = 0;
            }
        }

        return $favoriteArr;
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
                DB::raw('COUNT(buyer_id) as count_favorite')
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
                        $item['value'] = $graph_item['count_favorite'];
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
                DB::raw('COUNT(buyer_id) as count_favorite')
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
                        $item['value'] = $graph_item['count_favorite'];
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

        $result['current_favorites'] = $current;
        $result['increase_number'] = $current - $last;
        return $result;
    }
}
