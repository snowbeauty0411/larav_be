<?php

namespace Database\Seeders;

use App\Models\Delivery;
use App\Models\NumberAccessServiceDetailPage;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BusinessSeeder::class,
            AdminSeeder::class,
            AccountSeeder::class,
            UrlOfficialSeeder::class,
            BuyerSeeder::class,
            SellerSeeder::class,
            ServiceCategorySeeder::class,
            ServiceTypeSeeder::class,
            ServiceSeeder::class,
            ServiceLinkSeeder::class,
            ServiceStepSeeder::class,
            ServiceCourseSeeder::class,
            ServiceDeliverySeeder::class,
            ServiceDeliveryBuyerSeeder::class,
            ServiceStoreSeeder::class,
            ServiceStoreBuyerSeeder::class,
            // FavoriteTagSeeder::class,
            FavoriteSeeder::class,
            TermOfServiceSeeder::class,
            CompanyInfoSeeder::class,
            PrivacyPolicySeeder::class,
            ServiceReviewSeeder::class,
            // ServiceFavoriteTagSeeder::class,
            AreaSeeder::class,
            PrefectureSeeder::class,
            ServiceAreaSeeder::class,
            ShippingInfoSeeder::class,
            DeliverySeeder::class,
            BuyerServiceReserveSeeder::class,
            ServiceHourSeeder::class,
            ServiceReserveSettingSeeder::class,
            PaymentSeeder::class,
            MessageThreadSeeder::class,
            NumberAccessListServicePageSeeder::class,
            NumberAccessServiceDetailPageSeeder::class,
            NumberClickOfficialUrlSeeder::class,
            ContactSeeder::class,
            // SellerCardInfoSeeder::class,
            TransferHistorySeeder::class,
            TagSeeder::class,
            ServiceTagSeeder::class,
            BanksSeeder::class,
        ]);
    }
}
