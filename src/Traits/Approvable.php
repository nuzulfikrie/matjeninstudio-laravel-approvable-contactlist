<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use MatJeninStudio\ContactApprovable\Events\ApprovalRequestedEvent;
use MatJeninStudio\ContactApprovable\Models\Approval;
use MatJeninStudio\ContactApprovable\Models\Contact;

trait Approvable
{
    /**
     * Get all approvals for this model.
     */
    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    /**
     * Request approval for this model using a contact.
     *
     * @param  Contact|int  $contact  The contact or contact ID to request approval from
     * @return Approval The created approval instance
     */
    public function requestApproval(Contact|int $contact): Approval
    {
        $contactId = $contact instanceof Contact ? $contact->id : $contact;

        // Check if there's already a pending approval
        $pendingApproval = $this->hasPendingApproval();
        if ($pendingApproval) {
            return $pendingApproval;
        }

        $approval = Approval::create([
            'approvable_type' => static::class,
            'approvable_id' => $this->id,
            'contact_id' => $contactId,
        ]);

        // Dispatch event if enabled
        if (config('contact-approvable.events.enabled', true) &&
            config('contact-approvable.events.dispatch.approval_requested', true)) {
            event(new ApprovalRequestedEvent($approval));
        }

        return $approval;
    }

    /**
     * Check if this model has a pending approval.
     */
    public function hasPendingApproval(): ?Approval
    {
        return $this->approvals()
            ->doesntHave('records')
            ->latest()
            ->first();
    }

    /**
     * Get the latest approval for this model.
     */
    public function latestApproval(): ?Approval
    {
        return $this->approvals()
            ->latest()
            ->first();
    }

    /**
     * Get the approval status for this model.
     */
    public function getApprovalStatus(): ?string
    {
        $latestApproval = $this->latestApproval();

        if (! $latestApproval) {
            return null;
        }

        return $latestApproval->status;
    }
}

