<?php


namespace Omnipay\InterKassa\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    public function isSuccessful()
    {
        return isset($this->data['resultMsg']) === 'Success';
    }


    public function isRedirect()
    {
        return isset($this->data['resultData']['paymentForm']['action']);
    }

    public function getRedirectUrl()
    {
        return $this->isRedirect() ? $this->data['resultData']['paymentForm']['action'] : null;
    }


    public function getMessage()
    {
        return isset($this->data['resultMsg']) ? json_encode($this->data['resultMsg']) : null;
    }


}
