<?php

namespace App\Enums;

enum ActivityEvent: string
{
    case AuthLogin = 'auth.login';
    case AuthLogout = 'auth.logout';
    case BranchSwitched = 'branch.switched';
    case SyncMutationApplied = 'sync.mutation_applied';
    case CommissionMpesaInitiated = 'commission.mpesa_initiated';
    case CommissionPaid = 'commission.paid';
}
