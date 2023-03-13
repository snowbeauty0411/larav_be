<?php

namespace App\Providers;

use App\Models\ServiceReviewImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        Validator::extend('time_schedule', function($attribute, $value, $parameters)
        {
            $parts = explode(':', $value);
            if(count($parts) <= 1 || count($parts) > 2){
                return false;
            }
            $hours =  $parts[0];
            $minutes =  $parts[1];
            if($hours >= 9 && $hours <= 22 && is_numeric($hours) && is_numeric($minutes)){
                return true;
            }
            return false;
        });

        Validator::extend('time_distance', function($attribute, $value, $parameters)
        {
            $parts = explode(':', $value);
            if(count($parts) <= 1 || count($parts) > 2){
                return false;
            }
            $hours = (int) $parts[0];
            $minutes = (int) $parts[1];
            $array_minutes_aceept = [0, 15, 30, 45];
            if($hours >= 1 && $hours <= 12 && is_numeric($hours) && is_numeric($minutes) && in_array($minutes, $array_minutes_aceept)){
                return true;
            }
            return false;
        });

        Validator::extend('work_hours', function($attribute, $value, $parameters)
        {
            if(count($value) <= 1){
                return true;
            }

            foreach($value as $key1 => $item1){
                $parts_start1 = explode(':', $item1['start']);
                if(count($parts_start1) <= 1 || count($parts_start1) > 2){
                    return false;
                }
                $hour_start1 = (int) $parts_start1[0];
                $minutes_start1 = (int) $parts_start1[1];

                $parts_end1 = explode(':', $item1['end']);
                if(count($parts_end1) <= 1 || count($parts_end1) > 2){
                    return false;
                }
                $hour_end1 = (int) $parts_end1[0];
                $minutes_end1 = (int) $parts_end1[1];

                foreach($value as  $key2 => $item2){

                    $parts_start2 = explode(':', $item2['start']);
                    $hour_start2 = (int) $parts_start2[0];
                    $minutes_start2 = (int) $parts_start2[1];

                    $parts_end2 = explode(':', $item2['end']);
                    $hour_end2 = (int) $parts_end2[0];

                    if($key1 == $key2)  ;

                    if(($hour_end1 > $hour_start2 && $hour_end1 < $hour_end2)
                        || ($hour_start1 > $hour_start2 && $hour_start1 <  $hour_end2)
                        || ($hour_end1 == $hour_start2 & $minutes_end1 >= $minutes_start2)
                        || ($hour_start1 == $hour_end2 & $minutes_start1 <= $minutes_start2)
                        ){
                            return false;
                    }
                }
            }
            return true;
        });

        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient(config('services.stripe.secret'));
        });
    }
}
