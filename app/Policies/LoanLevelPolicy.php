<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LoanLevel;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoanLevelPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LoanLevel');
    }

    public function view(AuthUser $authUser, LoanLevel $loanLevel): bool
    {
        return $authUser->can('View:LoanLevel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LoanLevel');
    }

    public function update(AuthUser $authUser, LoanLevel $loanLevel): bool
    {
        return $authUser->can('Update:LoanLevel');
    }

    public function delete(AuthUser $authUser, LoanLevel $loanLevel): bool
    {
        return $authUser->can('Delete:LoanLevel');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LoanLevel');
    }

    public function restore(AuthUser $authUser, LoanLevel $loanLevel): bool
    {
        return $authUser->can('Restore:LoanLevel');
    }

    public function forceDelete(AuthUser $authUser, LoanLevel $loanLevel): bool
    {
        return $authUser->can('ForceDelete:LoanLevel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LoanLevel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LoanLevel');
    }

    public function replicate(AuthUser $authUser, LoanLevel $loanLevel): bool
    {
        return $authUser->can('Replicate:LoanLevel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LoanLevel');
    }

}