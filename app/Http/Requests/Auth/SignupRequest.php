<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Requests/Auth/SignupRequest.php
// ======================================================

namespace App\Http\Requests\Auth;

use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SignupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $selectedPlanCode = (string) $this->input('selected_plan_code', '');
        $selectedPlan = $selectedPlanCode !== ''
            ? SubscriptionPlan::query()->where('code', $selectedPlanCode)->first()
            : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'workspace_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'selected_plan_code' => ['nullable', 'string'],
            'selected_base_plan_code' => [
                $selectedPlan?->plan_type === 'addon' ? 'required' : 'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter your name.',
            'workspace_name.required' => 'Enter your workspace name.',
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'This email is already in the system. Login instead or use another email.',
            'password.required' => 'Create a password.',
            'password.confirmed' => 'Password confirmation does not match.',
            'selected_base_plan_code.required' => 'Choose a base plan before adding this add-on.',
        ];
    }
}
