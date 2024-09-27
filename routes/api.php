<?php
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
// Route::post('login', 'PassportController@login');
use Illuminate\Support\Facades\Route;
Route::post('login', 'PassportController@login')->name('login');
Route::post('social_login', 'PassportController@social_login')->name('social_login');
Route::post('register', 'PassportController@register');
Route::get('/getlocationdata/{input}', 'API\PropertiesController@getLocationData');
Route::post('forgot-password', 'PassportController@forgotPassword');
Route::post('reset-password', 'PassportController@resetPassword');
Route::get('/getPlaceData/{placeId}', 'API\PropertiesController@getPlaceData');
Route::post('/properties-search', 'API\PropertiesController@getPropertyBySearch');
Route::resource('/contract-lengths', 'API\ContractLengthsController');
Route::post('/store_contactus', 'API\ContactUsController@storeContactUs');
Route::post('/email/verify/', 'PassportController@verifyEmail')->middleware('cors'); // Make sure to keep this as your route name
Route::post('/get-distance-between-points-new', 'API\PropertiesController@getDistanceBetweenPointsNew');
Route::post('/schedule_tour', 'API\TourController@scheduleTour');
Route::get('/get_locations', 'API\PropertiesController@getUniqueAddresses');
Route::get('/update-class', 'API\PropertyController@updateClass');
Route::get('/update-max-length', 'API\PropertyController@updateMaxContractLengthType');

// Route::middleware('auth:api')->group(function () {
//     Route::get('/details', 'PassportController@details');
//     Route::resource('/properties', 'API\PropertiesController');
//     Route::resource('/contract-lengths', 'API\ContractLengthsController');
//     Route::resource('/additional-options', 'API\AdditionalOptionsController');
//     Route::resource('/aminities', 'API\AminitiesController');
// });



Route::get('/getCalculation/{propertyId}/{contractLengthId}/{propertyLayoutDesign}', 'API\PropertiesController@get_calculation');
Route::group([
    'middleware' => ['cors', 'auth:api']
], function () {
    Route::get('/get-user-profile', 'API\UsersController@getUserProfile');
    Route::post('/update-profile/{id}', 'API\UsersController@updateProfile');
    Route::post('/change-password/{id}', 'API\UsersController@changePassword');
    Route::get('/details', 'PassportController@details')->name('details');
    Route::get('/layout-designs', 'API\LayoutDesignsController@index');
    Route::get('/user-contracts/{id}', 'API\ContractsController@getByUser');
    Route::get('/property-contracts/{id}/{propertyId}', 'API\ContractsController@getByUserProperty');
    Route::post('/properties/{id}', 'API\PropertiesController@update');
    Route::delete('/property-image-delete/{id}', 'API\PropertiesController@deletePropertyImage');
    Route::resource('/properties', 'API\PropertiesController');
    Route::resource('/additional-options', 'API\AdditionalOptionsController');
    Route::resource('/aminities', 'API\AminitiesController');
    Route::resource('/users', 'API\UsersController');
    Route::resource('/near-by-amenities', 'API\NearByAmenitiesController');
    Route::resource('/contracts', 'API\ContractsController');
    Route::post('/contracts-file-upload', 'API\ContractsController@contractsFileUpload');
    Route::get('/contact_us', 'API\ContactUsController@index');
    Route::get('/contact_us/{id}', 'API\ContactUsController@show_contact_us');
    Route::post('/contract_offer', 'API\ContractOfferController@index');
    Route::post('/contract_offer/{id}', 'API\ContractOfferController@show_contract_offer');
    Route::post('/store_contract_offer', 'API\ContractOfferController@store');
    Route::post('/update_offer_status', 'API\ContractOfferController@update_offer_status');
    Route::post('/update_title', 'API\PropertiesController@update_title');
    //Calculation Route //


    Route::resource('/layout-designs', 'API\LayoutDesignController');

    Route::post('/bulk/delete/properties','API\PropertiesController@bulkDelete');
    Route::post('/bulk/upload/properties','API\PropertiesController@bulkUpload');
    Route::get('/bulk/upload/instructions','API\PropertiesController@excelInstructions');
});
Route::get('/additional-options', 'API\AdditionalOptionsController@index');
Route::get('/properties/{property}', 'API\PropertiesController@show');

