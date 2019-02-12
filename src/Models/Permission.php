<?php

namespace A17\Twill\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Permission extends BaseModel
{
    public static $available = [
        "global" => ["edit-settings", "edit-users", "edit-user-roles", "edit-user-groups"],
        "module" => ["list", "reorder", "create", "feature"],
        "item" => ["publish", "edit", "delete"],
    ];

    protected $fillable = [
        'name',
        'permissionable_type',
        'permissionable_id',
    ];

    public function permissionable()
    {
        return $this->morphTo();
    }
}
