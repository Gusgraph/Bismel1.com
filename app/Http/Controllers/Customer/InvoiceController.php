<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/InvoiceController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\InvoicePageData;

class InvoiceController extends Controller
{
    public function index(CurrentCustomerAccountResolver $currentCustomerAccountResolver)
    {
        $account = $currentCustomerAccountResolver->resolveForPreset(request()->user(), 'invoice');
        $data = InvoicePageData::make($account);

        return view('customer.invoices.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'subscriptionSummary' => $data['subscriptionSummary'],
            'invoiceSummary' => $data['invoiceSummary'],
            'invoiceDetails' => $data['invoiceDetails'],
            'invoiceSummaryMeta' => $data['invoiceSummaryMeta'],
            'invoiceDetailsMeta' => $data['invoiceDetailsMeta'],
            'summary' => $data['summary'],
            'hasSubscriptionData' => $data['hasSubscriptionData'],
            'hasInvoiceData' => $data['hasInvoiceData'],
        ]);
    }
}
