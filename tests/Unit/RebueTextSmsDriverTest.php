<?php

namespace Tests\Unit;

use App\Data\Integrations\SmsMessage;
use App\Integrations\Sms\RebueTextSmsDriver;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RebueTextSmsDriverTest extends TestCase
{
    public function test_send_posts_to_rebuetext_api_with_normalized_phone(): void
    {
        Http::fake([
            'rebuetext.com/api/v1/send-sms' => Http::response([
                [
                    'status' => true,
                    'message' => 'Message successfully sent',
                    'data' => [
                        'date' => '2025-03-15',
                        'remarks' => 'Message delivered successfully to our gateway for delivery',
                        'uniqueId' => 'abc-123',
                    ],
                ],
            ], 200),
        ]);

        $driver = new RebueTextSmsDriver('test-token', 'AUTOSPA');
        $result = $driver->send(new SmsMessage('0700123456', 'Hello from AutoSpa'));

        $this->assertTrue($result->success);
        $this->assertSame('abc-123', $result->reference);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://rebuetext.com/api/v1/send-sms'
                && $request['phone'] === '254700123456'
                && $request['sender'] === 'AUTOSPA'
                && $request['message'] === 'Hello from AutoSpa'
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function test_send_fails_when_not_configured(): void
    {
        Http::fake();

        $driver = new RebueTextSmsDriver('', '');
        $result = $driver->send(new SmsMessage('254700000000', 'Hello'));

        $this->assertFalse($result->success);
        Http::assertNothingSent();
    }

    public function test_send_handles_api_error_response(): void
    {
        Http::fake([
            'rebuetext.com/api/v1/send-sms' => Http::response([
                'message' => 'Invalid sender ID',
            ], 422),
        ]);

        $driver = new RebueTextSmsDriver('test-token', 'BAD');
        $result = $driver->send(new SmsMessage('254700000000', 'Hello'));

        $this->assertFalse($result->success);
        $this->assertSame('Invalid sender ID', $result->message);
    }
}
