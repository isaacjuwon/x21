<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ShareOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShareOrderPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ShareOrder');
    }

    public function view(AuthUser $authUser, ShareOrder $shareOrder): bool
    {
        return $authUser->can('View:ShareOrder');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ShareOrder');
    }

    public function update(AuthUser $authUser, ShareOrder $shareOrder): bool
    {
        return $authUser->can('Update:ShareOrder');
    }

    public function delete(AuthUser $authUser, ShareOrder $shareOrder): bool
    {
        return $authUser->can('Delete:ShareOrder');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ShareOrder');
    }

    public function restore(AuthUser $authUser, ShareOrder $shareOrder): bool
    {
        return $authUser->can('Restore:ShareOrder');
    }

    public function forceDelete(AuthUser $authUser, ShareOrder $shareOrder): bool
    {
        return $authUser->can('ForceDelete:ShareOrder');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ShareOrder');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ShareOrder');
    }

    public function replicate(AuthUser $authUser, ShareOrder $shareOrder): bool
    {
        return $authUser->can('Replicate:ShareOrder');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ShareOrder');
    }

}