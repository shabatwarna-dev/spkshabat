<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Subscribe user ke push notification.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint'   => 'required|string',
            'public_key' => 'required|string',
            'auth_token' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            [
                'user_id'  => auth()->id(),
                'endpoint' => $request->endpoint,
            ],
            [
                'public_key'       => $request->public_key,
                'auth_token'       => $request->auth_token,
                'content_encoding' => $request->content_encoding ?? 'aesgcm',
            ]
        );

        return response()->json(['status' => 'subscribed']);
    }

    /**
     * Unsubscribe user dari push notification.
     */
    public function unsubscribe(Request $request)
    {
        PushSubscription::where('user_id', auth()->id())
                        ->where('endpoint', $request->endpoint)
                        ->delete();

        return response()->json(['status' => 'unsubscribed']);
    }

    /**
     * Kirim push notification ke user tertentu atau semua user di tim.
     * Dipanggil dari event/observer, bukan langsung dari request.
     */
    public static function sendToUser(User $user, string $title, string $body, string $url = '/'): void
    {
        $subscriptions = $user->pushSubscriptions;
        if ($subscriptions->isEmpty()) return;

        $vapidPublicKey  = config('app.vapid_public_key');
        $vapidPrivateKey = config('app.vapid_private_key');

        if (!$vapidPublicKey || !$vapidPrivateKey) return;

        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'url'   => $url,
            'icon'  => '/icon-192.png',
            'badge' => '/icon-192.png',
        ]);

        foreach ($subscriptions as $sub) {
            try {
                $auth = [
                    'VAPID' => [
                        'subject'    => config('app.url'),
                        'publicKey'  => $vapidPublicKey,
                        'privateKey' => $vapidPrivateKey,
                    ],
                ];

                $webPush = new \Minishlink\WebPush\WebPush($auth);
                $subscription = \Minishlink\WebPush\Subscription::create([
                    'endpoint'        => $sub->endpoint,
                    'keys' => [
                        'p256dh' => $sub->public_key,
                        'auth'   => $sub->auth_token,
                    ],
                ]);

                $webPush->queueNotification($subscription, $payload);
                $webPush->flush();

            } catch (\Exception $e) {
                // Kalau endpoint tidak valid, hapus subscription
                if (str_contains($e->getMessage(), '410') || str_contains($e->getMessage(), '404')) {
                    $sub->delete();
                }
            }
        }
    }

    /**
     * Kirim notif ke semua user di tim tertentu.
     */
    public static function sendToTeam(int $teamId, string $title, string $body, string $url = '/'): void
    {
        $users = User::whereHas('teams', fn($q) => $q->where('teams.id', $teamId))->get();
        foreach ($users as $user) {
            static::sendToUser($user, $title, $body, $url);
        }
    }
}
