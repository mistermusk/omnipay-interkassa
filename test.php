<?php

// Подключение автозагрузчика Composer
require 'vendor/autoload.php';

use Omnipay\Omnipay;
use Omnipay\InterKassa\Gateway; // Подставьте ваш пространство имён и имя шлюза Omnipay

// Создание экземпляра шлюза
$gateway = Omnipay::create('InterKassa'); // Используйте имя вашего шлюза

// Установка параметров для шлюза (ключей, методов и т.д.)
$gateway->setKeys('pix', '63ed398d6ddac55dba8592fc', 'zE08pOp3c1V0ZKNaeglbzZBoM7TOqirZ');
// Добавьте остальные параметры, если это необходимо

// Подготовка данных для запроса
$requestData = [
    'currency' => 'BRL',
    'tx' => '123456789',
    'method' => 'pix',
    'amount' => '100.00',
];

// Создание объекта запроса
$request = $gateway->purchase($requestData);

// Отправка запроса и получение ответа
$response = $request->send();

// Обработка ответа
if ($response->isSuccessful()) {
    // Обработка успешного ответа
    $transactionReference = $response->getTransactionReference();
    echo "Payment was successful. Reference: $transactionReference";
} elseif ($response->isRedirect()) {
    // Редирект пользователя на страницу оплаты, если необходимо
    $response->redirect();
} else {
    // Обработка ошибок
    $errorMessage = $response->getMessage();
    echo "Payment failed: $errorMessage";
}

