<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDelivery extends Model
{
    use HasFactory;

    protected $table = 'service_deliveries';

    protected $fillable = [
        'service_id',
        'interval',
        'month_delivery',
        'with_skip',
        'skip'
    ];

    public function updateByServiceID($service_id, $service_delivery)
    {
        $this->where('service_id', $service_id)->update($service_delivery);
    }

    public function createOrUpdateByServiceID($service_id, $data)
    {
        $service_delivery = $this->where('service_id', $service_id)->first();

        if ($service_delivery) {
            $service_delivery->update($data);
        } else {
            $data['service_id'] = $service_id;
            $this->create($data);
        }
    }
}
