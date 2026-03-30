<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EducationPlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class EducationPlanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EducationPlan');
    }

    public function view(AuthUser $authUser, EducationPlan $educationPlan): bool
    {
        return $authUser->can('View:EducationPlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EducationPlan');
    }

    public function update(AuthUser $authUser, EducationPlan $educationPlan): bool
    {
        return $authUser->can('Update:EducationPlan');
    }

    public function delete(AuthUser $authUser, EducationPlan $educationPlan): bool
    {
        return $authUser->can('Delete:EducationPlan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EducationPlan');
    }

    public function restore(AuthUser $authUser, EducationPlan $educationPlan): bool
    {
        return $authUser->can('Restore:EducationPlan');
    }

    public function forceDelete(AuthUser $authUser, EducationPlan $educationPlan): bool
    {
        return $authUser->can('ForceDelete:EducationPlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EducationPlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EducationPlan');
    }

    public function replicate(AuthUser $authUser, EducationPlan $educationPlan): bool
    {
        return $authUser->can('Replicate:EducationPlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EducationPlan');
    }

}