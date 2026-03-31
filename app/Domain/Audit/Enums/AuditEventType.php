<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Domain/Audit/Enums/AuditEventType.php
// =====================================================

namespace App\Domain\Audit\Enums;

enum AuditEventType: string
{
    case AccountReviewed = 'account_reviewed';
    case BillingViewed = 'billing_viewed';
    case BrokerChecked = 'broker_checked';
    case LicenseChecked = 'license_checked';

    public static function labels(): array
    {
        return [
            self::AccountReviewed->value => 'Account Reviewed',
            self::BillingViewed->value => 'Billing Viewed',
            self::BrokerChecked->value => 'Broker Checked',
            self::LicenseChecked->value => 'License Checked',
        ];
    }
}
