<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceLink extends Model
{
    use HasFactory;

    protected $table = 'service_links';

    protected $fillable = [
        'url',
        'jump_count'
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function updateServiceLink($id, $service_link)
    {
        $this->where('id', $id)->update($service_link);
    }

}
