<?php

namespace MatJeninStudio\ContactApprovable\Tests\Feature;

use Illuminate\Support\Facades\Notification;
use MatJeninStudio\ContactApprovable\Facades\ContactApprovable;
use MatJeninStudio\ContactApprovable\Models\Contact;
use MatJeninStudio\ContactApprovable\Notifications\ApprovalRequestedNotification;
use MatJeninStudio\ContactApprovable\Tests\Feature\TestModels\Document;
use MatJeninStudio\ContactApprovable\Tests\Models\User;

it('notifies approvers when approval is requested', function () {
    Notification::fake();

    $contact = Contact::factory()->create();
    $approvers = User::factory()->count(2)->create();

    ContactApprovable::addUserToContact($contact->id, $approvers->pluck('id')->toArray());
    ContactApprovable::setApprover($contact->id, $approvers[0]->id);
    ContactApprovable::setApprover($contact->id, $approvers[1]->id);

    $document = Document::create([]);
    $document->requestApproval($contact);

    Notification::assertSentTo(
        $approvers,
        ApprovalRequestedNotification::class
    );
});
