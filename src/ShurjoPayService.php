<?php

namespace Sowren\ShurjoPay;


use Illuminate\Support\Facades\Request;

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
     * ShurjoPayService constructor.
     * @param  float  $amount  Amount to pay
     * @param  string  $successUrl  URL to return after a successful transaction
     * @param  string|null  $serverUrl  Server URL provided by ShurjoPay
     * @param  string|null  $merchantUsername  Merchant username provided by ShurjoPay
     * @param  string|null  $merchantPassword  Merchant password provided by ShurjoPay
     * @param  string|null  $merchantKeyPrefix  Merchant key prefix provided by ShurjoPay
     */
    public function __construct(
        float $amount,
        string $successUrl,
        string $serverUrl = null,
        string $merchantUsername = null,
        string $merchantPassword = null,
        string $merchantKeyPrefix = null
    ) {
        $this->amount = $amount;
        $this->successUrl = $successUrl;
        $this->serverUrl = $serverUrl ?? config('shurjopay.server_url');
        $this->merchantUsername = $merchantUsername ?? config('shurjopay.merchant_username');
        $this->merchantPassword = $merchantPassword ?? config('shurjopay.merchant_password');
        $this->merchantKeyPrefix = $merchantKeyPrefix ?? config('shurjopay.merchant_key_prefix');
        $this->clientIp = Request::ip();
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
     * Attempt a payment via ShurjoPay payment gateway.
     *
     */
    public function sendPayment()
    {
        $requestUrl = $this->serverUrl."/sp-data.php";

        $returnUrl = route('shurjopay.response');
        $returnUrl .= "?success_url={$this->successUrl}";

        $requestXmlData = 'spdata=<?xml version="1.0" encoding="utf-8"?>
                            <shurjoPay><merchantName>'.$this->merchantUsername.'</merchantName>
                            <merchantPass>'.$this->merchantPassword.'</merchantPass>
                            <userIP>'.$this->clientIp.'</userIP>
                            <uniqID>'.$this->txnId.'</uniqID>
                            <totalAmount>'.$this->amount.'</totalAmount>
                            <paymentOption>shurjopay</paymentOption>
                            <returnURL>'.$returnUrl.'</returnURL></shurjoPay>';

        $this->sendRequest($requestUrl, $requestXmlData);
    }


    /**
     * Send a payment request using curl to ShurjoPay payment gateway.
     *
     * @param  string  $requestUrl  The request URL
     * @param  string  $requestXmlData  XML data containing transaction information
     */
    private function sendRequest(string $requestUrl, string $requestXmlData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXmlData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        print_r($response);
    }
}
