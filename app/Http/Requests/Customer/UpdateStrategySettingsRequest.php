<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Requests/Customer/UpdateStrategySettingsRequest.php
// ======================================================

namespace App\Http\Requests\Customer;

use Illuminate\Validation\Rule;

class UpdateStrategySettingsRequest extends BaseCustomerRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'mode' => ['required', 'string', Rule::in(['review_first', 'assist_only', 'scanner_ready'])],
            'timeframe' => ['required', 'string', Rule::in(['intraday', 'swing', 'mixed'])],
            'symbol_scope' => ['required', 'string', Rule::in(['focused', 'watchlist', 'account'])],
            'style' => ['required', 'string', Rule::in(['conservative', 'balanced', 'aggressive'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
