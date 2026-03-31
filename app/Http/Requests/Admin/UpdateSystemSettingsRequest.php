<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Requests/Admin/UpdateSystemSettingsRequest.php
// ======================================================

namespace App\Http\Requests\Admin;

use App\Domain\Audit\Enums\SystemStatusLevel;

class UpdateSystemSettingsRequest extends BaseAdminRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'runtime_mode' => ['required', 'string', 'max:50'],
            'review_channel' => ['required', 'string', 'max:50'],
            'status_level' => ['required', 'string', 'in:'.implode(',', array_keys(SystemStatusLevel::labels()))],
            'header_image' => ['nullable', 'image', 'max:2048'],
            'remove_header_image' => ['sometimes', 'boolean'],
        ];
    }
}
