<?php

use MatJeninStudio\ContactApprovable\Models\ApprovalRecord;

it('can be created', function () {
    $approvalRecord = ApprovalRecord::factory()->create();
    expect($approvalRecord)->toBeInstanceOf(ApprovalRecord::class);
});
