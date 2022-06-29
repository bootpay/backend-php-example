<?php
/*
 * Access Token 요청 예제입니다.
 */
// require_once '../vendor/autoload.php'; 
require_once 'vendor/autoload.php'; 
use Bootpay\ServerPhp\BootpayApi; 

 

BootpayApi::setConfiguration(
    '5b8f6a4d396fa665fdc2b5ea',
    'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw='
);
 

//  1. (부트페이 통신을 위한) 토큰 발급
$token = BootpayApi::getAccessToken();
var_dump($response);

if ($token->error_code) { 
    //토큰 발급 실패
    return;
}

// 2. 결제 단건 조회 
$response = BootpayApi::receiptPayment('61b009aaec81b4057e7f6ecd');
var_dump($response);


// 3. 결제 취소 (전액 취소 / 부분 취소)
$response = BootpayApi::cancelPayment(
    array(
        'receipt_id' => '62591cfcd01c7e001c19e259',
        'cancel_price' => 1000,
        'cancel_tax_free' => '0',
        'cancel_id' => null,
        'cancel_username' => 'test',
        'cancel_message' => '테스트 결제 취소',
        'refund' => array(
            'bank_account' => '',
            'bank_username' => '',
            'bank_code' => ''
        )
    )
);
var_dump($response);


// 4-1. 빌링키 발급
$response = BootpayApi::requestSubscribeBillingKey(array(
    'pg' => '나이스페이',
    'order_name' => '테스트결제', 
    'subscription_id' => time(),
    'card_no' => '5570********1074', //카드번호 
    'card_pw' => '**', //카드 비밀번호 2자리 
    'card_identity_no' => '******',  //카드 소유주 생년월일 6자리 
    'card_expire_year' => '**',  //카드 유효기간 년 2자리 
    'card_expire_month' => '**', //카드 유효기간 월 2자리 
    'user' => array(
        'phone' => '01000000000',
        'username' => '홍길동',
        'email' => 'test@bootpay.co.kr'
    ),
    'reserve_execute_at' => date("Y-m-d H:i:s \U\T\C", time() + 5)
));
var_dump($response);

// var_dump($response); 


// 4-2. 발급된 빌링키로 결제 승인 요청 
$response = BootpayApi::requestSubscribeCardPayment(array(
    'billing_key' => '62b41f88cf9f6d001ad212ad',
    'order_name' => '테스트결제',
    'price' => 1000,
    'order_id' => time()
));
var_dump($response);


// 4-3. 발급된 빌링키로 결제 예약 요청
$response = BootpayApi::subscribePaymentReserve(array(
    'billing_key' => '62b41f88cf9f6d001ad212ad',
    'order_name' => '테스트결제',
    'price' => 1000,
    'order_id' => time(),
    'user' => array(
        'phone' => '01000000000',
        'username' => '홍길동',
        'email' => 'test@bootpay.co.kr'
    ),
    'reserve_execute_at' => date("Y-m-d H:i:s \U\T\C", time() + 5)
));
var_dump($response); 


// 4-4. 발급된 빌링키로 결제 예약 - 취소 요청 
$cancel = BootpayApi::cancelSubscribeReserve('62b41f88cf9f6d001ad212ad');
var_dump($cancel);
 


// 4-5. 빌링키 삭제
$response = BootpayApi::destroyBillingKey('62b41f88cf9f6d001ad212ad');
var_dump($response); 
 


// 4-6. 빌링키 조회
$response = BootpayApi::lookupSubscribeBillingKey('62b41f68cf9f6d001ad212a5');
var_dump($response); 


// 5. (생체인증, 비밀번호 결제를 위한) 구매자 토큰 발급
$response = BootpayApi::requestUserToken(array(
    'user_id' => 'gosomi1',
    'phone' => '01012345678'
));
var_dump($response);


// 6. 서버 승인 요청
$response = BootpayApi::confirmPayment('62b4200acf9f6d001ad212b1');
var_dump($response);

 
// 7. 본인 인증 결과 조회
$response = BootpayApi::certificate('625783a6cf9f6d001d0aed19');
var_dump($response);

// 8. (에스크로 이용시) PG사로 배송정보 보내기
$response = BootpayApi::shippingStart(
    array(
        'receipt_id' => "62b4200acf9f6d001ad212b1",
        'tracking_number' => '3982983',
        'delivery_corp' => 'CJ대한통운',
        'user' => array(
            'username' => '테스트',
            'phone' => '01000000000',
            'zipcode' => '099382',
            'address' => '서울특별시 종로구'
        )
    )
);
var_dump($response);