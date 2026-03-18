<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @category Magekc
 * @package  Magekc_PayCools
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)  
 *
 * @author   Kristian Claridad<kristianrafael.claridad@gmail.com>
 */
namespace Magekc\PayCools\Controller\Checkout;

use Magento\Payment\Model\InfoInterface;
use Magekc\PayCools\Model\ApiClient;
use Magento\Sales\Api\OrderRepositoryInterface;
/**
 * RedirectPaymentChannel class
 */
class RedirectPaymentChannel extends \Magento\Framework\App\Action\Action
{
	/**
	* @var \Magento\Checkout\Model\Session
	*/
	protected $_checkoutSession;

    /**
	* @var \Magento\Framework\App\Action\Context
	*/
	protected $_resultRedirectFactory;

	/**
	* @var \Magento\Framework\UrlInterface
	*/
	protected $_urlBuilder;

	/** @var ApiClient */
    private $apiClient;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

	/**
	* @param \Magento\Framework\App\Action\Context $context
	* @param \Magento\Checkout\Model\Session $checkoutSession
	* @param \Magento\Framework\UrlInterface $urlBuilder
	*/
	public function __construct(
	\Magento\Framework\App\Action\Context $context,
	\Magento\Checkout\Model\Session $checkoutSession,
	\Magento\Framework\UrlInterface $urlBuilder,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	 ApiClient $apiClient,
	 OrderRepositoryInterface $orderRepository
	) {
		$this->apiClient = $apiClient;
		$this->orderRepository = $orderRepository;
		$this->scopeConfig = $scopeConfig;
		$this->_checkoutSession = $checkoutSession;
		$this->_urlBuilder = $urlBuilder;
		$this->_resultRedirectFactory = $context->getResultRedirectFactory();
		parent::__construct($context);
	}

	/**
	* Start checkout by creating request data and redirect customer to rising sun payment gateway.
	*/
    public function execute()
    {
        $order = $this->_getOrder();
		
		if (empty($order->getIncrementId())) {
			$resultRedirect = $this->resultRedirectFactory->create();
			$response_url = $this->_urlBuilder->getUrl('checkout/cart');
       		$resultRedirect->setUrl($response_url);
			return $resultRedirect;
		}
		
		
		$params = $this->buildPayload($order->getId());

        $result =  $this->apiClient->createPaymentMultiChannel($params);
		
		// $result = '{"code":1000,"message":"success","data":{"redirectUrl":"https://a.api-uat.paycools.com/1L9Aa1W","transactionStatus":"PENDING","transactionAmount":200500,"transactionId":"C5N3742144480477184","expiresTime":"2026-03-17 11:18:07"}}';
		
		if( strlen(@$result) > 0 ){
			
			$res_code = '';
			$res_message = '';
			$res_referenceid = '';
			$redirect_url_gateway = '';
			$res = json_decode($result, true);
			
			if( $res['code'] === 1000){ // success
				$redirect_url_gateway = $res['data']['redirectUrl'];
				return $this->_redirect($redirect_url_gateway);
			} else {
				$res_message = $res['message'];
				if ($res_message) {
					$this->messageManager->addErrorMessage(__($res_message));
				} else {
					$this->messageManager->addErrorMessage(__('Payment failed or invalid response.'));
				}
				return $this->_redirect('checkout/cart');
			}
		}
    }

	/**
	* Get order object.
	*
	* @return \Magento\Sales\Model\Order
	*/
	protected function _getOrder()
	{
		return $this->_checkoutSession->getLastRealOrder();
	}

	protected function getStateCode($billingAddress)
	{
		$country = $billingAddress->getCountryId();
		$region  = $billingAddress->getRegionCode(); // Magento stores region code

		if (in_array($country, ['US', 'CA'])) {
			return strtoupper(substr($region, 0, 2)); // ensure 2-digit ISO code
		}
		return 'NA';
	}

	/**
	 * Validate and normalize Philippine mobile numbers
	 *
	 * Rules:
	 *  - Must start with "09"
	 *  - Must be exactly 11 digits
	 *  - If shorter, pad with zeros
	 *  - If longer, trim to 11 digits
	 *
	 * @param string $phone
	 * @return string
	 */
	protected function normalizePhone(string $phone): string
	{
		// Remove non-digit characters
		$phone = preg_replace('/\D/', '', $phone);

		// Ensure it starts with "09"
		if (substr($phone, 0, 2) !== "09") {
			$phone = "09" . $phone;
		}

		// Adjust length to exactly 11 digits
		if (strlen($phone) < 11) {
			// Pad with zeros at the end
			$phone = str_pad($phone, 11, "0");
		} elseif (strlen($phone) > 11) {
			// Trim to first 11 digits
			$phone = substr($phone, 0, 11);
		}

		return $phone;
	}

	public function buildPayload(int $orderId): array
    {

        $order = $this->orderRepository->get($orderId);
		$payment = $order->getPayment();
		$channelCode = $payment->getAdditionalInformation('channel');
		
		$billing = $order->getBillingAddress();

        // Basic order info
        $payload = [
            'appId'         	 => $this->scopeConfig->getValue('payment/paycools/app_id'),
            'appName'         	 => 'Stripe Trading',
            'mchOrderId'         => $order->getIncrementId(),
            'amount'             => (int) round($order->getGrandTotal() * 100), // convert to cents
			'customerName'       => $order->getCustomerName(),
            "channelCode"  		 => $channelCode,
            "email"        		 => $order->getCustomerEmail(),
			"mobile"       		 => $this->normalizePhone($billing->getTelephone()),
			'remark'             => 'Magento Order',
			"callbackUrl"  		 => $this->scopeConfig->getValue('payment/paycools_multi_channel/callback_url'),
			"redirectUrl"  		 => $this->scopeConfig->getValue('payment/paycools_multi_channel/redirect_url'),
        ];
		// $billingAddress = [
		// 	"countryCode"   => $billing->getCountryId(),
		// 	"province"      => $billing->getRegion(),
		// 	"city"          => $billing->getCity(),
		// 	"postCode"      => $billing->getPostcode(),
		// 	"addressLine1"  => $billing->getStreetLine(1),
		// 	"addressLine2"  => $billing->getStreetLine(2),
		// 	"customerName"  => $billing->getFirstname() . ' ' . $billing->getLastname()
		// ];
		// $payload['billingAddress'] = json_encode($billingAddress, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $payload;
    }

}
