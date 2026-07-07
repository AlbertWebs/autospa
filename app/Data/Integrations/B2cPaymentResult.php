<?php

namespace App\Data\Integrations;

readonly class B2cPaymentResult
{
    public static function success(string $reference, ?string $message = null): self
    {
        return new self(true, $reference, $message);
    }

    public static function failed(?string $message = null): self
    {
        return new self(false, null, $message);
    }

    public static function pending(
        string $payoutToken,
        string $otpSentTo,
        ?string $message = null,
    ): self {
        return new self(
            successful: false,
            reference: null,
            message: $message ?? 'Enter the OTP sent to your phone to authorize this payout.',
            requiresOtp: true,
            payoutToken: $payoutToken,
            otpSentTo: $otpSentTo,
        );
    }

    public function __construct(
        public bool $successful,
        public ?string $reference = null,
        public ?string $message = null,
        public bool $requiresOtp = false,
        public ?string $payoutToken = null,
        public ?string $otpSentTo = null,
    ) {}
}
