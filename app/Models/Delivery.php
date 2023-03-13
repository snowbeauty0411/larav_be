<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Delivery extends Model
{
    use HasFactory;
    protected $table = 'deliveries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_store_buyer_id',
        'payment_id',
        'estimated_date',
        'actual_date',
        'delivery_status',
        'delivery_address',
        'buyer_full_name'
    ];

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function serviceStoreBuyer()
    {
        return $this->belongsTo(ServiceStoreBuyer::class, 'service_store_buyer_id', 'id');
    }

    public function serviceCourse()
    {
        return $this->belongsTo(ServiceStoreBuyer::class, 'service_store_buyer_id', 'id')
            ->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->select(
                'service_store_buyers.id',
                'service_store_buyers.course_id',
                'service_courses.course_id',
                'service_courses.service_id',
                'service_courses.price',
                'service_courses.name'
            );
    }

    public function service()
    {
        return $this->belongsTo(ServiceStoreBuyer::class, 'service_store_buyer_id', 'id')
            ->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->leftJoin('services', 'services.id', '=', 'service_courses.service_id')
            ->select(
                'service_store_buyers.id',
                'service_store_buyers.course_id',
                'service_courses.course_id',
                'service_courses.service_id',
                'service_courses.price',
                'service_courses.name',
                'services.id',
                'services.name'
            );
    }

    public function getDeliveryByService($service_id, $condition)
    {
        $query = $this->leftJoin('service_store_buyers', 'service_store_buyers.id', '=', 'deliveries.service_store_buyer_id')
            ->leftJoin('buyers as buyer', 'buyer.account_id', '=', 'service_store_buyers.buyer_id')
            ->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->leftJoin('services', 'services.id', '=', 'service_courses.service_id')
            ->leftJoin('payments', 'payments.id', '=', 'deliveries.payment_id')
            ->select(
                'deliveries.*',
                'payments.id as payment_id',
                'payments.payment_status',
                'buyer.profile_image_url_buy',
                'service_courses.name as course_name',
                'service_courses.course_id',
                'buyer.account_name',
                'buyer.account_id as buyer_id'
            )->where('services.id', '=', $service_id);

        if (isset($condition->delivery_status)) {
            $query->where('deliveries.delivery_status', $condition->delivery_status);
        }

        if (isset($condition->payment_status)) {
            $query->where('payments.payment_status', $condition->payment_status);
        }

        if (isset($condition->course_id)) {
            $query->where('service_courses.course_id', $condition->course_id);
        }
        if (!isset($condition->per_page)) {
            $result = $query->orderBy('created_at', 'DESC')->paginate(50);
        } else {
            $result = $query->orderBy('created_at', 'DESC')->paginate($condition->per_page);
        }

        return $result;
    }

    public function getDeliveryByService2($service_id)
    {
        $result =  $this->leftJoin('service_store_buyers', 'service_store_buyers.id', '=', 'deliveries.service_store_buyer_id')
            ->leftJoin('buyers as buyer', 'buyer.account_id', '=', 'service_store_buyers.buyer_id')
            ->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->leftJoin('services', 'services.id', '=', 'service_courses.service_id')
            ->leftJoin('payments', 'payments.id', '=', 'deliveries.payment_id')
            ->select(
                'deliveries.*',
                'payments.stripe_charge_id',
                'payments.payment_status',
                'buyer.profile_image_url_buy',
                'service_courses.name as course_name',
                'service_courses.course_id',
            )->where('services.id', '=', $service_id)
            ->orderBy('created_at', 'DESC')->get();
        return $result;
    }

    public function getAllByUserIdAndServiceId($user_id, $service_id, $condition)
    {
        $query = $this->leftJoin('service_store_buyers', 'service_store_buyers.id', '=', 'deliveries.service_store_buyer_id')
            ->leftJoin('buyers as buyer', 'buyer.account_id', '=', 'service_store_buyers.buyer_id')
            ->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->leftJoin('services', 'services.id', '=', 'service_courses.service_id')
            ->leftJoin('payments', 'payments.id', '=', 'deliveries.payment_id')
            ->select(
                'deliveries.*',
                'payments.stripe_charge_id',
                'payments.id as payment_id',
                'payments.payment_status',
                'payments.pay_expire_at_date as payment_date',
                'service_courses.name as course_name',
                'service_courses.course_id',
                'buyer.account_id as buyer_id',
                'buyer.account_name'
            )
            ->where('services.id', '=', $service_id)
            ->where('buyer.account_id', $user_id);
        if (isset($condition->delivery_status)) {
            $query->where('deliveries.delivery_status', $condition->delivery_status);
        }

        if (isset($condition->payment_status)) {
            $query->where('payments.payment_status', $condition->payment_status);
        }

        // if (isset($condition->course_id)) {
        //     $query->where('deliveries.course_id', $condition->course_id);
        // }

        if (!isset($condition->per_page)) {
            $result = $query->orderBy('created_at', 'DESC')->paginate(10);
        } else {
            $result = $query->orderBy('created_at', 'DESC')->paginate($condition->per_page);
        }
        return $result;
    }

    public function getDeliveryByUserIdAndServiceId($user_id, $service_id)
    {
        $result = $this->leftJoin('service_store_buyers', 'service_store_buyers.id', '=', 'deliveries.service_store_buyer_id')
            ->leftJoin('buyers as buyer', 'buyer.account_id', '=', 'service_store_buyers.buyer_id')
            ->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->leftJoin('services', 'services.id', '=', 'service_courses.service_id')
            ->leftJoin('payments', 'payments.id', '=', 'deliveries.payment_id')
            ->select(
                'deliveries.*',
                'payments.stripe_charge_id',
                'payments.pay_expire_at_date as payment_date',
                'service_courses.name as course_name',
                'service_courses.course_id',
                'buyer.account_id as buyer_id',
                'buyer.account_name'
                // DB::raw('CONCAT(shipping_info.post_code," ",shipping_info.address) as delivery_address'),
            )
            ->where('services.id', '=', $service_id)
            ->where('buyer.account_id', $user_id)
            ->orderBy('created_at', 'DESC')->get();
        return $result;
    }

    public function countEstimatedDeliveryByService($service_id)
    {
        $result = $this->where('delivery_status', 1)
            ->join('service_store_buyers', 'service_store_buyers.id', '=', 'deliveries.service_store_buyer_id')
            ->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->where('service_courses.service_id', '=', $service_id)
            ->count('deliveries.id');
        return $result;
    }

    public function getLastByStoreId($service_store_buyer_id)
    {
        return $this->where('service_store_buyer_id', $service_store_buyer_id)->orderBy('created_at', 'DESC')->first();
    }
}
