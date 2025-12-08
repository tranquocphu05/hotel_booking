<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SePayService
{
    protected string $merchantId;
    protected string $secretKey;
    protected string $baseUrl;
    protected string $bankAccountNumber;
    protected string $bankCode;
    protected string $accountName;
    protected string $pattern;

    public function __construct()
    {
        $this->merchantId = config('services.sepay.merchant_id');
        $this->secretKey = config('services.sepay.secret_key');
        $this->baseUrl = config('services.sepay.base_url', 'https://my.sepay.vn/userapi');
        $this->bankAccountNumber = config('services.sepay.bank_account_number');
        $this->bankCode = config('services.sepay.bank_code');
        $this->accountName = config('services.sepay.account_name');
        $this->pattern = config('services.sepay.pattern', 'SE');
    }

    /**
     * Generate payment code for booking
     * Format: SE{booking_id} - e.g., SE123
     */
    public function generatePaymentCode(int $bookingId): string
    {
        return $this->pattern . $bookingId;
    }

    /**
     * Generate VietQR URL for payment
     * 
     * @param int $bookingId
     * @param float $amount
     * @param string|null $description
     * @return array
     */
    public function generateQRCode(int $bookingId, float $amount, ?string $description = null): array
    {
        $paymentCode = $this->generatePaymentCode($bookingId);
        $description = $description ?? "Thanh toan don hang {$paymentCode}";
        
        // Build VietQR URL
        // Format: https://img.vietqr.io/image/{bank_code}-{account_number}-compact2.png?amount={amount}&addInfo={content}&accountName={account_name}
        $qrUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
            $this->bankCode,
            $this->bankAccountNumber,
            (int) $amount,
            urlencode($paymentCode),
            urlencode($this->accountName)
        );

        // Alternative SePay QR format (if configured)
        $sepayQrUrl = sprintf(
            'https://qr.sepay.vn/img?acc=%s&bank=%s&amount=%d&des=%s',
            $this->bankAccountNumber,
            $this->bankCode,
            (int) $amount,
            urlencode($paymentCode)
        );

        return [
            'payment_code' => $paymentCode,
            'qr_url' => $qrUrl,
            'sepay_qr_url' => $sepayQrUrl,
            'bank_account' => $this->bankAccountNumber,
            'bank_code' => $this->bankCode,
            'bank_name' => $this->getBankName($this->bankCode),
            'account_name' => $this->accountName,
            'amount' => $amount,
            'description' => $paymentCode,
        ];
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhook(string $receivedToken): bool
    {
        $configuredToken = config('services.sepay.webhook_token');
        
        if (empty($configuredToken)) {
            Log::warning('SePay webhook token not configured');
            return false;
        }

        // SePay uses Bearer token format: "Apikey {token}"
        $expectedToken = $configuredToken;
        
        // Remove "Apikey " prefix if present
        if (str_starts_with($receivedToken, 'Apikey ')) {
            $receivedToken = substr($receivedToken, 7);
        }
        
        return hash_equals($expectedToken, $receivedToken);
    }

    /**
     * Parse webhook data from SePay
     */
    public function parseWebhookData(array $data): array
    {
        return [
            'id' => $data['id'] ?? null,
            'gateway' => $data['gateway'] ?? null,
            'transaction_date' => $data['transactionDate'] ?? null,
            'account_number' => $data['accountNumber'] ?? null,
            'sub_account' => $data['subAccount'] ?? null,
            'code' => $data['code'] ?? null,
            'content' => $data['content'] ?? null,
            'transfer_type' => $data['transferType'] ?? null,
            'description' => $data['description'] ?? null,
            'transfer_amount' => $data['transferAmount'] ?? 0,
            'reference_code' => $data['referenceCode'] ?? null,
            'accumulated' => $data['accumulated'] ?? 0,
        ];
    }

    /**
     * Extract booking ID from payment content
     * Looking for pattern like "SE123" in the content
     */
    public function extractBookingId(string $content): ?int
    {
        $pattern = '/(?:' . preg_quote($this->pattern, '/') . ')(\d+)/i';
        
        if (preg_match($pattern, $content, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Get bank name from bank code
     */
    public function getBankName(string $bankCode): string
    {
        $banks = [
            'MB' => 'MB Bank',
            'MBBank' => 'MB Bank',
            'VCB' => 'Vietcombank',
            'Vietcombank' => 'Vietcombank',
            'TCB' => 'Techcombank',
            'Techcombank' => 'Techcombank',
            'ACB' => 'ACB',
            'VPB' => 'VPBank',
            'VPBank' => 'VPBank',
            'BIDV' => 'BIDV',
            'VTB' => 'VietinBank',
            'VietinBank' => 'VietinBank',
            'TPB' => 'TPBank',
            'TPBank' => 'TPBank',
            'STB' => 'Sacombank',
            'Sacombank' => 'Sacombank',
            'OCB' => 'OCB',
            'MSB' => 'MSB',
            'SHB' => 'SHB',
            'EIB' => 'Eximbank',
            'Eximbank' => 'Eximbank',
            'HSBC' => 'HSBC Vietnam',
            'SCB' => 'SCB',
            'HDBank' => 'HDBank',
            'HDB' => 'HDBank',
        ];

        return $banks[$bankCode] ?? $bankCode;
    }

    /**
     * Check transaction via API (optional - requires API access)
     */
    public function checkTransaction(int $transactionId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/transactions/details/' . $transactionId);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('SePay transaction check failed', [
                'transaction_id' => $transactionId,
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('SePay API error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);
            return null;
        }
    }

    /**
     * Get list of transactions (optional - requires API access)
     */
    public function getTransactions(array $params = []): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/transactions/list', $params);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('SePay get transactions error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
