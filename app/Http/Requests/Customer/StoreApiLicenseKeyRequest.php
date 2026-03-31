<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Requests/Customer/StoreApiLicenseKeyRequest.php
// ======================================================

namespace App\Http\Requests\Customer;

class StoreApiLicenseKeyRequest extends BaseCustomerRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'license_name' => ['required', 'string', 'max:120'],
            'key_name' => ['required', 'string', 'max:120'],
            'token_value' => ['required', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}
