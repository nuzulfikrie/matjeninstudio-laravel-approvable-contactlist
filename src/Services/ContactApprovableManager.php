<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Services;

use Illuminate\Database\Eloquent\Collection;
use MatJeninStudio\ContactApprovable\Events\ApprovalApprovedEvent;
use MatJeninStudio\ContactApprovable\Events\ApprovalRejectedEvent;
use MatJeninStudio\ContactApprovable\Events\ContactCreatedEvent;
use MatJeninStudio\ContactApprovable\Events\ContactUpdatedEvent;
use MatJeninStudio\ContactApprovable\Models\Approval;
use MatJeninStudio\ContactApprovable\Models\ApprovalRecord;
use MatJeninStudio\ContactApprovable\Models\Contact;
use MatJeninStudio\ContactApprovable\Notifications\ApprovalApprovedNotification;
use MatJeninStudio\ContactApprovable\Notifications\ApprovalRejectedNotification;

class ContactApprovableManager
{
    /**
     * Create a new contact.
     */
    public function createContact(array $data): Contact
    {
        $contact = Contact::create($data);

        // Dispatch event if enabled
        if (config('contact-approvable.events.enabled', true) &&
            config('contact-approvable.events.dispatch.contact_created', true)) {
            event(new ContactCreatedEvent($contact));
        }

        return $contact;
    }

    /**
     * Update an existing contact.
     */
    public function updateContact(int $id, array $data): Contact
    {
        $contact = Contact::findOrFail($id);
        $contact->update($data);
        $contact = $contact->fresh();

        // Dispatch event if enabled
        if (config('contact-approvable.events.enabled', true) &&
            config('contact-approvable.events.dispatch.contact_updated', true)) {
            event(new ContactUpdatedEvent($contact));
        }

        return $contact;
    }

    /**
     * Delete a contact.
     */
    public function deleteContact(int $id): bool
    {
        $contact = Contact::findOrFail($id);

        return $contact->delete();
    }

    /**
     * Add user(s) to a contact.
     */
    public function addUserToContact(int $contactId, int|array $userIds): void
    {
        $contact = Contact::findOrFail($contactId);
        $userIds = is_array($userIds) ? $userIds : [$userIds];

        $contact->users()->syncWithoutDetaching($userIds);
    }

    /**
     * Remove a user from a contact.
     */
    public function removeUserFromContact(int $contactId, int $userId): void
    {
        $contact = Contact::findOrFail($contactId);
        $contact->users()->detach($userId);
    }

    /**
     * Set a user as an approver in a contact.
     */
    public function setApprover(int $contactId, int $userId): void
    {
        $contact = Contact::findOrFail($contactId);

        // Update the pivot table to set is_approver = true
        $contact->users()->updateExistingPivot($userId, ['is_approver' => true]);
    }

    /**
     * Remove approver status from a user in a contact.
     */
    public function removeApprover(int $contactId, int $userId): void
    {
        $contact = Contact::findOrFail($contactId);

        // Update the pivot table to set is_approver = false
        $contact->users()->updateExistingPivot($userId, ['is_approver' => false]);
    }

    /**
     * Approve an approval request.
     */
    public function approve(int $approvalId, int $userId, ?string $comment = null): ApprovalRecord
    {
        $approval = Approval::findOrFail($approvalId);

        $record = ApprovalRecord::create([
            'approval_id' => $approval->id,
            'user_id' => $userId,
            'is_approved' => true,
            'comment' => $comment,
        ]);

        // Dispatch event if enabled
        if (config('contact-approvable.events.enabled', true) &&
            config('contact-approvable.events.dispatch.approval_approved', true)) {
            event(new ApprovalApprovedEvent($record));
        }

        // Send notification to all approvers in the contact if notifications are enabled
        // Note: To notify the requester specifically, add a requested_by column to approvals table
        if (config('contact-approvable.notifications.enabled', true)) {
            $approvers = $approval->contact->approvers()->get();
            foreach ($approvers as $approver) {
                $approver->notify(new ApprovalApprovedNotification($record));
            }
        }

        return $record;
    }

    /**
     * Reject an approval request.
     */
    public function reject(int $approvalId, int $userId, string $comment): ApprovalRecord
    {
        $approval = Approval::findOrFail($approvalId);

        $record = ApprovalRecord::create([
            'approval_id' => $approval->id,
            'user_id' => $userId,
            'is_approved' => false,
            'comment' => $comment,
        ]);

        // Dispatch event if enabled
        if (config('contact-approvable.events.enabled', true) &&
            config('contact-approvable.events.dispatch.approval_rejected', true)) {
            event(new ApprovalRejectedEvent($record));
        }

        // Send notification to all approvers in the contact if notifications are enabled
        // Note: To notify the requester specifically, add a requested_by column to approvals table
        if (config('contact-approvable.notifications.enabled', true)) {
            $approvers = $approval->contact->approvers()->get();
            foreach ($approvers as $approver) {
                $approver->notify(new ApprovalRejectedNotification($record));
            }
        }

        return $record;
    }

    /**
     * Get all pending approvals for a user.
     */
    public function getPendingApprovals(int $userId): Collection
    {
        // Get all contacts where the user is an approver
        $contacts = Contact::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('is_approver', true);
        })->pluck('id');

        // Get all pending approvals for those contacts
        return Approval::whereIn('contact_id', $contacts)
            ->doesntHave('records')
            ->with(['approvable', 'contact'])
            ->get();
    }
}
