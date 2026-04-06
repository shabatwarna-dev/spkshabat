<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint'   => 'required|string',
            'public_key' => 'required|string',
            'auth_token' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['user_id' => auth()->id(), 'endpoint' => $request->endpoint],
            [
                'public_key'       => $request->public_key,
                'auth_token'       => $request->auth_token,
                'content_encoding' => $request->content_encoding ?? 'aesgcm',
            ]
        );

        return response()->json(['status' => 'subscribed']);
    }

    public function unsubscribe(Request $request)
    {
        PushSubscription::where('user_id', auth()->id())
                        ->where('endpoint', $request->endpoint)
                        ->delete();

        return response()->json(['status' => 'unsubscribed']);
    }

    public static function sendToUser(User $user, string $title, string $body, string $url = '/'): void
    {
        $subscriptions = $user->pushSubscriptions;

        \Log::info('[PUSH] sendToUser', ['user' => $user->name, 'subs' => $subscriptions->count()]);

        if ($subscriptions->isEmpty()) {
            \Log::warning('[PUSH] No subscriptions', ['user' => $user->name]);
            return;
        }

        $vapidPublicKey  = config('app.vapid_public_key');
        $vapidPrivateKey = config('app.vapid_private_key');

        \Log::info('[PUSH] VAPID', ['has_pub' => !empty($vapidPublicKey), 'has_priv' => !empty($vapidPrivateKey)]);

        if (!$vapidPublicKey || !$vapidPrivateKey) {
            \Log::error('[PUSH] VAPID keys missing');
            return;
        }

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
                    'endpoint' => $sub->endpoint,
                    'keys'     => [
                        'p256dh' => $sub->public_key,
                        'auth'   => $sub->auth_token,
                    ],
                ]);

                $webPush->queueNotification($subscription, $payload);
                $reports = $webPush->flush();

                foreach ($reports as $report) {
                    if ($report->isSuccess()) {
                        \Log::info('[PUSH] Success', ['endpoint' => substr($sub->endpoint, 0, 50)]);
                    } else {
                        \Log::error('[PUSH] Failed', [
                            'reason' => $report->getReason(),
                            'status' => $report->getResponse()?->getStatusCode(),
                        ]);
                        if (in_array($report->getResponse()?->getStatusCode(), [404, 410])) {
                            $sub->delete();
                        }
                    }
                }

            } catch (\Exception $e) {
                \Log::error('[PUSH] Exception', ['error' => $e->getMessage()]);
            }
        }
    }

    public static function sendToTeam(int $teamId, string $title, string $body, string $url = '/'): void
    {
        $users = User::whereHas('teams', fn($q) => $q->where('teams.id', $teamId))->get();
        \Log::info('[PUSH] sendToTeam', ['team_id' => $teamId, 'users' => $users->count()]);
        foreach ($users as $user) {
            static::sendToUser($user, $title, $body, $url);
        }
    }
}