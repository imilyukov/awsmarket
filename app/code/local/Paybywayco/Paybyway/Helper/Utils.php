<?php

class Paybywayco_Paybyway_Helper_Utils  extends Mage_Core_Helper_Abstract
{
  public static function addOrderIdPrefix($orderId)
  {
    $orderIdPrefix = Mage::getStoreConfig('payment/paybyway/orderid_prefix');
    return $orderIdPrefix . "_" . $orderId;
  }

  public static function removeOrderIdPrefix($orderId)
  {
    $orderIdPrefix = Mage::getStoreConfig('payment/paybyway/orderid_prefix');
    $replaceCount = 1;
    return str_replace($orderIdPrefix . "_" , "", $orderId, $replaceCount);
  }
}