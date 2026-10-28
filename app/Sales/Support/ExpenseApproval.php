<?php

namespace App\Sales\Support;

use App\Models\User;
use App\Sales\Models\SalesExpense;
use Illuminate\Support\Facades\DB;

/**
 * The ONE place that decides waiting expenses from every screen (the Expenses queue, the Sales management page, a rep's profile).
 * The same rules everywhere: the manager must be allowed to manage expenses, the expense must still be waiting, the policy `review`
 * must allow it (never one's own), and SalesExpense::approve() / reject() write the audit trail. A row is locked while it is checked and
 * decided, so two managers clicking together decide it once, with one audit entry.
 */
class ExpenseApproval
{
    /** @return 'approved'|'decided' decided = somebody else got there first (nothing was changed) */
    public static function approve(?User $user, int $id): string
    {
        abort_unless($user?->canManage('sales-expenses'), 403);

        return DB::transaction(function () use ($user, $id) {
            $expense = SalesExpense::whereKey($id)->where('status', SalesExpense::PENDING)->lockForUpdate()->first();

            if (! $expense) {
                return 'decided';
            }

            abort_unless($user->can('review', $expense), 403);
            $expense->approve($user);

            return 'approved';
        });
    }

    /** @return 'rejected'|'decided' */
    public static function reject(?User $user, int $id, string $reason): string
    {
        abort_unless($user?->canManage('sales-expenses'), 403);

        return DB::transaction(function () use ($user, $id, $reason) {
            $expense = SalesExpense::whereKey($id)->where('status', SalesExpense::PENDING)->lockForUpdate()->first();

            if (! $expense) {
                return 'decided';
            }

            abort_unless($user->can('review', $expense), 403);
            $expense->reject($user, trim($reason));

            return 'rejected';
        });
    }

    /**
     * Approve several at once, the safe way: only an expense that is still waiting, says it has a bill AND has its photo, and that this
     * manager may review (never their own). Everything else is skipped and counted; nothing without a bill is ever approved here.
     *
     * @param  list<int>  $ids
     * @return array{approved: int, skipped: int}
     */
    public static function approveWithBill(?User $user, array $ids): array
    {
        abort_unless($user?->canManage('sales-expenses'), 403);

        $approved = 0;

        foreach (array_values(array_unique(array_map('intval', $ids))) as $id) {
            $approved += (int) DB::transaction(function () use ($user, $id) {
                $expense = SalesExpense::whereKey($id)->where('status', SalesExpense::PENDING)->where('bill_type', 'with_bill')->whereNotNull('bill_path')->where('bill_path', '!=', '')->lockForUpdate()->first();

                if (! $expense || ! $user->can('review', $expense)) {
                    return false;
                }

                $expense->approve($user);

                return true;
            });
        }

        return ['approved' => $approved, 'skipped' => count(array_unique($ids)) - $approved];
    }
}
