<?php
namespace Barzahlen\Payment\Model\Session;
class Storage extends \Magento\Framework\Session\Storage
{
    /**
     * Storage constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $namespace
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $namespace = 'barzahlen',
        array $data = []
    ) {
        parent::__construct($namespace, $data);
    }
}