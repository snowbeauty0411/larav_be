<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AreaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisteredMemberController;
use App\Http\Controllers\Api\BuyerController;
use App\Http\Controllers\Api\BuyerServiceReserveController;
use App\Http\Controllers\Api\CompanyInfoController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\VerifyIdentityController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\PrivacyPolicyController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\TermOfServiceController;
use App\Http\Controllers\Api\ShippingInfoController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\EkispertController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\SellController;
use App\Http\Controllers\Api\ServiceReviewController;
use App\Http\Controllers\Api\StatisticalController;
use App\Http\Controllers\Api\CountClickOfficialUrlController;
use App\Http\Controllers\Api\MessageManagementController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Auth\ResetMailController;
use App\Http\Controllers\Api\RevenueController;
use App\Http\Controllers\Api\SellerCardInfoController;
use App\Http\Controllers\Api\ServiceCourseController;
use App\Http\Controllers\Api\TransferHistoryController;
use App\Http\Controllers\Api\BanksController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Public API
Route::post('/signup/check-email', [RegisteredMemberController::class, 'checkEmailRegistrationMember']);
Route::post('signup/create', [RegisteredMemberController::class, 'registerMemberStep1']);

Route::group(['middleware' => ['assign.guard:users']], function () {
    Route::patch('signup/done/{id}', [RegisteredMemberController::class, 'registerMemberStep2']);
});
Route::get('signup/check/active-link/{token}', [RegisteredMemberController::class, 'checkActiveLinkExpired']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('login', [LoginController::class, 'loginUser']);
Route::post('forgot/input', [ResetPasswordController::class, 'sendMailUser']);
Route::post('password/reset', [ResetPasswordController::class, 'resetPassUser']);
Route::post('password/check', [ResetPasswordController::class, 'checkTokenPassword']);

Route::group(['prefix' => 'mobile'], function(){
    Route::post('signup/check-email', [RegisteredMemberController::class, 'checkEmailRegistrationMemberOtp']);
    Route::post('signup/create', [RegisteredMemberController::class, 'registerMemberStep1Otp']);
    Route::post('forgot/input', [ResetPasswordController::class, 'sendMailOTPUser']);
    Route::post('password/reset', [ResetPasswordController::class, 'resetPasswordUseOTP']);
});

Route::group(['prefix' => 'admin'], function () {
    Route::post('login', [LoginController::class, 'loginAdmin']);
});



Route::group(['prefix' => 'chat'], function () {
    //chat by role
    Route::group(['middleware' => ['assign.guard:users']], function () {
        Route::post('user/thread/list', [MessageManagementController::class, 'listThreadUser']);
        Route::post('user/thread/index/{thread_id}', [MessageManagementController::class, 'indexUser']);
        Route::post('user/create/thread', [MessageManagementController::class, 'createThreadToUserByUser']);
    });

    Route::group(['middleware' => ['assign.guard:users,admins']], function () {
        Route::post('message-create', [MessageManagementController::class, 'createMessage']);
        Route::get('{thread_id}/{file_name}', [MessageManagementController::class, 'getResourceFile']);
    });
});

Route::group(['middleware' => ['assign.guard:users']], function () {
    Route::get('logout', [LoginController::class, 'logout']);
});

Route::post('seller/service/creating-and-selling', [ServiceController::class, 'getAllServiceByCreatingOrSelling']);
Route::get('seller/profile-public/{id}', [SellerController::class, 'getProfile']);
Route::post('seller/service/getAllService', [ServiceController::class, 'getAllSerivceOfSeller']);

Route::group(['prefix' => '/account'], function () {
    Route::get('/banks',[BanksController::class,'getListBanks']);
    Route::get('/branches',[BanksController::class,'getListBranches']);
});

Route::group(['middleware' => ['assign.guard:users']], function () {
    Route::group(['prefix' => '/buyer'], function () {
        Route::put('profile/edit/{id}', [BuyerController::class, 'update']);
        Route::get('account/{id}', [BuyerController::class, 'getAccount']);
        Route::post('profile/avatar/edit', [BuyerController::class, 'uploadAvatarProfile']);
        Route::get('service/{id}', [ServiceController::class, 'serviceOverview']);
        Route::get('service/deleted/{id}', [ServiceController::class, 'serviceDeleted']);
        Route::post('{id}/service/list-stop', [ServiceController::class, 'getAllServiceStopByBuyer']);
        Route::get('service/stop/{id}', [ServiceController::class, 'stopService']);
        Route::post('service/buying-or-bought', [ServiceController::class, 'getServiceByBuyerId']);
        Route::post('service/favorite', [ServiceController::class, 'favoriteRegisteredService']);
        Route::get('service/invoice/{id}', [ServiceController::class, 'showInvoiceServiceByBuyer']);
        Route::post('service/{service_id}/delivery/list', [DeliveryController::class, 'listDeliveryBuyer']);
        Route::post('service/{hash_id}/payment/list/{page?}', [PaymentController::class, 'listPaymentBuyer']);
        Route::get('service/{id}/reservations', [ServiceController::class, 'serviceReservesManagerByBuyer']);
        Route::get('service/{id}/reservations/list', [BuyerServiceReserveController::class, 'getAllByBuyer']);
        Route::get('payment/detail/{id}', [PaymentController::class, 'detailPaymentBuyer']);
        Route::get('{id}/statistic-reservation', [BuyerServiceReserveController::class, 'statisticReservationByBuyer']);
        Route::post('/reservations/create', [BuyerServiceReserveController::class, 'store']);
        Route::post('/reservations/delete', [BuyerServiceReserveController::class, 'destroy']);
    });

    Route::group(['prefix' => '/seller'], function () {
        Route::put('profile/edit/{id}', [SellerController::class, 'update']);
        Route::get('account/{id}', [SellerController::class, 'getAccount']);
        Route::post('profile/avatar/edit', [SellerController::class, 'uploadAvatarProfile']);
        Route::post('service/creating-or-selling', [ServiceController::class, 'getAllServiceByCreatingOrSelling']);
        Route::post('service/approved', [ServiceController::class, 'getAllServiceByApproved']);
        Route::post('service/{id}/list-customer', [ServiceController::class, 'getAllBuyerUseService']);
        Route::get('service/{id}/list-customer/data-select', [ServiceController::class, 'getAllCourseAndDateUseService']);
        Route::get('service/{id}/reservations', [ServiceController::class, 'serviceReservesManagerBySeller']);
        Route::get('service/{id}/reservations/list-course', [BuyerServiceReserveController::class, 'getCourseByServiceId']);
        Route::get('service/{id}/business-schedule', [ServiceController::class, 'getBusinessSchedule']);
        Route::get('service/{id}/service-hours-temp', [ServiceController::class, 'getBusinessScheduleTemp']);
        Route::post('service/{id}/service-hours-temp/update', [ServiceController::class, 'updateBusinessScheduleTemp']);
        Route::post('service/{id}/business-schedule/update', [ServiceController::class, 'updateBusinessSchedule']);
        Route::post('service/{id}/status-reserves', [ServiceController::class, 'updateStatusReserves']);
        Route::post('service/revenue/{hash_id}/{page?}', [RevenueController::class, 'revenueBySeller']);
        //seller delivery api
        Route::post('service/{hash_id}/delivery/list/{page?}', [DeliveryController::class, 'listDeliveryByServiceId']);
        Route::patch('delivery/{id}/delivery-status', [DeliveryController::class, 'updateDeliveryStatus']);
        //statistical api
        Route::get('statistical-month/{hash_id}', [StatisticalController::class, 'serviceStatisticalMonth']);
        Route::post('statistical-graph/{hash_id}', [StatisticalController::class, 'graph']);
        Route::post('statistical-year/{seller_id}', [StatisticalController::class, 'serviceStatisticalYear']);
        Route::get('statistical/list-service/{seller_id}', [StatisticalController::class, 'getAllServiceBySellerId']);
        Route::get('{id}/transfer', [PaymentController::class, 'transferApplicationSeller']);
        Route::get('{id}/transfer/list', [TransferHistoryController::class, 'getAllBySeller']);
        Route::post('{id}/transfer/create', [TransferHistoryController::class, 'store']);
        Route::get('service/{id}', [ServiceController::class, 'serviceSelling']);
        Route::post('/service/start-date/update', [ServiceController::class, 'updateStartDate']);
        Route::get('support', [SellerController::class, 'requestSupport']);

        Route::post('service/lat-lng-from-address', [ServiceController::class, 'getLatLngByAddress']);
    });

    Route::group(['prefix' => 'seller-card'], function () {
        Route::post('create', [SellerCardInfoController::class, 'store']);
        Route::put('edit/{id}', [SellerCardInfoController::class, 'update']);
        Route::get('{id}', [SellerCardInfoController::class, 'show']);
    });

    Route::group(['prefix' => '/service-category'], function () {
        Route::post('create', [ServiceCategoryController::class, 'store']);
        Route::put('edit/{id}', [ServiceCategoryController::class, 'update']);
        Route::delete('delete/{id}', [ServiceCategoryController::class, 'destroy']);
    });

    Route::group(['prefix' => 'service'], function () {
        Route::post('edit/{id}', [ServiceController::class, 'update']);
        Route::post('create', [ServiceController::class, 'store']);
        Route::get('/detail/{id}/random-services', [ServiceController::class, 'getServicesFavoriteByBuyer']);
        Route::delete('delete/{id}', [ServiceController::class, 'destroy']);
        Route::post('create-edit-course', [ServiceController::class, 'createEditCourse']);
        Route::post('delete-course', [ServiceController::class, 'deleteCourse']);
    });

    Route::post('identity/upload-file', [VerifyIdentityController::class, 'uploadFile']);
    Route::post('account/avatar/edit', [VerifyIdentityController::class, 'uploadAvatarProfile']);

    Route::group(['prefix' => 'shipping-info'], function () {
        Route::get('list', [ShippingInfoController::class, 'index']);
        Route::get('list/{id}', [ShippingInfoController::class, 'shippingInfoBuyer']);
        Route::get('{id}', [ShippingInfoController::class, 'show']);
        Route::post('store', [ShippingInfoController::class, 'store']);
        Route::put('edit/{id}', [ShippingInfoController::class, 'update']);
        Route::delete('delete/{id}', [ShippingInfoController::class, 'destroy']);
    });

    Route::group(['prefix' => 'card'], function () {
        Route::get('{id}', [StripeController::class, 'showCard']);
        Route::post('create', [StripeController::class, 'addCard']);
        Route::post('edit/{id}', [StripeController::class, 'updateCard']);
        Route::post('token-card', [StripeController::class, 'getTokenCard']);
        Route::get('buyer/{id}', [StripeController::class, 'getAllCardByBuyer']);
        Route::get('buyer/{id}/default', [StripeController::class, 'showCardDefault']);
        Route::delete('delete/{id}', [StripeController::class, 'deleteCard']);
    });

    Route::post('/stripe-payment', [StripeController::class, 'store']);


    //sell api
    Route::post('sell/update/{id}', [SellController::class, 'update']);

    //reservation api
    Route::group(['prefix' => 'reservation'], function () {
        Route::post('update/{id}', [ReservationController::class, 'update']);
    });

    //delivery api
    Route::post('delivery/{id}/list', [DeliveryController::class, 'listDeliveryByServiceId']);
    Route::patch('delivery/{id}/delivery-status', [DeliveryController::class, 'updateDeliveryStatus']);

    Route::group(['prefix' => 'course'], function () {
        Route::get('{id}', [ServiceCourseController::class, 'show']);
    });


    //account setting APIs
    Route::group(['prefix' => '/account'], function () {
        // Route::get('info', [AccountController::class, 'infoAccount']);
        // Route::patch('setting', [AccountController::class, 'settingAccount']);
        Route::post('switch', [AccountController::class, 'switchAccount']);
        Route::get('setting/info', [AccountController::class, 'infoSettingAccount']);
        Route::patch('setting', [AccountController::class,'settingAccount']);
        Route::get('{id}/info-withdrawal',[AccountController::class,'infoWithdrawalAccount']);
        Route::patch('{id}/withdrawal',[AccountController::class,'withdrawalAccount']);
    });


    Route::post('mail/input', [ResetMailController::class, 'sendMailUser']);
    Route::post('mail/reset', [ResetMailController::class, 'resetMailUser']);

    Route::group(['prefix' => 'comment'], function () {
        Route::post('/create', [ServiceReviewController::class, 'store']);
        Route::post('/edit/{id}', [ServiceReviewController::class, 'update']);
        Route::get('/detail/{id}', [ServiceReviewController::class, 'show']);
        Route::delete('/delete/{id}', [ServiceReviewController::class, 'destroy']);
        Route::get('/buyer/{id}', [ServiceReviewController::class, 'getAllByBuyer']);
        Route::get('/buyer/{serviceId}/list/{id}', [ServiceReviewController::class, 'getAllByBuyerAndService']);
        Route::get('/seller/{id}', [ServiceReviewController::class, 'getAllBySeller']);
        Route::post('{id}/reply', [ServiceReviewController::class, 'sellerReply']);
    });

    Route::group(['prefix' => 'service'], function () {
        Route::post('management/list', [ServiceController::class, 'getAllServiceShopperId']);
        Route::post('edit/{id}', [ServiceController::class, 'update']);
        Route::get('hash-id', [ServiceController::class, 'createHashServiceId']);
        Route::post('getServiceByBuyerId', [ServiceController::class, 'getServiceByBuyerId']);
        Route::post('favoriteRegisteredService', [ServiceController::class, 'favoriteRegisteredService']);
        Route::post('{id}/favorite', [ServiceController::class, 'registerFavoriteService']);
        Route::get('/prefectures/{zipcode}', [ServiceController::class, 'getPrefecturesByZipCode']);
    });
});

Route::group(['middleware' => ['assign.guard:users,admins', 'auth:users,admins']], function () {
    Route::get('account/{account_id}/{fileName}/{index}', [VerifyIdentityController::class, 'getResourcePrivateFile'])->name('getResourcePrivateFile');
    Route::get('identity/account/file/{id}', [VerifyIdentityController::class, 'getFileIdentityByAccountId']);
});
Route::get('avatar/{file_name}', [VerifyIdentityController::class, 'getResourcePublicFile'])->name('getAvatar');

Route::group(['middleware' => ['assign.guard:admins']], function () {
    Route::group(['prefix' => '/admin'], function () {
        Route::group(['prefix' => '/user'], function () {
            Route::patch('identity/reject/{id}', [VerifyIdentityController::class, 'rejectIdentificationVerify']);
            Route::patch('identity/confirm/{id}', [VerifyIdentityController::class, 'confirmIdentificationVerify']);
            Route::patch('pending/{id}', [VerifyIdentityController::class, 'blockAccount']);
            Route::post('buyer/list', [VerifyIdentityController::class, 'getListBuyers']);
            Route::post('seller/list', [VerifyIdentityController::class, 'getListSellers']);
        });
        Route::patch('service/{id}/approve', [ServiceController::class, 'approveService']);
        Route::post('service-manage', [ServiceController::class, 'adminGetAllService']);
        Route::patch('service-manage/{id}/setting', [ServiceController::class, 'adminSettingService']);
        Route::get('account-info/{id}', [VerifyIdentityController::class, 'getAccountInfo']);
        // Route::resource('/favorite-tag', FavoriteTagController::class);
        // Route::post('/favorite-tag/list', [FavoriteTagController::class, 'listTagForAdmin']);
        Route::group(['prefix' => 'chat'], function () {
            Route::post('list-thread/{page?}', [MessageManagementController::class, 'listThreadByAdmin']);
            Route::post('thread/index', [MessageManagementController::class, 'indexAdmin']);
            Route::post('thread/to-seller/create', [MessageManagementController::class, 'createThreadToSellerByAdmin']);
            Route::post('thread/to-buyer/create', [MessageManagementController::class, 'createThreadToBuyerByAdmin']);
        });
        Route::group(['prefix' => 'contact'], function () {
            Route::post('list', [ContactController::class, 'listContactAdmin']);
            Route::get('detail/{id}', [ContactController::class, 'detail']);
            Route::patch('approve/{id}', [ContactController::class, 'approve']);
            Route::post('reply/{id}', [ContactController::class, 'reply']);
        });
        Route::group(['prefix' => 'transfer'], function () {
            Route::post('list', [TransferHistoryController::class, 'getAllByAdmin']);
            Route::patch('{id}/completed', [TransferHistoryController::class, 'completedTransfer']);
        });

        Route::group(['prefix' => 'comment'], function () {
            Route::post('list', [ServiceReviewController::class, 'index']);
            Route::patch('{id}/approve', [ServiceReviewController::class, 'approveReview']);
            Route::patch('seller/{id}/approve', [ServiceReviewController::class, 'approveReviewSeller']);
        });
    });
});

Route::group(['prefix' => 'service'], function () {
    Route::get('/list', [ServiceController::class, 'index']);
    Route::get('/detail/{id}', [ServiceController::class, 'show']);
    Route::get('recommend-list/{page}', [ServiceController::class, 'getServiceRecommend']);
    Route::get('special/{page}', [ServiceController::class, 'getServiceSpecial']);
    Route::post('new-list/{page?}', [ServiceController::class, 'getServiceNew']);
    Route::post('featured-list/{page?}', [ServiceController::class, 'getServiceFeatured']);
    Route::get('search-keyword/{page}', [ServiceController::class, 'searchServiceByKeyword']);
    Route::get('search-category/{page}', [ServiceController::class, 'searchServiceByCategory']);
    Route::get('search-area/{page}', [ServiceController::class, 'searchServiceByArea']);
    Route::get('search-tag/{per_page}', [ServiceController::class, 'searchServiceByTag']);
    Route::get('{hash_id}/browsing-history', [ServiceController::class, 'getBrowsingHistoryServices']);
});

Route::group(['prefix' => 'service'], function () {
    Route::get('/list', [ServiceController::class, 'index']);
    Route::get('/detail/{hash_id}', [ServiceController::class, 'show']);
    Route::get('/detail/{hash_id}/list-review', [ServiceController::class, 'getReviewsById']);
    Route::get('/detail/{hash_id}/other-list', [ServiceController::class, 'getOtherServices']);
    Route::get('/detail/{hash_id}/same-category-services', [ServiceController::class, 'getServicesByCategory']);
    Route::get('/detail/{hash_id}/list-course', [ServiceCourseController::class, 'getCourseByServiceHashId']);
});

Route::get('term', [TermOfServiceController::class, 'listTermOfServices']);
Route::get('company-info', [CompanyInfoController::class, 'companyInfo']);
Route::get('privacy-policy', [PrivacyPolicyController::class, 'listPrivacyPolicy']);
Route::post('contact/create', [ContactController::class, 'create']);
Route::get('/ekispert-station', [EkispertController::class, 'index']);
Route::get('/ekispert-service', [EkispertController::class, 'getServiceArea']);
Route::get('/ekispert/station-area/service-list/{page}', [EkispertController::class, 'getServiceAreaByStation']);


Route::get('statistical/detail/{id}', [StatisticalController::class, 'index']);
Route::get('service/{id}/{fileName}', [VerifyIdentityController::class, 'getImageService'])->name('getImageService');
// Route::post('stripe-payment-test', [StripeController::class, 'testPayment'])->name('testPayment');
Route::get('/hash-tag', [ServiceController::class, 'topTag']);

Route::group(['prefix' => 'service-category'], function () {
    Route::get('/list', [ServiceCategoryController::class, 'index']);
    Route::get('/detail/{id}', [ServiceCategoryController::class, 'show']);
});

Route::group(['prefix' => 'area'], function () {
    Route::get('/list', [AreaController::class, 'index']);
});

Route::post('count/click/official-url/{hash_id}',[CountClickOfficialUrlController::class,'countClickOfficialUrl']);

// Route::get('test', [StripeController::class, 'test']);

Route::get('service/update-location', [ServiceController::class, 'updateAllLocationService']);