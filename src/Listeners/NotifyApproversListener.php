<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Listeners;

use MatJeninStudio\ContactApprovable\Events\ApprovalRequestedEvent;
use MatJeninStudio\ContactApprovable\Notifications\ApprovalRequestedNotification;

class NotifyApproversListener
{
    /**
     * Handle the event.
     */
    public function handle(ApprovalRequestedEvent $event): void
    {
        // Only send notifications if enabled
        if (! config('contact-approvable.notifications.enabled', true)) {
            return;
        }

        $approval = $event->approval;
        $contact = $approval->contact;

        // Get all approvers for this contact
        $approvers = $contact->approvers()->get();

        // Send notification to each approver
        foreach ($approvers as $approver) {
            $approver->notify(new ApprovalRequestedNotification($approval));
        }
    }
}

