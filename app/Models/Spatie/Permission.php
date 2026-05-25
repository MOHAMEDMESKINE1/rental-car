<?php

namespace App\Models\Spatie;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Translatable\HasTranslations;

class Permission extends SpatiePermission
{
    use HasTranslations;

    public $translatable = ['display_name'];
}
