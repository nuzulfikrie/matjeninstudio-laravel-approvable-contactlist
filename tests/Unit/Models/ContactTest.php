<?php

use MatJeninStudio\ContactApprovable\Models\Contact;

it('can be created', function () {
    $contact = Contact::factory()->create();
    expect($contact)->toBeInstanceOf(Contact::class);
});
