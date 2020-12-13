<?php

namespace Sowren\ShurjoPay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Sowren\ShurjoPay\ShurjoPayService;

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
            $data = ShurjoPayService::decryptResponse($request->spdata);
            $txnId = $data->txID;
            $bankTxnId = $data->bankTxID;
            $amount = $data->txnAmount;
            $bankStatus = $data->bankTxStatus;
            $resCode = $data->spCode;
            $resCodeDescription = $data->spCodeDes;
            $paymentOption = $data->paymentOption;
            $status = "";
            $res = [];

            switch ($resCode) {
                case '000':
                    $status = 'Success';
                    $res['status'] = true;
                    $res['message'] = "Transaction attempt successful";
                    break;
                default:
                    $status = 'Failed';
                    $res['status'] = false;
                    $res['message'] = "Transaction attempt failed";
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
}
