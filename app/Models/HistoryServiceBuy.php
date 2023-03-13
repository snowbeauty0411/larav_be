<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryServiceBuy extends Model
{
    use HasFactory;

    protected $table = 'history_service_buys';

    protected $fillable = [
        'history_service_buy_id',
        'buyer_id',
        'service_id',
        'service_date_buy',
        'service_date_cancel'
    ];
}
