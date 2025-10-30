<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \MatJeninStudio\ContactApprovable\Models\Contact createContact(array $data)
 * @method static \MatJeninStudio\ContactApprovable\Models\Contact updateContact(int $id, array $data)
 * @method static bool deleteContact(int $id)
 * @method static void addUserToContact(int $contactId, int|array $userIds)
 * @method static void removeUserFromContact(int $contactId, int $userId)
 * @method static void setApprover(int $contactId, int $userId)
 * @method static void removeApprover(int $contactId, int $userId)
 * @method static \MatJeninStudio\ContactApprovable\Models\ApprovalRecord approve(int $approvalId, int $userId, ?string $comment = null)
 * @method static \MatJeninStudio\ContactApprovable\Models\ApprovalRecord reject(int $approvalId, int $userId, string $comment)
 * @method static \Illuminate\Database\Eloquent\Collection getPendingApprovals(int $userId)
 *
 * @see \MatJeninStudio\ContactApprovable\Services\ContactApprovableManager
 */
class ContactApprovable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'contact-approvable';
    }
}
