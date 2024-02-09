<?php

namespace Omnipay\InterKassa\Message;

use Omnipay\Common\Message\AbstractResponse;

class PayoutResponse extends AbstractResponse
{

    public function isSuccessful()
    {

        if(@$this->data['status'] === 'error') {
            return false;
        }
        if (isset($this->data['data'])) {
            $status = (int)@$this->data['data']['state'];
            if (in_array($status, [9, 10, 11])) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }
    public function getStatus()
    {
        $status = (int)@$this->data['data']['state'];
        if(!$status) {
            return 'canceled';
        }
        if (in_array($status, [9, 10, 11])) {
            return 'canceled';
        } elseif (in_array($status, [8])) {
            return 'success';
        }
        return 'pending';
    }

    public function getMessage()
    {
        return isset($this->data['message']) ? json_encode($this->data['message']) : null;
    }

}
