<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDeliveryBuyer extends Model
{
    use HasFactory;

    protected $table = 'service_delivery_buyers';

    protected $fillable = [
        'course_id',
        'buyer_id',
        'start',
        'end',
        'address'
    ];
}
