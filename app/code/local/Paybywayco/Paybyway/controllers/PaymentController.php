<?php
class ReturnCodes
{
  const Success =0;
  const Failed = 1;
  const DuplicateOrderId = 2;
  const UserDisabled = 3;
  const BankError = 4;
  const Maintenance = 10;
}

class Paybywayco_Paybyway_PaymentController extends Mage_Core_Controller_Front_Action 
{
  public function redirectAction()
  {
    $order = Mage::getModel('sales/order');
    $order->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());

    $orderId = Paybywayco_Paybyway_Helper_Utils::addOrderIdPrefix($order->getIncrementId());
    $order->setPbwOrderNumber($orderId);
    $order->save();

    $order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
    $order->addStatusToHistory($order->getStatus(), "Maksu ohjattu Paybywayn portaaliin tilaustunnuksella " . $orderId);
    $order->save();

    $this->loadLayout();
    $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','paybyway',array('template' => 'paybyway/redirect.phtml'));
    $this->getLayout()->getBlock('content')->append($block);
    $this->renderLayout();
  }
  
  public function responseAction() 
  {
    $returnCode = $_POST['RETURN_CODE'];      
    $orderNumber = $_POST['ORDER_NUMBER'];
    $authCode = $_POST['AUTHCODE'];   
    $incidentId = isset($_POST['INCIDENT_ID']) ? $_POST['INCIDENT_ID'] : '';

    $merchantPrivateKey =  Mage::getStoreConfig('payment/paybyway/merchant_private_key');

    $authCodeConfirm = $merchantPrivateKey.'|'.$returnCode.'|'.$orderNumber;

    if ($returnCode != ReturnCodes::Success && isset($_POST['INCIDENT_ID']))
      $authCodeConfirm .= '|'.$incidentId;
    else if ($returnCode == ReturnCodes::Success)
    {
      $settled =  $_POST['SETTLED'];
      $authCodeConfirm .= '|'.$_POST['SETTLED'];
    }

    $authCodeConfirm = strtoupper(md5($authCodeConfirm));
    $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();    
    $order = Mage::getModel('sales/order');
    $order->loadByIncrementId($orderId);

    if ($orderNumber != $order->getPbwOrderNumber()) 
      Mage::throwException('Last order number does not match received order number');

    if ($authCodeConfirm == $authCode)
    {
      switch ($returnCode) {
        case ReturnCodes::Success:
          $this->success($order, $settled);
          break;
        case ReturnCodes::Failed:
          $this->cancel($order, $returnCode, $incidentId);            
          break;
        case ReturnCodes::DuplicateOrderId:
          $this->cancel($order, $returnCode, $incidentId);
          break;
        case ReturnCodes::UserDisabled:
          $this->cancel($order, $returnCode, $incidentId);
          break;
        case ReturnCodes::BankError:
          $this->hold($order, $incidentId); 
          break;
        case ReturnCodes::Maintenance:
          $this->cancel($order, $returnCode, $incidentId);
          break;
        default:
          $this->cancel($order, $returnCode, $incidentIdt);         
          break;
      }
    }
    else     
      Mage::throwException('Response MAC code not matching.');
  }
  
  private function cancel($order, $returnCode, $incidentId = '') 
  {
    if (!$order->getId())
      Mage::throwException('No order id found.');

    $order->cancel();
    $order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);

    $comment = $this->getReturnCodeComment($returnCode, $order->getPbwOrderNumber(), $incidentId);
    $order->addStatusToHistory($order->getStatus(), $comment);
    $order->save();

    Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure' => true));
  }

  private function success($order, $settled)
  {
    if (!$order->getId())
      Mage::throwException('No order id found.');

    $order->setPbwSettled($settled)->save();

    if ($settled == 1)
    {
      $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
      $this->invoice($order);
      $comment = "Maksu hyväksytty ja veloitettu. Paybyway tilaustunnus - " . $order->getPbwOrderNumber();
    }
    else if($settled == 0)
    {
      $order->setStatus(Mage_Sales_Model_Order::STATE_HOLDED);
      $comment = "Maksu hyväksytty. Veloitus tapahtuu laskun teon yhteydessä. Paybyway tilaustunnus - " . $order->getPbwOrderNumber();
    }
    else
      Mage::throwException("Invalid value of 'settled'");     
    
    $order->addStatusToHistory($order->getStatus(), $comment);

    try
    {
      $order->sendNewOrderEmail();
    }
    catch(Exception $e)
    {
    }

    $order->save();

    Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true));    
  }

  private function hold($order, $incidentId)
  {
    if (!$order->getId())
      Mage::throwException('No order id found.');

    $order->setStatus(Mage_Sales_Model_Order::STATE_HOLDED);
    $order->addStatusToHistory($order->getStatus(), "Paybyway - virhe maksaessa. Päivitä maksun tila manuaalisesti. - tilausnumero: " . $order->getPbwOrderNumber() . " virhetunnus - " . $incident_id);
    $order->save();

    Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true));
  }

  private function invoice($order)
  {
    if (!$order->canInvoice())
      Mage::throwException("Could not create invoice");

    $invoice = $order->prepareInvoice();

    $invoice->register();
    $invoice->capture();

    $transaction = Mage::getModel('core/resource_transaction')
      ->addObject($invoice)
      ->addObject($invoice->getOrder());

    $transaction->save();

    $order->setPbwSettled(1);
    $order->save();
  }

  private function getReturnCodeComment($returnCode, $orderId, $incidentId)
  {
    switch ($returnCode) 
    {
      case ReturnCodes::Failed:
        $comment = "Paybyway - peruutettu - tilaustunnus: " . $orderId . " virhetunnus - " . $incidentId;
        break;
      case ReturnCodes::DuplicateOrderId:
        $comment = "Paybyway - hylätty, syynä duplikaatti tilausnumero - tilaustunnus: " . $orderId . " virhetunnus - " . $incidentId;
        break;
      case ReturnCodes::UserDisabled:
        $comment = "Paybyway - hylätty, käyttäjätili on suljettu - tilaustunnus: " . $orderId . " virhetunnus - " . $incidentId;
        break;        
      case ReturnCodes::Maintenance:
        $comment = "Paybyway - Maksun tilaustunnuksella " . $orderId . " käsittely keskeytetty, syynä huoltotauko palvelussamme. - " . $incidentId; 
        break;
      default:
        $comment = "Paybyway - tuntematon paluuarvo - " . $returnCode;
        break;
    }
    return $comment;
  }
}
