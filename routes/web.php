<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'Ecommerce\FrontController@index')->name('front.index');
Route::get('/product','Ecommerce\FrontController@product')->name('front.product');
Route::get('/product/{slug}', 'Ecommerce\FrontController@show')->name('front.show_product');
Route::get('/category/{slug}', 'Ecommerce\FrontController@categoryProduct')->name('front.category');
Route::post('cart','Ecommerce\CartController@addToCart')->name('front.cart');
Route::get('/cart','Ecommerce\CartController@listCart')->name('front.list_cart');
Route::post('/cart','Ecommerce\CartController@updateCart')->name('front.update_cart');
Route::post('/cart/update', 'Ecommerce\CartController@updateCart')->name('front.update_cart');
Route::get('/checkout' ,'Ecommerce\CartController@checkout')->name('front.checkout');
Route::post('/checkout','Ecommerce\CartController@processCheckout')->name('front.store_checkout');
Route::post('/checkout/{invoice}','Ecommerce\CartController@checkoutFinish')->name('front.checkout_finish');

// Route::group(['prefix' => 'member','namespace' => 'Ecommerce'], function() {
//     Route::get('verify/{token}', 'FrontController@verifyCustomerRegistration')->name('customer.verify');
//     Route::get('login','LoginController@loginForm')->name('customer.login');
// });

Auth::routes();


Route::group(['prefix' => 'administrator','middleware'=>'auth'], function(){
    
    Route::resource('category', 'CategoryController')->except(['create', 'show']);
    Route::get('/home', 'HomeController@index')->name('home');
    Route::resource('product','ProductController')->except(['show']);
    Route::get('/product/bulk', 'ProductController@massUploadForm')->name('product.bulk');
    Route::post('/product/bulk', 'ProductController@massUploadForm')->name('product.saveBulk');
    


});



