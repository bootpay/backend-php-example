<?php

namespace Bootpay\ServerPhp;

class BootpayApi
{
    static $token = '';
    private static $applicationId = '';
    private static $privateKey = '';
    private static $mode = 'production';
    private static $API_URL = array(
        'development' => 'https://dev-api.bootpay.co.kr/v2',
        'stage' => 'https://stage-api.bootpay.co.kr/v2',
        'production' => 'https://api.bootpay.co.kr/v2'
    );
    private static $postMethods = array('POST', 'PUT');

    private static function entrypoints($url)
    {
        return implode('/', array(self::$API_URL[self::$mode], $url));
    }

    private static function setHeaders($headers)
    {
        !isset($headers) && $headers = array();
        return implode("\r\n", $headers);
    }

    private static function createHeaders($headers = null)
    {
        !isset($headers) && $headers = array();
        return array_merge($headers, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: ' . (strlen(self::$token) ? "Bearer " . self::$token : null)
        ));
    }

    private static function request($method, $url, $data = null, $headers = null)
    {
        !isset($headers) && $headers = array();
        $isPost = in_array($method, self::$postMethods);
        $channel = curl_init(self::entrypoints($url));
        curl_setopt($channel, CURLOPT_URL, self::entrypoints($url));
        curl_setopt($channel, CURLOPT_POST, $isPost);
        curl_setopt($channel, CURLOPT_HTTPHEADER, self::createHeaders($headers));
        if ($isPost) {
            curl_setopt($channel, CURLOPT_POSTFIELDS, json_encode($data));
        }
        if (in_array($method, array('DELETE', 'PUT'))) {
            curl_setopt($channel, CURLOPT_CUSTOMREQUEST, $method);
        }
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($channel);
        $errno = curl_errno($channel);
        $errMsg = curl_error($channel);
        if ($errno) {
            throw new Exception('error: ' . $errno . ', msg: ' . $errMsg);
        }
        curl_close($channel);
        $json = json_decode(trim($response));
        return $json;
    }

    private static function exception($message)
    {
        throw new \Exception($message);
    }

    public static function setConfiguration($applicationId, $privateKey, $mode = 'production')
    {
        self::$applicationId = $applicationId;
        self::$privateKey = $privateKey;
        self::$mode = $mode;
    }

    /**
     * request access token
     * Comment by GOSOMI
     */
    public static function getAccessToken()
    {
        $response = self::request(
            'POST',
            'request/token',
            array(
                'application_id' => self::$applicationId,
                'private_key' => self::$privateKey
            )
        );
        if (!$response->error_code) {
            self::$token = $response->access_token;
        }
        return $response;
    }

    /**
     * Lookup Receipt Payment
     * Comment by GOSOMI
     */
    public static function receiptPayment($receiptId)
    {
        return self::request(
            'GET',
            implode('/', array('receipt', $receiptId))
        );
    }

    /**
     * Cancel Payment
     * Comment by GOSOMI
     * @throws \Exception
     */
    public static function cancelPayment($cancelPaymentRequestParameters)
    {
        if (!$cancelPaymentRequestParameters['receipt_id']) {
            return self::exception('receipt_id??? ??????????????????.');
        }
        if (!$cancelPaymentRequestParameters['cancel_price']) {
            return self::exception('cancel_price??? 0??????????????? ??????????????????.');
        }
        return self::request(
            'POST',
            'cancel',
            $cancelPaymentRequestParameters
        );
    }

    /**
     * lookup certificate
     * Comment by GOSOMI
     * @date: 2022-04-15
     */
    public static function certificate($receiptId)
    {
        return self::request(
            'GET',
            implode('/', array('certificate', $receiptId))
        );
    }

    /**
     * Confirm Payment
     * Comment by GOSOMI
     */
    public static function confirmPayment($receiptId)
    {
        return self::request(
            'POST',
            'confirm',
            array(
                'receipt_id' => $receiptId
            )
        );
    }

    /**
     * Lookup Subscribe Billing Key
     * Comment by GOSOMI
     */
    public static function lookupSubscribeBillingKey($receiptId)
    {
        return self::request(
            'GET',
            implode('/', array('subscribe', 'billing_key', $receiptId))
        );
    }

    /**
     * Request Billing Key
     * Comment by GOSOMI
     * @throws \Exception
     */
    public static function requestSubscribeBillingKey($requestBillingKeyParameters)
    {
        if (!$requestBillingKeyParameters['pg']) {
            return self::exception('PG Symbol??? ??????????????????.');
        }
        if (!$requestBillingKeyParameters['subscription_id']) {
            return self::exception('??????????????? ????????? ?????? ???????????? ID??? ??????????????????.');
        }
        if (!$requestBillingKeyParameters['order_name']) {
            return self::exception('???????????? ???????????? ??????????????????.');
        }
        if (!$requestBillingKeyParameters['card_no']) {
            return self::exception('??????????????? ??????????????????.');
        }
        if (!$requestBillingKeyParameters['card_pw']) {
            return self::exception('?????? ???????????? ??? 2????????? ??????????????????.');
        }
        if (!$requestBillingKeyParameters['card_identity_no']) {
            return self::exception('?????? ????????? ???????????? 6?????? ?????? ?????????????????? ??????????????????.');
        }
        if (!$requestBillingKeyParameters['card_expire_year']) {
            return self::exception('?????? ?????? ????????? ??????????????????.');
        }
        if (!$requestBillingKeyParameters['card_expire_month']) {
            return self::exception('?????? ?????? ?????? ??????????????????.');
        }
        return self::request(
            'POST',
            'request/subscribe',
            $requestBillingKeyParameters
        );
    }

    /**
     * Request Subscribe Card Payment
     * Comment by GOSOMI
     * @throws \Exception
     */
    public static function requestSubscribeCardPayment($subscriptionCardRequestParameters)
    {
        if (!$subscriptionCardRequestParameters['billing_key']) {
            return self::exception('???????????? ??????????????????.');
        }
        if (!$subscriptionCardRequestParameters['order_name']) {
            return self::exception('??????????????? ???????????? ??????????????????.');
        }
        if (!$subscriptionCardRequestParameters['price']) {
            return self::exception('???????????? ????????? ??????????????????.');
        }
        if (!$subscriptionCardRequestParameters['order_id']) {
            return self::exception('??????????????? ????????? ?????? ??????????????? ??????????????????.');
        }
        return self::request(
            'POST',
            'subscribe/payment',
            $subscriptionCardRequestParameters
        );
    }

    /**
     * Destroy Billing Key
     * Comment by GOSOMI
     */
    public static function destroyBillingKey($billing_key)
    {
        return self::request(
            'DELETE',
            implode('/', array('subscribe', 'billing_key', $billing_key))
        );
    }

    /**
     * Request User Token
     * Comment by GOSOMI
     * @throws \Exception
     */
    public static function requestUserToken($userTokenParameters)
    {
        if (!$userTokenParameters['user_id']) {
            return self::exception('?????? ???????????? ????????? ??????????????????.');
        }
        return self::request(
            'POST',
            'request/user/token',
            $userTokenParameters
        );
    }

    /**
     * Subscribe Payment Reserve
     * Comment by GOSOMI
     * @throws \Exception
     */
    public static function subscribePaymentReserve($reserveParameters)
    {
        if (!$reserveParameters['billing_key']) {
            return self::exception('?????? ???????????? ????????? ???????????? ??????????????????.');
        }
        if (!$reserveParameters['reserve_execute_at']) {
            return self::exception('?????? ??????????????? ??????????????? ????????? ??????????????????.');
        }
        return self::request(
            'POST',
            'subscribe/payment/reserve',
            $reserveParameters
        );
    }

    /**
     * Cancel Subscribe Reserve
     * Comment by GOSOMI
     * @throws \Exception
     */
    public static function cancelSubscribeReserve($reserveId)
    {
        return self::request(
            'DELETE',
            'subscribe/payment/reserve/' . $reserveId
        );
    }

    /**
     * Shipping Start
     * Comment by GOSOMI
     * @date: 2022-06-15
     * @throws \Exception
     */
    public static function shippingStart($shippingParameters)
    {
        return self::request(
            'PUT',
            'escrow/shipping/start/' . $shippingParameters['receipt_id'],
            $shippingParameters
        );
    }
}