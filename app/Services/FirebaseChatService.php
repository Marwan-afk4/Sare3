<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseChatService
{
    private $database;
    private $firebaseUrl;
    private $firebaseSecret;

    public function __construct()
    {
        $this->firebaseUrl = config('services.firebase.database_url');
        $this->firebaseSecret = config('services.firebase.secret');
        
        try {
            $serviceAccountPath = config('services.firebase.service_account');
            
            if ($serviceAccountPath && file_exists(base_path($serviceAccountPath))) {
                // Use Firebase Admin SDK with service account
                $factory = (new Factory)->withServiceAccount(base_path($serviceAccountPath));
                
                if ($this->firebaseUrl) {
                    $factory = $factory->withDatabaseUri($this->firebaseUrl);
                }
                
                $this->database = $factory->createDatabase();
                Log::info('Firebase initialized with service account');
            } else {
                Log::warning('Firebase service account not found, falling back to HTTP requests');
                $this->database = null;
            }
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
            $this->database = null;
        }
    }

    public function sendMessage($roomId, $messageData)
    {
        try {
            if ($this->database) {
                // Use Firebase Admin SDK
                $reference = $this->database->getReference("chats/{$roomId}/messages");
                $newMessageRef = $reference->push($messageData);
                return $newMessageRef->getKey();
            } else {
                // Fallback to HTTP requests
                $url = "{$this->firebaseUrl}/chats/{$roomId}/messages.json";
                
                if ($this->firebaseSecret) {
                    $url .= "?auth={$this->firebaseSecret}";
                }

                $response = Http::post($url, $messageData);

                if ($response->successful()) {
                    $responseData = $response->json();
                    return $responseData['name'] ?? null; // Firebase returns the generated key
                }

                Log::error('Firebase message send failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return null;
            }
        } catch (\Exception $e) {
            Log::error('Firebase service error: ' . $e->getMessage());
            return null;
        }
    }

    public function getMessages($roomId, $limit = 50)
    {
        try {
            Log::info('FirebaseChatService: getMessages called', [
                'roomId' => $roomId,
                'limit' => $limit,
                'hasDatabaseConnection' => !is_null($this->database)
            ]);

            if ($this->database) {
                // Use Firebase Admin SDK
                $reference = $this->database->getReference("chats/{$roomId}/messages");
                $query = $reference->orderByChild('timestamp')->limitToLast($limit);
                $snapshot = $query->getSnapshot();
                $result = $snapshot->getValue() ?? [];
                
                Log::info('Firebase Admin SDK result', [
                    'roomId' => $roomId,
                    'result' => $result,
                    'resultType' => gettype($result),
                    'count' => is_array($result) ? count($result) : 0
                ]);
                
                return $result;
            } else {
                // Fallback to HTTP requests
                $url = "{$this->firebaseUrl}/chats/{$roomId}/messages.json";
                
                $params = [];
                if ($this->firebaseSecret) {
                    $params['auth'] = $this->firebaseSecret;
                }
                $params['orderBy'] = '"timestamp"';
                $params['limitToLast'] = $limit;

                Log::info('Firebase HTTP request', [
                    'url' => $url,
                    'params' => $params
                ]);

                $response = Http::get($url, $params);

                if ($response->successful()) {
                    $result = $response->json() ?? [];
                    Log::info('Firebase HTTP result', [
                        'roomId' => $roomId,
                        'result' => $result,
                        'resultType' => gettype($result),
                        'count' => is_array($result) ? count($result) : 0
                    ]);
                    return $result;
                } else {
                    Log::error('Firebase HTTP request failed', [
                        'url' => $url,
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }

                return [];
            }
        } catch (\Exception $e) {
            Log::error('Firebase get messages error: ' . $e->getMessage(), [
                'roomId' => $roomId,
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function createSupportRequest($userId, $userType)
    {
        try {
            if ($this->database) {
                // Use Firebase Admin SDK
                $data = [
                    'user_id' => $userId,
                    'user_type' => $userType,
                    'timestamp' => now()->timestamp * 1000, // Firebase expects milliseconds
                    'status' => 'pending'
                ];
                
                $reference = $this->database->getReference("support_requests/{$userType}_{$userId}");
                $reference->set($data);
                return true;
            } else {
                // Fallback to HTTP requests
                $url = "{$this->firebaseUrl}/support_requests.json";
                
                if ($this->firebaseSecret) {
                    $url .= "?auth={$this->firebaseSecret}";
                }

                $data = [
                    "{$userType}_{$userId}" => [
                        'user_id' => $userId,
                        'user_type' => $userType,
                        'timestamp' => now()->timestamp * 1000, // Firebase expects milliseconds
                        'status' => 'pending'
                    ]
                ];

                $response = Http::patch($url, $data);

                return $response->successful();
            }
        } catch (\Exception $e) {
            Log::error('Firebase create support request error: ' . $e->getMessage());
            return false;
        }
    }

    public function removeSupportRequest($userId, $userType)
    {
        try {
            if ($this->database) {
                // Use Firebase Admin SDK
                $reference = $this->database->getReference("support_requests/{$userType}_{$userId}");
                $reference->remove();
                return true;
            } else {
                // Fallback to HTTP requests
                $url = "{$this->firebaseUrl}/support_requests/{$userType}_{$userId}.json";
                
                if ($this->firebaseSecret) {
                    $url .= "?auth={$this->firebaseSecret}";
                }

                $response = Http::delete($url);

                return $response->successful();
            }
        } catch (\Exception $e) {
            Log::error('Firebase remove support request error: ' . $e->getMessage());
            return false;
        }
    }

    public function getAllSupportRequests()
    {
        try {
            if ($this->database) {
                // Use Firebase Admin SDK
                $reference = $this->database->getReference('support_requests');
                $snapshot = $reference->getSnapshot();
                return $snapshot->getValue() ?? [];
            } else {
                // Fallback to HTTP requests
                $url = "{$this->firebaseUrl}/support_requests.json";
                
                if ($this->firebaseSecret) {
                    $url .= "?auth={$this->firebaseSecret}";
                }

                $response = Http::get($url);

                if ($response->successful()) {
                    return $response->json() ?? [];
                }

                return [];
            }
        } catch (\Exception $e) {
            Log::error('Firebase get support requests error: ' . $e->getMessage());
            return [];
        }
    }

    public function getAllChats()
    {
        try {
            if ($this->database) {
                // Use Firebase Admin SDK
                Log::info('Fetching chats using Firebase Admin SDK');
                $reference = $this->database->getReference('chats');
                $snapshot = $reference->getSnapshot();
                $chats = $snapshot->getValue() ?? [];
                
                Log::info('Firebase Admin SDK response', [
                    'chats_count' => count($chats),
                    'chats_data' => $chats
                ]);
                
                return $chats;
            } else {
                // Fallback to HTTP requests
                if (empty($this->firebaseUrl)) {
                    Log::error('Firebase URL not configured');
                    return [];
                }

                $url = "{$this->firebaseUrl}/chats.json";
                
                if ($this->firebaseSecret) {
                    $url .= "?auth={$this->firebaseSecret}";
                }

                Log::info('Fetching chats from Firebase via HTTP', ['url' => $url]);

                $response = Http::get($url);

                Log::info('Firebase HTTP response', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                if ($response->successful()) {
                    return $response->json() ?? [];
                }

                Log::error('Firebase HTTP request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [];
            }
        } catch (\Exception $e) {
            Log::error('Firebase get chats error: ' . $e->getMessage());
            return [];
        }
    }

    public function getChatByRoomId($roomId)
    {
        try {
            if ($this->database) {
                // Use Firebase Admin SDK
                $reference = $this->database->getReference("chats/{$roomId}");
                $snapshot = $reference->getSnapshot();
                return $snapshot->getValue();
            } else {
                // Fallback to HTTP requests
                $url = "{$this->firebaseUrl}/chats/{$roomId}.json";
                
                if ($this->firebaseSecret) {
                    $url .= "?auth={$this->firebaseSecret}";
                }

                $response = Http::get($url);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            }
        } catch (\Exception $e) {
            Log::error('Firebase get chat error: ' . $e->getMessage());
            return null;
        }
    }
}