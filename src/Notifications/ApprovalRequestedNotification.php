<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use MatJeninStudio\ContactApprovable\Models\Approval;

class ApprovalRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Approval $approval
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return config('contact-approvable.notifications.channels', ['mail', 'database']);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $approvable = $this->approval->approvable;
        $contact = $this->approval->contact;

        return (new MailMessage)
            ->subject('Approval Requested: '.class_basename($approvable))
            ->line('A new approval has been requested for: '.class_basename($approvable))
            ->line('Contact: '.$contact->name)
            ->action('View Approval', url('/contact-approvable/approvals/'.$this->approval->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $approvable = $this->approval->approvable;
        $contact = $this->approval->contact;

        return [
            'approval_id' => $this->approval->id,
            'approvable_type' => $this->approval->approvable_type,
            'approvable_id' => $this->approval->approvable_id,
            'contact_name' => $contact->name,
            'message' => 'A new approval has been requested for '.class_basename($approvable),
            'url' => url('/contact-approvable/approvals/'.$this->approval->id),
        ];
    }
}
