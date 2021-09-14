<?php
/*
 * Access Token 요청 예제입니다.
 */
require_once '../vendor/autoload.php'; 
use Bootpay\BackendPhp\BootpayApi; 


$bootpay = BootpayApi::setConfig(
    '5b8f6a4d396fa665fdc2b5ea',
    'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw='
);

# 1. 토큰 발급 
$response = $bootpay->requestAccessToken();
var_dump($response);


// # 2. 결제 검증 
$response = $bootpay->verify('612c31000199430036b5165d');
var_dump($response);


# 3. 결제 취소 (전액 취소 / 부분 취소)
$response = $bootpay->cancel(
    '613f101f0d681b0023e6e53f', 
    null, 
    'API 관리자',
    'API에 의한 요청',
    time(),
    null,
    [
        'account' => '66569112432134',
        'accountholder' => '홍길동',
        'bankcode' => BootpayApi::BankCode['국민은행']
    ]
);
var_dump($response);


# 4. 빌링키 발급 
$response = $bootpay->getSubscribeBillingKey(
    'nicepay',
    time(),
    '30일 정기권 결제', 
    '카드 번호',
    '카드 비밀번호 앞에 2자리',
    '카드 만료 연도 2자리',
    '카드 만료 월 2자리',
    '주민등록번호 또는 사업자번호'
); 

var_dump($response); 


// # 4-1. 발급된 빌링키로 결제 승인 요청 
$response = $bootpay->subscribeCardBilling(
    '613af2600199430027b5cb83',
    time(),
    '정기결제 테스트 아이템',
    1000
);

var_dump($response); 


// # 4-2. 발급된 빌링키로 결제 예약 요청
$response = $bootpay->subscribeCardBillingReserve(
    '613af2600199430027b5cb83',
    time(),
    '정기결제 테스트 아이템',
    1000,
    time() + 10
); 

var_dump($response);

// # 4-2-1. 발급된 빌링키로 결제 예약 - 취소 요청 
$response = $bootpay->subscribeBillingReserveCancel(
    '613af2600199430027b5cb83'
); 

var_dump($response);


// # 4-3. 빌링키 삭제 
$response = $bootpay->deleteBillingKey(
    '613af2600199430027b5cb83'
); 

var_dump($response);


// # 5. (부트페이 단독 - 간편결제창, 생체인증 기반의 사용자를 위한) 사용자 토큰 발급 
$response = $bootpay->getUserToken(
    'user1234', # 필수
    '01012341234', # 선택
    'rupy1014@gmail.com', # 선택
    '홍길동', # 선택
    1, # 선택
    '861014' # 선택
);

var_dump($response);


// # 6. 결제링크 생성 
$response = $bootpay->requestPayment(
    time(),
    '테스트 부트페이 상품',
    1000,
    [
        'pg' => 'nicepay',
        // 'method' => 'card',
        'methods' => ['card', 'phone', 'bank', 'vbank'],
    ]
); 
var_dump($response);


// # 7. 서버 승인 요청
$price = 3000; // 원래 서버에서 결제하려고 했던 금액
$receiptId = '5c6dfb1fe13f3371b38f9008';
$result = $bootpay->verify($receiptId);
// 서버에서 200을 받고, 원래 결제하려고 했던 금액과 일치하면 서버에 submit을 보냅니다.
if ($result->status == 200 && $result->data->price == $price) {
    $receiptData = $bootpay->submit($receiptId);
    var_dump($receiptData);
}
// 결제 완료되면 status 200을 리턴하고 실패하면 에러를 호출하며, 결제가 승인이 되지 않은 사유에 대해서 $receiptData->message로 받아보실 수 있습니다.
// 해당 데이터는 결제완료된 시점의 JS SDK의 done함수에서 호출되어 보내는 데이터와 동일합니다.



// # 8. 본인 인증 결과 검증 
$result = $bootpay->certificate('613af2600199430027b5cb83');
var_dump($result);
