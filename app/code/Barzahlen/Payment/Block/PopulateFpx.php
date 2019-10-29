<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Barzahlen\Payment\Block;

use Magento\Framework\View\Asset\Repository;

class PopulateFpx extends \Magento\Framework\View\Element\Template
{

    /**
     * @var Repository
     */
    protected $assetRepository;


    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;


    /**
     * PopulateFpx constructor.
     * @param Repository $assetRepository
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        Repository $assetRepository,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    )
    {
        $this->assetRepository = $assetRepository;
        $this->request = $request;
        parent::__construct($context, $data);
    }


    /**
     * @return mixed
     */
    public function getFpxConfig() {

        // Default
        $sLogo = 'barzahlen';

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $aBillingAddress = $cart->getQuote()->getBillingAddress();

        $sRegionCode = $aBillingAddress['country_id'];
        if ($sRegionCode != 'AT' && $sRegionCode != 'DE') {
            $sLogo = 'viacash';
        }

        $output['fpxLogoImageUrl'] = $this->getViewFileUrl('Barzahlen_Payment::image/' . $sLogo . '_logo.png');

        return $output;
    }


    /**
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        $params = array_merge(['_secure' => $this->request->isSecure()], $params);

        return $this->assetRepository->getUrlWithParams($fileId, $params);
    }
}
