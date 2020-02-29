<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\InvoicePaid;
use App\User;
use App\Invoice;

class UserController extends Controller
{
    public function sendmail(){
    	$invoice = Invoice::find(2);
    	$user=User::find(6);
    	//return $user->email;

    return (new InvoicePaid($user))
                ->toMail($user->email);
    	
    }
}
