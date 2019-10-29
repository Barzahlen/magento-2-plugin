<?php

namespace Barzahlen\Payment\Observer\Adminhtml;

use Barzahlen\Client;
use \Magento\Framework\Event\ObserverInterface;
use \Psr\Log\LoggerInterface;
use \Barzahlen\Request\InvalidateRequest;

class Order implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    /**
    private $logger;


    /**
     * Order constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Barzahlen\Exception\ApiException
     * @throws \Barzahlen\Exception\AuthException
     * @throws \Barzahlen\Exception\CurlException
     * @throws \Barzahlen\Exception\IdempotencyException
     * @throws \Barzahlen\Exception\InvalidFormatException
     * @throws \Barzahlen\Exception\InvalidParameterException
     * @throws \Barzahlen\Exception\InvalidStateException
     * @throws \Barzahlen\Exception\NotAllowedException
     * @throws \Barzahlen\Exception\RateLimitException
     * @throws \Barzahlen\Exception\ServerException
     * @throws \Barzahlen\Exception\TransportException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $sBarzahlenSlipId = $order->getBarzahlenSlipId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $settings = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $sDivisionId = $settings->getValue('payment/barzahlen_gateway/division_id');
        $sApiKey = $settings->getValue('payment/barzahlen_gateway/api_key');

        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $sMagentoVersion = $productMetadata->getVersion();

        // We get authentification data
        $client = new Client($sDivisionId, $sApiKey, true);
        $client->setUserAgent('Magento ' . $sMagentoVersion . ' - Plugin v2.0.0');


        // Invalidate slip
        $request = new InvalidateRequest($sBarzahlenSlipId);
        $client->handle($request);
    }
}