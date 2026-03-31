<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Unit/FirestoreBridgeSmokeCheckTest.php
// ======================================================

namespace Tests\Unit;

use App\Support\Firestore\FirestoreBridge;
use Tests\TestCase;

class FirestoreBridgeSmokeCheckTest extends TestCase
{
    public function test_smoke_check_reports_safe_structure(): void
    {
        config()->set('firestore.enabled', true);
        config()->set('firestore.project_id', 'demo-project');
        config()->set('firestore.credentials', null);

        $result = app(FirestoreBridge::class)->smokeCheck();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('details', $result);
        $this->assertContains(
            $result['status'],
            ['missing_client', 'missing_grpc', 'missing_credentials', 'ready'],
        );
    }
}
