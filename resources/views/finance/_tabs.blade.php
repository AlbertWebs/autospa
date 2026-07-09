<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('finance.index', request()->only(['from', 'to'])) }}" @class(['asp-btn !py-2', 'asp-btn-primary' => request()->routeIs('finance.index'), 'asp-btn-secondary' => !request()->routeIs('finance.index')])>Overview</a>
    <a href="{{ route('finance.income', request()->only(['from', 'to'])) }}" @class(['asp-btn !py-2', 'asp-btn-primary' => request()->routeIs('finance.income'), 'asp-btn-secondary' => !request()->routeIs('finance.income')])>Income</a>
    <a href="{{ route('finance.expenses', request()->only(['from', 'to'])) }}" @class(['asp-btn !py-2', 'asp-btn-primary' => request()->routeIs('finance.expenses'), 'asp-btn-secondary' => !request()->routeIs('finance.expenses')])>Expenses</a>
    <a href="{{ route('finance.profit-loss', request()->only(['from', 'to'])) }}" @class(['asp-btn !py-2', 'asp-btn-primary' => request()->routeIs('finance.profit-loss'), 'asp-btn-secondary' => !request()->routeIs('finance.profit-loss')])>Profit &amp; Loss</a>
</div>
