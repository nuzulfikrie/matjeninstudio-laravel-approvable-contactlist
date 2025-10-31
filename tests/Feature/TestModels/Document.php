<?php

namespace MatJeninStudio\ContactApprovable\Tests\Feature\TestModels;

use Illuminate\Database\Eloquent\Model;
use MatJeninStudio\ContactApprovable\Traits\Approvable;

class Document extends Model
{
    use Approvable;

    protected $guarded = [];
}
