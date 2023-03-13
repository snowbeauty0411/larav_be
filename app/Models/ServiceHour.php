<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceHour extends Model
{
    use HasFactory;

    protected $table = 'service_hours';

    protected $fillable = [
        'service_id',
        'day_of_week',
        'work_hour',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function updateByServiceID($service_id, $day_of_week, $serviceHour)
    {
        $this->updateOrCreate([
            'day_of_week' => $day_of_week,
            'service_id'=> $service_id
            ] ,$serviceHour);
    }
}
