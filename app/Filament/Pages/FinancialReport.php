<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use App\Models\AccountsReceivable;
use App\Models\AccountsPayable;
use App\Models\Tax;
use App\Models\Budget;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;

class FinancialReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Financial Reports';
    protected static string $view = 'filament.pages.financial-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'report_type' => 'profit_loss',
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Report Configuration')
                    ->schema([
                        Select::make('report_type')
                            ->options([
                                'profit_loss' => 'Profit & Loss (Laba Rugi)',
                                'balance_sheet' => 'Balance Sheet (Neraca)',
                                'budget_vs_actual' => 'Budget vs Actual',
                                'project_profitability' => 'Project Profitability',
                                'tax_summary' => 'Tax Summary (Recap PPN)',
                                'ar_aging' => 'AR Aging (Piutang)',
                                'ap_aging' => 'AP Aging (Hutang)',
                            ])
                            ->required()
                            ->reactive(),
                        DatePicker::make('date_from'),
                        DatePicker::make('date_to'),
                        Select::make('branch_id')
                            ->label('Branch Scope')
                            ->options(Branch::pluck('name', 'id'))
                            ->placeholder('All Branches'),
                    ])->columns(['md' => 4]),
            ])
            ->statePath('data');
    }

    public function getReportData(): array
    {
        $type = $this->data['report_type'] ?? 'profit_loss';

        return match ($type) {
            'profit_loss' => $this->getProfitLossData(),
            'balance_sheet' => $this->getBalanceSheetData(),
            'budget_vs_actual' => $this->getBudgetData(),
            'project_profitability' => $this->getProjectProfitabilityData(),
            'tax_summary' => $this->getTaxSummaryData(),
            'ar_aging' => $this->getArAgingData(),
            'ap_aging' => $this->getApAgingData(),
            default => [],
        };
    }

    protected function getProfitLossData(): array
    {
        $from = $this->data['date_from'] ?? null;
        $to = $this->data['date_to'] ?? null;
        $branchId = $this->data['branch_id'] ?? null;

        $incomeQuery = ChartOfAccount::where('type', 'revenue')->withSum(['journalEntryLines as total' => function ($query) use ($from, $to, $branchId) {
            $query->whereHas('journalEntry', function ($q) use ($from, $to, $branchId) {
                $q->where('status', 'posted');
                if ($from) $q->where('date', '>=', $from);
                if ($to) $q->where('date', '<=', $to);
                if ($branchId) $q->where('branch_id', $branchId);
            });
        }], 'credit');

        $cogsQuery = ChartOfAccount::where('type', 'cost_of_goods_sold')->withSum(['journalEntryLines as total' => function ($query) use ($from, $to, $branchId) {
            $query->whereHas('journalEntry', function ($q) use ($from, $to, $branchId) {
                $q->where('status', 'posted');
                if ($from) $q->where('date', '>=', $from);
                if ($to) $q->where('date', '<=', $to);
                if ($branchId) $q->where('branch_id', $branchId);
            });
        }], 'debit');

        $expenseQuery = ChartOfAccount::where('type', 'expense')->withSum(['journalEntryLines as total' => function ($query) use ($from, $to, $branchId) {
            $query->whereHas('journalEntry', function ($q) use ($from, $to, $branchId) {
                $q->where('status', 'posted');
                if ($from) $q->where('date', '>=', $from);
                if ($to) $q->where('date', '<=', $to);
                if ($branchId) $q->where('branch_id', $branchId);
            });
        }], 'debit');

        $income = $incomeQuery->get();
        $cogs = $cogsQuery->get();
        $expense = $expenseQuery->get();

        $totalRevenue = $income->sum('total');
        $totalCogs = $cogs->sum('total');
        $totalExpense = $expense->sum('total');

        return [
            'type' => 'profit_loss',
            'period' => ($from ?? '...') . ' to ' . ($to ?? '...'),
            'revenue' => $totalRevenue,
            'cogs' => $totalCogs,
            'gross_profit' => $totalRevenue - $totalCogs,
            'operating_expenses' => $totalExpense,
            'net_profit' => $totalRevenue - $totalCogs - $totalExpense,
            'income' => $income,
            'expense' => $expense,
        ];
    }

    protected function getBalanceSheetData(): array
    {
        $branchId = $this->data['branch_id'] ?? null;
        $date = $this->data['date_to'] ?? now()->format('Y-m-d');

        $assets = ChartOfAccount::where('type', 'asset')->get();
        $liabilities = ChartOfAccount::where('type', 'liability')->get();
        $equity = ChartOfAccount::where('type', 'equity')->get();

        $balances = JournalEntryLine::whereHas('journalEntry', function ($q) use ($date, $branchId) {
            $q->where('status', 'posted')->where('date', '<=', $date);
            if ($branchId) $q->where('branch_id', $branchId);
        })
            ->select('account_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        return [
            'type' => 'balance_sheet',
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'balances' => $balances,
        ];
    }

    protected function getArAgingData(): array
    {
        $ar = AccountsReceivable::with(['customer', 'salesInvoice'])
            ->where(function($query) {
                $query->where('status', 'unpaid');
            })
            ->orderBy('due_date')
            ->get()
            ->map(function ($item) {
                $daysOverdue = now()->diffInDays($item->due_date, false);
                $item->days_overdue = $daysOverdue < 0 ? abs($daysOverdue) : 0;
                $item->aging_bucket = match (true) {
                    $daysOverdue >= 0 => 'Current',
                    $daysOverdue >= -30 => '1-30 Days',
                    $daysOverdue >= -60 => '31-60 Days',
                    $daysOverdue >= -90 => '61-90 Days',
                    default => '90+ Days',
                };
                return $item;
            });

        return [
            'type' => 'ar_aging',
            'data' => $ar,
            'total' => $ar->sum('amount'),
        ];
    }

    protected function getApAgingData(): array
    {
        $ap = AccountsPayable::with(['supplier', 'purchaseInvoice'])
            ->where(function($query) {
                $query->where('status', 'unpaid');
            })
            ->orderBy('due_date')
            ->get()
            ->map(function ($item) {
                $daysOverdue = now()->diffInDays($item->due_date, false);
                $item->days_overdue = $daysOverdue < 0 ? abs($daysOverdue) : 0;
                $item->aging_bucket = match (true) {
                    $daysOverdue >= 0 => 'Current',
                    $daysOverdue >= -30 => '1-30 Days',
                    $daysOverdue >= -60 => '31-60 Days',
                    $daysOverdue >= -90 => '61-90 Days',
                    default => '90+ Days',
                };
                return $item;
            });

        return [
            'type' => 'ap_aging',
            'data' => $ap,
            'total' => $ap->sum('amount'),
        ];
    }

    protected function getBudgetData(): array
    {
        $from = $this->data['date_from'] ?? null;
        $to = $this->data['date_to'] ?? null;
        $branchId = $this->data['branch_id'] ?? null;

        $budgets = Budget::with(['account', 'branch'])->get();
        
        $transformedData = $budgets->map(function ($b) use ($from, $to, $branchId) {
            $actual = JournalEntryLine::where('account_id', $b->account_id)
                ->whereHas('journalEntry', function ($q) use ($from, $to, $branchId) {
                    $q->where('status', 'posted');
                    if ($from) $q->where('date', '>=', $from);
                    if ($to) $q->where('date', '<=', $to);
                    if ($branchId) $q->where('branch_id', $branchId);
                })
                ->sum('debit');

            return [
                'account' => ($b->account->code ?? '') . ' - ' . ($b->account->name ?? ''),
                'budget' => $b->amount,
                'actual' => $actual,
                'variance' => $b->amount - $actual,
                'percent' => $b->amount > 0 ? ($actual / $b->amount) * 100 : 0,
            ];
        });

        return [
            'type' => 'budget_vs_actual',
            'period' => ($from ?? '...') . ' to ' . ($to ?? '...'),
            'data' => $transformedData,
        ];
    }

    protected function getProjectProfitabilityData(): array
    {
        $projects = \App\Models\Project::all();
        
        $data = $projects->map(function ($p) {
            $revenue = JournalEntryLine::whereHas('journalEntry', function($q) use ($p) {
                $q->where('status', 'posted')->where('project_id', $p->id);
            })
                ->whereHas('account', function($q) {
                    $q->where('code', 'like', '4-%');
                })
                ->sum('credit');

            $expense = JournalEntryLine::whereHas('journalEntry', function($q) use ($p) {
                $q->where('status', 'posted')->where('project_id', $p->id);
            })
                ->whereHas('account', function($q) {
                    $q->where('code', 'like', '5-%')->orWhere('code', 'like', '6-%');
                })
                ->sum('debit');

            return [
                'name' => $p->name,
                'revenue' => $revenue,
                'expense' => $expense,
                'profit' => $revenue - $expense,
            ];
        });

        return [
            'type' => 'project_profitability',
            'data' => $data,
        ];
    }

    protected function getTaxSummaryData(): array
    {
        $from = $this->data['date_from'] ?? null;
        $to = $this->data['date_to'] ?? null;
        $branchId = $this->data['branch_id'] ?? null;

        $vatIn = JournalEntryLine::whereHas('account', function($q) { $q->where('code', 'like', '1-14%'); }) // PPN Masukan
            ->whereHas('journalEntry', function($q) use ($from, $to, $branchId) {
                $q->where('status', 'posted');
                if ($from) $q->where('date', '>=', $from);
                if ($to) $q->where('date', '<=', $to);
                if ($branchId) $q->where('branch_id', $branchId);
            })->sum('debit');

        $vatOut = JournalEntryLine::whereHas('account', function($q) { $q->where('code', 'like', '2-14%'); }) // PPN Keluaran
            ->whereHas('journalEntry', function($q) use ($from, $to, $branchId) {
                $q->where('status', 'posted');
                if ($from) $q->where('date', '>=', $from);
                if ($to) $q->where('date', '<=', $to);
                if ($branchId) $q->where('branch_id', $branchId);
            })->sum('credit');

        return [
            'type' => 'tax_summary',
            'ppn_masukan' => $vatIn,
            'ppn_keluaran' => $vatOut,
            'net_payable' => $vatOut - $vatIn,
        ];
    }
}
