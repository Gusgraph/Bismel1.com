<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Admin/SystemController.php
// ======================================================

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\Enums\SystemStatusLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;
use App\Models\SystemSetting;
use App\Support\Admin\Bismel1AdminOperationsService;
use App\Support\Admin\PlatformSummaryService;
use App\Support\Navigation\AdminNavigation;
use App\Support\ViewData\AdminAlertData;
use App\Support\ViewData\AdminSystemPageData;
use Illuminate\Support\Facades\Storage;

class SystemController extends Controller
{
    public function index(
        PlatformSummaryService $platformSummaryService,
        Bismel1AdminOperationsService $bismel1AdminOperationsService
    )
    {
        $snapshot = $platformSummaryService->snapshot();
        $operationsOverview = $bismel1AdminOperationsService->platformOverview();
        $data = AdminSystemPageData::make(
            $snapshot['system_setting'],
            $platformSummaryService->systemPlatformState($snapshot)
        );
        $alertData = AdminAlertData::make();

        return view('admin.system.index', [
            'navItems' => AdminNavigation::items(),
            'page' => $data['page'],
            'healthSummary' => $data['healthSummary'],
            'auditOverview' => $data['auditOverview'],
            'platformState' => $data['platformState'],
            'statusLevels' => $data['statusLevels'],
            'summary' => $data['summary'],
            'operationsOverview' => $operationsOverview,
            'alerts' => $alertData['alerts'],
            'notices' => $alertData['notices'],
            'currentSettings' => $data['currentSettings'],
            'hasOperationsData' => $operationsOverview['has_operations_data'] ?? false,
        ]);
    }

    public function edit()
    {
        $systemSetting = SystemSetting::query()->first();

        return view('admin.system.edit', [
            'navItems' => AdminNavigation::items(),
            'page' => [
                'title' => 'Edit Platform Settings',
                'subtitle' => 'Update the single local system settings record with a narrow persisted admin flow.',
            ],
            'form' => [
                'runtime_mode' => $systemSetting?->runtime_mode ?? 'local',
                'review_channel' => $systemSetting?->review_channel ?? 'manual',
                'status_level' => $systemSetting?->status_level ?? SystemStatusLevel::Medium->value,
                'header_image_path' => $systemSetting?->header_image_path,
                'header_image_preview' => $systemSetting?->header_image_path ? Storage::url($systemSetting->header_image_path) : null,
            ],
        ]);
    }

    public function update(UpdateSystemSettingsRequest $request)
    {
        $validated = $request->validated();

        $systemSetting = SystemSetting::query()->first() ?? new SystemSetting();
        $systemSetting->runtime_mode = $validated['runtime_mode'];
        $systemSetting->review_channel = $validated['review_channel'];
        $systemSetting->status_level = $validated['status_level'];

        if ($request->boolean('remove_header_image') && $systemSetting->header_image_path) {
            Storage::disk('public')->delete($systemSetting->header_image_path);
            $systemSetting->header_image_path = null;
        }

        if ($request->file('header_image')) {
            $newPath = $request->file('header_image')->storePublicly('header-images', 'public');

            if ($systemSetting->header_image_path) {
                Storage::disk('public')->delete($systemSetting->header_image_path);
            }

            $systemSetting->header_image_path = $newPath;
        }

        $systemSetting->save();

        return redirect()
            ->route('admin.system.index')
            ->with('status', 'Platform settings were saved to the local admin system record.');
    }
}
