<?php

namespace App\Support;

use App\Mail\UserNotifyEmail;
use App\Models\User;
use App\Notification;
use Illuminate\Support\Facades\Mail;

class NotificationEmailSender
{
    public function send(Notification $notification): int
    {
        if ((int) $notification->is_email !== 1) {
            return 0;
        }

        $recipients = User::query();

        // Eine persönliche Nachricht darf nie zusätzlich an die Gruppe gehen.
        if (filled($notification->user_id)) {
            $recipients->whereKey($notification->user_id);
        } else {
            if (filled($notification->role)) {
                $recipients->where('role_id', $notification->role);
            }

            if (in_array((int) $notification->verified, [1, 2], true)) {
                $recipients->where('verified', (int) $notification->verified === 1 ? 1 : 0);
            }
        }

        $count = 0;
        $data = [
            'subject' => 'Neue Nachricht',
            'title' => $notification->title,
            'body' => $notification->description,
            'button_link' => url('/login'),
            'button_text' => 'Zum Dashboard',
        ];

        $recipients->select(['id', 'email'])->chunkById(200, function ($users) use ($data, &$count): void {
            foreach ($users as $user) {
                if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                Mail::to($user->email)->queue((new UserNotifyEmail($data))->afterCommit());
                $count++;
            }
        });

        return $count;
    }
}
