<?php

namespace App\Http\Controllers\Customer\Concerns;

use App\Models\Account;
use App\Models\User;
use App\Support\Firestore\FirestoreBridge;
use Throwable;

trait HandlesFirestoreSummary
{
    protected function safeFirestoreReadSummary(
        FirestoreBridge $firestoreBridge,
        ?User $user,
        ?Account $account = null
    ): array {
        if (! $user) {
            return [
                'status' => 'not_mapped',
                'headline' => 'This user is not mapped to Firestore yet.',
                'details' => 'No signed-in user is available for Firestore summary.',
                'items' => [],
            ];
        }

        try {
            return $firestoreBridge->readUserIntegrationSummary($user, $account);
        } catch (Throwable $exception) {
            report($exception);

            return [
                'status' => 'error',
                'headline' => 'Runtime-support signals are currently unavailable.',
                'details' => $exception->getMessage(),
                'items' => [
                    ['label' => 'Firestore Runtime Mapping', 'value' => 'Unavailable'],
                    ['label' => 'Runtime User', 'value' => $user->firestore_uid ?: 'No firestore_uid'],
                    ['label' => 'Runtime Document', 'value' => 'Not ready'],
                    ['label' => 'Source', 'value' => 'Unknown'],
                    ['label' => 'User Status', 'value' => 'Unknown'],
                    ['label' => 'Bot Runtime Records', 'value' => 'Unavailable'],
                    ['label' => 'Live Runtime Records', 'value' => 'Unavailable'],
                ],
            ];
        }
    }
}
