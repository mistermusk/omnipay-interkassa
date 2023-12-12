<?php

namespace Omnipay\InterKassa\Message;

use Omnipay\Common\Message\AbstractResponse;

class PayoutResponse extends AbstractResponse
{

    public function isSuccessful()
    {
        if (isset($this->data['status'])) {
            if ($this->data['status'] == 'ok') {
                return true;
            }
        }
    }

    public function getTransactionReference()
    {
        return isset($this->data['tx']['tx']) ? $this->data['tx']['tx'] : null;
    }

    public function getMessage()
    {
        return isset($this->data['message']) ? json_encode($this->data['message']) : null;
    }

}
