<?php
namespace Barzahlen\Payment\Block;
class Success extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;


    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;


    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;


    /**
     * Success constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Barzahlen\Payment\Model\Session $barzahlensession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->barzahlensession = $barzahlensession;
        $this->_orderFactory = $orderFactory;
    }


    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return  $this->_order = $this->_orderFactory->create()->loadByIncrementId(
            $this->checkoutSession->getLastRealOrderId());
    }


    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }


    /**
     * Retrieve checkout token and mode status
     * @return string
     */
    public function getPaymentSlip() {

        $sCheckoutToken = $this->barzahlensession->getBarzahlenCheckoutToken();
        $bTestModeStatus = $this->barzahlensession->getBarzahlenTestModeStatus();


        // LIVE-MODE!
        if ($bTestModeStatus == 0) {
            $sPaymentSlip = '<script src="https://cdn.barzahlen.de/js/v2/checkout.js" class="bz-checkout" data-token="' . $sCheckoutToken . '"></script>';
        }
        // SANDBOX-MODE!
        else {
            $sPaymentSlip = '<script src="https://cdn.barzahlen.de/js/v2/checkout-sandbox.js" class="bz-checkout" data-token="' . $sCheckoutToken . '"></script>';
        }

        return $sPaymentSlip;
    }
}