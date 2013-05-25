<?php
class Paybywayco_Paybyway_Model_Standard extends Mage_Payment_Model_Method_Abstract 
{
  protected $_code = 'paybyway';
  
  protected $_canUseForMultishipping  = false;
  protected $_isGateway = true;
  protected $_canAuthorize = true;
  protected $_canCapture = true;
  protected $_canCapturePartial = false;
  protected $_canRefund = false;
  protected $_canVoid = false;
  protected $_canUseInternal = false;
  protected $_canUseCheckout = true;

  public function getOrderPlaceRedirectUrl() 
  {
    return Mage::getUrl('paybyway/payment/redirect', array('_secure' => true));
  }


  public function authorize(Varien_Object $payment, $amount)
  {
    return $this;
  }

  public function capture(Varien_Object $payment, $amount)
  { 
    $order = $payment->getOrder();

    if ($order->getPbwSettled() == 1)
      return $this;

    $url = Mage::getStoreConfig('payment/paybyway/settle_url');
    $ctype = 'application/json';
    $posts = array(
      'MERCHANT_ID' => Mage::getStoreConfig('payment/paybyway/sub_merchant_id'),
      'AUTHCODE' => Mage::getStoreConfig('payment/paybyway/merchant_private_key'),
      'ORDER_NUMBER' =>$order->getPbwOrderNumber()
    );
    $settlement = json_decode($this->settle($url, $ctype, $posts));

    if (!isset($settlement->RETURN_CODE))
      Mage::throwException("Error in executing Paybyway API call.");

    switch ($settlement->RETURN_CODE) {
      case 0:
        $order->setPbwSettled(1)->save();
        return $this;       
      case 1:
        Mage::throwException('Authcode and submerchant id did not match with given order number.');       
      case 2:
        Mage::throwException('Payment cannot be settled. Either the payment has already been settled or the payment gateway refused to settle payment for given transaction.');
      case 3:
        Mage::throwException('Invalid request. The settlement request did not contain all the needed fields.');
      default:
        Mage::throwException('Unknown return value on settlement');
    }
  }

  function settle($url, $ctype, $posts)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($ctype));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
    $curl_response = curl_exec ($ch);
    curl_close ($ch);
    return $curl_response;
  }
}
?>