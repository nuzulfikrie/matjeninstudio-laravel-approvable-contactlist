<?php

use MatJeninStudio\ContactApprovable\Facades\ContactApprovable;
use MatJeninStudio\ContactApprovable\Models\Contact;

it('can create a contact via facade', function () {
    $contact = ContactApprovable::createContact([
        'name' => 'Test Approvers',
    ]);

    expect($contact)
        ->toBeInstanceOf(Contact::class)
        ->name->toBe('Test Approvers')
        ->is_active->toBeTrue();
});
