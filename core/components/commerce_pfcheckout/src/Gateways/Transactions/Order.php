<?php

namespace DigitalPenguin\Commerce_PFCheckout\Gateways\Transactions;

use modmore\Commerce\Gateways\Interfaces\TransactionInterface;

class Order implements TransactionInterface
{
    private $order;
    private $data;

    public function __construct($order, $data)
    {
        $this->order = $order;
        $this->data = $data;
    }

    /**
     * Indicate if the transaction was paid
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->data['state'] === 'AUTHORIZED';
    }

    /**
     * Indicate if a transaction is waiting for confirmation/cancellation/failure. This is the case when a payment
     * is handled off-site, offline, or asynchronously in another why.
     *
     * When a transaction is marked as awaiting confirmation, a special page is shown when the customer returns
     * to the checkout.
     *
     * If the payment is a redirect (@see WebhookTransactionInterface), the payment pending page will offer the
     * customer to return to the redirectUrl.
     *
     * @return bool
     */
    public function isAwaitingConfirmation()
    {
        if(in_array($this->data['state'],['AUTHORIZED','FAILED'],true)) {
            return false;
        }
        return true;
    }

    public function isRedirect()
    {
        return false;
    }

    /**
     * Indicate if the payment has failed.
     *
     * @return bool
     * @see TransactionInterface::getExtraInformation()
     */
    public function isFailed()
    {
        if($this->data['state'] === 'FAILED') {
            if($this->data['failureReason']['category'] !== 'END_USER') {
                return true;
            }
        }
        return false;
    }

    /**
     * Indicate if the payment was cancelled by the user (or possibly merchant); which is a separate scenario
     * from a payment that failed.
     *
     * @return bool
     */
    public function isCancelled()
    {
        if($this->data['state'] === 'FAILED') {
            if($this->data['failureReason']['category'] === 'END_USER') {
                return true;
            }
        }
        return false;
    }

    /**
     * If an error happened, return the error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        if(isset($this->data['failureReason']['description'])) {
            return $this->data['failureReason']['description']['en-US'];
        }
        return '';
    }


    /**
     * Return the (payment providers') reference for this order. Treated as a string.
     *
     * @return string
     */
    public function getPaymentReference()
    {
        return $this->data['id'];
    }

    /**
     * Return a key => value array of transaction information that should be made available to merchant users
     * in the dashboard.
     *
     * @return array
     */
    public function getExtraInformation()
    {
        return [];
    }

    /**
     * Return an array of all (raw) transaction data, for debugging purposes.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}