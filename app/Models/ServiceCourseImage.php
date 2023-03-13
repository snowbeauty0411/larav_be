<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCourseImage extends Model
{
    use HasFactory;

    protected $table = 'service_course_images';

    protected $fillable = [
        'course_id',
        'image_url',
        'position'
    ];

    public function findByCourseId($course_id)
    {
        return $this->where('course_id', $course_id)->first();
    }
}
