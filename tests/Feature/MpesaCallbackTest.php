<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethodType;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MpesaTransaction;
use App\Models\Payment;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MpesaCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_stk_callback_updates_payment_and_transaction_log(): void
    {
        $branch = Branch::query()->firstOrFail();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $invoice = Invoice::query()->create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TEST-001',
            'status' => InvoiceStatus::Paid,
            'subtotal' => 500,
            'total_amount' => 500,
            'paid_amount' => 500,
            'balance_due' => 0,
            'issued_at' => now(),
        ]);

        $checkoutRequestId = 'ws_CO_123456';

        MpesaTransaction::query()->create([
            'branch_id' => $branch->id,
            'flow' => 'stk',
            'direction' => 'outbound',
            'reference' => $checkoutRequestId,
            'checkout_request_id' => $checkoutRequestId,
            'phone' => '254712345678',
            'amount' => 500,
            'status' => 'accepted',
            'result_code' => '0',
            'processed_at' => now(),
        ]);

        $payment = Payment::query()->create([
            'branch_id' => $branch->id,
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'method' => PaymentMethodType::Mpesa,
            'amount' => 500,
            'status' => 'pending',
            'reference' => $checkoutRequestId,
            'metadata' => [
                'stk_checkout_request_id' => $checkoutRequestId,
            ],
            'paid_at' => now(),
        ]);

        $response = $this->postJson(route('api.mpesa.stk.result'), [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => '17413-123',
                    'CheckoutRequestID' => $checkoutRequestId,
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is processed successfully.',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 500],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'QGH123ABC'],
                            ['Name' => 'PhoneNumber', 'Value' => 254712345678],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertOk()->assertJson([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);

        $payment->refresh();

        $this->assertSame('completed', $payment->status);
        $this->assertSame('QGH123ABC', $payment->reference);
        $this->assertSame('0', $payment->metadata['stk_callback']['result_code']);

        $this->assertDatabaseHas('mpesa_transactions', [
            'flow' => 'stk',
            'direction' => 'inbound',
            'checkout_request_id' => $checkoutRequestId,
            'status' => 'success',
        ]);
    }

    public function test_b2c_callback_is_logged_and_links_outbound_request(): void
    {
        $conversationId = 'AG_20250709_00001';

        MpesaTransaction::query()->create([
            'flow' => 'b2c',
            'direction' => 'outbound',
            'reference' => $conversationId,
            'conversation_id' => $conversationId,
            'phone' => '254712345678',
            'amount' => 1500,
            'status' => 'accepted',
            'result_code' => '0',
            'processed_at' => now(),
        ]);

        $response = $this->postJson(route('api.mpesa.result'), [
            'Result' => [
                'ResultType' => 0,
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'OriginatorConversationID' => '12345-67890',
                'ConversationID' => $conversationId,
                'TransactionID' => 'QGH123XYZ',
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('mpesa_transactions', [
            'flow' => 'b2c',
            'direction' => 'inbound',
            'conversation_id' => $conversationId,
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('mpesa_transactions', [
            'flow' => 'b2c',
            'direction' => 'outbound',
            'conversation_id' => $conversationId,
            'status' => 'success',
        ]);
    }

    public function test_balance_callback_is_logged(): void
    {
        $conversationId = 'AG_20250709_BAL01';

        MpesaTransaction::query()->create([
            'flow' => 'balance',
            'direction' => 'outbound',
            'reference' => $conversationId,
            'conversation_id' => $conversationId,
            'status' => 'accepted',
            'result_code' => '0',
            'processed_at' => now(),
        ]);

        $response = $this->postJson(route('api.mpesa.balance.result'), [
            'Result' => [
                'ResultType' => 0,
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'OriginatorConversationID' => '12345-00001',
                'ConversationID' => $conversationId,
                'ResultParameters' => [
                    'ResultParameter' => [
                        ['Key' => 'AccountBalance', 'Value' => 'Working Account|KES|150000.00'],
                    ],
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('mpesa_transactions', [
            'flow' => 'balance',
            'direction' => 'inbound',
            'conversation_id' => $conversationId,
            'status' => 'success',
        ]);
    }

    public function test_timeout_callback_is_logged(): void
    {
        $conversationId = 'AG_20250709_TIMEOUT';

        MpesaTransaction::query()->create([
            'flow' => 'b2c',
            'direction' => 'outbound',
            'reference' => $conversationId,
            'conversation_id' => $conversationId,
            'status' => 'accepted',
            'processed_at' => now(),
        ]);

        $response = $this->postJson(route('api.mpesa.timeout'), [
            'ConversationID' => $conversationId,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('mpesa_transactions', [
            'flow' => 'b2c',
            'direction' => 'inbound',
            'status' => 'timeout',
            'conversation_id' => $conversationId,
        ]);
    }
}
