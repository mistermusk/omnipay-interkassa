<?php

namespace Omnipay\InterKassa;

use Omnipay\Common\AbstractGateway;

class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'InterKassa';
    }

    private $keys = [];
    public function setKeys($apiData)
    {
        $this->keys = $apiData;
    }

    public function getKeys()
    {
        return $this->keys;
    }

    public function formatLevel($level){
        if ($level){
            return 'first_level';
        }
        return 'second_level';
    }

    function deleteSignData($array)
    {
        $array['dt']['ik_sign'] = null;
        return array_filter($array, function ($value) {
            return $value !== null;
        });
    }

    function sortByKeyRecursive(array $array): array
    {
        ksort($array, SORT_STRING);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sortByKeyRecursive($value);
            }
        }
        return $array;
    }

    function implodeRecursive(string $separator, array $array): string
    {
        $result = '';
        foreach ($array as $item) {
            $result .= (is_array($item) ? $this->implodeRecursive($separator, $item) : (string)$item) . $separator;
        }

        return substr($result, 0, -1);
    }


    public function isSignatureValidDeposit($sign, $data, $level, $method, $currency) {

        $sign = (string) $sign;
        $secretKey = (string) $this->getKeys()['api_deposit'][$this->formatLevel($level)][$method][$currency]['secret_key'];

        $sortedDataByKeys = $this->sortByKeyRecursive($this->deleteSignData($data));
        $sortedDataByKeys[] = $secretKey;

        $signString = $this->implodeRecursive(':', $sortedDataByKeys);
        $ik_sign = base64_encode(hash('sha256', $signString, true));

        return $sign === $ik_sign;
    }


    public function purchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\InterKassa\Message\PurchaseRequest', $parameters)
            ->setKeys($this->getKeys());
    }

    public function payout(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\InterKassa\Message\PayoutRequest', $parameters)
            ->setKeys($this->getKeys());
    }
}
