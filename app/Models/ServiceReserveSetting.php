<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceReserveSetting extends Model
{
    use HasFactory;

    protected $table = 'service_reserves_setting';

    protected $fillable = [
        'service_id',
        'is_enable',
        'max',
        'time_distance',
        'duration_before',
        'duration_after',
        'type_duration_after',
    ];

    public function updateServiceReservesSetting($service_id, $serviceReservesSetting)
    {
        $this->updateOrCreate(['service_id' => $service_id], $serviceReservesSetting);
    }
}
