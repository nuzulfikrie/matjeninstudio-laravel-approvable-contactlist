<?php

use MatJeninStudio\ContactApprovable\Facades\ContactApprovable;
use MatJeninStudio\ContactApprovable\Services\ContactApprovableManager;

it('facade returns manager', function () {
    $manager = ContactApprovable::getFacadeRoot();
    expect($manager)->toBeInstanceOf(ContactApprovableManager::class);
});
