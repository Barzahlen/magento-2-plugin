<?php

namespace Barzahlen\Payment\Controller\Ipn;

use Barzahlen\Exception\ApiException;
use Barzahlen\Webhook;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Index extends Action
{

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    private $order;


    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;


    /**
     * Index constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param Context $context
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterface $order,
        Context $context
    ) {
        $this->logger = $logger;
        $this->order = $order;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context);
    }


    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $headers = $this->_request->getHeaders()->toArray();
        $body    = stripslashes($this->_request->getContent());

        try {

            $aHeader = array_merge($headers, $_SERVER);

            // Get api key from config
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $settings = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
            $sApiKey = $settings->getValue('payment/barzahlen_gateway/api_key');

            // Verify BZ signature before continue
            $webhook = new Webhook($sApiKey);
            $result = $webhook->verify($aHeader, $body);

            if($result == false) {
                return $result;
            }

            $oBody = json_decode($body);

            $sSlipType = $oBody->event;


            if (empty($oBody->slip)) {
                die('Slip data not available.');
            }

            $sEmail = $oBody->slip->customer->email;
            if (empty($sEmail)) {
                die('Error: Mail data not available.');
            }

            $iOrderId = $oBody->slip->reference_key;
            if (empty($iOrderId)) {
                die('Error: reference key (order id) not available.');
            }


            // Get transactions of order
            $aTransactions = $oBody->slip->transactions;
            if (empty($aTransactions)) {
                die('Error: No transaction data available.');
            }

            switch($sSlipType) {
                case 'paid': {
                    $this->_processTransactionPaid($iOrderId);
                    exit;
                }

                case 'expired': {
                    $this->_processTransactionExpired($iOrderId);
                    exit;
                }
            }

        } catch (ApiException $e) {
            $this->logger->debug($e->getMessage());
            exit;
        }
    }


    /**
     * @param $iOrderId
     */
    protected function _processTransactionPaid($iOrderId) {


        // Get order data
        $order = $this->order->loadByIncrementId($iOrderId);

        // Change status to STATE_PROCESSING
        $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
        $order->setState($orderState)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)->save();

        // Set total paid
        $order->setTotalPaid($order->getTotalDue())->save();

        // Add "slip was paid"-message to order comment history
        $order->addCommentToStatusHistory(__('Barzahlen/viacash: The payment slip was paid to the retail partner.'))->save();
    }


    /**
     * @param $iOrderId
     */
    protected function _processTransactionExpired($iOrderId) {

        // Get order data
        $order = $this->order->loadByIncrementId($iOrderId);

        // Change status to STATE_CANCELED
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true)->save();
        $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED, true)->save();

        // Add "slip has expired"-message to order comment history
        $order->addCommentToStatusHistory(__('Barzahlen/viacash: The payment period of the payment slip has expired.'))->save();
    }
}
