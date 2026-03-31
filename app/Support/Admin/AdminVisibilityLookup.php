<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Admin/AdminVisibilityLookup.php
// ======================================================

namespace App\Support\Admin;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\ApiLicense;
use App\Models\AuditLog;
use App\Support\Display\RecordWindow;
use Illuminate\Support\Collection;

class AdminVisibilityLookup
{
    public function accounts(?int $limit = null): Collection
    {
        return Account::query()
            ->with(['owner', 'users', 'subscriptions.subscriptionPlan', 'apiLicenses'])
            ->orderBy('name')
            ->limit($limit ?? RecordWindow::limit())
            ->get();
    }

    public function accountDetail(Account $account): Account
    {
        return Account::query()
            ->with([
                'owner',
                'users',
                'subscriptions.subscriptionPlan',
                'brokerConnections.brokerCredentials',
                'apiLicenses.apiKeys',
                'auditLogs',
                'activityLogs',
            ])
            ->findOrFail($account->getKey());
    }

    public function licenses(?int $limit = null): Collection
    {
        return ApiLicense::query()
            ->with(['account', 'apiKeys'])
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->limit($limit ?? RecordWindow::limit())
            ->get();
    }

    public function activityLogs(?int $limit = null): Collection
    {
        return ActivityLog::query()
            ->with(['account', 'user'])
            ->latest()
            ->limit($limit ?? RecordWindow::limit())
            ->get();
    }

    public function auditLogs(?int $limit = null): Collection
    {
        return AuditLog::query()
            ->with(['account', 'user'])
            ->latest()
            ->limit($limit ?? RecordWindow::limit())
            ->get();
    }
}
