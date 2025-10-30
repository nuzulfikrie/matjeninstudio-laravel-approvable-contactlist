<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use MatJeninStudio\ContactApprovable\Models\ApprovalRecord;

class ApprovalRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ApprovalRecord $approvalRecord
    ) {
    }

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
        $approval = $this->approvalRecord->approval;
        $approvable = $approval->approvable;
        $user = $this->approvalRecord->user;

        return (new MailMessage)
            ->subject('Approval Rejected: '.class_basename($approvable))
            ->line('Your approval request has been rejected.')
            ->line('Rejected by: '.$user->name)
            ->when($this->approvalRecord->comment, function ($mail) {
                return $mail->line('Comment: '.$this->approvalRecord->comment);
            })
            ->action('View Details', url('/contact-approvable/approvals/'.$approval->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $approval = $this->approvalRecord->approval;
        $approvable = $approval->approvable;
        $user = $this->approvalRecord->user;

        return [
            'approval_id' => $approval->id,
            'approval_record_id' => $this->approvalRecord->id,
            'approvable_type' => $approval->approvable_type,
            'approvable_id' => $approval->approvable_id,
            'rejected_by' => $user->name,
            'comment' => $this->approvalRecord->comment,
            'message' => 'Your approval request for '.class_basename($approvable).' has been rejected.',
            'url' => url('/contact-approvable/approvals/'.$approval->id),
        ];
    }
}

