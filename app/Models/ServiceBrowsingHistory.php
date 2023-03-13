<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBrowsingHistory extends Model
{
    use HasFactory;

    protected $table = 'service_browsing_histories';

    protected $fillable = [
        'ip_address',
        'service_id',
        'created_at',
        'updated_at',
    ];
}
