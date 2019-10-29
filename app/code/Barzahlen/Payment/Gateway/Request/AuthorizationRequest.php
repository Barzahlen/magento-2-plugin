<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Barzahlen\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $address = $order->getShippingAddress();


        /*
        echo '--------payment:--------';
        var_dump($payment->getOrder());
        echo '--------address:--------';
        var_dump(get_class_methods($address));
        echo '--------order:--------';
        var_dump(get_class_methods($order));
        */

        $sAddress = $address->getStreetLine1();
        $sAddress2 = $address->getStreetLine2();

        if (!empty($sAddress2)) {
            $sAddress = $sAddress . ' ' . $sAddress2;
        }

        return [
            'TXN_TYPE' => 'A',
            'INVOICE' => $order->getOrderIncrementId(),
            'AMOUNT' => $order->getGrandTotalAmount(),
            'CURRENCY' => $order->getCurrencyCode(),
            'FIRSTNAME' => $address->getFirstname(),
            'LASTNAME' => $address->getLastname(),
            'EMAIL' => $address->getEmail(),
            'ADDRESS' => $sAddress,
            'ZIP' => $address->getPostcode(),
            'CITY' => $address->getCity(),
            'COUNTRY' => $address->getCountryId(),
            'DIVISIONID' => $this->config->getValue('division_id'),
            'APIKEY' => $this->config->getValue('api_key'),
            'TESTMODE' => $this->config->getValue('testmode'),
        ];
    }
}
