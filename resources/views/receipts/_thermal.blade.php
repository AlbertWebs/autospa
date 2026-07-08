@php
    $company = app(\App\Services\CompanyService::class);
    $companyModel = $company->company();
    $branch = $receipt->branch;
    $invoice = $receipt->invoice;
    $customer = $invoice?->customer;
    $vehicle = $invoice?->vehicle;
    $payments = $invoice?->payments ?? collect();
    $items = $invoice?->items ?? collect();
@endphp

<article class="asp-receipt-thermal" aria-hidden="true">
    <header class="asp-receipt-thermal__header">
        <p class="asp-receipt-thermal__brand">{{ $company->displayName() }}</p>
        @if (filled($branch?->name))
            <p class="asp-receipt-thermal__meta">{{ $branch->name }}</p>
        @endif
        @if (filled($companyModel?->address))
            <p class="asp-receipt-thermal__meta">{{ $companyModel->address }}</p>
        @elseif (filled($branch?->address))
            <p class="asp-receipt-thermal__meta">{{ $branch->address }}</p>
        @endif
        @if (filled($companyModel?->phone))
            <p class="asp-receipt-thermal__meta">Tel: {{ $companyModel->phone }}</p>
        @elseif (filled($branch?->phone))
            <p class="asp-receipt-thermal__meta">Tel: {{ $branch->phone }}</p>
        @endif
        @if (filled($companyModel?->tax_number))
            <p class="asp-receipt-thermal__meta">PIN: {{ $companyModel->tax_number }}</p>
        @endif
    </header>

    <p class="asp-receipt-thermal__title">SALES RECEIPT</p>
    <p class="asp-receipt-thermal__rule" aria-hidden="true"></p>

    <dl class="asp-receipt-thermal__meta-list">
        <div class="asp-receipt-thermal__meta-row">
            <dt>Receipt</dt>
            <dd>{{ $receipt->receipt_number }}</dd>
        </div>
        <div class="asp-receipt-thermal__meta-row">
            <dt>Date</dt>
            <dd>{{ $receipt->created_at?->format('d/m/Y H:i') }}</dd>
        </div>
        @if (filled($invoice?->invoice_number))
            <div class="asp-receipt-thermal__meta-row">
                <dt>Invoice</dt>
                <dd>{{ $invoice->invoice_number }}</dd>
            </div>
        @endif
        <div class="asp-receipt-thermal__meta-row">
            <dt>Customer</dt>
            <dd>{{ $customer?->full_name ?? 'Walk-in' }}</dd>
        </div>
        @if (filled($customer?->phone))
            <div class="asp-receipt-thermal__meta-row">
                <dt>Phone</dt>
                <dd>{{ $customer->phone }}</dd>
            </div>
        @endif
        @if (filled($vehicle?->registration_number))
            <div class="asp-receipt-thermal__meta-row">
                <dt>Vehicle</dt>
                <dd>{{ $vehicle->registration_number }}</dd>
            </div>
        @endif
    </dl>

    <p class="asp-receipt-thermal__rule" aria-hidden="true"></p>

    <div class="asp-receipt-thermal__items">
        @foreach ($items as $item)
            <div class="asp-receipt-thermal__item">
                <p class="asp-receipt-thermal__item-name">{{ $item->description }}</p>
                <div class="asp-receipt-thermal__item-row">
                    <span>{{ number_format((float) $item->quantity, $item->quantity == floor($item->quantity) ? 0 : 2) }} x {{ number_format((float) $item->unit_price, 2) }}</span>
                    <span>{{ number_format((float) $item->total, 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <p class="asp-receipt-thermal__rule" aria-hidden="true"></p>

    <dl class="asp-receipt-thermal__totals">
        <div class="asp-receipt-thermal__total-row">
            <dt>Subtotal</dt>
            <dd>{{ number_format((float) ($invoice?->subtotal ?? 0), 2) }}</dd>
        </div>
        @if ((float) ($invoice?->tax_amount ?? 0) > 0)
            <div class="asp-receipt-thermal__total-row">
                <dt>Tax</dt>
                <dd>{{ number_format((float) $invoice->tax_amount, 2) }}</dd>
            </div>
        @endif
        <div class="asp-receipt-thermal__total-row asp-receipt-thermal__total-row--grand">
            <dt>TOTAL KES</dt>
            <dd>{{ number_format((float) ($invoice?->total_amount ?? $receipt->amount), 2) }}</dd>
        </div>
    </dl>

    @if ($payments->isNotEmpty())
        <p class="asp-receipt-thermal__rule" aria-hidden="true"></p>
        <div class="asp-receipt-thermal__payments">
            @foreach ($payments as $payment)
                <div class="asp-receipt-thermal__payment">
                    <div class="asp-receipt-thermal__payment-row">
                        <span>{{ $payment->paymentMethod?->name ?? $payment->method?->value ?? 'Payment' }}</span>
                        <span>{{ number_format((float) $payment->amount, 2) }}</span>
                    </div>
                    @if (filled($payment->reference))
                        <p class="asp-receipt-thermal__payment-ref">Ref: {{ $payment->reference }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <p class="asp-receipt-thermal__rule" aria-hidden="true"></p>
    <p class="asp-receipt-thermal__thanks">Thank you for your business!</p>
    <p class="asp-receipt-thermal__footer">{{ config('app.name') }}</p>
</article>
