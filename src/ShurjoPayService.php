<?php

namespace Sowren\ShurjoPay;


use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;
use GuzzleHttp\Exception\GuzzleException;

class ShurjoPayService
{
    /**
     * Amount to pay.
     *
     * @var float
     */
    protected $amount;

    /**
     * URL to return after a successful transaction.
     *
     * @var string
     */
    protected $successUrl;

    /**
     * ShurjoPay server URL.
     *
     * @var string
     */
    protected $serverUrl;

    /**
     * ShurjoPay merchant username.
     *
     * @var string
     */
    protected $merchantUsername;

    /**
     * ShurjoPay merchant password.
     *
     * @var string
     */
    protected $merchantPassword;

    /**
     * ShurjoPay merchant key prefix.
     *
     * @var string
     */
    protected $merchantKeyPrefix;

    /**
     * Requesting IP address.
     *
     * @var string
     */
    protected $clientIp;

    /**
     * An unique transaction ID.
     *
     * @var string
     */
    protected $txnId;

    /**
     * XML data required by ShurjoPay.
     *
     * @var string
     */
    protected $xmlData;

    /**
     * Whether or not to use cURL.
     *
     * @var bool
     */
    protected $useCurl;

    /**
     * ShurjoPayService constructor.
     * @param  float  $amount  Amount to pay
     * @param  string  $successUrl  URL to return after a successful transaction
     * @param  string|null  $serverUrl  Server URL provided by ShurjoPay
     * @param  string|null  $merchantUsername  Merchant username provided by ShurjoPay
     * @param  string|null  $merchantPassword  Merchant password provided by ShurjoPay
     * @param  string|null  $merchantKeyPrefix  Merchant key prefix provided by ShurjoPay
     * @param  bool  $useCurl  If false, cURL will be used instead of Guzzle
     */
    public function __construct(
        float $amount,
        string $successUrl,
        string $serverUrl = null,
        string $merchantUsername = null,
        string $merchantPassword = null,
        string $merchantKeyPrefix = null,
        bool $useCurl = false
    ) {
        $this->amount = $amount;
        $this->successUrl = $successUrl;
        $this->serverUrl = $serverUrl ?? config('shurjopay.server_url');
        $this->merchantUsername = $merchantUsername ?? config('shurjopay.merchant_username');
        $this->merchantPassword = $merchantPassword ?? config('shurjopay.merchant_password');
        $this->merchantKeyPrefix = $merchantKeyPrefix ?? config('shurjopay.merchant_key_prefix');
        $this->clientIp = Request::ip();
        $this->useCurl = $useCurl;
    }

    /**
     * Generate an unique transaction ID for current transaction.
     *
     * @param  string|null  $uniqueId
     * @return string
     */
    public function generateTxnId(string $uniqueId = null)
    {
        $this->txnId = $uniqueId
            ? $this->merchantKeyPrefix.$uniqueId : $this->merchantKeyPrefix.uniqid();
        return $this->txnId;
    }

    /**
     * Guzzle client post request options.
     *
     * @return array
     */
    private function postOptions()
    {
        return [
            'form_params' => [
                'spdata' => $this->xmlData
            ],
            'verify' => false,
            'timeout' => 3,
            'curl' => [
                CURLOPT_RETURNTRANSFER => true
            ]
        ];
    }

    /**
     * Prepare XML data to send.
     *
     * @return void
     */
    private function setXmlData()
    {
        $default = '<?xml version="1.0" encoding="utf-8"?>'.
            '<shurjoPay><merchantName>'.$this->merchantUsername.'</merchantName>'.
            '<merchantPass>'.$this->merchantPassword.'</merchantPass>'.
            '<userIP>'.$this->clientIp.'</userIP>'.
            '<uniqID>'.$this->txnId.'</uniqID>'.
            '<totalAmount>'.$this->amount.'</totalAmount>'.
            '<paymentOption>shurjopay</paymentOption>'.
            '<returnURL>'.$this->returnUrl().'</returnURL></shurjoPay>';

        $this->xmlData = $this->useCurl ? 'spdata='.$default : $default;
    }

    /**
     * Get return URL.
     *
     * @return string
     */
    private function returnUrl()
    {
        $returnUrl = route('shurjopay.response');
        $returnUrl .= "?success_url={$this->successUrl}";
        return $returnUrl;
    }

    /**
     * Get request URL.
     *
     * @return string
     */
    private function requestUrl()
    {
        return Str::endsWith($this->serverUrl, '/')
            ? $this->serverUrl.'sp-data.php'
            : $this->serverUrl.'/sp-data.php';
    }

    /**
     * Attempt a payment via ShurjoPay payment gateway.
     *
     * @throws GuzzleException
     */
    public function makePayment()
    {
        $this->setXmlData();
        return $this->useCurl ? $this->sendRequestCurl() : $this->sendRequest();
    }

    /**
     * Send a HTTP request using Guzzle to ShurjoPay.
     *
     * @return \Psr\Http\Message\StreamInterface
     * @throws GuzzleException
     */
    private function sendRequest()
    {
        try {
            $client = new Client();
            $response = $client->request('POST', $this->requestUrl(), $this->postOptions());
            return $response->getBody();
        } catch (GuzzleException $exception) {
            throw $exception;
        }
    }

    /**
     * Send a payment request using cURL to ShurjoPay payment gateway.
     *
     * @return string|true
     */
    private function sendRequestCurl()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->requestUrl());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xmlData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        return print_r($response);
    }
}
