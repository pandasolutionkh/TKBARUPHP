<?php

use Illuminate\Http\Request;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::group(['prefix' => 'secure/api', 'middleware' => 'auth:api'], function() {
    Route::post('/user/set_settings', 'StoreController@applySettings')->name('api.user.apply_settings');
});

Route::get('/po/code', function () {
    return \App\Util\POCodeGenerator::generateCode();
})->name('api.po.code');

Route::get('/so/code', function () {
    return \App\Util\SOCodeGenerator::generateCode();
})->name('api.so.code');

Route::group(['prefix' => 'warehouse'], function () {
    Route::group(['prefix' => 'outflow'], function () {
        Route::get('/so/{id?}', 'WarehouseOutflowController@getWarehouseSOs')->name('api.warehouse.outflow.so');
    });

    Route::group(['prefix' => 'inflow'], function () {
        Route::get('/po/{id?}', 'WarehouseInflowController@getWarehousePOs')->name('api.warehouse.inflow.po');
    });
});

Route::group(['prefix' => 'customer'], function () {
    Route::get('search', 'CustomerController@searchCustomers')->name('api.customer.search');
});

Route::group(['prefix' => 'phone_provider'], function() {
    Route::get('search/{param?}', 'PhoneProviderController@getPhoneProviderByDigit')->name('api.phone_provider.search');
});

Route::group(['prefix' => 'purchase_order'], function() {
    Route::get('due_purchase_order', 'PurchaseOrderController@getDuePO')->name('api.purchase_order.due_purchase_order');
});

Route::group(['prefix' => 'sales_order'], function() {
    Route::get('due_sales_order', 'SalesOrderController@getDueSO')->name('api.sales_order.due_sales_order');
    Route::get('today_sales_order', 'SalesOrderController@getTodaySO')->name('api.sales_order.today_sales_order');
    Route::get('today_sales_order_amount', 'SalesOrderController@getTodaySOTotalAmount')->name('api.sales_order.today_sales_order_amount');
    Route::get('today_yesterday_sales_order', 'SalesOrderController@getTodayAndYesterdaySO')->name('api.sales_order.today_yesterday_sales_order');
});

Route::group(['prefix' => 'stock'], function() {
    Route::get('current_stocks', 'StockController@getCurrentStocks')->name('api.stock.current_stocks');
});

Route::get('/user/get/calendar', 'CalendarController@retrieveEvents')->name('api.user.get.calendar');

Route::get('/get/unfinish/store', 'StoreController@isUnfinishedStoreExist')->name('api.get.unfinish.store');

Route::get('/get/unfinish/warehouse', 'WarehouseController@isUnfinishedWarehouseExist')->name('api.get.unfinish.warehouse');