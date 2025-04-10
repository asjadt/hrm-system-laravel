<?php
namespace App\Http\Components;

use Exception;

class AuthorizationComponent
{

    public function hasPermission($permission)
    {
        if (!auth()->user()->hasPermissionTo($permission)) {
          throw new Exception("You can not perform this action",401);
        }

        return true;
    }
}
