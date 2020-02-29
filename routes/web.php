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

Route::get('/', function () {
    return view('welcome');
});
Route::get('stripe', 'StripePaymentController@stripe');
Route::post('stripe', 'StripePaymentController@stripePost')->name('stripe.post');
//Route::get('paypal', 'PaymentController@index');
Route::get('paypal', function () {
    return view('paypal');
});
Route::post('payment','PaymentController@payWithpaypal');
Route::post('status','PaymentController@getPaymentStatus');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
//Route::get('index','UserController@index');
/*Route::get('mail', function () {
    $invoice = App\Invoice::find(1);

    return (new App\Notifications\InvoicePaid($invoice))
                ->toMail($invoice->user);
});*/
Route::get('mail','UserController@sendmail');

