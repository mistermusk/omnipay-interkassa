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

    public function setKeys($method, $shop_id, $secret_key)
    {
        $this->keys[$method] = [
            'shop_id' => $shop_id,
            'secret_key' => $secret_key,
        ];
    }

    public function getKeys($method)
    {
        return isset($this->keys[$method]) ? $this->keys[$method] : null;
    }

    public function getFullKeys()
    {
        return $this->keys;
    }


    public function purchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\InterKassa\Message\PurchaseRequest', $parameters)
            ->setFullKeys($this->getFullKeys());
    }

    public function payout(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\InterKassa\Message\PayoutRequest', $parameters)
            ->setFullKeys($this->getFullKeys());
    }
}
