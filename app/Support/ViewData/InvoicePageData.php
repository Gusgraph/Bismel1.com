<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/InvoicePageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Domain\Billing\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Support\Display\RecordWindow;
use App\Support\Display\SafeDisplay;

class InvoicePageData
{
    public static function make(?Account $account = null): array
    {
        $subscription = $account?->subscriptions
            ?->sortByDesc(fn ($item) => $item->starts_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();
        $subscriptionStatus = $subscription?->status instanceof SubscriptionStatus
            ? $subscription->status->value
            : (string) ($subscription?->status ?? 'trial');
        $statusLabels = SubscriptionStatus::labels();
        $invoices = $account?->invoices
            ?->sortByDesc(fn ($invoice) => $invoice->issued_at?->getTimestamp() ?? $invoice->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();

        return [
            'page' => [
                'title' => 'Invoices',
                'intro' => 'Review subscription billing and invoice history for this workspace.',
                'subtitle' => $account
                    ? 'Current subscription and invoice history stay visible here for the current workspace.'
                    : 'No workspace is available yet, so invoices will appear here after billing activity begins.',
                'sections' => [
                    ['heading' => 'Subscription Snapshot', 'description' => 'Review the current plan and subscription status in one place.'],
                    ['heading' => 'Invoice History', 'description' => 'See invoice records with clear status, amount, and timing.'],
                    ['heading' => 'Billing Follow-Up', 'description' => 'Use this page to understand what has been billed and what still needs attention.'],
                ],
            ],
            'subscriptionSummary' => $subscription ? [
                ['label' => 'Workspace Name', 'value' => $account->name],
                ['label' => 'Workspace Slug', 'value' => $account->slug],
                ['label' => 'Workspace Owner', 'value' => $account->owner?->name ?? 'No owner assigned'],
                ['label' => 'Current Plan', 'value' => $subscription->subscriptionPlan?->name ?? 'No linked plan'],
                ['label' => 'Subscription Status', 'value' => $statusLabels[$subscriptionStatus] ?? ucfirst(str_replace('_', ' ', $subscriptionStatus))],
                ['label' => 'Subscription Start', 'value' => SafeDisplay::date($subscription->starts_at, 'Not started')],
                ['label' => 'Subscription End', 'value' => SafeDisplay::date($subscription->ends_at, 'No end date recorded')],
            ] : [],
            'invoiceSummary' => $invoices->map(function ($invoice) {
                return [
                    'title' => $invoice->number,
                    'status' => SafeDisplay::statusMeta((string) $invoice->status),
                    'details' => [
                        ['label' => 'Total', 'value' => strtoupper((string) $invoice->currency).' '.number_format((float) $invoice->total, 2)],
                        ['label' => 'Issued', 'value' => SafeDisplay::date($invoice->issued_at, 'No issue date')],
                        ['label' => 'Paid', 'value' => SafeDisplay::date($invoice->paid_at, 'Not paid')],
                    ],
                ];
            })->all(),
            'invoiceDetails' => $invoices->map(function ($invoice) use ($account) {
                return [
                    'title' => $invoice->number,
                    'status' => SafeDisplay::statusMeta((string) $invoice->status),
                    'details' => [
                        ['label' => 'subtotal', 'value' => strtoupper((string) $invoice->currency).' '.number_format((float) $invoice->subtotal, 2)],
                        ['label' => 'total', 'value' => strtoupper((string) $invoice->currency).' '.number_format((float) $invoice->total, 2)],
                        ['label' => 'Account '.($account?->name ?? 'Unassigned'), 'value' => $account?->slug ?? 'No slug recorded'],
                        ['label' => 'Plan', 'value' => $invoice->subscription?->subscriptionPlan?->name ?? 'No linked plan'],
                        ['label' => 'Issued', 'value' => SafeDisplay::dateTime($invoice->issued_at, 'No issue date')],
                        ['label' => 'Paid', 'value' => SafeDisplay::dateTime($invoice->paid_at, 'Not paid')],
                        ['label' => 'Created', 'value' => SafeDisplay::dateTime($invoice->created_at)],
                    ],
                ];
            })->all(),
            'invoiceSummaryMeta' => RecordWindow::meta($invoices, 'invoice summary rows'),
            'invoiceDetailsMeta' => RecordWindow::meta($invoices, 'invoice records'),
            'summary' => [
                'headline' => $invoices->isNotEmpty()
                    ? 'Your invoice history is visible and up to date for this workspace.'
                    : 'No invoices are showing for this workspace yet.',
                'details' => $invoices->isNotEmpty()
                    ? 'Review current billing records, invoice totals, and payment timing without leaving the workspace.'
                    : 'Invoices will appear here after the first billing event is recorded for this workspace.',
            ],
            'hasSubscriptionData' => (bool) $subscription,
            'hasInvoiceData' => $invoices->isNotEmpty(),
        ];
    }
}
