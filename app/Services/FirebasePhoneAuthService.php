<?php

namespace App\Services;

use App\Exceptions\FirebasePhoneAuthException;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;

class FirebasePhoneAuthService
{
    public function verifyIdTokenAndGetPhone(string $idToken): string
    {
        $serviceAccountPath = $this->resolveServiceAccountPath();

        if (! $serviceAccountPath) {
            throw new FirebasePhoneAuthException(
                'Firebase phone authentication is not configured.',
                503
            );
        }

        try {
            $auth = (new Factory)
                ->withServiceAccount($serviceAccountPath)
                ->createAuth();

            $verifiedToken = $auth->verifyIdToken($idToken);
            $phoneNumber = $verifiedToken->claims()->get('phone_number');

            if (empty($phoneNumber)) {
                throw new FirebasePhoneAuthException(
                    'Firebase token does not contain a verified phone number.',
                    422
                );
            }

            return $phoneNumber;
        } catch (FirebasePhoneAuthException $e) {
            throw $e;
        } catch (FailedToVerifyToken $e) {
            throw new FirebasePhoneAuthException('Invalid or expired Firebase token.', 401);
        } catch (\Throwable $e) {
            Log::error('Firebase phone auth verification failed: '.$e->getMessage());

            throw new FirebasePhoneAuthException('Invalid or expired Firebase token.', 401);
        }
    }

    public function isConfigured(): bool
    {
        return $this->resolveServiceAccountPath() !== null;
    }

    private function resolveServiceAccountPath(): ?string
    {
        $path = config('firebase.service_account');

        if (empty($path)) {
            return null;
        }

        foreach ([$path, base_path($path), storage_path($path)] as $candidate) {
            if (is_string($candidate) && file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
