<?php

namespace App\Services\PaymentGateway;

interface PaymentGatewayInterface
{
    public function initializePayment(array $data): array;

    public function verifyPayment(string $reference): array;

    public function processWebhook(array $payload): array;

    public function getName(): string;
}
