<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatMsgController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DeductPaidController;
use App\Http\Controllers\DeductTypeController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\FrammerAreaController;
use App\Http\Controllers\FrammerAreaMixController;
use App\Http\Controllers\FrammerAreaMixEventTypeController;
use App\Http\Controllers\FrammerAreaMixLocationController;
use App\Http\Controllers\FrammersController;
use App\Http\Controllers\IncomePaidController;
use App\Http\Controllers\IncomeTypeController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\RadioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FactoryActivityController;
use App\Http\Controllers\RainController;
use App\Http\Controllers\CompanyDetailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotifyLogUserController;
use App\Http\Controllers\RainImageController;
use App\Http\Controllers\NotiSettingController;
use App\Http\Controllers\FaqController;
use Illuminate\Support\Facades\Route;

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

//////////////////////////////////////////web no route group/////////////////////////////////////////////////////
//Login Admin
Route::post('/login', [LoginController::class, 'login']);

Route::post('/check_login', [LoginController::class, 'checkLogin']);
Route::post('/login_app', [LoginController::class, 'loginApp']);

//user
Route::post('/create_admin', [UserController::class, 'createUserAdmin']);
Route::post('/forgot_password_user', [UserController::class, 'ForgotPasswordUser']);

// //controller
Route::post('/upload_images', [Controller::class, 'uploadImages']);
Route::post('/upload_file', [Controller::class, 'uploadFiles']);

//user
Route::resource('user', UserController::class);
Route::post('/user_page', [UserController::class, 'getPage']);
Route::get('/get_user', [UserController::class, 'getUser']);
Route::get('/user_profile', [UserController::class, 'getProfileUser']);
Route::post('/update_user', [UserController::class, 'update']);
// Route::post('/user_page', [UserController::class, 'UserPage']);
Route::put('/reset_password_user/{id}', [UserController::class, 'ResetPasswordUser']);
Route::post('/update_profile_user', [UserController::class, 'updateProfileUser']);
Route::get('/get_profile_user', [UserController::class, 'getProfileUser']);

Route::put('/update_password_user/{id}', [UserController::class, 'updatePasswordUser']);


// Fearture
Route::resource('feature', FeatureController::class);
Route::post('/feature_page', [FeatureController::class, 'getPage']);
Route::get('/get_feature', [FeatureController::class, 'getList']);
Route::post('/update_feature', [FeatureController::class, 'updateData']);


// Fearture
Route::resource('factorie', FactoryController::class);
Route::post('/factorie_page', [FactoryController::class, 'getPage']);
Route::get('/get_factorie', [FactoryController::class, 'getList']);

// Contractor
Route::resource('contractor', ContractorController::class);
Route::post('/contractor_page', [ContractorController::class, 'getPage']);
Route::get('/get_contractor', [ContractorController::class, 'getList']);
Route::post('/update_contractor', [ContractorController::class, 'updateData']);

// Province
Route::resource('province', ProvinceController::class);
Route::post('/province_page', [ProvinceController::class, 'getPage']);
Route::get('/get_province', [ProvinceController::class, 'getList']);

// Country
Route::resource('country', CountryController::class);
Route::post('/country_page', [CountryController::class, 'getPage']);
Route::get('/get_country/{id}', [CountryController::class, 'getList']);

// frammer_area_mix_event_type
Route::resource('frammer_area_mix_event_type', FrammerAreaMixEventTypeController::class);
Route::post('/frammer_area_mix_event_type_page', [FrammerAreaMixEventTypeController::class, 'getPage']);
Route::get('/get_frammer_area_mix_event_type', [FrammerAreaMixEventTypeController::class, 'getList']);

// frammer_area_mix_location
Route::resource('frammer_area_mix_location', FrammerAreaMixLocationController::class);
Route::post('/frammer_area_mix_location_page', [FrammerAreaMixLocationController::class, 'getPage']);
Route::get('/get_frammer_area_mix_location', [FrammerAreaMixLocationController::class, 'getList']);

// Frammer
Route::resource('frammer', FrammersController::class);
Route::post('/frammer_page', [FrammersController::class, 'getPage']);
Route::get('/get_frammer', [FrammersController::class, 'getList']);
Route::post('/update_frammer', [FrammersController::class, 'updateData']);
Route::post('/image_profile', [FrammersController::class, 'imageProfile']);
Route::get('/get_image_profile/{id}', [FrammersController::class, 'GetProfile']);

// News
Route::resource('news', NewsController::class);
Route::post('/news_page', [NewsController::class, 'getPage']);
Route::get('/get_news', [FrammersController::class, 'getList']);
Route::post('/update_news', [NewsController::class, 'updateData']);

// Framer Area
Route::resource('frammer_area', FrammerAreaController::class);
Route::post('/frammer_area_page', [FrammerAreaController::class, 'getPage']);
Route::post('/get_frammer_area', [FrammerAreaController::class, 'getList']);
Route::post('/get_plot_list', [FrammerAreaController::class, 'getPlotList']);
Route::post('/update_image_area', [FrammerAreaController::class, 'updateImageArea']);



// Framer Area Mix
Route::resource('frammer_area_mix', FrammerAreaMixController::class);
Route::post('/frammer_area_mix_page', [FrammerAreaMixController::class, 'getPage']);
Route::get('/get_frammer_area_mix', [FrammerAreaMixController::class, 'getList']);

// Journals
Route::resource('journal', JournalController::class);
Route::post('/journal_page', [JournalController::class, 'getPage']);
Route::get('/get_journal', [JournalController::class, 'getList']);
Route::post('/update_journal', [JournalController::class, 'updateData']);

Route::post('/confirm_otp', [LoginController::class, 'requestOTP']);
Route::post('/verify_otp', [LoginController::class, 'confirmOtp']);


// Framer Area Mix
Route::resource('frammer_area_mix', FrammerAreaMixController::class);
Route::post('/frammer_area_mix_page', [FrammerAreaMixController::class, 'getPage']);
Route::get('/get_frammer_area_mix', [FrammerAreaMixController::class, 'getList']);


// Radio
Route::resource('radio', RadioController::class);
Route::post('/radio_page', [RadioController::class, 'getPage']);
Route::get('/get_radio', [RadioController::class, 'getList']);
Route::post('/update_radio', [RadioController::class, 'updateData']);


// Income Type
Route::resource('income_type', IncomeTypeController::class);
Route::post('/income_type_page', [IncomeTypeController::class, 'getPage']);
Route::get('/get_income_type', [IncomeTypeController::class, 'getList']);


// Deduct Type
Route::resource('deduct_type', DeductTypeController::class);
Route::post('/deduct_type_page', [DeductTypeController::class, 'getPage']);
Route::get('/get_deduct_type', [DeductTypeController::class, 'getList']);


// Income Paid
Route::resource('income_paid', IncomePaidController::class);
Route::post('/income_paid_page', [IncomePaidController::class, 'getPage']);
Route::get('/get_income_paid/{frammer_id}/{month}/{year}', [IncomePaidController::class, 'getList']);

// Deduct Paid
Route::resource('deduct_paid', DeductPaidController::class);
Route::post('/deduct_paid_page', [DeductPaidController::class, 'getPage']);
Route::get('/get_deduct_paid/{frammer_id}/{month}/{year}', [DeductPaidController::class, 'getList']);

// Factory Activity
Route::resource('factoryactivity', FactoryActivityController::class);
Route::post('/factoryactivity_page', [FactoryActivityController::class, 'getPage']);
Route::get('/get_factoryactivity', [FactoryActivityController::class, 'getList']);
Route::post('/get_summaryactivity', [FactoryActivityController::class, 'summaryActivity']);
Route::post('/post_summaryactivity', [FactoryActivityController::class, 'savesummaryActivity']);
Route::post('/factoryactivity_page_mobile', [FactoryActivityController::class, 'getPagemobile']);
Route::post('/factoryactivity_schedule', [FactoryActivityController::class, 'schedule']);
Route::post('/check_no', [FactoryActivityController::class, 'check_no']);
Route::post('/new_delete', [FactoryActivityController::class, 'newdestroy']);

// Rain
Route::resource('rain', RainController::class);
Route::post('/get_rain', [RainController::class, 'getList']);

Route::post('/upload_rain_image', [RainImageController::class, 'uploadrainimage']);
Route::post('/get_rain_image', [RainImageController::class, 'getList']);

// CompanyDetails
Route::resource('company', CompanyDetailController::class);
Route::post('/get_company_byfactory', [CompanyDetailController::class, 'getbyfacID']);

//dashboard
Route::post('/get_byactivitytype', [DashboardController::class, 'groutpby_activitytype']);
Route::post('/get_byweekly', [DashboardController::class, 'groutpby_weekly']);
Route::post('/get_incomededuct', [DashboardController::class, 'incomededuct']);

//notify log user
Route::post('/notify_log_user_page', [NotifyLogUserController::class, 'Page']);
Route::get('/get_notify_log_user', [NotifyLogUserController::class, 'get']);
Route::get('/testNoti', [Controller::class, 'testNoti']);
Route::post('/notify_alert', [NotifyLogUserController::class, 'notiAlert']);


//chat
Route::resource('chat', ChatController::class);
Route::post('/get_chat', [ChatController::class, 'getChat']);
Route::post('/chat_page', [ChatController::class, 'ChatPage']);

//chat msg
Route::resource('chat_msg', ChatMsgController::class);
Route::post('/get_chat_msg', [ChatMsgController::class, 'getChatMsg']);
Route::post('/chat_msg_page', [ChatMsgController::class, 'ChatMsgPage']);

// NotiSetting
Route::resource('noti_setting', NotiSettingController::class);
Route::post('/noti_setting_page', [NotiSettingController::class, 'getPage']);
Route::get('/get_noti_setting/{frammer_id}', [NotiSettingController::class, 'getList']);


// Faq
Route::resource('faq', FaqController::class);
Route::post('/faq_page', [FaqController::class, 'getPage']);
Route::get('/get_faq', [FaqController::class, 'getList']);

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['middleware' => 'checkjwt'], function () {});
