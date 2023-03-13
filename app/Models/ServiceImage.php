<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ServiceImage extends Model
{
    use HasFactory;

    protected $table = 'service_images';

    protected $fillable = [
        'service_id',
        'image_url',
        'posotion'
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function deleteServiceImage($id)
    {
        $this->where('id', $id)->delete();
    }

    public function deleteServiceImageByServiceId($service_id)
    {
        $results = $this->where('service_id', $service_id)->get();
        foreach ($results as $item) {
            if (!empty($item['image_url'])) {
                $path = $item['image_url'];
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
            $item->delete();
        }
        $dir = 'images/services/' . $service_id;
        if (Storage::disk('public')->exists($dir)) Storage::disk('public')->deleteDirectory($dir);
    }

    public function findByServiceId($id)
    {
        return $this->where('service_id', $id)->get();
    }

    public function updateServiceImage($id, $service_images)
    {
        $this->where('id', $id)->update($service_images);
    }

    public function countImageNullByServiceId($id)
    {
        return $this->where('service_id', $id)
                    ->whereNull('image_url')
                    ->count();
    }
}
