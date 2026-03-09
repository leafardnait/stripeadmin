<?php
namespace Magekc\PayCools\Model;

use Magekc\PayCools\Helper\Signature;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magekc\PayCools\Logger\Handler\Debug as PayCoolsLogger;

class ApiClient
{
    protected $signatureHelper;
    protected $scopeConfig;
    protected $payCoolsLogger;

    public function __construct(
        Signature $signatureHelper,
        ScopeConfigInterface $scopeConfig,
        PayCoolsLogger $payCoolsLogger
    ) {
        $this->signatureHelper = $signatureHelper;
        $this->scopeConfig     = $scopeConfig;
        $this->payCoolsLogger  = $payCoolsLogger;
    }

    /**
     * Create a payment request and send it to the PayCools API.
     *
     * @param array $params  Payment parameters (orderId, amount, etc.)
     * @return string        Raw JSON response from API
     * @throws \Exception    If signature verification or cURL fails
     */
    public function createPayment(array $params): string
    {
        // 1. Load credentials and API URL
        $privateKeyPem = $this->scopeConfig->getValue('payment/paycools/private_key');
        $publicKeyPem  = $this->scopeConfig->getValue('payment/paycools/public_key');
        $apiUrl        = $this->scopeConfig->getValue('payment/paycools/api_url');
        $appId         = $this->scopeConfig->getValue('payment/paycools/app_id');
        $debugMode     = $this->scopeConfig->getValue('payment/paycools/debug_mode');
        
        // 2. Encode param JSON string (must match exactly what you send)
        $paramJson = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // 3. Build canonical string: appId + param
        $signString = "appId={$appId}&param={$paramJson}";

        // 4. Generate signature
        $signature = $this->signatureHelper->sign($signString, $privateKeyPem);

        // 5. Verify signature locally
        $isValid = $this->signatureHelper->verify($signString, $signature, $publicKeyPem);
        if (!$isValid) {
            throw new \Exception('Signature verification failed locally.');
        }

        // 6. Prepare request payload
        $requestData = [
            'appId' => $appId,
            'sign'  => $signature,
            'param' => $paramJson
        ];

        // 7. Send request
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => rtrim($apiUrl, '/') . '/api/v2/checkout/generate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($requestData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new \Exception('cURL error: ' . $error);
        }
        
        curl_close($curl);
        if ($debugMode) {
            $this->payCoolsLogger->customLog(print_r($requestData, true));
            $this->payCoolsLogger->customLog(print_r($response, true));
        }
        return $response;
    }
}
