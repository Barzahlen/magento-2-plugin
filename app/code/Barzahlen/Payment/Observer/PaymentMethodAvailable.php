<?php

namespace Barzahlen\Payment\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Psr\Log\LoggerInterface;

class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($observer->getEvent()->getMethodInstance()->getCode()=="barzahlen_gateway") {
            $objectManager = ObjectManager::getInstance();

            $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

            if (empty($cart)) {
                die('Error: Cart is empty');
            }

            $oTotal = $cart->getQuote()->getTotals();
            $fTotal = ($oTotal['grand_total']->getValue());
            $fTotal = number_format($fTotal, 2);

            // Get max-order-sum-value from config to decide whether barzahlen should be displayed or not
            $fMaxOrderSum = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/barzahlen_gateway/max_order_sum');

            if (strpos($fMaxOrderSum, ',') !== false) {
                $fMaxOrderSum = str_replace(',', '.', $fMaxOrderSum);
            }

            $fMaxOrderSum = number_format($fMaxOrderSum, 2);

            $checkResult = $observer->getEvent()->getResult();


            // Show or hide payment method
            if ($fTotal > $fMaxOrderSum) {
                $checkResult->setData('is_available', false);
            } else {
                $checkResult->setData('is_available', true);
            }


        }
    }
}