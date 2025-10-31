<?php

namespace MatJeninStudio\ContactApprovable\Tests\Feature;

use MatJeninStudio\ContactApprovable\Models\Approval;
use MatJeninStudio\ContactApprovable\Models\Contact;
use MatJeninStudio\ContactApprovable\Tests\Feature\TestModels\Document;

it('can request approval for a model', function () {
    $contact = Contact::factory()->create();
    $document = Document::create([]);

    $approval = $document->requestApproval($contact);

    expect($approval)
        ->toBeInstanceOf(Approval::class)
        ->contact_id->toBe($contact->id);

    expect($document->hasPendingApproval())
        ->toBeInstanceOf(Approval::class)
        ->not->toBeNull();
});
