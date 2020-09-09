<?php

namespace Sowren\ShurjoPay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ShurjoPayController extends Controller
{
    /**
     * Handle a response coming from ShurjoPay server
     * after a successful or failed transaction.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function response(Request $request)
    {
        try {
            $data = $this->decryptResponse($request->spdata);
            $txnId = $data->txID;
            $bankTxnId = $data->bankTxID;
            $amount = $data->txnAmount;
            $bankStatus = $data->bankTxStatus;
            $resCode = $data->spCode;
            $resCodeDescription = $data->spCodeDes;
            $paymentOption = $data->paymentOption;
            $status = "";

            switch ($resCode) {
                case '000':
                    $status = "Success";
                    $res = ['status' => true, 'message' => 'Transaction attempt successful'];
                    break;
                case '001':
                    $status = "Failed";
                    $res = ['status' => false, 'message' => 'Transaction attempt failed'];
                    break;
            }

            $redirectUrl = $request->get('success_url').
                "?status={$status}&msg={$res['message']}".
                "&tx_id={$txnId}&bank_tx_id={$bankTxnId}".
                "&amount={$amount}&bank_status={$bankStatus}&sp_code={$resCode}".
                "&sp_code_des={$resCodeDescription}&sp_payment_option={$paymentOption}";

            return redirect($redirectUrl);

        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Decrypt ShurjoPay response.
     *
     * @param  string  $data
     * @return \SimpleXMLElement
     */
    private function decryptResponse(string $data)
    {
        $decryptionServerUrl = $this->getDecryptionServerUrl();
        $decryptedResponse = file_get_contents($decryptionServerUrl."/merchant/decrypt.php?data=".$data);
        $parsedObject = simplexml_load_string($decryptedResponse) or die("Error: Failed to create an object!");
        return $parsedObject;
    }

    /**
     * Determine the decryption URL based on application environment.
     *
     * @return string
     */
    private function getDecryptionServerUrl()
    {
        return app()->environment('local') ? 'https://shurjotest.com' : 'https://shurjopay.com';
    }
}
