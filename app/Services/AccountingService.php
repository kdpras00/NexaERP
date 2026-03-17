<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Auth;

class AccountingService
{
    public static function createJournalEntry($date, $description, $lines)
    {
        $je = JournalEntry::create([
            'date' => $date,
            'description' => $description,
            'status' => 'posted', // Auto post for automation
            'created_by' => Auth::id() ?? 1,
        ]);

        foreach ($lines as $line) {
            $je->lines()->create([
                'account_id' => $line['account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
            ]);
        }

        return $je;
    }

    public static function getAccountByCode($code)
    {
        return ChartOfAccount::where('code', $code)->first();
    }
}
