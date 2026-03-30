<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DataPlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class DataPlanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DataPlan');
    }

    public function view(AuthUser $authUser, DataPlan $dataPlan): bool
    {
        return $authUser->can('View:DataPlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DataPlan');
    }

    public function update(AuthUser $authUser, DataPlan $dataPlan): bool
    {
        return $authUser->can('Update:DataPlan');
    }

    public function delete(AuthUser $authUser, DataPlan $dataPlan): bool
    {
        return $authUser->can('Delete:DataPlan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DataPlan');
    }

    public function restore(AuthUser $authUser, DataPlan $dataPlan): bool
    {
        return $authUser->can('Restore:DataPlan');
    }

    public function forceDelete(AuthUser $authUser, DataPlan $dataPlan): bool
    {
        return $authUser->can('ForceDelete:DataPlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DataPlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DataPlan');
    }

    public function replicate(AuthUser $authUser, DataPlan $dataPlan): bool
    {
        return $authUser->can('Replicate:DataPlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DataPlan');
    }

}