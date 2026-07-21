<?php

namespace App\Notifications\Channels;

use App\Integrations\KudiSms\Entities\SendSms;
use App\Integrations\KudiSms\KudiSmsConnector;
use App\Settings\IntegrationSettings;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class KudiSmsChannel
{
    public function __construct(
        private readonly KudiSmsConnector $connector,
        private readonly IntegrationSettings $integrationSettings,
    ) {}

    /**
     * Send the given notification via SMS.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $phone = $this->resolvePhone($notifiable);

        if (blank($phone)) {
            return;
        }

        $message = $notification->toSms($notifiable);

        if (blank($message)) {
            return;
        }

        $senderId = $this->integrationSettings->kudisms_sender_id ?? config('services.kudisms.sender_id', 'INFO');

        try {
            $this->connector->sms()->send(new SendSms(
                senderId: $senderId,
                recipients: is_array($phone) ? $phone : [$phone],
                message: $message,
            ));
        } catch (\Throwable $e) {
            Log::error('KudiSMS notification failed', [
                'notifiable' => get_class($notifiable),
                'notification' => get_class($notification),
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolvePhone(object $notifiable): ?string
    {
        if (method_exists($notifiable, 'routeNotificationForKudisms')) {
            return $notifiable->routeNotificationForKudisms();
        }

        return $notifiable->phone_number ?? null;
    }
}
