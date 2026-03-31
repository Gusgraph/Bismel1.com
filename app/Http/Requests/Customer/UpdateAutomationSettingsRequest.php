<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Requests/Customer/UpdateAutomationSettingsRequest.php
// ======================================================

namespace App\Http\Requests\Customer;

use Illuminate\Validation\Rule;

class UpdateAutomationSettingsRequest extends BaseCustomerRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', Rule::in(['draft', 'review', 'armed'])],
            'risk_level' => ['required', 'string', Rule::in(['conservative', 'balanced', 'aggressive'])],
            'ai_enabled' => ['nullable', 'boolean'],
            'action_mode' => ['nullable', 'string', Rule::in(['save', 'start', 'stop'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ai_enabled' => $this->boolean('ai_enabled'),
            'action_mode' => $this->input('action_mode', 'save'),
        ]);
    }
}
