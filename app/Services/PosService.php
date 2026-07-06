<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethodType;
use App\Data\Integrations\StkPushData;
use App\Data\Integrations\StkPushResult;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\Scopes\BranchScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosService
{
    public function __construct(
        protected BranchService $branchService,
        protected IntegrationService $integrationService,
        protected VehicleSmsNotificationService $vehicleSmsNotificationService,
    ) {}

    public function checkoutData(?int $branchId = null, ?JobCard $jobCard = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        $data = [
            'services' => Service::query()->where('branch_id', $branchId)->where('is_active', true)->with('category')->orderBy('name')->get(),
            'products' => Product::query()->where('branch_id', $branchId)->where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::query()
                ->where('branch_id', $branchId)
                ->with([
                    'vehicles' => fn ($query) => $query
                        ->select(['id', 'customer_id', 'registration_number'])
                        ->orderByDesc('id'),
                ])
                ->orderBy('full_name')
                ->get(),
            'paymentMethods' => PaymentMethod::query()
                ->withoutGlobalScope(BranchScope::class)
                ->where('is_active', true)
                ->whereIn('slug', [PaymentMethodType::Cash->value, PaymentMethodType::Mpesa->value])
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get(),
        ];

        if ($jobCard !== null) {
            $data['jobCardCart'] = $this->buildCartFromJobCard($jobCard);
        }

        return $data;
    }

    /** @return array{job_card_id: int, customer_id: int|null, vehicle_id: int|null, customer: array<string, mixed>|null, vehicle: array<string, mixed>|null, items: list<array<string, mixed>>} */
    public function buildCartFromJobCard(JobCard $jobCard): array
    {
        $jobCard->loadMissing(['services.service', 'products.product', 'customer', 'vehicle']);

        $items = [];

        foreach ($jobCard->services as $line) {
            $items[] = [
                'item_id' => $line->service_id,
                'item_type' => 'service',
                'description' => $line->service?->name ?? 'Service',
                'quantity' => 1,
                'unit_price' => (float) $line->price,
            ];
        }

        foreach ($jobCard->products as $line) {
            $items[] = [
                'item_id' => $line->product_id,
                'item_type' => 'product',
                'description' => $line->product_name ?: ($line->product?->name ?? 'Product'),
                'quantity' => (float) $line->quantity,
                'unit_price' => (float) $line->unit_price,
            ];
        }

        return [
            'job_card_id' => $jobCard->id,
            'customer_id' => $jobCard->customer_id,
            'vehicle_id' => $jobCard->vehicle_id,
            'customer' => $jobCard->customer ? [
                'id' => $jobCard->customer->id,
                'full_name' => $jobCard->customer->full_name,
                'phone' => $jobCard->customer->phone,
            ] : null,
            'vehicle' => $jobCard->vehicle ? [
                'id' => $jobCard->vehicle->id,
                'registration_number' => $jobCard->vehicle->registration_number,
                'make' => $jobCard->vehicle->make,
                'model' => $jobCard->vehicle->model,
            ] : null,
            'items' => $items,
        ];
    }

    public function initiateStkPush(int $branchId, array $data): StkPushResult
    {
        $customer = Customer::query()
            ->where('branch_id', $branchId)
            ->findOrFail($data['customer_id']);

        return $this->integrationService->mpesa()->initiateStkPush(
            new StkPushData(
                phone: $data['phone'],
                amount: (float) $data['amount'],
                reference: $this->generateStkReference(),
                description: 'POS sale for ' . ($customer->full_name ?: 'customer #' . $customer->id),
            )
        );
    }

    public function checkout(int $branchId, int $userId, array $data): Receipt
    {
        $receipt = DB::transaction(function () use ($branchId, $userId, $data) {
            $invoice = Invoice::create([
                'branch_id' => $branchId,
                'customer_id' => $data['customer_id'],
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'job_card_id' => $data['job_card_id'] ?? null,
                'created_by' => $userId,
                'invoice_number' => $this->nextInvoiceNumber($branchId),
                'status' => InvoiceStatus::Paid,
                'subtotal' => $data['subtotal'],
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'total_amount' => $data['total_amount'],
                'paid_amount' => $data['total_amount'],
                'balance_due' => 0,
                'issued_at' => now(),
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => 0,
                    'tax_amount' => 0,
                    'total' => $item['total'],
                ]);
            }

            Payment::create([
                'branch_id' => $branchId,
                'invoice_id' => $invoice->id,
                'customer_id' => $data['customer_id'],
                'received_by' => $userId,
                'payment_method_id' => $data['payment_method_id'],
                'method' => $data['method'],
                'amount' => $data['total_amount'],
                'status' => 'completed',
                'reference' => $data['stk_reference'] ?? null,
                'metadata' => array_filter([
                    'stk_phone' => $data['stk_phone'] ?? null,
                    'stk_status' => $data['stk_status'] ?? null,
                ], fn ($value) => filled($value)),
                'paid_at' => now(),
            ]);

            return Receipt::create([
                'branch_id' => $branchId,
                'invoice_id' => $invoice->id,
                'receipt_number' => $this->nextReceiptNumber($branchId),
                'amount' => $data['total_amount'],
                'delivery_method' => $data['method'] === PaymentMethodType::Cash->value ? 'printed' : 'digital',
            ]);
        });

        $receipt->load([
            'invoice.customer',
            'invoice.vehicle',
            'invoice.items',
            'invoice.payments.paymentMethod',
        ]);

        if ($receipt->invoice?->customer) {
            $this->vehicleSmsNotificationService->sendVehicleCollected(
                $receipt->invoice->customer,
                $receipt->invoice->vehicle
            );
        }

        return $receipt;
    }

    protected function nextInvoiceNumber(int $branchId): string
    {
        $count = Invoice::query()->where('branch_id', $branchId)->count() + 1;

        return sprintf('INV-%s-%05d', now()->format('Ymd'), $count);
    }

    protected function nextReceiptNumber(int $branchId): string
    {
        $count = Receipt::query()
            ->withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)
            ->count() + 1;

        return sprintf('RCP-%s-%05d', now()->format('Ymd'), $count);
    }

    protected function generateStkReference(): string
    {
        return sprintf('POS-%s-%s', now()->format('YmdHis'), strtoupper(Str::random(4)));
    }
}
