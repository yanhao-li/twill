<?php

namespace A17\Twill\Repositories\Behaviors;

use A17\Twill\Repositories\UserRepository;
use A17\Twill\Repositories\GroupRepository;
use A17\Twill\Models\Permission;

trait HandlePermissions
{
    public function getFormFieldsHandlePermissions($object, $fields)
    {
        // User form
        if (get_class($object) === "A17\Twill\Models\User") {
            foreach($object->permissions as $permission) {
                $module = $permission->permissionable()->withoutGlobalScope('authorized')->first();
                $module_name = str_plural(lcfirst(class_basename($module)));
                $fields[$module_name . '_' . $module->id . '_permission'] = '"' . $permission->name . '"';
            }
        }
        // Module instance form
        else {
            $fields = $this->renderUserPermissions($object, $fields);
            $fields = $this->renderGroupPermissions($object, $fields);
        }
        
        return $fields;
    }

    public function afterSaveHandlePermissions($object, $fields)
    {
        foreach($fields as $key => $value) {
            if (ends_with($key, '_permission')) {

                // Handle permissions fields on module item page
                if (starts_with($key, 'user')) {
                    $user_id = explode('_', $key)[1];
                    $user = app()->make(UserRepository::class)->getById($user_id);
                    $item = $object;
                }
                // Handle permissions fields on user page
                else {
                    $item_name = explode('_', $key)[0];
                    $item_id = explode('_', $key)[1];
                    $item = getRepositoryByModuleName($item_name)->getById($item_id);
                    $user = $object;
                }

                $permission = $user->itemPermission($item) ?? new Permission;

                // Only value existed, do update or create
                if ($value) {
                    $permission->name = $value;
                    $permission->permissionable()->associate($item);
                    $user->permissions()->save($permission);
                    $permission->save();
                }
                // If the existed permission has been set as none, delete the origin permission
                elseif ($permission) {
                    $permission->delete();
                }
            }
        }        
    }

    // Render each user's permission under a item
    protected function renderUserPermissions($object, $fields)
    {
        $users = app()->make(UserRepository::class)->get(["permissions" => function ($query) use ($object) {
            $query->where([['permissionable_type', get_class($object)], ['permissionable_id', $object->id]]);
        }]);
        
        foreach($users as $user) {
            $permission = $user->permissions->first();
            $fields['user_' . $user->id . '_permission'] = $permission ? "'" . $permission->name . "'" : "";
        }

        return $fields;
    }

    // Render each group's permission under a item
    protected function renderGroupPermissions($object, $fields)
    {
        $groups = app()->make(GroupRepository::class)->get(['users.permissions']);
        foreach($groups as $group) {
            $allUsersInGroupAuthorized = $group->users()
                ->whereDoesntHave('permissions', function ($query) use ($object) {
                $query->where([
                    ['permissionable_type', get_class($object)],
                    ['permissionable_id', $object->id]
                ]);
            })->get()->count() === 0;

            $fields['group_' . $group->id] = $allUsersInGroupAuthorized;
        }
        return $fields;
    }
    
}
