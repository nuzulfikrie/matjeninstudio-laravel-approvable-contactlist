<?php

use MatJeninStudio\ContactApprovable\Models\Approval;

it('can be created', function () {
    $approval = Approval::factory()->create();
    expect($approval)->toBeInstanceOf(Approval::class);
});
