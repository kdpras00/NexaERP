<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use App\Traits\BelongsToBranch;
use App\Services\AccountingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model
{
    use SoftDeletes, LogsActivity, AutoGeneratesNumber, BelongsToBranch;

    protected $fillable = [
        'number', 'date', 'branch_id', 'expense_category_id', 'project_id',
        'amount', 'tax_amount', 'total', 'payment_method', 'status', 'notes', 'receipt_path'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function getNumberPrefix(): string
    {
        return 'EXP';
    }

    protected static function booted()
    {
        static::saving(function ($expense) {
            $expense->total = $expense->amount + $expense->tax_amount;
        });

        static::saved(function ($expense) {
            // Automation: Create Journal Entry
            $reference = "Expense #{$expense->number}";
            $existingJe = JournalEntry::where('description', $reference)->exists();

            if (!$existingJe && $expense->total > 0 && $expense->status === 'paid') {
                $category = $expense->category;
                $expenseAccId = $category->gl_account_id;
                
                // Determine Payment Account
                $paymentAccCode = $expense->payment_method === 'cash' ? '1-1100' : '1-1200'; // Default Cash/Bank codes
                $paymentAcc = AccountingService::getAccountByCode($paymentAccCode);

                if ($expenseAccId && $paymentAcc) {
                    $lines = [
                        ['account_id' => $expenseAccId, 'debit' => $expense->total, 'credit' => 0],
                        ['account_id' => $paymentAcc->id, 'debit' => 0, 'credit' => $expense->total],
                    ];

                    AccountingService::createJournalEntry($expense->date, $reference, $lines);
                }
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'total', 'status'])
            ->logOnlyDirty();
    }
}
