<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\Plan;

//use PayPal\Api\Session;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use Session;


use Carbon\Carbon;

class PaymentController extends Controller
{
    public function __construct()
    {
/** PayPal api context **/
        $paypal_conf = \Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
}

public function payWithpaypal(Request $request)
    {
$payer = new Payer();
$payer->setPaymentMethod('paypal');
$item_1 = new Item();
$item_1->setName('Item 1')
        ->setCurrency('USD')
        ->setQuantity(1)
        ->setPrice($request->get('amount'));
$item_list = new ItemList();
        $item_list->setItems(array($item_1));
$amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal($request->get('amount'));
$transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Your transaction description');
/*$redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::route('status'))
            ->setCancelUrl(URL::route('status'));*/
            $baseUrl = "http://127.0.0.1:8000/paypal";
$redirect_urls = new RedirectUrls();
$redirect_urls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
    ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");
$payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));
        /*dd($payment->create($this->_api_context));exit;*/
        try {
$payment->create($this->_api_context);
} catch (\PayPal\Exception\PPConnectionException $ex) {
if (\Config::get('app.debug')) {
\Session::put('error', 'Connection timeout');
                return Redirect::route('paywithpaypal');
} else {
\Session::put('error', 'Some error occur, sorry for inconvenient');
                return Redirect::route('paywithpaypal');
}
}
foreach ($payment->getLinks() as $link) {
if ($link->getRel() == 'approval_url') {
$redirect_urls = $link->getHref();
                break;
}
}
/** add payment ID to session **/
        Session::put('paypal_payment_id', $payment->getId());
if (isset($redirect_urls)) {
/** redirect to paypal **/
            return Redirect::away($redirect_urls);
            //return redirect()->back();
			//return getPaymentStatus();

}
\Session::put('error', 'Unknown error occurred');
        return Redirect::route('paywithpaypal');
}
public function getPaymentStatus()
    {
        /** Get the payment ID before session clear **/
        $payment_id = Session::get('paypal_payment_id');
/** clear the session payment ID **/
        Session::forget('paypal_payment_id');
        if (empty(Input::get('PayerID')) || empty(Input::get('token'))) {
\Session::put('error', 'Payment failed');
            return Redirect::route('paypal');
}
$payment = Payment::get($payment_id, $this->_api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId(Input::get('PayerID'));
/**Execute the payment **/
        $result = $payment->execute($execution, $this->_api_context);
if ($result->getState() == 'approved') {
\Session::put('success', 'Payment success');
            return Redirect::route('paypal');
}
\Session::put('error', 'Payment failed');
        return Redirect::route('paypal');
}
}
