<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyerServiceReserve extends Model
{
    use HasFactory;

    protected $table = 'buyer_service_reserves';

    protected $fillable = [
        'buyer_id',
        'service_id',
        'course_id',
        'reserve_start',
        'reserve_end'
    ];

    // protected $casts = [
    //     'reserve_start' => 'datetime:Y-m-d G:i',
    //     'reserve_end' => 'datetime:Y-m-d G:i',
    // ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id', 'account_id')
            ->join('accounts', 'accounts.id', '=', 'buyers.account_id')
            ->select(
                'buyers.account_id',
                'buyers.account_name',
                'buyers.first_name',
                'buyers.last_name',
                'buyers.gender',
                'accounts.postcode',
                'buyers.profile_image_url_buy',
                'buyers.profile_text_buy',
            );
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id')->select(['id', 'hash_id', 'name', 'caption']);
    }

    public function serviceCourse()
    {
        return $this->belongsTo(ServiceCourse::class, 'course_id', 'course_id')->select(['course_id', 'name']);
    }

    public function createDate($date = null)
    {
        if ($date) return Carbon::createFromFormat('Y-m-d', $date);
        return Carbon::now();
    }

    public function createDateTimestamp($date = null)
    {
        if ($date) return Carbon::createFromFormat('Y-m-d H:i:s', $date);
        return Carbon::now();
    }

    public function toDateTimestamp($date = null)
    {
        if ($date) return Carbon::createFromFormat('Y-m-d G:i', $date)->format('Y-m-d H:i');
        return Carbon::now()->format('Y-m-d H:i');
    }

    public function formatDateToTime($date)
    {
        return Carbon::parse($date)->format('H:i');
    }

    public function createTime($date = null)
    {
        if ($date) return Carbon::createFromFormat('H:i:', $date);
        return Carbon::now()->format('H:i');
    }

    public function getDayJapan($day_of_week)
    {
        $data = [
            '0' => '日',
            '1' => '月',
            '2' => '火',
            '3' => '水',
            '4' => '木',
            '5' => '金',
            '6' => '土',
        ];
        return $data[$day_of_week];
    }

    public function checkWeekOfMonth($condition)
    {
        $week = $condition->week;
        $date_select = $condition->year . '-' . $condition->month . '-01 00:00:00';

        if ($this->getWeekOfMonth($date_select, $week) == false) {
            return false;
        }
        return true;
    }

    public function getWeekOfMonth($date, $week)
    {
        $data_month = [];
        //create date
        $dateSelect = $this->createDateTimestamp($date);

        $startOfMonth = $dateSelect->startOfMonth()->format('Y-m-d');
        //get month
        $monthSelect = $dateSelect->month;

        $nextWeek = true;
        $currentWeek = 1;
        $dayOfWeek = $startOfMonth;

        // create array save week of month
        while ($nextWeek) {

            $data_week = [];
            $checkDate = $this->createDate($dayOfWeek);

            $startOfWeek = $checkDate->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
            $endOfWeek = $checkDate->endOfWeek(Carbon::SATURDAY)->format('Y-m-d');

            if ($date >= $startOfMonth && $date <= $endOfWeek && $week == null) {
                $week = $currentWeek;
            }

            $data_week['start_of_week'] = $startOfWeek;
            $data_week['end_of_week'] = $endOfWeek;
            $data_week['week_current'] = $currentWeek;
            $data_week['week_count'] = $currentWeek;
            $data_month[$currentWeek] = $data_week;

            $currentWeek++;
            $nextFirstDayOfWeek = $this->createDate($endOfWeek)->addDays(1);
            $dayOfWeek =  $nextFirstDayOfWeek->format('Y-m-d');

            if ($this->createDate($endOfWeek)->month != $monthSelect || $nextFirstDayOfWeek->month != $monthSelect) {
                $nextWeek = false;
            }
        }

        if (isset($data_month[$week])) {
            $data_month[$week]['week_count'] = count($data_month);
            return $data_month[$week];
        }
        return false;
    }

    public function checkStatusServiceHour($service_id, $date, $day_of_week, $time_start, $time_end)
    {
        $serviceHours = ServiceHour::where(['service_id' => $service_id, 'day_of_week' => $day_of_week])->first();

        $serviceHoursTemps = ServiceHoursTemp::where(['service_id' => $service_id])->whereDate('date', $date)->first();

        $settingHour = $serviceHours;

        if ($serviceHoursTemps) {
            $settingHour = $serviceHoursTemps;
        }

        if (!$settingHour['status']) return false;

        // set hour and minutes of time_start
        $parts_start1 = explode(':', $time_start);
        $hour_start1 = (int) $parts_start1[0];
        $minutes_start1 = (int) $parts_start1[1];

        // set hour and minutes of time_end
        $parts_end1 = explode(':', $time_end);
        $hour_end1 = (int) $parts_end1[0];
        $minutes_end1 = (int) $parts_end1[1];

        if (isset($settingHour['work_hour'])) {

            $work_hour = json_decode($settingHour['work_hour'], true);

            foreach ($work_hour as $hour) {
                // set hour and minutes of hour start
                $parts_start2 = explode(':', $hour['start']);
                $hour_start2 = (int) $parts_start2[0];
                $minutes_start2 = (int) $parts_start2[1];

                // set hour and minutes of hour end
                $parts_end2 = explode(':', $hour['end']);
                $hour_end2 = (int) $parts_end2[0];
                $minutes_end2 = (int) $parts_end2[1];
                //check hours service operation
                if (
                    $settingHour['status'] == true &&
                    ($hour_start1 > $hour_start2 && $hour_start1 < $hour_end2) ||
                    (($hour_start1 == $hour_start2 && $minutes_start1 >= $minutes_start2) || ($hour_end1 == $hour_end2 && $minutes_end1 <= $minutes_end2))
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    public function serviceReservesManagerBySeller($service_id, $condition)
    {
        $now = Carbon::now();
        $now->year;
        $now->month;

        // set date start and select week
        if (!isset($condition->week) && $now->year == $condition->year && $now->month == $condition->month) {
            $date_select = $now->format('Y-m-d H:i:s');
            $week = null;
        } elseif (!isset($condition->week)) {
            $week = 1;
            $date_select = $condition->year . '-' . $condition->month . '-01 00:00:00';
        } else {
            $week = $condition->week;
            $date_select = $condition->year . '-' . $condition->month . '-01 00:00:00';
        }

        // get data day of select week
        $date_week = $this->getWeekOfMonth($date_select, $week);


        $serviceReservesSetting = ServiceReserveSetting::where('service_id', $service_id)->first()->toArray();

        $dayCurrent = $this->createDate($date_week['start_of_week']);

        $data = [];
        $dayOfWeekCurrent = 0;
        $data['week_info'] = $date_week;
        $data['service_reserves'] = [];

        while ($dayOfWeekCurrent <= 6) {

            $eventOfDay = [];

            $dateJapan = $dayCurrent->month . '月' . $dayCurrent->day . '日' . '（' . $this->getDayJapan($dayOfWeekCurrent) . '）';

            $eventOfDay['date'] = $dayCurrent->format('Y-m-d');
            $eventOfDay['date_format'] = $dateJapan;
            $eventOfDay['event'] = [];

            $dataOfDay = [];

            $part_distances = explode(':', $serviceReservesSetting['time_distance']);
            $hours_distance = (int) $part_distances[0];
            $minutes_distance = (int) $part_distances[1];

            $hours_start = 9;
            $hours_end = $hours_start + $hours_distance;
            $minutes_start = 0;
            $minutes_end = $minutes_distance;
            $minutes_start = 0;

            while ($hours_end < 22 || $hours_end == 22 && $minutes_end == 0) {
                $dataOfHours = [];
                $buyers = [];
                $time_start = $hours_start . ':' . ($minutes_start <= 9 ? ($minutes_start . '0') : $minutes_start);
                $time_end = $hours_end . ':' . ($minutes_end + $minutes_distance);
                $dataOfHours['time_start'] = $hours_start . ':' . ($minutes_start <= 9 ? ($minutes_start . '0') : $minutes_start);
                $dataOfHours['time_end'] = $hours_end . ':' . ($minutes_end <= 9 ? ($minutes_end . '0') : ($minutes_end == 60 ? '00' : $minutes_end));
                $dataOfHours['hours_setting'] = $hours_distance;
                //get status operation of service from time_start to time_end
                $checkStatus = $this->checkStatusServiceHour($service_id, $dayCurrent->format('Y-m-d'), $dayOfWeekCurrent, $time_start, $time_end);

                $listReserves = $this->where([
                    'buyer_service_reserves.service_id' => $service_id,
                    'buyer_service_reserves.course_id' => $condition->course_id,
                    'buyer_service_reserves.reserve_start' => $dayCurrent->format('Y-m-d') . ' ' .$dataOfHours['time_start']
                    ])
                    ->join('service_store_buyers', function($join){
                        $join->on('service_store_buyers.course_id', '=', 'buyer_service_reserves.course_id')
                             ->on('service_store_buyers.buyer_id', '=', 'buyer_service_reserves.buyer_id');
                    })
                    ->where(function($q){
                        $q->where([
                            'service_store_buyers.status' => 1
                        ])->orWhere(function ($q) {
                            $q->where([
                                'service_store_buyers.status' => 2
                            ])->whereDate('service_store_buyers.end', '>', Carbon::now());
                        });
                    })
                    ->with('buyer')
                    ->orderBy('buyer_service_reserves.created_at')
                    ->get()
                    ->toArray();
                foreach ($listReserves as $item) {
                    if (isset($item['buyer']['profile_image_url_buy'])) {
                        $item['buyer']['profile_image_url_buy'] = config('app.app_resource_path') . 'avatar/' . $item['buyer']['profile_image_url_buy'];
                    }
                    array_push($buyers, $item['buyer']);
                }

                $dataOfHours['buyers'] = $buyers;

                if ($dayCurrent->format('Y-m-d') < $this->createDate()->format('Y-m-d')) {
                    $dataOfHours['status'] = 0;
                } else {
                    if (!$checkStatus) {
                        $dataOfHours['status'] = 0;
                    } elseif ($checkStatus && count($buyers) == 0) {
                        $dataOfHours['status'] = 1;
                    } elseif ($checkStatus && count($buyers) > 0) {
                        $dataOfHours['status'] = 2;
                    }
                }

                $minutes_start = $minutes_end;
                $minutes_start_check = $minutes_start + $minutes_distance;
                $minutes_end += $minutes_distance;

                if ($minutes_start_check >= 60) {
                    $minutes_end = $minutes_start_check - 60;
                    $hours_end++;
                    $hours_start = $hours_end - $hours_distance;
                } else {
                    $hours_start = $hours_end;
                }

                if ($minutes_end >= 60) {
                    $minutes_end = $minutes_end - 60;
                }

                $hours_end += $hours_distance;
                array_push($dataOfDay, $dataOfHours);
            }

            $eventOfDay['event'] = $dataOfDay;
            array_push($data['service_reserves'],  $eventOfDay);

            $dayCurrent->addDays(1);
            $dayOfWeekCurrent++;
        }
        return  $data;
    }

    public function serviceReservesManagerByBuyer($service_id, $course_id = null, $buyer_id, $condition)
    {
        $now = Carbon::now();
        $now->year;
        $now->month;

        // set date start and select week
        if (!isset($condition->week) && $now->year == $condition->year && $now->month == $condition->month) {
            $date_select = $now->format('Y-m-d H:i:s');
            $week = null;
        } elseif (!isset($condition->week)) {
            $week = 1;
            $date_select = $condition->year . '-' . $condition->month . '-01 00:00:00';
        } else {
            $week = $condition->week;
            $date_select = $condition->year . '-' . $condition->month . '-01 00:00:00';
        }

        // get data day of select week
        $date_week = $this->getWeekOfMonth($date_select, $week);

        // get setting reserves
        $serviceReservesSetting = ServiceReserveSetting::where('service_id', $service_id)->first()->toArray();

        // get time last reserves
        $last_day_reserves = $this->where([
            'buyer_service_reserves.service_id' => $service_id,
            'buyer_service_reserves.course_id' => $course_id,
            'buyer_service_reserves.buyer_id' => $buyer_id
            ])
            ->join('service_store_buyers', function($join){
                $join->on('service_store_buyers.course_id', '=', 'buyer_service_reserves.course_id')
                     ->on('service_store_buyers.buyer_id', '=', 'buyer_service_reserves.buyer_id');
            })
            ->where(function($q){
                $q->where([
                    'service_store_buyers.status' => 1
                ])->orWhere(function ($q) {
                    $q->where([
                        'service_store_buyers.status' => 2
                    ])->whereDate('service_store_buyers.end', '>', Carbon::now());
                });
            })
            ->max('buyer_service_reserves.created_at');

        $dayCurrent = $this->createDate($date_week['start_of_week']);

        $data = [];
        $dayOfWeekCurrent = 0;
        $data['last_day_reserves'] = $last_day_reserves ? $this->createDateTimestamp($last_day_reserves)->format('Y-m-d') : null;
        $data['week_info'] = $date_week;
        $data['service_reserves'] = [];

        $service = Service::where('id', $service_id)->first();

        $reserveSetting =  $service->serviceReserveSetting;
        $data['service_reserve_setting'] = [
            'is_enable' => $reserveSetting->is_enable,
            'duration_after' => $reserveSetting->duration_after,
            'duration_before' => $reserveSetting->duration_before,
            'max' => $reserveSetting->max,
            'time_distance' => $reserveSetting->time_distance,
            'type_duration_after' => $reserveSetting->type_duration_after,
        ];

        $serviceHours = $service->serviceHours->toArray();
        $el_first = array_shift($serviceHours);
        array_push($serviceHours, $el_first);
        $data['service_hours'] = $serviceHours;

        $data['is_reserves'] = $service->is_reserves;

        $data['hours_temp'] = $service->ServiceHoursTemps->toArray();
        
        // $reserves_max = $serviceReservesSetting['max'];

        while ($dayOfWeekCurrent <= 6) {

            $eventOfDay = [];

            $dateJapan = $dayCurrent->month . '月' . $dayCurrent->day . '日' . '（' . $this->getDayJapan($dayOfWeekCurrent) . '）';

            $eventOfDay['date'] = $dayCurrent->format('Y-m-d');
            $eventOfDay['date_format'] = $dateJapan;
            $eventOfDay['event'] = [];

            $dataOfDay = [];

            $part_distances = explode(':', $serviceReservesSetting['time_distance']);
            $hours_distance = (int) $part_distances[0];
            $minutes_distance =  (int) $part_distances[1];

            $hours_start = 9;
            $hours_end = $hours_start + $hours_distance;
            $minutes_start = 0;
            $minutes_end = $minutes_distance;
            $minutes_start = 0;

            while ($hours_end < 22 || $hours_end == 22 && $minutes_end == 0) {

                $dataOfHours = [];
                $buyers = [];

                $dataOfHours['time_start'] = $hours_start . ':' . ($minutes_start <= 9 ? ($minutes_start . '0') : $minutes_start);
                $dataOfHours['time_end'] = $hours_end . ':' . ($minutes_end <= 9 ? ($minutes_end . '0') : ($minutes_end == 60 ? '00' : $minutes_end));
                $dataOfHours['hours_setting'] = $hours_distance;

                //get status operation of service from time_start to time_end
                $checkStatus = $this->checkStatusServiceHour($service_id, $dayCurrent->format('Y-m-d'), $dayOfWeekCurrent,  $dataOfHours['time_start'], $dataOfHours['time_end']);

                $listReserves = $this->where([
                    'buyer_service_reserves.service_id' => $service_id,
                    'buyer_service_reserves.course_id' => $course_id,
                    'buyer_service_reserves.buyer_id' => $buyer_id,
                    'buyer_service_reserves.reserve_start' => $dayCurrent->format('Y-m-d') . ' ' .$dataOfHours['time_start']
                    ])
                    ->join('service_store_buyers', function($join){
                        $join->on('service_store_buyers.course_id', '=', 'buyer_service_reserves.course_id')
                             ->on('service_store_buyers.buyer_id', '=', 'buyer_service_reserves.buyer_id');
                    })
                    ->where(function($q){
                        $q->where([
                            'service_store_buyers.status' => 1
                        ])->orWhere(function ($q) {
                            $q->where([
                                'service_store_buyers.status' => 2
                            ])->whereDate('service_store_buyers.end', '>', Carbon::now());
                        });
                    })
                    ->with('buyer')
                    ->orderBy('buyer_service_reserves.created_at')
                    ->get()
                    ->toArray();
                foreach ($listReserves as $item) {
                    if (isset($item['buyer']['profile_image_url_buy'])) {
                        $item['buyer']['profile_image_url_buy'] = config('app.app_resource_path') . 'avatar/' . $item['buyer']['profile_image_url_buy'];
                    }
                    array_push($buyers, $item['buyer']);
                }

                $dataOfHours['buyers'] = $buyers;

                if ($dayCurrent->format('Y-m-d') < $this->createDate()->format('Y-m-d')) {
                    $dataOfHours['status'] = 0;
                } else {
                    if (!$checkStatus) {
                        $dataOfHours['status'] = 0;
                    } elseif ($checkStatus && count($buyers) == 0) {
                        $dataOfHours['status'] = 1;
                    } elseif ($checkStatus && count($buyers) > 0) {
                        $dataOfHours['status'] = 2;
                    }
                }

                $minutes_start = $minutes_end;
                $minutes_start_check = $minutes_start + $minutes_distance;
                $minutes_end += $minutes_distance;

                if ($minutes_start_check >= 60) {
                    $minutes_end = $minutes_start_check - 60;
                    $hours_end++;
                    $hours_start = $hours_end - $hours_distance;
                } else {
                    $hours_start = $hours_end;
                }

                if ($minutes_end >= 60) {
                    $minutes_end = $minutes_end - 60;
                }

                $hours_end += $hours_distance;
                array_push($dataOfDay, $dataOfHours);
            }

            $eventOfDay['event'] = $dataOfDay;
            array_push($data['service_reserves'],  $eventOfDay);

            $dayCurrent->addDays(1);
            $dayOfWeekCurrent++;
        }
        return  $data;
    }

    public function statisticReservationByBuyer($buyer_id)
    {
        $date_now = $this->createDate();
        
        $results['reservation_current'] = [];

        $reservation_to_day = $this->where('buyer_service_reserves.buyer_id', $buyer_id)
                                    ->join('service_store_buyers', function($join){
                                        $join->on('service_store_buyers.course_id', '=', 'buyer_service_reserves.course_id')
                                            ->on('service_store_buyers.buyer_id', '=', 'buyer_service_reserves.buyer_id');
                                    })
                                    ->with(['service'])
                                    ->where(function($q){
                                        $q->where([
                                            'service_store_buyers.status' => 1
                                        ])->orWhere(function ($q) {
                                            $q->where([
                                                'service_store_buyers.status' => 2
                                            ])->whereDate('service_store_buyers.end', '>', Carbon::now());
                                        });
                                    })
                                    ->whereDate('buyer_service_reserves.reserve_start', $date_now)
                                    ->orderBy('buyer_service_reserves.reserve_start')
                                    ->limit(6)
                                    ->get();

        $reservation_after_day = $this->where('buyer_service_reserves.buyer_id', $buyer_id)
                                    ->join('service_store_buyers', function($join){
                                        $join->on('service_store_buyers.course_id', '=', 'buyer_service_reserves.course_id')
                                        ->on('service_store_buyers.buyer_id', '=', 'buyer_service_reserves.buyer_id');
                                    })
                                    ->with('service')
                                    ->where(function($q){
                                        $q->where([
                                            'service_store_buyers.status' => 1
                                        ])->orWhere(function ($q) {
                                            $q->where([
                                                'service_store_buyers.status' => 2
                                            ])->whereDate('service_store_buyers.end', '>', Carbon::now());
                                        });
                                    })
                                    ->whereDate('buyer_service_reserves.reserve_start', '>', $date_now)
                                    ->count();

        $results['reservation_after_current'] = $reservation_after_day;

        foreach ($reservation_to_day as $reservation) {
            $current_reservation['time'] = $this->formatDateToTime($reservation->reserve_start);
            $current_reservation['service'] = $reservation->service;
            array_push($results['reservation_current'], $current_reservation);
        }

        return  $results;
    }

    public function countReserveByService($service_id)
    {
        $current_date = Carbon::now()->toDateString();
        $next_date = Carbon::now()->addDay(1)->toDateString();
        $result = [];
        $result_today = $this->where('buyer_service_reserves.service_id', $service_id)
                            ->join('service_store_buyers', function($join){
                                $join->on('service_store_buyers.course_id', '=', 'buyer_service_reserves.course_id')
                                     ->on('service_store_buyers.buyer_id', '=', 'buyer_service_reserves.buyer_id');
                            })
                            ->where(function($q){
                                $q->where([
                                    'service_store_buyers.status' => 1
                                ])->orWhere(function ($q) {
                                    $q->where([
                                        'service_store_buyers.status' => 2
                                    ])->whereDate('service_store_buyers.end', '>', Carbon::now());
                                });
                            })
                            ->whereDate('buyer_service_reserves.reserve_start', $current_date)
                            ->count('buyer_service_reserves.buyer_id');
                                
        $result_next_date = $this->where('buyer_service_reserves.service_id', $service_id)
                                ->join('service_store_buyers', function($join){
                                    $join->on('service_store_buyers.course_id', '=', 'buyer_service_reserves.course_id')
                                         ->on('service_store_buyers.buyer_id', '=', 'buyer_service_reserves.buyer_id');
                                })
                                ->where(function($q){
                                    $q->where([
                                        'service_store_buyers.status' => 1
                                    ])->orWhere(function ($q) {
                                        $q->where([
                                            'service_store_buyers.status' => 2
                                        ])->whereDate('service_store_buyers.end', '>', Carbon::now());
                                    });
                                })
                                ->whereDate('buyer_service_reserves.reserve_start', $next_date)
                                ->count('buyer_service_reserves.buyer_id');

        $last_reserves_today = $this->getLastUserReservesCurrentDay($service_id);
        $last_reserve_user_today = [];

        if (count($last_reserves_today) != 0) {
            foreach ($last_reserves_today as $value) {
                $profile_image_url_buy = null;
                if ($value->buyer->profile_image_url_buy != null) {
                    $profile_image_url_buy = config('app.app_resource_path') . 'avatar/' . $value->buyer->profile_image_url_buy;
                }
                array_push($last_reserve_user_today, [
                    'buyer_id' => $value->buyer->account_id,
                    'profile_image_url_buy' => $profile_image_url_buy
                ]);
            }
        }

        $last_reserves_next_day = $this->getLastUserReservesNextDay($service_id);
        $last_reserve_user_next_day = [];

        if (count($last_reserves_next_day) != 0) {
            foreach ($last_reserves_next_day as $value) {
                $profile_image_url_buy = null;
                if ($value->buyer->profile_image_url_buy != null) {
                    $profile_image_url_buy = config('app.app_resource_path') . 'avatar/' .  $value->buyer->profile_image_url_buy;
                }
                array_push($last_reserve_user_next_day, [
                    'buyer_id' => $value->buyer->account_id,
                    'profile_image_url_buy' => $profile_image_url_buy
                ]);
            }
        }
        $today_reserves = [];
        $today_reserves['number_reserves'] = $result_today;
        $today_reserves['last_user_reserves'] = $last_reserve_user_today;

        $next_date_reserves = [];
        $next_date_reserves['number_reserves'] = $result_next_date;
        $next_date_reserves['last_user_reserves'] = $last_reserve_user_next_day;

        array_push($result, [
            'today_reserves' => $today_reserves,
            'next_date_reserves' => $next_date_reserves
        ]);
        return $result;
    }

    public function getLastUserReservesCurrentDay($service_id)
    {
        $current_date = Carbon::now()->toDateString();
        $result = $this->where('buyer_service_reserves.service_id', $service_id)
            ->join('service_store_buyers', function($join){
                $join->on('service_store_buyers.course_id', '=', 'buyer_service_reserves.course_id')
                    ->on('service_store_buyers.buyer_id', '=', 'buyer_service_reserves.buyer_id');
            })
            ->where(function($q){
                $q->where([
                    'service_store_buyers.status' => 1
                ])->orWhere(function ($q) {
                    $q->where([
                        'service_store_buyers.status' => 2
                    ])->whereDate('service_store_buyers.end', '>', Carbon::now());
                });
            })
            ->whereDate('buyer_service_reserves.reserve_start', $current_date)
            ->with('buyer')
            ->orderBy('buyer_service_reserves.reserve_start', 'DESC')
            ->limit(3)->get();
        return $result;
    }

    public function getLastUserReservesNextDay($service_id)
    {
        $next_date = Carbon::now()->addDay(1)->toDateString();
        $result = $this->where('buyer_service_reserves.service_id', $service_id)
            ->join('service_store_buyers', function($join){
                $join->on('service_store_buyers.course_id', '=', 'buyer_service_reserves.course_id')
                    ->on('service_store_buyers.buyer_id', '=', 'buyer_service_reserves.buyer_id');
            })
            ->where(function($q){
                $q->where([
                    'service_store_buyers.status' => 1
                ])->orWhere(function ($q) {
                    $q->where([
                        'service_store_buyers.status' => 2
                    ])->whereDate('service_store_buyers.end', '>', Carbon::now());
                });
            })
            ->whereDate('buyer_service_reserves.reserve_start', $next_date)
            ->with('buyer')
            ->orderBy('buyer_service_reserves.reserve_start', 'DESC')
            ->limit(3)->get();
        return $result;
    }

    public function getCourseByServiceId($service_id)
    {
        $results = $this->where(['service_id' => $service_id])
            ->with(['serviceCourse'])
            ->groupBy('course_id')
            ->get();
        $data = [];
        foreach ($results as $item) {
            if ($item->serviceCourse) {
                array_push($data, $item->serviceCourse);
            }
        }
        return $data;
    }

    public function findByBuyerAndReservesStart($condition)
    {
        return $this->where([
            'buyer_id' => $condition->buyer_id,
            'course_id' => $condition->course_id,
            'reserve_start' => $this->toDateTimestamp($condition->reserve_start)
        ])->first();
    }

    public function deleteByBuyerAndReservesStart($condition)
    {
        return $this->where([
            'buyer_id' => $condition->buyer_id,
            'course_id' => $condition->course_id,
            'reserve_start' => $this->toDateTimestamp($condition->reserve_start)
        ])->delete();
    }

    public function findByReservesStartAndCourseId($condition)
    {
        $currentTime = Carbon::now();
        return $this
            ->where('reserve_start', '>=', $currentTime)
            ->where('course_id', $condition->course_id)
            ->get();
    }

    public function getAllByBuyerAndCourseId($buyer_id, $course_id, $per_page = null)
    {
        $date_now = $this->createDate();
        $hour_now = $this->createTime();

        $query = $this->where([
                                'buyer_id' => $buyer_id,
                                'course_id' => $course_id
                            ])
                            ->where(function ($query) use ($date_now, $hour_now) {
                                $query->whereDate('reserve_start', '=', $date_now)
                                    ->WhereTime('reserve_start', '>', $hour_now)
                                    ->orWhereDate('reserve_start', '>', $date_now);
                            })
                            ->orderBy('reserve_start');
        if (!isset($per_page)) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }

        foreach ($results->items() as $item) {
            $date_start = $this->createDateTimestamp($item->reserve_start);
            $date_end = $this->createDateTimestamp($item->reserve_end);
            $day_format = $this->getDayJapan($date_start->dayOfWeek) ;
            $month = $date_start->month;
            $day = $date_start->day;
            $hours_start = $date_start->format('G:i');
            $hours_end = $date_end->format('G:i');
            $dateJapan = $month . '月' . $day . '日' . '（' . $day_format . '）' . $hours_start. '-' .$hours_end;
            $item->date_format = $dateJapan;
        }

        return $results;
    }

    public function deleteAllByBuyerAndCourseId($buyer_id, $course_id)
    {
        $querys = $this->where([
            'buyer_id' => $buyer_id,
            'course_id' => $course_id
        ])->get();
        
        if ($querys) {
            foreach ($querys as $query) {
                $this->where([
                    'buyer_id' => $query->buyer_id,
                    'course_id' => $$query->course_id
                ])->delete();
            }
        }
        
        return true;
    }
}
