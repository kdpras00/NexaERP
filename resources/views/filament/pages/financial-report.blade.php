<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        @php $data = $this->getReportData(); @endphp

        @if(isset($data['type']))
            @if($data['type'] === 'profit_loss')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Laporan Laba Rugi (Profit & Loss)</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Periode: {{ $data['period'] }}</p>
                    </div>
                    <div class="p-6">
                        <table class="w-full text-sm">
                            <tbody>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-3 font-semibold text-gray-900 dark:text-white">Pendapatan (Revenue)</td>
                                    <td class="py-3 text-right font-semibold text-green-600">Rp {{ number_format($data['revenue'], 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-3 text-gray-600 dark:text-gray-300">Harga Pokok Penjualan (COGS)</td>
                                    <td class="py-3 text-right text-red-600">(Rp {{ number_format($data['cogs'], 0, ',', '.') }})</td>
                                </tr>
                                <tr class="border-b-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                                    <td class="py-3 px-2 font-bold text-gray-900 dark:text-white">Laba Kotor (Gross Profit)</td>
                                    <td class="py-3 px-2 text-right font-bold {{ $data['gross_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($data['gross_profit'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-3 text-gray-600 dark:text-gray-300">Beban Operasional</td>
                                    <td class="py-3 text-right text-red-600">(Rp {{ number_format($data['operating_expenses'], 0, ',', '.') }})</td>
                                </tr>
                                <tr class="bg-indigo-50 dark:bg-indigo-900/30 border-t-2 border-indigo-300 dark:border-indigo-700">
                                    <td class="py-4 px-2 font-bold text-lg text-gray-900 dark:text-white">Laba Bersih (Net Profit)</td>
                                    <td class="py-4 px-2 text-right font-bold text-lg {{ $data['net_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($data['net_profit'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($data['type'] === 'balance_sheet')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Neraca (Balance Sheet)</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Assets --}}
                            <div>
                                <h3 class="text-lg font-bold text-green-600 mb-4 border-b-2 border-green-200 pb-2">ASET</h3>
                                @foreach ($data['assets'] as $group)
                                    @if($group->children->count())
                                        @foreach($group->children as $child)
                                            <div class="flex justify-between py-1 text-sm">
                                                <span class="text-gray-600 dark:text-gray-300">{{ $child->code }} - {{ $child->name }}</span>
                                                @php
                                                    $bal = $data['balances']->get($child->id);
                                                    $amount = $bal ? ((float)($bal->total_debit ?? 0) - (float)($bal->total_credit ?? 0)) : 0;
                                                @endphp
                                                <span class="font-mono">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                            {{-- Liabilities + Equity --}}
                            <div>
                                <h3 class="text-lg font-bold text-red-600 mb-4 border-b-2 border-red-200 pb-2">KEWAJIBAN & EKUITAS</h3>
                                @foreach ($data['liabilities'] as $group)
                                    @if($group->children->count())
                                        @foreach($group->children as $child)
                                            <div class="flex justify-between py-1 text-sm">
                                                <span class="text-gray-600 dark:text-gray-300">{{ $child->code }} - {{ $child->name }}</span>
                                                @php
                                                    $bal = $data['balances']->get($child->id);
                                                    $amount = $bal ? ((float)($bal->total_credit ?? 0) - (float)($bal->total_debit ?? 0)) : 0;
                                                @endphp
                                                <span class="font-mono">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach
                                @foreach ($data['equity'] as $group)
                                    @if($group->children->count())
                                        @foreach($group->children as $child)
                                            <div class="flex justify-between py-1 text-sm">
                                                <span class="text-gray-600 dark:text-gray-300">{{ $child->code }} - {{ $child->name }}</span>
                                                @php
                                                    $bal = $data['balances']->get($child->id);
                                                    $amount = $bal ? ((float)($bal->total_credit ?? 0) - (float)($bal->total_debit ?? 0)) : 0;
                                                @endphp
                                                <span class="font-mono">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($data['type'] === 'budget_vs_actual')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Budget vs Actual</h2>
                        <p class="text-sm text-gray-500">{{ $data['period'] }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Account</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Budget</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Actual</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Variance</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">% Usage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['data'] as $row)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="px-6 py-3">{{ $row['account'] }}</td>
                                    <td class="px-6 py-3 text-right font-mono">Rp {{ number_format($row['budget'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-red-600">Rp {{ number_format($row['actual'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono {{ $row['variance'] < 0 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                                        Rp {{ number_format($row['variance'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 overflow-hidden">
                                            <div class="bg-{{ $row['percent'] > 100 ? 'red' : ($row['percent'] > 80 ? 'yellow' : 'green') }}-600 h-2.5" style="width: {{ min(100, $row['percent']) }}%"></div>
                                        </div>
                                        <span class="text-[10px]">{{ number_format($row['percent'], 1) }}%</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($data['type'] === 'tax_summary')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Rekapitulasi PPN (Tax Summary)</h2>
                        <p class="text-sm text-gray-500">Periode: {{ $this->data['date_from'] ?? '...' }} s/d {{ $this->data['date_to'] ?? '...' }}</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-blue-800 dark:text-blue-300">PPN Keluaran (VAT Collected on Sales)</p>
                                    <p class="text-2xl font-bold text-blue-900 dark:text-white">Rp {{ number_format($data['ppn_keluaran'], 0, ',', '.') }}</p>
                                </div>
                                <x-heroicon-o-arrow-up-right class="w-8 h-8 text-blue-500" />
                            </div>
                            <div class="flex justify-between items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-green-800 dark:text-green-300">PPN Masukan (VAT Paid on Purchases)</p>
                                    <p class="text-2xl font-bold text-green-900 dark:text-white">Rp {{ number_format($data['ppn_masukan'], 0, ',', '.') }}</p>
                                </div>
                                <x-heroicon-o-arrow-down-left class="w-8 h-8 text-green-500" />
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">Sisa PPN yang Harus Dibayar / (Lebih Bayar)</p>
                                <p class="text-2xl font-extrabold {{ $data['net_payable'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                    Rp {{ number_format($data['net_payable'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($data['type'] === 'project_profitability')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Project Profitability</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Project Name</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Total Revenue</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Total Expense</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Net Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['data'] as $row)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="px-6 py-3 font-medium">{{ $row['name'] }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-green-600">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-red-600">Rp {{ number_format($row['expense'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono font-bold {{ $row['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($row['profit'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($data['type'] === 'ar_aging')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">AR Aging Report (Piutang Jatuh Tempo)</h2>
                        <p class="text-sm text-gray-500">Total Outstanding: <strong class="text-red-600">Rp {{ number_format($data['total'], 0, ',', '.') }}</strong></p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Customer</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Invoice</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Amount</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Due Date</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Aging</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($data['data'] ?? []) > 0)
                                    @foreach ($data['data'] as $item)
                                    <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-3">{{ $item->customer?->name ?? '-' }}</td>
                                        <td class="px-6 py-3">{{ $item->salesInvoice?->number ?? '-' }}</td>
                                        <td class="px-6 py-3 text-right font-mono">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-3">{{ $item->due_date?->format('d M Y') ?? '-' }}</td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $item->aging_bucket === 'Current' ? 'bg-green-100 text-green-800' :
                                                   ($item->aging_bucket === '1-30 Days' ? 'bg-yellow-100 text-yellow-800' :
                                                   ($item->aging_bucket === '31-60 Days' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')) }}">
                                                {{ $item->aging_bucket }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No outstanding receivables.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($data['type'] === 'ap_aging')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">AP Aging Report (Hutang Jatuh Tempo)</h2>
                        <p class="text-sm text-gray-500">Total Outstanding: <strong class="text-red-600">Rp {{ number_format($data['total'], 0, ',', '.') }}</strong></p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Supplier</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Invoice</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Amount</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Due Date</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Aging</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($data['data'] ?? []) > 0)
                                    @foreach ($data['data'] as $item)
                                    <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-3">{{ $item->supplier?->name ?? '-' }}</td>
                                        <td class="px-6 py-3">{{ $item->purchaseInvoice?->number ?? '-' }}</td>
                                        <td class="px-6 py-3 text-right font-mono">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-3">{{ $item->due_date?->format('d M Y') ?? '-' }}</td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $item->aging_bucket === 'Current' ? 'bg-green-100 text-green-800' :
                                                   ($item->aging_bucket === '1-30 Days' ? 'bg-yellow-100 text-yellow-800' :
                                                   ($item->aging_bucket === '31-60 Days' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')) }}">
                                                {{ $item->aging_bucket }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No outstanding payables.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
