<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CablePlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class CablePlanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CablePlan');
    }

    public function view(AuthUser $authUser, CablePlan $cablePlan): bool
    {
        return $authUser->can('View:CablePlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CablePlan');
    }

    public function update(AuthUser $authUser, CablePlan $cablePlan): bool
    {
        return $authUser->can('Update:CablePlan');
    }

    public function delete(AuthUser $authUser, CablePlan $cablePlan): bool
    {
        return $authUser->can('Delete:CablePlan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CablePlan');
    }

    public function restore(AuthUser $authUser, CablePlan $cablePlan): bool
    {
        return $authUser->can('Restore:CablePlan');
    }

    public function forceDelete(AuthUser $authUser, CablePlan $cablePlan): bool
    {
        return $authUser->can('ForceDelete:CablePlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CablePlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CablePlan');
    }

    public function replicate(AuthUser $authUser, CablePlan $cablePlan): bool
    {
        return $authUser->can('Replicate:CablePlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CablePlan');
    }

}