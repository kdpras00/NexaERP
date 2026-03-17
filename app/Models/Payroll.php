<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use App\Services\AccountingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Payroll extends Model
{
    use SoftDeletes, AutoGeneratesNumber, \App\Traits\BelongsToBranch;

    protected $fillable = [
        'number', 'employee_id', 'branch_id', 'month', 'pay_date',
        'basic_salary', 'allowances', 'deductions', 'net_salary', 'status'
    ];

    protected $casts = [
        'pay_date' => 'date',
    ];

    protected static function getNumberPrefix(): string
    {
        return 'PAY';
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function pay()
    {
        if ($this->status === 'paid') return;

        \DB::transaction(function () {
            $salaryExpense = AccountingService::getAccountByCode('5-2100');
            $cashAccount = AccountingService::getAccountByCode('1-1100'); // Default to Kas

            if ($salaryExpense && $cashAccount) {
                AccountingService::createJournalEntry(
                    now(),
                    "Payroll Payment #{$this->number} - {$this->employee->name} ({$this->month})",
                    [
                        [
                            'account_id' => $salaryExpense->id,
                            'debit' => $this->net_salary,
                            'credit' => 0,
                        ],
                        [
                            'account_id' => $cashAccount->id,
                            'debit' => 0,
                            'credit' => $this->net_salary,
                        ],
                    ]
                );
            }

            $this->update(['status' => 'paid', 'pay_date' => now()]);
        });
    }
}
