<?php

if (!function_exists('can_admin')) {
    /**
     * Check if the current user has admin permission for a specific feature
     *
     * @param string $permission
     * @return bool
     */
    function can_admin($permission)
    {
        if (!auth()->check() || auth()->user()->user_type !== 'admin') {
            return false;
        }
        
        return auth()->user()->hasAdminPermission($permission);
    }
}
