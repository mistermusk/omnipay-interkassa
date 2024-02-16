<?php

namespace Omnipay\InterKassa\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\InterKassa\Helpers\DataHandler;

class PayoutDataByIdResponse extends AbstractResponse
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

    function getItem() {
        return empty($this->data['data'][0]) ? null : (object)$this->data['data'][0];
    }


    public function getState()
    {
        $target = $this->getItem();
        return DataHandler::convertRawState(@$target->state);
    }


}
