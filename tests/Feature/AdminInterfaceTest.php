<?php

use MatJeninStudio\ContactApprovable\Models\Contact;

it('displays contacts in admin interface', function () {
    $contacts = Contact::factory()->count(5)->create();

    $response = $this->get(route('contact-approvable.contacts.index'));

    $response->assertOk();
    $response->assertViewHas('contacts');
});
