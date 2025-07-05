<?php

namespace App\Models;

enum AccountStatusEnum: string {// Active, Suspended, Deleted, Pending
    case Active = 'Active';
    case Suspended = 'Suspended';
    case Deleted = 'Deleted';
    case Pending = 'Pending';
    case Banned = 'Banned'; // Added new status for banned accounts
    case Deactivated = 'Deactivated'; // Added new status for deactivated accounts
    case Closed = 'Closed'; // Added new status for closed accounts
    case Archived = 'Archived'; // Added new status for archived accounts
    case Unverified = 'Unverified'; // Added new status for unverified accounts
    case Locked = 'Locked'; // Added new status for locked accounts
    case Frozen = 'Frozen'; // Added new status for frozen accounts
    case Inactive = 'Inactive'; // Added new status for inactive accounts
    case Error = 'Error'; // Added new status for accounts with errors
    case PendingVerification = 'PendingVerification'; // Added new status for accounts pending verification
    case SuspendedForReview = 'SuspendedForReview'; // Added new status for accounts suspended for review
    case TemporarySuspension = 'TemporarySuspension'; // Added new status for temporarily suspended accounts
    case PermanentSuspension = 'PermanentSuspension'; // Added new status for permanently suspended accounts
    case UnderInvestigation = 'UnderInvestigation'; // Added new status for accounts under investigation
    case Reinstated = 'Reinstated'; // Added new status for reinstated accounts
    case PendingDeletion = 'PendingDeletion'; // Added new status for accounts pending deletion
    case PendingClosure = 'PendingClosure'; // Added new status for accounts pending closure
    case PendingArchival = 'PendingArchival'; // Added new status for accounts pending archival
    case PendingDeactivation = 'PendingDeactivation'; // Added new status for accounts pending deactivation
    case PendingLock = 'PendingLock'; // Added new status for accounts pending lock
    case PendingUnlock = 'PendingUnlock'; // Added new status for accounts pending unlock
    case PendingFreeze = 'PendingFreeze'; // Added new status for accounts pending freeze
    case PendingReactivation = 'PendingReactivation'; // Added new status for accounts pending reactivation
}