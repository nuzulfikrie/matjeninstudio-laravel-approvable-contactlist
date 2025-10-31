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

    /**
     * Create a new contact and optionally attach users/entities to it.
     *
     * @param  string  $name  The contact name
     * @param  array|object|null  $users  User entity/entities to attach (User model, array of User models, or array of user IDs)
     * @param  bool  $isActive  Whether the contact is active (default: true)
     * @param  bool  $markAsApprover  Whether to mark attached users as approvers (default: false)
     * @return Contact The created contact instance
     *
     * @throws \InvalidArgumentException
     *
     * @example
     * // Create contact with single user as approver
     * $contact = $model->createContact('Legal Department', $user, isActive: true, markAsApprover: true);
     *
     * // Create contact with multiple users
     * $contact = $model->createContact('Finance Team', [$user1, $user2], markAsApprover: true);
     *
     * // Create contact with user IDs
     * $contact = $model->createContact('HR Department', [1, 2, 3]);
     *
     * // Create empty contact (attach users later)
     * $contact = $model->createContact('Sales Department');
     */
    public function createContact(
        string $name,
        array|object|null $users = null,
        bool $isActive = true,
        bool $markAsApprover = false
    ): Contact {
        // Create the contact
        $contact = Contact::create([
            'name' => $name,
            'is_active' => $isActive,
        ]);

        // Attach users if provided
        if ($users !== null) {
            $this->attachUsersToContact($contact, $users, $markAsApprover);
        }

        return $contact->fresh(['users', 'approvers']);
    }

    /**
     * Attach users/entities to a contact.
     *
     * @param  Contact|int  $contact  The contact instance or ID
     * @param  array|object  $users  User entity/entities to attach (User model, array of User models, or array of user IDs)
     * @param  bool  $markAsApprover  Whether to mark attached users as approvers (default: false)
     * @return Contact The contact instance with attached users
     *
     * @throws \InvalidArgumentException
     *
     * @example
     * // Attach single user
     * $contact = $model->attachUsersToContact($contact, $user, true);
     *
     * // Attach multiple users
     * $contact = $model->attachUsersToContact($contact, [$user1, $user2]);
     *
     * // Attach user IDs with different approver statuses
     * $contact = $model->attachUsersToContact($contact, [1 => ['is_approver' => true], 2 => ['is_approver' => false]]);
     */
    public function attachUsersToContact(
        Contact|int $contact,
        array|object $users,
        bool $markAsApprover = false
    ): Contact {
        // Resolve contact
        $contactInstance = $contact instanceof Contact ? $contact : Contact::findOrFail($contact);

        // Normalize users to sync format
        $syncData = $this->normalizeUsersForSync($users, $markAsApprover);

        // Attach users (won't duplicate due to unique constraint)
        $contactInstance->users()->syncWithoutDetaching($syncData);

        return $contactInstance->fresh(['users', 'approvers']);
    }

    /**
     * Sync users/entities to a contact (removes existing users not in the list).
     *
     * @param  Contact|int  $contact  The contact instance or ID
     * @param  array|object  $users  User entity/entities to sync
     * @param  bool  $markAsApprover  Whether to mark synced users as approvers (default: false)
     * @return Contact The contact instance with synced users
     *
     * @example
     * // Replace all users with new set
     * $contact = $model->syncContactUsers($contact, [$user1, $user2], true);
     */
    public function syncContactUsers(
        Contact|int $contact,
        array|object $users,
        bool $markAsApprover = false
    ): Contact {
        // Resolve contact
        $contactInstance = $contact instanceof Contact ? $contact : Contact::findOrFail($contact);

        // Normalize users to sync format
        $syncData = $this->normalizeUsersForSync($users, $markAsApprover);

        // Sync users (removes users not in the list)
        $contactInstance->users()->sync($syncData);

        return $contactInstance->fresh(['users', 'approvers']);
    }

    /**
     * Detach users/entities from a contact.
     *
     * @param  Contact|int  $contact  The contact instance or ID
     * @param  array|object|null  $users  User entity/entities to detach (null to detach all)
     * @return Contact The contact instance
     */
    public function detachUsersFromContact(Contact|int $contact, array|object|null $users = null): Contact
    {
        // Resolve contact
        $contactInstance = $contact instanceof Contact ? $contact : Contact::findOrFail($contact);

        if ($users === null) {
            // Detach all users
            $contactInstance->users()->detach();
        } else {
            // Detach specific users
            $userIds = $this->extractUserIds($users);
            $contactInstance->users()->detach($userIds);
        }

        return $contactInstance->fresh(['users', 'approvers']);
    }

    /**
     * Update approver status for a user on a contact.
     *
     * @param  Contact|int  $contact  The contact instance or ID
     * @param  object|int  $user  The user instance or ID
     * @param  bool  $isApprover  Whether the user should be marked as approver
     * @return Contact The contact instance
     */
    public function updateContactUserApproverStatus(
        Contact|int $contact,
        object|int $user,
        bool $isApprover
    ): Contact {
        // Resolve contact
        $contactInstance = $contact instanceof Contact ? $contact : Contact::findOrFail($contact);

        // Extract user ID
        $userId = is_object($user) ? $user->id : $user;

        // Update pivot data
        $contactInstance->users()->updateExistingPivot($userId, ['is_approver' => $isApprover]);

        return $contactInstance->fresh(['users', 'approvers']);
    }

    /**
     * Normalize users to sync format for pivot table.
     *
     * @param  array|object  $users  User entities or IDs
     * @param  bool  $markAsApprover  Default approver status
     * @return array Normalized sync data
     */
    protected function normalizeUsersForSync(array|object $users, bool $markAsApprover): array
    {
        // Convert single object to array
        if (is_object($users)) {
            $users = [$users];
        }

        $syncData = [];

        foreach ($users as $key => $value) {
            // Handle different input formats:

            // [1 => ['is_approver' => true], 2 => ['is_approver' => false]]
            if (is_array($value) && is_numeric($key)) {
                $syncData[$key] = array_merge(['is_approver' => $markAsApprover], $value);
            }
            // [1, 2, 3] or [$user1, $user2]
            elseif (is_numeric($key)) {
                $userId = is_object($value) ? $value->id : $value;
                $syncData[$userId] = ['is_approver' => $markAsApprover];
            }
            // Invalid format
            else {
                throw new \InvalidArgumentException('Invalid user data format');
            }
        }

        return $syncData;
    }

    /**
     * Extract user IDs from various input formats.
     *
     * @param  array|object  $users  User entities or IDs
     * @return array Array of user IDs
     */
    protected function extractUserIds(array|object $users): array
    {
        // Convert single object to array
        if (is_object($users)) {
            $users = [$users];
        }

        $userIds = [];

        foreach ($users as $key => $value) {
            if (is_numeric($key) && is_object($value)) {
                $userIds[] = $value->id;
            } elseif (is_numeric($key) && is_numeric($value)) {
                $userIds[] = $value;
            } elseif (is_numeric($key)) {
                $userIds[] = $key;
            }
        }

        return $userIds;
    }
}
