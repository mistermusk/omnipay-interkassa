<?php

namespace Omnipay\InterKassa\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\PayPlanet\Message\MapperCodeCurrency;


class PayoutRequest extends AbstractRequest
{

    public function getCurrency()
    {
        return $this->getParameter('currency');
    }

    public function getTx()
    {
        return $this->getParameter('tx');
    }

    public function getMethod()
    {
        return $this->getParameter('method');
    }

    public function getAmount()
    {
        return $this->getParameter('amount');
    }

    public function setFullKeys($fullKeys){
        return $this->setParameter('fullKeys', $fullKeys);
    }

    public function getFullKeys()
    {
        return $this->getParameter('fullKeys');
    }

    public function getUserApi()
    {
        return $this->getFullKeys()['api']['shop_id'];
    }
    public function getKeyApi()
    {
        return $this->getFullKeys()['api']['secret_key'];
    }

    public function getShopId()
    {
        return $this->getFullKeys()[$this->getMethod()]['shop_id'];
    }
    public function getDetails()
    {
        return $this->getParameter('details');
    }

    public function setDetails($value)
    {
        return $this->setParameter('details', $value);
    }

    public function getSecretKey()
    {
        return $this->getFullKeys()[$this->getMethod()]['secret_key'];
    }


    public function setCurrency($value)
    {
        return $this->setParameter('currency', $value);
    }

    public function setTx($value)
    {
        return $this->setParameter('tx', $value);
    }

    public function setMethod($value)
    {
        return $this->setParameter('method', $value);
    }

    public function setAmount($value)
    {
        return $this->setParameter('amount', $value);
    }


    public function getCard()
    {
        return $this->getParameter('card');
    }

    public function setCard($value)
    {
        return $this->setParameter('card', $value);
    }

    public function getFirstname()
    {
        return $this->getParameter('first_name');
    }

    public function setFirstname($value)
    {
        return $this->setParameter('first_name', $value);
    }

    public function getLastname()
    {
        return $this->getParameter('last_name');
    }

    public function setLastname($value)
    {
        return $this->setParameter('last_name', $value);
    }

    public function getPhone()
    {
        return $this->getParameter('phone');
    }

    public function setPhone($value)
    {
        return $this->setParameter('phone', $value);
    }

    public function getPurseid()
    {
        return $this->getParameter('purseId');
    }

    public function setPurseid($value)
    {
        return $this->setParameter('purseId', $value);
    }

    public function getAction()
    {
        return $this->getParameter('action');
    }

    public function setAction($value)
    {
        return $this->setParameter('action', $value);
    }



    public function getData()
    {

        $data = [
            'purseId' => $this->getPurseid(),
            'paymentNo' => $this->getTx(),
            'calcKey' => 'psPayeeAmount',
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'action' => $this->getAction(),
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

    public function sendData($data)
    {
        $userId = $this->getUserApi();
        $apiKey = $this->getKeyApi();
        $authHeaderValue = 'Basic ' . base64_encode($userId . ':' . $apiKey);

        $postData = http_build_query($data);

        $headers = [
            "Authorization" => $authHeaderValue,
            "Ik-Api-Account-Id" => $userId,
            'Content-Type' => 'application/x-www-form-urlencoded',

        ];

        $response = $this->httpClient->request('POST', 'https://api.interkassa.com/v1/withdraw',
            $headers, $postData
        );

        return $this->createResponse($response->getBody()->getContents());
    }




    protected function createResponse($data)
    {
        return $this->response = new PayoutResponse($this, json_decode($data, true));
    }


}
