<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Firestore/FirestoreBridge.php
// ======================================================

namespace App\Support\Firestore;

use App\Models\Account;
use App\Models\User;
use Google\ApiCore\ApiException;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Cloud\Firestore\V1\Client\FirestoreClient as GapicFirestoreClient;
use Google\Cloud\Firestore\V1\GetDocumentRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class FirestoreBridge
{
    public function __construct(
        protected ?FirestoreUserMapper $userMapper = null,
    ) {
        $this->userMapper ??= new FirestoreUserMapper();
    }

    public function status(): array
    {
        $enabled = (bool) config('firestore.enabled', false);
        $projectId = (string) config('firestore.project_id', '');
        $credentials = $this->credentialsPath();
        $clientAvailable = class_exists(GapicFirestoreClient::class);
        $grpcAvailable = extension_loaded('grpc');

        return [
            'enabled' => $enabled,
            'role' => (string) config('firestore.role', 'runtime_support'),
            'client_available' => $clientAvailable,
            'grpc_available' => $grpcAvailable,
            'project_id_configured' => $projectId !== '',
            'credentials_configured' => $credentials !== null,
            'ready' => $enabled && $clientAvailable && $grpcAvailable && $projectId !== '' && $credentials !== null,
        ];
    }

    public function smokeCheck(): array
    {
        $status = $this->status();

        if (! $status['enabled']) {
            return [
                'status' => 'disabled',
                'message' => 'Firestore integration is disabled in configuration.',
                'details' => $status,
            ];
        }

        if (! $status['client_available']) {
            return [
                'status' => 'missing_client',
                'message' => 'The Google Cloud Firestore PHP client package is not installed.',
                'details' => $status,
            ];
        }

        if (! $status['grpc_available']) {
            return [
                'status' => 'missing_grpc',
                'message' => 'The PHP grpc extension is required by the Firestore client.',
                'details' => $status,
            ];
        }

        if (! $status['project_id_configured']) {
            return [
                'status' => 'misconfigured',
                'message' => 'Firestore project configuration is incomplete.',
                'details' => $status,
            ];
        }

        if (! $status['credentials_configured']) {
            return [
                'status' => 'missing_credentials',
                'message' => 'Set FIRESTORE_CREDENTIALS or GOOGLE_APPLICATION_CREDENTIALS to a readable service account JSON path.',
                'details' => $status,
            ];
        }

        $this->newClient();

        return [
            'status' => 'ready',
            'message' => 'Firestore client instantiation passed the safe smoke check.',
            'details' => $status,
        ];
    }

    public function userReference(User $user, ?Account $account = null): array
    {
        return $this->userMapper->describe($user, $account);
    }

    public function readUserDocument(User $user, ?Account $account = null): array
    {
        $status = $this->status();
        $reference = $this->userReference($user, $account);

        if (! $reference['uid']) {
            return [
                'status' => 'not_mapped',
                'message' => 'The current Laravel user does not have a mapped firestore_uid yet.',
                'reference' => $reference,
                'data' => null,
            ];
        }

        if (! $status['enabled']) {
            return [
                'status' => 'disabled',
                'message' => 'Firestore integration is disabled in configuration.',
                'reference' => $reference,
                'data' => null,
            ];
        }

        if (! $status['client_available']) {
            return [
                'status' => 'missing_client',
                'message' => 'The Google Cloud Firestore PHP client is not installed yet.',
                'reference' => $reference,
                'data' => null,
            ];
        }

        if (! $status['project_id_configured']) {
            return [
                'status' => 'misconfigured',
                'message' => 'Firestore project configuration is incomplete.',
                'reference' => $reference,
                'data' => null,
            ];
        }

        try {
            $document = $this->newClient()->getDocument(
                (new GetDocumentRequest())
                    ->setName($this->documentName($reference['document_path']))
            );

            return [
                'status' => 'ok',
                'message' => 'Firestore user document was read successfully.',
                'reference' => $reference,
                'data' => $this->decodeDocumentFields(
                    iterator_to_array($document->getFields()->getIterator())
                ),
            ];
        } catch (ApiException $exception) {
            if ($this->isDocumentNotFound($exception)) {
                return [
                    'status' => 'not_found',
                    'message' => 'No Firestore user document exists at the mapped path yet.',
                    'reference' => $reference,
                    'data' => null,
                ];
            }

            return [
                'status' => 'error',
                'message' => $exception->getMessage(),
                'reference' => $reference,
                'data' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'error',
                'message' => $exception->getMessage(),
                'reference' => $reference,
                'data' => null,
            ];
        }
    }

    public function readBotSummary(User $user, ?Account $account = null): array
    {
        return $this->readUserIntegrationSummary($user, $account);
    }

    public function readUserIntegrationSummary(User $user, ?Account $account = null): array
    {
        $userDocument = $this->readUserDocument($user, $account);

        if ($userDocument['status'] !== 'ok') {
            return $this->buildUserIntegrationState($userDocument, []);
        }

        $reference = $userDocument['reference'];
        $botCollection = $this->readCollectionSummary($reference['related_paths']['bots'] ?? null);
        $runtimeCollection = $this->readCollectionSummary($reference['related_paths']['runtimes'] ?? null);

        return $this->buildUserIntegrationState($userDocument, [
            'bot_count' => $botCollection['count'] ?? null,
            'runtime_count' => $runtimeCollection['count'] ?? null,
        ]);
    }

    public function readMappedUsersSummary(?User $user = null, ?Account $account = null): array
    {
        $status = $this->status();
        $mappedUsers = User::query()
            ->whereNotNull('firestore_uid')
            ->where('firestore_uid', '!=', '')
            ->count();
        $currentUserSummary = $user ? $this->readUserIntegrationSummary($user, $account) : null;

        return [
            'status' => $status['ready'] ? 'ok' : 'error',
            'headline' => $status['ready']
                ? 'Firestore runtime-support signals are available for review.'
                : 'Firestore runtime-support signals are currently unavailable.',
            'details' => $status['ready']
                ? 'This block shows how many Laravel users are explicitly mapped to runtime-only Firestore records and whether the current signed-in admin has a readable live-state summary.'
                : 'Firestore client or credential readiness is incomplete for runtime-support reporting.',
            'items' => [
                ['label' => 'Mapped Laravel Users', 'value' => (string) $mappedUsers],
                ['label' => 'Runtime Layer Ready', 'value' => $status['ready'] ? 'Yes' : 'No'],
                ['label' => 'Current Admin Mapping', 'value' => $currentUserSummary ? $this->stateLabel($currentUserSummary['status']) : 'Not checked'],
                ['label' => 'Current Admin User', 'value' => $currentUserSummary['reference']['uid'] ?? 'No firestore_uid'],
            ],
        ];
    }

    protected function newClient(): object
    {
        if (! class_exists(GapicFirestoreClient::class)) {
            throw new RuntimeException('The Google Cloud Firestore PHP client is not installed.');
        }

        return new GapicFirestoreClient([
            'apiEndpoint' => 'firestore.googleapis.com:443',
            'credentials' => $this->credentialsPath()
                ?? throw new RuntimeException('Set FIRESTORE_CREDENTIALS or GOOGLE_APPLICATION_CREDENTIALS to a readable service account JSON path.'),
            'transport' => 'rest',
        ]);
    }

    protected function credentialsPath(): ?string
    {
        $credentials = config('firestore.credentials');

        if (! is_string($credentials)) {
            return null;
        }

        $credentials = trim($credentials);

        if ($credentials === '') {
            return null;
        }

        if (! is_file($credentials) || ! is_readable($credentials)) {
            throw new RuntimeException('The configured Firestore credentials file is missing or not readable: '.$credentials);
        }

        return $credentials;
    }

    protected function documentName(string $documentPath): string
    {
        return sprintf(
            'projects/%s/databases/%s/documents/%s',
            (string) config('firestore.project_id'),
            (string) config('firestore.database', '(default)'),
            trim($documentPath, '/')
        );
    }

    protected function decodeDocumentFields(array $fields): array
    {
        $decoded = [];

        foreach ($fields as $key => $value) {
            $decoded[$key] = $this->decodeFieldValue($value);
        }

        return $decoded;
    }

    protected function decodeFieldValue(object $value): mixed
    {
        return match ($value->getValueType()) {
            'null_value' => null,
            'boolean_value' => $value->getBooleanValue(),
            'integer_value' => (int) $value->getIntegerValue(),
            'double_value' => $value->getDoubleValue(),
            'timestamp_value' => $value->getTimestampValue()?->toDateTime()?->format(DATE_ATOM),
            'string_value' => $value->getStringValue(),
            'bytes_value' => $value->getBytesValue(),
            'reference_value' => $value->getReferenceValue(),
            'geo_point_value' => [
                'latitude' => $value->getGeoPointValue()?->getLatitude(),
                'longitude' => $value->getGeoPointValue()?->getLongitude(),
            ],
            'array_value' => $this->decodeArrayValue($value),
            'map_value' => $this->decodeMapValue($value),
            default => null,
        };
    }

    protected function decodeArrayValue(object $value): array
    {
        $decoded = [];

        foreach ($value->getArrayValue()?->getValues() ?? [] as $item) {
            $decoded[] = $this->decodeFieldValue($item);
        }

        return $decoded;
    }

    protected function decodeMapValue(object $value): array
    {
        return $this->decodeDocumentFields(
            iterator_to_array($value->getMapValue()?->getFields()?->getIterator() ?? new \ArrayIterator([]))
        );
    }

    protected function isDocumentNotFound(ApiException $exception): bool
    {
        return (int) $exception->getCode() === 404
            || (int) $exception->getCode() === 5
            || str_contains($exception->getStatus(), 'NOT_FOUND');
    }

    protected function firestoreAccessToken(): string
    {
        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/cloud-platform'],
            json_decode((string) file_get_contents($this->credentialsPath()), true, 512, JSON_THROW_ON_ERROR)
        );

        $token = $credentials->fetchAuthToken()['access_token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Unable to fetch a Firestore access token from the configured credentials.');
        }

        return $token;
    }

    protected function collectionUrl(string $collectionPath): string
    {
        return sprintf(
            'https://firestore.googleapis.com/v1/projects/%s/databases/%s/documents/%s?pageSize=5',
            (string) config('firestore.project_id'),
            (string) config('firestore.database', '(default)'),
            trim($collectionPath, '/')
        );
    }

    protected function readCollectionSummary(?string $collectionPath): array
    {
        if (! is_string($collectionPath) || trim($collectionPath) === '') {
            return ['status' => 'not_available', 'count' => null];
        }

        $response = Http::withToken($this->firestoreAccessToken())
            ->acceptJson()
            ->get($this->collectionUrl($collectionPath));

        if ($response->status() === 404) {
            return ['status' => 'not_found', 'count' => 0];
        }

        if ($response->failed()) {
            return ['status' => 'error', 'count' => null];
        }

        $documents = collect($response->json('documents', []))
            ->filter(fn ($document) => is_array($document))
            ->values();

        return ['status' => 'ok', 'count' => $documents->count()];
    }

    protected function buildUserIntegrationState(array $userDocument, array $collections): array
    {
        $status = $userDocument['status'];
        $reference = $userDocument['reference'] ?? [];
        $userData = is_array($userDocument['data'] ?? null) ? $userDocument['data'] : [];

        return [
            'status' => $status,
            'headline' => match ($status) {
                'ok' => 'Runtime-support signals are active for this user.',
                'not_mapped' => 'This user is not mapped to the runtime layer yet.',
                'not_found' => 'The mapped runtime user document was not found.',
                default => 'Runtime-support signals are currently unavailable.',
            },
            'details' => match ($status) {
                'ok' => 'Read-only Firestore runtime user, bot, and live-state signals are available for this mapped user.',
                'not_mapped' => 'Set a real firestore_uid on the Laravel user before runtime-backed signals can appear.',
                'not_found' => 'A firestore_uid is present, but no matching Firestore runtime /users/{uid} document currently exists.',
                default => $userDocument['message'] ?? 'Runtime-support signals are currently unavailable.',
            },
            'reference' => $reference,
            'items' => [
                ['label' => 'Firestore Runtime Mapping', 'value' => $this->stateLabel($status)],
                ['label' => 'Runtime User', 'value' => $reference['uid'] ?? 'No firestore_uid'],
                ['label' => 'Runtime Document', 'value' => $status === 'ok' ? 'Available' : ($status === 'not_found' ? 'Missing' : 'Not ready')],
                ['label' => 'Source', 'value' => (string) ($userData['source'] ?? 'Unknown')],
                ['label' => 'User Status', 'value' => $status === 'ok'
                    ? $this->stringifySummaryValue($userData['status'] ?? ($userData['is_active'] ?? 'Unknown'))
                    : 'Unknown'],
                ['label' => 'Bot Runtime Records', 'value' => $this->stringifySummaryValue($collections['bot_count'] ?? 'Unavailable')],
                ['label' => 'Live Runtime Records', 'value' => $this->stringifySummaryValue($collections['runtime_count'] ?? 'Unavailable')],
            ],
        ];
    }

    protected function stringifySummaryValue(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'Active' : 'Inactive',
            is_scalar($value) => (string) $value,
            default => 'Unknown',
        };
    }

    protected function stateLabel(string $status): string
    {
        return match ($status) {
            'ok' => 'Mapped',
            'not_mapped' => 'Unmapped',
            'not_found' => 'Missing',
            default => 'Unavailable',
        };
    }
}
