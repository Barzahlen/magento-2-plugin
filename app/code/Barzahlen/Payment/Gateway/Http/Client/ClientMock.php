<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Barzahlen\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

use Barzahlen\Client;
use Barzahlen\Exception\ApiException;
use Barzahlen\Request\CreateRequest;

class ClientMock implements ClientInterface
{
    const SUCCESS = 1;
    const FAILURE = 0;


    /**
     * @var array
     */
    private $results = [
        self::SUCCESS,
        self::FAILURE
    ];


    /**
     * @var Logger
     */
    private $logger;


    /**
     * ClientMock constructor.
     * @param \Barzahlen\Payment\Model\Session $barzahlensession
     * @param Logger $logger
     */
    public function __construct(
        \Barzahlen\Payment\Model\Session $barzahlensession,
        Logger $logger
    ) {
        $this->barzahlensession = $barzahlensession;
        $this->logger = $logger;
    }


    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array|string
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        try {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            // Get current magento version
            $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
            $sMagentoVersion = $productMetadata->getVersion();

            $aBody = $transferObject->getBody();
            if (empty($aBody)) {
                die('Error: Empty body given.');
            }

            // If barzahlen division id is empty, cancel
            if (empty($aBody['DIVISIONID'])) {
                die('Error: Division id is empty');
            }

            // If barzahlen api key is empty, cancel
            if (empty($aBody['APIKEY'])) {
                die('Error: Api key id is empty');
            }

            // If barzahlen testmode is not defined
            if (!isset($aBody['TESTMODE'])) {
                die('Error: Testmode is not set in option.');
            }

            // We get authentification data
            $client = new Client($aBody['DIVISIONID'], $aBody['APIKEY'], true);
            $client->setUserAgent('Magento ' . $sMagentoVersion . ' - Plugin v2.0.0');

            $fAmount = number_format($aBody['AMOUNT'], 2, '.', '');

            $parameters = array(
                'slip_type' => 'payment',
                'reference_key' => $aBody['INVOICE'],
                'customer' => array(
                    'key' => $aBody['EMAIL'],
                    'email' => $aBody['EMAIL']
                ),
                'transactions' => array(
                    array(
                        'amount' => (string) $fAmount,
                        'currency' => $aBody['CURRENCY']
                    )
                )
            );


            // Create request
            $request = new CreateRequest();

            $request->setAddress(array(
                'street_and_no' => $aBody['ADDRESS'],
                'zipcode' => $aBody['ZIP'],
                'city' => $aBody['CITY'],
                'country' => $aBody['COUNTRY']
            ));

            $request->setCustomerLanguage('de-DE');

            $request->setBody($parameters);

            $response = $client->handle($request);

            $aResponse = json_decode($response, true);


            // Set barzahlen slip id
            $this->barzahlensession->setBarzahlenSlipId($aResponse['id']);

            // Set barzahlen checkout token to session for later generating payment slip
            $this->barzahlensession->setBarzahlenCheckoutToken($aResponse['checkout_token']);

            // Set testmode status to session
            $this->barzahlensession->setBarzahlenTestModeStatus($aBody['TESTMODE']);

        } catch (ApiException $e) {
            echo 'Error message: ' . $e->getMessage();
            exit;
        }


        $response = $this->generateResponseForCode(
            $this->getResultCode(
                $transferObject
            )
        );

        $this->logger->debug(
            [
                'request' => $transferObject->getBody(),
                'response' => $response
            ]
        );

        return $response;
    }


    /**
     * Generates response
     * @param $resultCode
     * @return array
     */
    protected function generateResponseForCode($resultCode)
    {

        return array_merge(
            [
                'RESULT_CODE' => $resultCode,
                'TXN_ID' => $this->generateTxnId()
            ],
            $this->getFieldsBasedOnResponseType($resultCode)
        );
    }


    /**
     * @return string
     */
    protected function generateTxnId()
    {
        return md5(mt_rand(0, 1000));
    }


    /**
     * Returns result code
     *
     * @param TransferInterface $transfer
     * @return int
     */
    private function getResultCode(TransferInterface $transfer)
    {
        $headers = $transfer->getHeaders();

        if (isset($headers['force_result'])) {
            return (int)$headers['force_result'];
        }

        return $this->results[mt_rand(0, 1)];
    }


    /**
     * Returns response fields for result code
     *
     * @param int $resultCode
     * @return array
     */
    private function getFieldsBasedOnResponseType($resultCode)
    {
        switch ($resultCode) {
            case self::FAILURE:
                return [
                    'FRAUD_MSG_LIST' => [
                        'Stolen card',
                        'Customer location differs'
                    ]
                ];
        }

        return [];
    }
}
