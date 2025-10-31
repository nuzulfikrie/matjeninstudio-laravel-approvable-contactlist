<?php

use Illuminate\Database\Eloquent\Model;
use MatJeninStudio\ContactApprovable\Models\Contact;
use MatJeninStudio\ContactApprovable\Tests\Feature\TestModels\Document;
use MatJeninStudio\ContactApprovable\Tests\Models\User;
use MatJeninStudio\ContactApprovable\Traits\Approvable;

beforeEach(function () {
    $this->document = Document::create(['title' => 'Test Document']);
    $this->user1 = User::factory()->create(['name' => 'User 1', 'email' => 'user1@example.com']);
    $this->user2 = User::factory()->create(['name' => 'User 2', 'email' => 'user2@example.com']);
    $this->user3 = User::factory()->create(['name' => 'User 3', 'email' => 'user3@example.com']);
});

it('can be used in a model', function () {
    $model = new class extends Model
    {
        use Approvable;
    };

    expect(method_exists($model, 'approvals'))->toBeTrue();
});

describe('createContact', function () {
    it('can create a contact without users', function () {
        $contact = $this->document->createContact('Legal Department');

        expect($contact)->toBeInstanceOf(Contact::class)
            ->and($contact->name)->toBe('Legal Department')
            ->and($contact->is_active)->toBeTrue()
            ->and($contact->users)->toHaveCount(0);
    });

    it('can create a contact with a single user entity', function () {
        $contact = $this->document->createContact('HR Department', $this->user1);

        expect($contact->name)->toBe('HR Department')
            ->and($contact->users)->toHaveCount(1)
            ->and($contact->users->first()->id)->toBe($this->user1->id)
            ->and((bool) $contact->users->first()->pivot->is_approver)->toBeFalse();
    });

    it('can create a contact with a single user as approver', function () {
        $contact = $this->document->createContact('Finance Team', $this->user1, markAsApprover: true);

        expect($contact->users)->toHaveCount(1)
            ->and((bool) $contact->users->first()->pivot->is_approver)->toBeTrue()
            ->and($contact->approvers)->toHaveCount(1);
    });

    it('can create a contact with multiple user entities', function () {
        $contact = $this->document->createContact(
            'Sales Department',
            [$this->user1, $this->user2, $this->user3]
        );

        expect($contact->users)->toHaveCount(3)
            ->and($contact->users->pluck('id')->toArray())
            ->toContain($this->user1->id, $this->user2->id, $this->user3->id);
    });

    it('can create a contact with multiple users as approvers', function () {
        $contact = $this->document->createContact(
            'Management',
            [$this->user1, $this->user2],
            markAsApprover: true
        );

        expect($contact->users)->toHaveCount(2)
            ->and($contact->approvers)->toHaveCount(2);
    });

    it('can create a contact with user IDs', function () {
        $contact = $this->document->createContact(
            'IT Department',
            [$this->user1->id, $this->user2->id]
        );

        expect($contact->users)->toHaveCount(2)
            ->and($contact->users->pluck('id')->toArray())
            ->toContain($this->user1->id, $this->user2->id);
    });

    it('can create an inactive contact', function () {
        $contact = $this->document->createContact('Archived Team', isActive: false);

        expect($contact->is_active)->toBeFalse();
    });
});

describe('attachUsersToContact', function () {
    it('can attach a single user to a contact', function () {
        $contact = $this->document->createContact('Legal Department');

        expect($contact->users)->toHaveCount(0);

        $updatedContact = $this->document->attachUsersToContact($contact, $this->user1);

        expect($updatedContact->users)->toHaveCount(1)
            ->and($updatedContact->users->first()->id)->toBe($this->user1->id)
            ->and((bool) $updatedContact->users->first()->pivot->is_approver)->toBeFalse();
    });

    it('can attach a single user as approver', function () {
        $contact = $this->document->createContact('HR Department');

        $updatedContact = $this->document->attachUsersToContact($contact, $this->user1, true);

        expect((bool) $updatedContact->users->first()->pivot->is_approver)->toBeTrue()
            ->and($updatedContact->approvers)->toHaveCount(1);
    });

    it('can attach multiple users to a contact', function () {
        $contact = $this->document->createContact('Finance Team');

        $updatedContact = $this->document->attachUsersToContact(
            $contact,
            [$this->user1, $this->user2, $this->user3]
        );

        expect($updatedContact->users)->toHaveCount(3);
    });

    it('does not duplicate users when attaching existing users', function () {
        $contact = $this->document->createContact('Sales', $this->user1);

        expect($contact->users)->toHaveCount(1);

        $updatedContact = $this->document->attachUsersToContact($contact, $this->user1);

        expect($updatedContact->users)->toHaveCount(1);
    });

    it('can attach users with different approver statuses', function () {
        $contact = $this->document->createContact('Management');

        $updatedContact = $this->document->attachUsersToContact($contact, [
            $this->user1->id => ['is_approver' => true],
            $this->user2->id => ['is_approver' => false],
        ]);

        expect($updatedContact->users)->toHaveCount(2)
            ->and($updatedContact->approvers)->toHaveCount(1)
            ->and($updatedContact->approvers->first()->id)->toBe($this->user1->id);
    });

    it('can attach users to contact by ID', function () {
        $contact = $this->document->createContact('IT Department');

        $updatedContact = $this->document->attachUsersToContact($contact->id, $this->user1);

        expect($updatedContact->users)->toHaveCount(1);
    });

    it('can attach user IDs instead of entities', function () {
        $contact = $this->document->createContact('Support Team');

        $updatedContact = $this->document->attachUsersToContact(
            $contact,
            [$this->user1->id, $this->user2->id]
        );

        expect($updatedContact->users)->toHaveCount(2);
    });
});

describe('syncContactUsers', function () {
    it('can sync users to a contact', function () {
        $contact = $this->document->createContact('Legal', [$this->user1, $this->user2]);

        expect($contact->users)->toHaveCount(2);

        $updatedContact = $this->document->syncContactUsers($contact, [$this->user3]);

        expect($updatedContact->users)->toHaveCount(1)
            ->and($updatedContact->users->first()->id)->toBe($this->user3->id);
    });

    it('removes users not in the sync list', function () {
        $contact = $this->document->createContact('HR', [$this->user1, $this->user2, $this->user3]);

        expect($contact->users)->toHaveCount(3);

        $updatedContact = $this->document->syncContactUsers($contact, [$this->user1]);

        expect($updatedContact->users)->toHaveCount(1)
            ->and($updatedContact->users->first()->id)->toBe($this->user1->id);
    });

    it('can sync with approver status', function () {
        $contact = $this->document->createContact('Finance');

        $updatedContact = $this->document->syncContactUsers(
            $contact,
            [$this->user1, $this->user2],
            markAsApprover: true
        );

        expect($updatedContact->users)->toHaveCount(2)
            ->and($updatedContact->approvers)->toHaveCount(2);
    });

    it('can sync users with mixed approver statuses', function () {
        $contact = $this->document->createContact('Management');

        $updatedContact = $this->document->syncContactUsers($contact, [
            $this->user1->id => ['is_approver' => true],
            $this->user2->id => ['is_approver' => false],
            $this->user3->id => ['is_approver' => true],
        ]);

        expect($updatedContact->users)->toHaveCount(3)
            ->and($updatedContact->approvers)->toHaveCount(2);
    });

    it('works with contact ID instead of instance', function () {
        $contact = $this->document->createContact('Sales', [$this->user1]);

        $updatedContact = $this->document->syncContactUsers($contact->id, [$this->user2, $this->user3]);

        expect($updatedContact->users)->toHaveCount(2)
            ->and($updatedContact->users->pluck('id')->toArray())
            ->toContain($this->user2->id, $this->user3->id)
            ->not->toContain($this->user1->id);
    });
});

describe('detachUsersFromContact', function () {
    it('can detach specific users from a contact', function () {
        $contact = $this->document->createContact('Legal', [$this->user1, $this->user2, $this->user3]);

        expect($contact->users)->toHaveCount(3);

        $updatedContact = $this->document->detachUsersFromContact($contact, [$this->user1, $this->user2]);

        expect($updatedContact->users)->toHaveCount(1)
            ->and($updatedContact->users->first()->id)->toBe($this->user3->id);
    });

    it('can detach all users when no users specified', function () {
        $contact = $this->document->createContact('HR', [$this->user1, $this->user2, $this->user3]);

        expect($contact->users)->toHaveCount(3);

        $updatedContact = $this->document->detachUsersFromContact($contact);

        expect($updatedContact->users)->toHaveCount(0);
    });

    it('can detach users by ID', function () {
        $contact = $this->document->createContact('Finance', [$this->user1, $this->user2]);

        $updatedContact = $this->document->detachUsersFromContact($contact, [$this->user1->id]);

        expect($updatedContact->users)->toHaveCount(1)
            ->and($updatedContact->users->first()->id)->toBe($this->user2->id);
    });

    it('can detach a single user entity', function () {
        $contact = $this->document->createContact('Sales', [$this->user1, $this->user2]);

        $updatedContact = $this->document->detachUsersFromContact($contact, $this->user1);

        expect($updatedContact->users)->toHaveCount(1)
            ->and($updatedContact->users->first()->id)->toBe($this->user2->id);
    });

    it('works with contact ID instead of instance', function () {
        $contact = $this->document->createContact('IT', [$this->user1, $this->user2]);

        $updatedContact = $this->document->detachUsersFromContact($contact->id, $this->user1);

        expect($updatedContact->users)->toHaveCount(1);
    });
});

describe('updateContactUserApproverStatus', function () {
    it('can mark a user as approver', function () {
        $contact = $this->document->createContact('Legal', $this->user1);

        expect((bool) $contact->users->first()->pivot->is_approver)->toBeFalse();

        $updatedContact = $this->document->updateContactUserApproverStatus($contact, $this->user1, true);

        expect((bool) $updatedContact->users->first()->pivot->is_approver)->toBeTrue()
            ->and($updatedContact->approvers)->toHaveCount(1);
    });

    it('can remove approver status from a user', function () {
        $contact = $this->document->createContact('HR', $this->user1, markAsApprover: true);

        expect($contact->approvers)->toHaveCount(1);

        $updatedContact = $this->document->updateContactUserApproverStatus($contact, $this->user1, false);

        expect((bool) $updatedContact->users->first()->pivot->is_approver)->toBeFalse()
            ->and($updatedContact->approvers)->toHaveCount(0);
    });

    it('can update status using user ID', function () {
        $contact = $this->document->createContact('Finance', $this->user1);

        $updatedContact = $this->document->updateContactUserApproverStatus(
            $contact,
            $this->user1->id,
            true
        );

        expect((bool) $updatedContact->users->first()->pivot->is_approver)->toBeTrue();
    });

    it('works with contact ID instead of instance', function () {
        $contact = $this->document->createContact('Sales', $this->user1);

        $updatedContact = $this->document->updateContactUserApproverStatus(
            $contact->id,
            $this->user1,
            true
        );

        expect($updatedContact->approvers)->toHaveCount(1);
    });

    it('can update approver status for multiple users independently', function () {
        $contact = $this->document->createContact('Management', [$this->user1, $this->user2, $this->user3]);

        $contact = $this->document->updateContactUserApproverStatus($contact, $this->user1, true);
        $contact = $this->document->updateContactUserApproverStatus($contact, $this->user3, true);

        expect($contact->approvers)->toHaveCount(2)
            ->and($contact->approvers->pluck('id')->toArray())
            ->toContain($this->user1->id, $this->user3->id)
            ->not->toContain($this->user2->id);
    });
});

describe('contact management integration', function () {
    it('can create and fully manage a contact workflow', function () {
        // Create contact with initial users
        $contact = $this->document->createContact('Project Team', [$this->user1]);

        expect($contact->users)->toHaveCount(1);

        // Attach more users
        $contact = $this->document->attachUsersToContact($contact, [$this->user2, $this->user3]);

        expect($contact->users)->toHaveCount(3);

        // Mark some as approvers
        $contact = $this->document->updateContactUserApproverStatus($contact, $this->user1, true);
        $contact = $this->document->updateContactUserApproverStatus($contact, $this->user2, true);

        expect($contact->approvers)->toHaveCount(2);

        // Remove one approver status
        $contact = $this->document->updateContactUserApproverStatus($contact, $this->user2, false);

        expect($contact->approvers)->toHaveCount(1);

        // Detach a user
        $contact = $this->document->detachUsersFromContact($contact, $this->user3);

        expect($contact->users)->toHaveCount(2);

        // Sync to a new set of users
        $contact = $this->document->syncContactUsers($contact, [$this->user3], markAsApprover: true);

        expect($contact->users)->toHaveCount(1)
            ->and($contact->approvers)->toHaveCount(1)
            ->and($contact->users->first()->id)->toBe($this->user3->id);
    });

    it('maintains unique constraint when attaching duplicate users', function () {
        $contact = $this->document->createContact('Tech Team', $this->user1);

        // Try to attach the same user again
        $contact = $this->document->attachUsersToContact($contact, $this->user1);

        expect($contact->users)->toHaveCount(1);

        // Try with array including duplicate
        $contact = $this->document->attachUsersToContact($contact, [$this->user1, $this->user2]);

        expect($contact->users)->toHaveCount(2);
    });
});
