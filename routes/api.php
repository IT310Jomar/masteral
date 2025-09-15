<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SystemConfigurationController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('user-profile', [AuthController::class, 'me']);
    //Rest Password
    Route::put('user-profile/changepass/{id}', [AuthController::class, 'Editpass']);
    Route::get('password/user-password', [AuthController::class, 'getPasswordData']);
    //setup password editor
    Route::put('editor/setuppass/{id}', [AuthController::class, 'EditorResetpass']);

    Route::post('/forgotPassword', [App\Http\Controllers\AuthController::class, 'forgotPassword']);
    Route::post('/reset/password', [App\Http\Controllers\AuthController::class, 'reset']);
    Route::post('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
/*
|--------------------------------------------------------------------------
| ADMIN - Settings - SystemConfigurationController
|--------------------------------------------------------------------------
|
*/

Route::post('/admin/toggle-maintenance', [SystemConfigurationController::class, 'toggleMaintenanceMode']);
Route::get('/admin/check-maintenance',[SystemConfigurationController::class,'getMaintenance']);

/*
|--------------------------------------------------------------------------
| ADMIN - CLIENT - ClientController
|--------------------------------------------------------------------------
|
*/
    Route::get('/admin/get-all-clients',[ClientController::class,'getAllClients']);
    Route::get('/admin/search-clients',[ClientController::class,'searchClient']);
    Route::get('/admin/get-client-data/{id}',[ClientController::class,'getSingleClient']);
    Route::put('/admin/active-client/{id}', [ClientController::class, 'activeClient']);
    Route::put('/admin/disable-client/{id}', [ClientController::class, 'disableClient']);
    Route::get('/admin/get-upload-data/{id}',[ClientController::class,'getSingleClientUploadData']);
    Route::get('/admin/search-client-upload-journal/{id}',[ClientController::class,'searchSingleClientUploadData']);

/*
|--------------------------------------------------------------------------
| ADMIN - EDITOR - EditorController
|--------------------------------------------------------------------------
|
*/
   Route::get('/admin/get-all-editors',[EditorController::class,'getAllDataEditor']);//ok
   Route::post('/admin/post-editor',[EditorController::class,'postEditor']);//ok
   Route::put('/admin/edit-editor/{id}',[EditorController::class,'editEditor']);//ok
   Route::get('/admin/search-editors',[EditorController::class,'searchEditor']);//ok
   Route::get('/admin/get-editor-data/{id}',[EditorController::class,'getSingleEditor']);//ok
   Route::put('/admin/active-editor/{id}', [EditorController::class, 'activeEditor']);//ok
   Route::put('/admin/disable-editor/{id}', [EditorController::class, 'disableEditor']);


/*
|--------------------------------------------------------------------------
| ADMIN - Services & Payment - ServicesController
|--------------------------------------------------------------------------
|
*/
//Payment Method
Route::get('/admin/get-all-payment-menthod',[ServicesController::class,'getAllPaymentMethod']);
Route::post('/admin/post-payment-method',[ServicesController::class,'addpaymentmethod']);
Route::post('/admin/edit-payment-method/{id}',[ServicesController::class,'edit_paymentmethod']);
Route::get('/admin/search-payment-method',[ServicesController::class,'searchPaymentMethods']);
Route::put('/admin/status-payment-method/{id}', [ServicesController::class, 'statusPayment']);

//Services
Route::get('/admin/get-all-services-data',[ServicesController::class,'getAllServices']);
Route::post('/admin/post-services-data',[ServicesController::class,'addServices']);
Route::post('/admin/edit-services-data/{id}',[ServicesController::class,'edit_Services']);
Route::get('/admin/search-services-data',[ServicesController::class,'searchServices']);
Route::put('/admin/status-services-data/{id}', [ServicesController::class, 'statusServices']);
/*
|--------------------------------------------------------------------------
| ADMIN - PAID ACCOUNTS - AdminController
|--------------------------------------------------------------------------
|
*/
Route::get('/admin/get-paid-accounts-lists',[AdminController::class,'getPaidAccountData']);//ok
Route::get('/admin/search-paid-accounts',[AdminController::class,'searchPaidAccounts']);//ok
Route::put('/admin/post-approved-paid/{id}',[AdminController::class,'approvedPaid']);
Route::put('/admin/post-rejected-paid/{id}',[AdminController::class,'rejectedPaid']);
Route::get('/admin/paid-list/range',[AdminController::class,'paidDateRangeSearch']);//ok
Route::get('/admin/get-published-editor-data/{id}',[AdminController::class,'getSingleEditorPublished']);//ok
Route::get('/admin/search-published-editor-journal/{id}',[AdminController::class,'searchSingleEditorPublishedData']);//ok
Route::get('/admin/get-editing-journal',[AdminController::class,'getEditingData']);//ok
Route::get('/admin/search-editing-journal',[AdminController::class,'searchEditingData']);//ok
/*
|--------------------------------------------------------------------------
| ADMIN - Sales - AdminController
|--------------------------------------------------------------------------
|
*/
Route::get('/admin/get-sales-data-lists',[AdminController::class,'getSalesData']);//ok
Route::get('/admin/search-sales-data',[AdminController::class,'searchSales']);//ok
Route::get('/admin/sales-list/range',[AdminController::class,'salesDateRangeSearch']);//ok

/*
|--------------------------------------------------------------------------
| ADMIN - REQUEST LIST - AdminController
|--------------------------------------------------------------------------
|
*/
Route::get('/admin/get-uploaded-journal-lists',[AdminController::class,'getRequestListData']);//ok
Route::get('/admin/search-uploaded-journal',[AdminController::class,'searchRequestList']);//ok
Route::get('/admin/get-uploaded-journal/{id}',[AdminController::class,'getRequestData']);//ok
Route::put('/admin/post-approved-request/{id}',[AdminController::class,'approvedRequest']);
Route::put('/admin/post-rejected-request/{id}',[AdminController::class,'rejectedRequest']);
Route::get('/admin/get-assigned-editor',[AdminController::class,'getEditor']);//ok
Route::post('/admin/post-re-uploadpdf/{id}',[AdminController::class,'requestReUpload']);
Route::get('/admin/get-profile/{id}',[AdminController::class,'view_profile']);//ok
Route::post('/admin/client-upload-profile/{id}',[AdminController::class,'UploadProfile_client']);
Route::post('/admin/editor-upload-profile/{id}',[AdminController::class,'UploadProfile_editor']);
Route::get('/admin/get-school-type',[AdminController::class,'getSchoolType']);//ok
Route::get('/admin/editors',[AdminController::class,'getAllEditor']);//ok
Route::put('/admin/edit-assigned-editor/{id}',[AdminController::class,'editEditorAssigned']);
//published journal
Route::get('/admin/get-all-published-journal',[AdminController::class,'getAllPublishedJournal']);//ok
Route::get('/admin/search-all-published-journal',[AdminController::class,'searchAllPublishedJournal']);//ok
Route::get('/admin/published-journal-list/range',[AdminController::class,'publishedDateRangeSearch']);//ok
/*
|--------------------------------------------------------------------------
| ADMIN - NOTIFICATION  - NotificationController
|--------------------------------------------------------------------------
|
*/
Route::get('/notification',[NotificationController::class,'adminNotification']);//ok
Route::put('/mark-as-read-all',[NotificationController::class,'readAdminAll']);//ok
Route::put('/admin/mark-as-read/{id}',[NotificationController::class,'readNotifAdmin']);//ok

/*
|--------------------------------------------------------------------------
| ADMIN - Payments - PaymentController
|--------------------------------------------------------------------------
|
*/

Route::get('/get-payments-data',[PaymentController::class,'getDataPayments']);//ok
Route::get('/get-statistics-data',[PaymentController::class,'usersAndPublished']);//ok
Route::get('/get-request-data',[PaymentController::class,'journalCount']);//ok
Route::get('/get-operational-data',[PaymentController::class,'operationalRatio']);//ok
Route::get('/get-payment-request-data',[PaymentController::class,'paymentData']);//ok
Route::get('/get-sales-request-data',[PaymentController::class,'salesStats']);//ok
Route::get('/get-school-type-data',[PaymentController::class,'schoolStats']);//ok
});



Route::group([
    'middleware' => 'api',
    'prefix' => 'client'
], function () {

    //client-api
    Route::post('client-register', [AuthController::class, 'register']);

    /*
    |--------------------------------------------------------------------------
    | CLIENT - PAYMENT - PaymentController
    |--------------------------------------------------------------------------
    |
    */

    Route::get('/client/get-mode-of-payment',[PaymentController::class,'getModePayment']);//ok
    Route::get('/client/get-mode-of-payment/{id}',[PaymentController::class,'getSingleModePayment']);//ok
    Route::get('/client/get-payment-list/{id}',[PaymentController::class,'getPaymentData']);//ok
    Route::get('/client/search-payments/{id}',[PaymentController::class,'searchPayment']);//ok
    Route::post('/client/post-payment',[PaymentController::class,'addpayment']);//ok
    Route::post('/client/edit-payment/{id}',[PaymentController::class,'edit_payment']);//ok

    Route::get('/client/get-services',[PaymentController::class,'getServices']);//ok
    Route::get('/client/get-services/{id}',[PaymentController::class,'getSingleServices']);//ok
    Route::get('/get-payment-request-data/{id}',[PaymentController::class,'paymentDataClient']);//ok
    Route::get('/get-request-data/{id}',[PaymentController::class,'paymentDataRequest']);//ok
    /*
    |--------------------------------------------------------------------------
    | CLIENT - REQUEST - RequestController
    |--------------------------------------------------------------------------
    |
    */

    Route::post('/client/post-uploadpdf',[RequestController::class,'requestUpload']);
    Route::get('/client/get-journal-requested-list/{id}',[RequestController::class,'clientPendingList']);
    Route::get('/client/get-search-journal-requested-list/{id}',[RequestController::class,'searchRequests']);
    Route::post('/client/post-re-uploadpdf/{id}',[ClientController::class,'myrequestReUpload']);

        //test


  Route::post('/admin/sample-email',[EmailController::class,'sendEmail']);

      /*
    |--------------------------------------------------------------------------
    | CLIENT - REQUEST - NotificationController
    |--------------------------------------------------------------------------
    |
    */
    Route::get('/client/notification/{id}',[NotificationController::class,'getClientNotification']);//ok
    Route::put('/client/mark-as-read-all/{id}',[NotificationController::class,'readAll']);//ok
    Route::put('/client/mark-as-read/{id}',[NotificationController::class,'readNotif']);//ok
    //PUBLISHED JOURNAL
    Route::get('/client/get-published-journal/{id}',[ClientController::class,'getClientPublishedJournal']);
    Route::get('/client/search-published-journal/{id}',[ClientController::class,'searchClientPublishedJournal']);
    Route::get('/client/published-journal-list/range/{id}',[ClientController::class,'publishedDateRangeSearchClient']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'editor'
], function () {

    /*
    |--------------------------------------------------------------------------
    | EDITOR - Assigned Documents - EditorController
    |--------------------------------------------------------------------------
    |
    */

   Route::get('/editor/get-assigned-editor/{id}',[EditorController::class,'getAssignedData']);//ok
   Route::get('/editor/search-assigned-journal/{id}',[EditorController::class,'searchAssignedList']);//ok
   Route::get('/editor/get-reset-pass-editor/{id}',[EditorController::class,'getEditorResetPass']);//ok
    //Edited Uploaded File
   Route::get('/editor/get-edited-uploaded-file/{id}',[EditorController::class,'getEditedData']);//ok

   //approved
   Route::put('/editor/approved-assigned-journal/{id}',[EditorController::class,'approvedAssignedDocs']);//ok
   Route::put('/editor/rejected-assigned-journal/{id}',[EditorController::class,'rejectedAssignedDocs']);//ok

   Route::get('/editor/search-edited-journal/{id}',[EditorController::class,'searchEditedList']);//ok
   Route::post('/editor/post-edited-uploadpdf/{id}',[EditorController::class,'requestEditedUpload']);//ok
   //published
   Route::put('/editor/published-journal-request/{id}',[EditorController::class,'publishedJournal']);//ok
   Route::get('/editor/get-published-journal/{id}',[EditorController::class,'getPublishedData']);//ok
   Route::get('/editor/search-published-journal/{id}',[EditorController::class,'searchPublishedList']);//ok


     /*
    |--------------------------------------------------------------------------
    | EDITOR - Notification - NotificationController
    |--------------------------------------------------------------------------
    |
    */
   
    Route::get('/editor/notification/{id}',[NotificationController::class,'editorNotification']);//ok
    Route::put('/editor/mark-as-read-all/{id}',[NotificationController::class,'readNotifEditor']);//ok
    Route::put('/editor/mark-as-read/{id}',[NotificationController::class,'readEditor']);//ok
    /*
    |--------------------------------------------------------------------------
    | EDITOR - Stats - PaymentController
    |--------------------------------------------------------------------------
    |
    */
    Route::get('/get-request-data-editor/{id}',[PaymentController::class,'paymentDataRequestEditor']);//ok
    Route::get('/get-request-published-editor/{id}',[PaymentController::class,'editorPublished']);//ok
});
