<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservation';

    protected $fillable = [
        'need_reservation',
        'time_reservation'
    ];

    public function findByReservationId($reservation_id)
    {
        return $this->where('id', $reservation_id)->first();
    }
}
