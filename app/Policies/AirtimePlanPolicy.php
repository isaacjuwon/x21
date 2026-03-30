<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AirtimePlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class AirtimePlanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AirtimePlan');
    }

    public function view(AuthUser $authUser, AirtimePlan $airtimePlan): bool
    {
        return $authUser->can('View:AirtimePlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AirtimePlan');
    }

    public function update(AuthUser $authUser, AirtimePlan $airtimePlan): bool
    {
        return $authUser->can('Update:AirtimePlan');
    }

    public function delete(AuthUser $authUser, AirtimePlan $airtimePlan): bool
    {
        return $authUser->can('Delete:AirtimePlan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AirtimePlan');
    }

    public function restore(AuthUser $authUser, AirtimePlan $airtimePlan): bool
    {
        return $authUser->can('Restore:AirtimePlan');
    }

    public function forceDelete(AuthUser $authUser, AirtimePlan $airtimePlan): bool
    {
        return $authUser->can('ForceDelete:AirtimePlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AirtimePlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AirtimePlan');
    }

    public function replicate(AuthUser $authUser, AirtimePlan $airtimePlan): bool
    {
        return $authUser->can('Replicate:AirtimePlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AirtimePlan');
    }

}