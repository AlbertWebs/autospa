<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class PosService
{
    public function __construct(
        protected BranchService $branchService,
    ) {}

    public function checkoutData(?int $branchId = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        return [
            'services' => Service::query()->where('branch_id', $branchId)->where('is_active', true)->with('category')->get(),
            'products' => Product::query()->where('branch_id', $branchId)->where('is_active', true)->get(),
            'customers' => Customer::query()->where('branch_id', $branchId)->orderBy('full_name')->get(),
            'paymentMethods' => PaymentMethod::query()->where('branch_id', $branchId)->where('is_active', true)->get(),
        ];
    }

    public function checkout(int $branchId, int $userId, array $data): Invoice
    {
        return DB::transaction(function () use ($branchId, $userId, $data) {
            $invoice = Invoice::create([
                'branch_id' => $branchId,
                'customer_id' => $data['customer_id'],
                'vehicle_id' => $data['vehicle_id'] ?? null,
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
                $invoice->items()->create($item);
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
                'paid_at' => now(),
            ]);

            return $invoice->load('items');
        });
    }

    protected function nextInvoiceNumber(int $branchId): string
    {
        $count = Invoice::query()->where('branch_id', $branchId)->count() + 1;

        return sprintf('INV-%s-%05d', now()->format('Ymd'), $count);
    }
}
