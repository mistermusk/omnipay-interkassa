<?php

namespace Omnipay\InterKassa\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\PayPlanet\Message\MapperCodeCurrency;


class PayoutDataByIdRequest extends AbstractRequest {

    public function getCurrency() {
        return $this->getParameter('currency');
    }

    public function getTx() {
        return $this->getParameter('tx');
    }

    public function getMethod() {
        return $this->getParameter('method');
    }

    public function getAmount() {
        return $this->getParameter('amount');
    }

    public function setKeys($fullKeys) {
        return $this->setParameter('keys', $fullKeys);
    }

    public function getKeys() {
        return $this->getParameter('keys');
    }

    public function getUserApi() {
        return $this->getKeys()['api_withdrawal'][$this->getMethod()][$this->getCurrency()]['user_id'];
    }

    public function getKeyApi() {
        return $this->getKeys()['api_withdrawal'][$this->getMethod()][$this->getCurrency()]['key'];
    }

    public function getShopId() {
        return $this->getKeys()['api_withdrawal'][$this->getMethod()][$this->getCurrency()]['api_key'];
    }

    public function getSecretKey() {
        return $this->getKeys()['api_withdrawal'][$this->getMethod()][$this->getCurrency()]['secret_key'];
    }


    public function setCurrency($value) {
        return $this->setParameter('currency', $value);
    }

    public function setTx($value) {
        return $this->setParameter('tx', $value);
    }

    public function setMethod($value) {
        return $this->setParameter('method', $value);
    }

    public function setAmount($value) {
        return $this->setParameter('amount', $value);
    }


    public function getCard() {
        return $this->getParameter('card');
    }

    public function setCard($value) {
        return $this->setParameter('card', $value);
    }

    public function getFirstname() {
        return $this->getParameter('first_name');
    }

    public function setFirstname($value) {
        return $this->setParameter('first_name', $value);
    }

    public function getLastname() {
        return $this->getParameter('last_name');
    }

    public function setLastname($value) {
        return $this->setParameter('last_name', $value);
    }

    public function getPhone() {
        return $this->getParameter('phone');
    }

    public function setPhone($value) {
        return $this->setParameter('phone', $value);
    }

    public function getPurseid() {
        $v = $this->getKeys()['api_withdrawal'][$this->getMethod()][$this->getCurrency()]['purse_id'];
        return $v;
    }


    public function getApiaccountid() {
        return $this->getKeys()['api_withdrawal'][$this->getMethod()][$this->getCurrency()]['api_account_id'];
    }


    public function sendData($data) {
        $userId = $this->getUserApi();
        $apiKey = $this->getKeyApi();
        $authHeaderValue = 'Basic ' . base64_encode($userId . ':' . $apiKey);
        $postData = http_build_query($data);
        $headers = ["Authorization" => $authHeaderValue, "Ik-Api-Account-Id" => $this->getApiaccountid(),];
        $uri = 'https://api.interkassa.com/v1/withdraw?paymentNo='.$this->getTx();
        $response = $this->httpClient->request('GET', $uri, $headers);
        return $this->createResponse($response->getBody()->getContents());
    }

    function getData() {
        $data = [
            'purseId' => $this->getPurseid(),
            'paymentNo' => $this->getTx(),
            'calcKey' => 'psPayeeAmount',
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'action' => 'process',
            'useShortAlias' => '1',
            'method' => $this->getMethod(),
            "details[card]" => $this->getCard(),
            "details[first_name]" => $this->getFirstname(),
            "details[last_name]" => $this->getLastname(),
            "details[phone]" => $this->getPhone()
        ];
        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }


    protected function createResponse($data) {
        return $this->response = new PayoutDataByIdResponse($this, json_decode($data, true));
    }


}
