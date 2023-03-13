<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceReviewImage extends Model
{
    use HasFactory;

    protected $table = 'service_reviews_images';

    protected $fillable = [
        'reviews_id',
        'image_url',
        'buyer_id',
        'service_id'
    ];

    public function findByReviewsId($reviews_id)
    {
        return $this->where('reviews_id', $reviews_id)->get();
    }
}
