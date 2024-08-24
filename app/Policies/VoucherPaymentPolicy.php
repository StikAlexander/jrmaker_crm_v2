<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VoucherPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class VoucherPaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_voucher::payment');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VoucherPayment $voucherPayment): bool
    {
        return $user->can('view_voucher::payment');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_voucher::payment');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VoucherPayment $voucherPayment): bool
    {
        return $user->can('update_voucher::payment');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VoucherPayment $voucherPayment): bool
    {
        return $user->can('delete_voucher::payment');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_voucher::payment');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, VoucherPayment $voucherPayment): bool
    {
        return $user->can('force_delete_voucher::payment');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_voucher::payment');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, VoucherPayment $voucherPayment): bool
    {
        return $user->can('restore_voucher::payment');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_voucher::payment');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, VoucherPayment $voucherPayment): bool
    {
        return $user->can('replicate_voucher::payment');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_voucher::payment');
    }
}
