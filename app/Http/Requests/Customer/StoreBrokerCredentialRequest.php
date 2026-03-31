<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Requests/Customer/StoreBrokerCredentialRequest.php
// ======================================================

namespace App\Http\Requests\Customer;

use App\Domain\Broker\Enums\BrokerProvider;
use Illuminate\Validation\Rule;

class StoreBrokerCredentialRequest extends BaseCustomerRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', Rule::in(array_keys(BrokerProvider::labels()))],
            'account_label' => ['required', 'string', 'max:100'],
            'access_mode' => ['required', 'string', Rule::in(['read_only', 'trade_disabled'])],
            'environment' => ['required', 'string', Rule::in(['paper', 'live'])],
            'market_data_feed' => ['required', 'string', Rule::in(['iex', 'sip'])],
            'access_key_id' => ['required', 'string', 'max:120'],
            'access_secret' => ['required', 'string', 'max:255'],
        ];
    }
}
