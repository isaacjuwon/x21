<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ElectricityPlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class ElectricityPlanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ElectricityPlan');
    }

    public function view(AuthUser $authUser, ElectricityPlan $electricityPlan): bool
    {
        return $authUser->can('View:ElectricityPlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ElectricityPlan');
    }

    public function update(AuthUser $authUser, ElectricityPlan $electricityPlan): bool
    {
        return $authUser->can('Update:ElectricityPlan');
    }

    public function delete(AuthUser $authUser, ElectricityPlan $electricityPlan): bool
    {
        return $authUser->can('Delete:ElectricityPlan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ElectricityPlan');
    }

    public function restore(AuthUser $authUser, ElectricityPlan $electricityPlan): bool
    {
        return $authUser->can('Restore:ElectricityPlan');
    }

    public function forceDelete(AuthUser $authUser, ElectricityPlan $electricityPlan): bool
    {
        return $authUser->can('ForceDelete:ElectricityPlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ElectricityPlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ElectricityPlan');
    }

    public function replicate(AuthUser $authUser, ElectricityPlan $electricityPlan): bool
    {
        return $authUser->can('Replicate:ElectricityPlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ElectricityPlan');
    }

}