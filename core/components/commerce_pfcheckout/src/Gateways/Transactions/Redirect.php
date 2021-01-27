<?php

namespace DigitalPenguin\Commerce_PFCheckout\Gateways\Transactions;

use modmore\Commerce\Gateways\Interfaces\RedirectTransactionInterface;

class Redirect implements RedirectTransactionInterface
{
    private $data;

    public function __construct($order,$data)
    {
        $this->data = $data;
    }

    /**
     * Indicate if the transaction requires the customer to be redirected off-site.
     *
     * @return bool
     */
    public function isRedirect() {
        return true;
    }

    /**
     * @return string Either GET or POST
     */
    public function getRedirectMethod() {
        return 'GET';
    }

    /**
     * Return the fully qualified URL to redirect the customer to.
     *
     * @return string
     */
    public function getRedirectUrl() {
        return $this->data['redirect_url'];
    }

    /**
     * Return the redirect data as a key => value array, when the redirectMethod is POST.
     *
     * @return array
     */
    public function getRedirectData() {
        return [];
    }

    public function isPaid()
    {
        return $this->data['is_paid'];
    }

    public function isAwaitingConfirmation()
    {
        return $this->data['awaiting_confirmation'];
    }

    public function isFailed()
    {
        return false;
    }

    public function isCancelled()
    {
        return false;
    }

    public function getErrorMessage()
    {
        return '';
    }

    public function getPaymentReference()
    {
        return $this->data['reference'];
    }

    public function getExtraInformation()
    {
        return [];
    }

    public function getData()
    {
        return $this->data;
    }
}
