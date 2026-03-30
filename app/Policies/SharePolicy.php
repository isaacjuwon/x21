<?php

namespace App\Policies;

use App\Models\ShareOrder;
use App\Models\User;

class SharePolicy
{
    /**
     * Determine whether the user can approve a share order.
     */
    public function approve(User $user, ShareOrder $order): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Determine whether the user can reject a share order.
     */
    public function reject(User $user, ShareOrder $order): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Determine whether the user can declare a dividend.
     */
    public function declareDividend(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Determine whether the user can update the share price.
     */
    public function updatePrice(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
