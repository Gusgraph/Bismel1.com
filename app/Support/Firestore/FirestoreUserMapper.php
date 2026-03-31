<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Firestore/FirestoreUserMapper.php
// ======================================================

namespace App\Support\Firestore;

use App\Models\Account;
use App\Models\User;

class FirestoreUserMapper
{
    public function resolveUid(User $user): ?string
    {
        $firestoreUid = $user->firestore_uid;

        if (! is_string($firestoreUid)) {
            return null;
        }

        $firestoreUid = trim($firestoreUid);

        if ($firestoreUid === '') {
            return null;
        }

        return $firestoreUid;
    }

    public function describe(User $user, ?Account $account = null): array
    {
        $uid = $this->resolveUid($user);
        $userCollection = trim((string) config('firestore.user_collection', 'users'), '/');
        $documentPath = $uid ? $userCollection.'/'.$uid : null;
        $childCollections = collect(config('firestore.child_collections', []))
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->map(fn (string $collection) => trim($collection, '/'));

        return [
            'uid' => $uid,
            'source' => 'firestore_uid',
            'document_path' => $documentPath,
            'account_context' => [
                'account_id' => $account?->getKey(),
                'account_slug' => $account?->slug,
                'owner_user_id' => $account?->owner_user_id,
            ],
            'related_paths' => $documentPath
                ? $childCollections
                    ->mapWithKeys(fn (string $collection, string $key) => [$key => $documentPath.'/'.$collection])
                    ->all()
                : [],
        ];
    }
}
