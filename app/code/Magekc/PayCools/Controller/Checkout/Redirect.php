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
 * Redirect class
 */
class Redirect extends \Magento\Framework\App\Action\Action
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

        $result =  $this->apiClient->createPayment($params);
		// $result = '{"code":1,"message":"success","data":{"checkoutId":"CH2028445694599102464","checkoutUrl":"https://api-uat.paycools.com/cashier/payment/checkout/bNs7eOY8lgyaQFi-BL6Rdt28doX1BDdOmWay5vqGZVQ=","status":"PENDING","expiresTime":"2026-03-02T12:51:54.949+00:00"}}';
		if( strlen(@$result) > 0 ){
			$res_code = '';
			$res_message = '';
			$res_referenceid = '';
			$redirect_url_gateway = '';
			$res = json_decode($result, true);
			
			if( $res['code'] === 1 ){ // success
			$redirect_url_gateway = $res['data']['checkoutUrl'];
				if (!empty($redirect_url_gateway)) {
					echo '<form action="' . $redirect_url_gateway . '" method="post" id="paycools_payment_form">
						<style type="text/css">
							@import url(https://fonts.googleapis.com/css?family=Raleway:100);
							.Absolute-Center {font-family: "Roboto", Helvetica, Arial, sans-serif;width: 600px;height: 100px;position: absolute;top:0;bottom: 0;left: 0;right: 0;margin: auto;font-size: 14px;}
							.Absolute-Center p{color:#ffffff}
							body{background:#000d3a;margin: 40px 50px;color:#4a8df8;font-family: "Raleway", cursive;font-weight:100;}h1{color:#ff8200;font-family: "Raleway", cursive;font-weight:100;font-stretch:normal;font-size:3em;font-weight:bold;}
							a{color:#ff8200;font-weight:bold;font-family: "Raleway", cursive;}.slider{position:absolute;width:400px;height:2px;margin-top:-20px;}.line{position:absolute;background:#ffffff;width:400px;height:2px;}
							.break{position:absolute;background:#222;width:6px;height:2px;}
							.dot1{-webkit-animation: loading 2s infinite;-moz-animation: loading 2s infinite;-ms-animation: loading 2s infinite;-o-animation: loading 2s infinite;animation: loading 2s infinite;}.dot2{-webkit-animation: loading 2s 0.5s infinite;-moz-animation: loading 2s 0.5s infinite;-ms-animation: loading 2s 0.5s infinite;-o-animation: loading 2s 0.5s infinite;animation: loading 2s 0.5s infinite;}
							.dot3{-webkit-animation: loading 2s 1s infinite;-moz-animation: loading 2s 1s infinite;-ms-animation: loading 2s 1s infinite;-o-animation: loading 2s 1s infinite;animation: loading 2s 1s infinite;}
							@keyframes "loading" {from { left: 0; }to { left: 400px; }}@-moz-keyframes loading {from { left: 0; }to { left: 400px; }}@-webkit-keyframes "loading" {from { left: 0; }to { left: 400px; }}@-ms-keyframes "loading" {from { left: 0; }to { left: 400px; }}@-o-keyframes "loading" {from { left: 0; }to { left: 400px; }
						</style>
						<div class="Absolute-Center">
							<h1>Just a moment...</h1>
							<div class="slider">
								<div class="line"></div>
								<div class="break dot1"></div>
								<div class="break dot2"></div>
								<div class="break dot3"></div>
							</div>
							<p>Please wait while you are being redirected to payment page... Not working? <a href="'.$redirect_url_gateway.'">Click here.</a></p>
						</div>
						<script type="text/javascript">
							window.onload=function(){
								document.forms["paycools_payment_form"].submit();
							}
						</script>
					</form>';
				}
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
		$shippingFee = $order->getShippingAmount();
		
		$totalQtyOrdered = $order->getTotalQtyOrdered();
		$shippingFeePerItem = $shippingFee / $totalQtyOrdered;
        // Basic order info
        $payload = [
            'mchOrderId'         => $order->getIncrementId(),
            'merchantLogo'       => $this->scopeConfig->getValue('payment/paycools/merchant_logo_url'),
            'currency'           => $order->getOrderCurrencyCode(),
            'settlementCurrency' => $order->getOrderCurrencyCode(),
            'countryCode'        => $order->getBillingAddress()->getCountryId(),
            'channelTypeList'    => null,
            'channelCodeList'    => null,
            'customerName'       => $order->getCustomerName(),
            'email'              => $order->getCustomerEmail(),
            'mobile'             => $this->normalizePhone($order->getBillingAddress()->getTelephone()),
            'amount'             => (int) round($order->getGrandTotal() * 100), // convert to cents
            'remark'             => 'Magento Order',
            'notifyUrl'			 => $this->scopeConfig->getValue('payment/paycools/notify_url'),
            'redirectUrl'        => $this->scopeConfig->getValue('payment/paycools/redirect_url'),
            'userId'             => $order->getCustomerId() ?: 'guest',
        ];

        // Goods details from items
        $goodsDetails = [];
        foreach ($order->getAllVisibleItems() as $item) {
			$itemPrice = $item->getPrice() + $shippingFeePerItem;
            $goodsDetails[] = [
                'name'     => $item->getName(),
                'price'    => (int) round($itemPrice * 100), // convert to cents,
                'quantity' => (int) $item->getQtyOrdered(),
                'sku'      => $item->getSku(),
                'url'      => $item->getProduct()->getProductUrl(),
                'category' => $item->getProduct()->getCategoryIds()[0] ?? 'default',
            ];
        }
        $payload['goodsDetails'] = $goodsDetails;

        // Billing address
        $billing = $order->getBillingAddress();
        $payload['billingAddress'] = [
            'name' => $billing->getFirstname() . ' ' . $billing->getLastname(),
            'customerName' => $billing->getFirstname() . ' ' . $billing->getLastname(),
            'addressLine1' => $billing->getStreetLine(1),
            'addressLine2' => $billing->getStreetLine(2),
            'city'         => $billing->getCity(),
            'province'     => $billing->getRegion(),
            'postCode'     => $billing->getPostcode(),
            'email'        => $billing->getEmail() ?: $order->getCustomerEmail(),
            'mobile'       => $this->normalizePhone($billing->getTelephone()),
            'countryCode'  => $billing->getCountryId(),
            'redirectUrl'  => $this->scopeConfig->getValue('payment/paycools/redirect_url'),
        ];

        // Shipping address
        if ($order->getShippingAddress()) {
            $shipping = $order->getShippingAddress();
            $payload['shippingAddress'] = [
                'name' => $shipping->getFirstname() . ' ' . $shipping->getLastname(),
                'customerName' => $shipping->getFirstname() . ' ' . $shipping->getLastname(),
                'addressLine1' => $shipping->getStreetLine(1),
                'addressLine2' => $shipping->getStreetLine(2),
                'city'         => $shipping->getCity(),
                'province'     => $shipping->getRegion(),
                'postCode'     => $shipping->getPostcode(),
                'email'        => $shipping->getEmail() ?: $order->getCustomerEmail(),
                'mobile'       => $this->normalizePhone($shipping->getTelephone()),
                'countryCode'  => $shipping->getCountryId(),
            ];
        }
        return $payload;
    }

}
