<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MatJeninStudio\ContactApprovable\Models\Contact;

class ContactUpdatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Contact $contact
    ) {
    }
}

