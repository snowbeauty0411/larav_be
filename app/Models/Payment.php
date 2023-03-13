<?php

namespace App\Models;

use App\Constants\ServiceConst;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        // 'course_id',
        // 'buyer_id',
        'id',
        'service_store_buyer_id',
        'delivery_id',
        'sub_total',
        'service_fee',
        'total',
        'pay_expire_at_date',
        'stripe_charge_id',
        'card_id',
        'recipient_name',
        'recipient_address',
        'recipient_phone',
        'post_code',
        'payment_status',
        'created_at',
        'updated_at',
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function serviceStoreBuyer()
    {
        return $this->belongsTo(ServiceStoreBuyer::class, 'service_store_buyer_id', 'id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id', 'account_id');
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
    
    public function updatePayment($id, $payment)
    {
        $this->where('id', $id)->update($payment);
    }

    public function generatePaymentId()
    {
        $random = strval(mt_rand(100000000, 999999999));
        while (!is_null($this->find($random))) {
            $random = strval(mt_rand(100000000, 999999999));
        }
        return $random;
    }

    public function showInvoiceServiceByBuyer($id)
    {
        $result =  $this->where('id', $id)
            ->first();
        $date_now = Carbon::now()->toArray();
        $result->invoice_create_at = $date_now['year'] . '年' . $date_now['month'] . '月' . $date_now['day'] . '日';
        return $result;
    }

    public function checkPayment($buyer_id, $course_id)
    {
        return $this->where('buyer_id', $buyer_id)->where('course_id', $course_id)->first();
    }


    public function getAllPaymentByBuyer($service_store_buyer_id, $request)
    {
        $query = $this->select(
            'id',
            'service_store_buyer_id',
            'total',
            'stripe_charge_id',
            'pay_expire_at_date as pay_at_date',
        )->where('service_store_buyer_id', $service_store_buyer_id);

        if (!$request->per_page) {
            $result = $query->orderBy('created', 'DESC')->paginate(10);
        } else {
            $result = $query->orderBy('created', 'DESC')->paginate($request->per_page);
        }

        return  $result;
    }


    public function findById($id)
    {
        $query = $this->leftJoin('service_store_buyers', 'service_store_buyers.id', '=', 'payments.service_store_buyer_id')
                      ->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
                      ->select(
                           'payments.*',
                           'service_courses.name as course_name',
                           'service_courses.course_id'
                              )
                     ->where('payments.id', $id);
        $result = $query->first();
        return $result;
    }

    public function getPaymentLastByStoreBuyer($service_store_buyer_id)
    {
        return $this->where('service_store_buyer_id', $service_store_buyer_id)->orderBy('created_at', 'DESC')->first();
    }

    public function getAmountCurrentBySeller($seller_id)
    {
        $amount_transfer = $this->join('service_store_buyers', 'payments.service_store_buyer_id', '=', 'service_store_buyers.id')
            ->join('service_courses', 'service_store_buyers.course_id', '=', 'service_courses.course_id')
            ->join('services', 'service_courses.service_id', '=', 'services.id')
            ->where([
                'services.seller_id' => $seller_id,
                'payments.payment_status' => 1,
            ])
            ->where('payments.sub_total', '<>', 0)
            ->get([
                'service_store_buyers.course_id',
                'payments.service_store_buyer_id',
                'payments.total',
                'payments.sub_total',
                DB::raw('(payments.sub_total - payments.service_fee * 0.5) as total_revenue'),
                'service_courses.course_id',
                'service_courses.service_id',
                'services.seller_id',
                'services.id as service_id',
            ])->sum('total_revenue');
        
        $amount_transfer_history = TransferHistory::where('seller_id', $seller_id)->sum('transfer_amount');

        return $amount_transfer - $amount_transfer_history;
    }

    public function findByExpireDateAndStoreID($service_store_buyer_id, $date)
    {
        return $this->whereDate('pay_expire_at_date', $date)
                    ->where([
                        'payment_status' => 0,
                        'service_store_buyer_id' => $service_store_buyer_id
                    ])->first();
    }

    public function getUnpaidByStoreID($service_store_buyer_id)
    {
        return $this->where([
                        'payment_status' => 0,
                        'service_store_buyer_id' => $service_store_buyer_id
                    ])->first();
    }

    public function getPaymentFreeByStoreID($service_store_buyer_id)
    {
        return $this->where([
                        'payment_status' => 1,
                        'service_store_buyer_id' => $service_store_buyer_id,
                        'total' => 0
                    ])->first();
    }

    public function getAllPaymentByService($service_id, $condition)
    {
        $query = $this->leftJoin('service_store_buyers', 'service_store_buyers.id', '=', 'payments.service_store_buyer_id')
            ->leftJoin('buyers as buyer', 'buyer.account_id', '=', 'service_store_buyers.buyer_id')
            ->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->leftJoin('services', 'services.id', '=', 'service_courses.service_id')
            ->select(
                'payments.*',
                'buyer.account_name',
                'buyer.profile_image_url_buy',
                'service_courses.name as course_name',
                'service_courses.course_id',
                'buyer.account_name',
                'buyer.account_id as buyer_id'
            )
            ->where('payments.payment_status', 1)
            ->where('services.id', '=', $service_id)
            ->orderBy('pay_expire_at_date');

        $current_month_name = Carbon::now()->format('M');
        $curr_month = date("m", strtotime($current_month_name));
        $current_year = Carbon::now()->format('Y');

        if (isset($condition->month) && isset($condition->year)) {
            $query->whereYear('payments.created_at', $condition->year)
                ->whereMonth('payments.created_at', $condition->month);
        } else {
            $query->whereYear('payments.created_at', $current_year)
                ->whereMonth('payments.created_at', $curr_month);
        }

        if (!isset($condition->per_page)) {
            $result = $query->paginate(10);
        } else {
            $result = $query->paginate($condition->per_page);
        }
        
        return $result;
    }

    public function getAllPaymentBuyer($service_id, $buyer_id, $condition)
    {
        $query = $this->leftJoin('service_store_buyers', 'service_store_buyers.id', '=', 'payments.service_store_buyer_id')
            ->leftJoin('buyers as buyer', 'buyer.account_id', '=', 'service_store_buyers.buyer_id')
            ->leftJoin('service_courses', 'service_courses.course_id', '=', 'service_store_buyers.course_id')
            ->leftJoin('services', 'services.id', '=', 'service_courses.service_id')
            ->select(
                'payments.*',
                'buyer.account_name',
                'service_courses.name as course_name',
                'service_courses.course_id'
            )
            ->where('services.id', '=', $service_id)
            ->where('buyer.account_id', $buyer_id)
            ->where('payments.payment_status', '!=', 0);

        if (!$condition->per_page) {
            $result = $query->orderBy('created_at', 'DESC')->paginate(10);
        } else {
            $result = $query->orderBy('created_at', 'DESC')->paginate($condition->per_page);
        }

        return  $result;
    }

    public function getPaymentIdByStoreAndTime($service_store_buyer_id, $time_delivery)
    {
        return $this->where([
                        'service_store_buyer_id' => $service_store_buyer_id
                    ])->whereDate('created_at', '<=', $time_delivery)
                    ->whereDate('pay_expire_at_date', '>=', $time_delivery)
                    ->first();
    }
}
