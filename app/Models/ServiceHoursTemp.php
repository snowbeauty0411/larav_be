<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceHoursTemp extends Model
{
    use HasFactory;

    protected $table = 'service_hours_temps';

    protected $fillable = [
        'service_id',
        'date',
        'work_hour',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function updateByServiceID($service_id, $date, $serviceHour)
    {
        $this->where([
            'service_id' => $service_id,
            'date' => $date
            ])->update($serviceHour);
    }

    public function findByServiceIdAndDate($service_id, $date)
    {
        return  $this->where([
            'service_id' => $service_id,
            'date' => $date
            ])->first();
    }
}
