<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceArea extends Model
{
    use HasFactory;

    protected $table = 'service_areas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'area_id',
        'pref_id',
        'service_id',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }

    public function getIdService($condition)
    {
        return $this->whereRelation('area', 'areas.name', '=', $condition->name)->get()
            ->pluck('service_id');
    }

    public function removeByServiceId($service_id)
    {
        return $this->where('service_id', $service_id)->delete();
    }
}
