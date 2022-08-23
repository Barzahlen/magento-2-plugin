<?php

namespace Barzahlen\Payment\Observer\Sales;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Psr\Log\LoggerInterface;

class Order implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;


    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    public $orderRepository;


    /**
     * Order constructor.
     * @param \Barzahlen\Payment\Model\Session $barzahlensession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param LoggerInterface $logger
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     */
    public function __construct(
        \Barzahlen\Payment\Model\Session $barzahlensession,
        \Magento\Framework\App\RequestInterface $request,
        LoggerInterface $logger,
        \Magento\Sales\Model\OrderRepository $orderRepository
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->_request = $request;
        $this->barzahlensession = $barzahlensession;
    }


    /**
     * @param Observer $observer
     */

    public function execute(
        \Magento\Framework\Event\Observer $observer
    )
    {


        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        //file_put_contents('var/log/barzahlen.log', date('Y-m-d H:i:s') . 'barzahlensession: ' . print_r($this->barzahlensession->getBarzahlenSlipId(), true), FILE_APPEND);
        //file_put_contents('var/log/barzahlen.log', date('Y-m-d H:i:s') . 'class methods $order ' . print_r($order->getPayment()->getMethod(), true), FILE_APPEND);


        if ($order->getPayment()->getMethod() == "barzahlen_gateway") {
            $sSlipId = $this->barzahlensession->getBarzahlenSlipId();
            $order->setBarzahlenSlipId($sSlipId)->save();

            $order->addCommentToStatusHistory(__('Barzahlen/viacash: Payment slip successfully requested and sent.'));

            $orderState = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
            $order->setState($orderState)->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            $this->orderRepository->save($order);
        }
    }
}