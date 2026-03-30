<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Kyc;
use Illuminate\Auth\Access\HandlesAuthorization;

class KycPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Kyc');
    }

    public function view(AuthUser $authUser, Kyc $kyc): bool
    {
        return $authUser->can('View:Kyc');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Kyc');
    }

    public function update(AuthUser $authUser, Kyc $kyc): bool
    {
        return $authUser->can('Update:Kyc');
    }

    public function delete(AuthUser $authUser, Kyc $kyc): bool
    {
        return $authUser->can('Delete:Kyc');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Kyc');
    }

    public function restore(AuthUser $authUser, Kyc $kyc): bool
    {
        return $authUser->can('Restore:Kyc');
    }

    public function forceDelete(AuthUser $authUser, Kyc $kyc): bool
    {
        return $authUser->can('ForceDelete:Kyc');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Kyc');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Kyc');
    }

    public function replicate(AuthUser $authUser, Kyc $kyc): bool
    {
        return $authUser->can('Replicate:Kyc');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Kyc');
    }

}