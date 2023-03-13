<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTag extends Model
{
    use HasFactory;

    protected $table = 'service_tags';

    protected $fillable = [
        'service_id',
        'tag_id',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function deleteByServiceIdAndTagId($service_id, $tag_id)
    {
        $this->where(['service_id' =>$service_id, 'tag_id' => $tag_id])->delete();
    }

    public function findByServiceIdAndTagId($service_id, $tag_id)
    {
        return $this->where(['service_id' => $service_id, 'tag_id' => $tag_id])->first();
    }

    public function deleteByServiceId($service_id)
    {
        $this->where('service_id' , $service_id)->delete();
    }
}
