<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Requests/Admin/RunBismel1OperatorActionRequest.php
// ======================================================

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class RunBismel1OperatorActionRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'action' => [
                'required',
                'string',
                Rule::in([
                    'scan_now',
                    'sync_broker_now',
                    'reconcile_positions_now',
                    'recheck_runtime_readiness_now',
                    'pause_automation',
                    'resume_automation',
                ]),
            ],
            'confirm_action' => [
                'nullable',
                'string',
            ],
        ];
    }
}
