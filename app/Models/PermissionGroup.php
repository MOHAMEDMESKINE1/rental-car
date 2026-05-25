<?php

namespace App\Models;

use App\Models\Spatie\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use App\Models\Spatie\Permission;
use Spatie\Translatable\HasTranslations;

class PermissionGroup extends Model
{
    use HasTranslations;

    protected $table = 'permissions_groups';

    public $translatable  = ['display_name'];
    protected $fillable = [
        'display_name',
    ];

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'group_id', 'id');
    }
}
