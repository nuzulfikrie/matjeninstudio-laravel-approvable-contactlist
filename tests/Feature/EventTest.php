<?php

namespace MatJeninStudio\ContactApprovable\Tests\Feature;

use Illuminate\Support\Facades\Event;
use MatJeninStudio\ContactApprovable\Events\ApprovalRequestedEvent;
use MatJeninStudio\ContactApprovable\Models\Contact;
use MatJeninStudio\ContactApprovable\Tests\Feature\TestModels\Document;

it('dispatches ApprovalRequestedEvent when approval is requested', function () {
    Event::fake([ApprovalRequestedEvent::class]);

    $contact = Contact::factory()->create();
    $document = Document::create([]);

    $document->requestApproval($contact);

    Event::assertDispatched(ApprovalRequestedEvent::class);
});
