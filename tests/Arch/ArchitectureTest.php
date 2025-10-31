<?php

arch('models')
    ->expect('MatJeninStudio\ContactApprovable\Models')
    ->toUseStrictTypes()
    ->toHaveSuffix('');

arch('no debug functions')
    ->expect(['dd', 'dump', 'var_dump', 'var_export', 'exec'])
    ->not->toBeUsed();

arch('models use HasFactory')
    ->expect('MatJeninStudio\ContactApprovable\Models')
    ->toUse('Illuminate\Database\Eloquent\Factories\HasFactory');
