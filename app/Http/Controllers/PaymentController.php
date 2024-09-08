<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function handleSuccess(Request $request)
    {
        return view('payment.success');
    }

    public function handleFailure(Request $request)
    {
        return view('payment.failure');
    }

    public function handlePending(Request $request)
    {
        return view('payment.pending');
    }
}
