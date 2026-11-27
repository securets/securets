<?php

namespace App\Http\Controllers\Admin;

use App\Admin\PurchaseReceipt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class AdminReceiptController extends Controller
{
    public function show(int $subscription, PurchaseReceipt $receipts): Response
    {
        $receipt = $receipts->find($subscription);

        abort_if($receipt === null, 404);

        return response()
            ->view('admin.receipt', ['receipt' => $receipt])
            ->header('Cache-Control', 'no-store, private');
    }
}
