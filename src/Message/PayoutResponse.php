<?php

namespace Omnipay\InterKassa\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\InterKassa\Helpers\DataHandler;

class PayoutResponse extends AbstractResponse
{

    
    
    public function isSuccessful()
    {

        if(@$this->data['status'] === 'error') {
            return false;
        }
        if (!isset($this->data['data'])) {
           return false;
        }
        return true;
    }

    public function getState() {
        return (int)@$this->data['data']['state'];
    }


    

    public function getPaymentState()
    {
        return DataHandler::convertRawState($this->getState());
    }

    public function getMessage()
    {
        return isset($this->data['message']) ? json_encode($this->data['message']) : null;
    }

}
