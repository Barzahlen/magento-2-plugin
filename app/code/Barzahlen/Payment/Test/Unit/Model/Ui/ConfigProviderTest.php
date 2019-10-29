<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Barzahlen\Payment\Test\Unit\Model\Ui;

use Barzahlen\Payment\Gateway\Http\Client\ClientMock;
use Barzahlen\Payment\Model\Ui\ConfigProvider;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $configProvider = new ConfigProvider();

        static::assertEquals(
            [
                'payment' => [
                    ConfigProvider::CODE => [
                        'transactionResults' => [
                            ClientMock::SUCCESS => __('Success'),
                            ClientMock::FAILURE => __('Fraud')
                        ]
                    ]
                ]
            ],
            $configProvider->getConfig()
        );
    }
}
