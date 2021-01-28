<?php

namespace DigitalPenguin\Commerce_PFCheckout\Gateways;

use Commerce;
use comOrder;
use comPaymentMethod;
use comTransaction;

use modmore\Commerce\Admin\Widgets\Form\Field;
use modmore\Commerce\Admin\Widgets\Form\PasswordField;
use modmore\Commerce\Admin\Widgets\Form\TextField;
use modmore\Commerce\Gateways\Exceptions\TransactionException;
use modmore\Commerce\Gateways\Helpers\GatewayHelper;
use modmore\Commerce\Gateways\Interfaces\GatewayInterface;
use modmore\Commerce\Gateways\Interfaces\TransactionInterface;
use PostFinanceCheckout\Sdk\Model\LineItemType;

class PostFinanceGateway implements GatewayInterface {
    /** @var Commerce */
    protected $commerce;
    protected $adapter;

    /** @var comPaymentMethod */
    protected $method;

    public function __construct(Commerce $commerce, comPaymentMethod $method)
    {
        $this->commerce = $commerce;
        $this->method = $method;
        $this->adapter = $commerce->adapter;
    }

    /**
     * Render the payment gateway for the customer; this may show issuers or a card form, for example.
     *
     * @param comOrder $order
     * @return string
     * @throws \modmore\Commerce\Exceptions\ViewException
     */
    public function view(comOrder $order)
    {
        return $this->commerce->view()->render('frontend/gateways/postfinancegateway.twig', [
            'method'        =>  $this->method->get('id')
        ]);
    }

    public function getClient(): \PostFinanceCheckout\Sdk\ApiClient
    {
        $userId = $this->method->getProperty('pfUserId');
        $secret = $this->method->getProperty('pfSecretApiKey');

        // Setup API client
        $client = new \PostFinanceCheckout\Sdk\ApiClient($userId, $secret);
        $httpClientType = \PostFinanceCheckout\Sdk\Http\HttpClientFactory::TYPE_CURL;
        $client->setHttpClientType($httpClientType);

        return $client;
    }

    /**
     * Handle the payment submit, returning an up-to-date instance of the PaymentInterface.
     *
     * @param comTransaction $transaction
     * @param array $data
     * @return Transactions\Redirect
     * @throws \PostFinanceCheckout\Sdk\ApiException
     * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException
     * @throws \PostFinanceCheckout\Sdk\VersioningException
     */
    public function submit(comTransaction $transaction, array $data) : Transactions\Redirect
    {
        $order = $transaction->getOrder();

        $client = $this->getClient();
        $spaceId = $this->method->getProperty('pfSpaceId');

        // Create test transaction
        $lineItem = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
        $lineItem->setName('Red T-Shirt');
        $lineItem->setUniqueId('1111');
        $lineItem->setSku('red-t-shirt-123');
        $lineItem->setQuantity(1);
        $lineItem->setAmountIncludingTax(29.95);
        $lineItem->setType(LineItemType::PRODUCT);


        $transactionPayload = new \PostFinanceCheckout\Sdk\Model\TransactionCreate();
        $transactionPayload->setCurrency('EUR');
        $transactionPayload->setLineItems(array($lineItem));
        $transactionPayload->setAutoConfirmationEnabled(true);
        $transactionPayload->setSuccessUrl(GatewayHelper::getReturnUrl($transaction));
        $transactionPayload->setFailedUrl(GatewayHelper::getReturnUrl($transaction));

        $pfTransaction = $client->getTransactionService()->create($spaceId, $transactionPayload);

        // Save the new id
        $transaction->setProperty('pfTransactionId',$pfTransaction->getId());
        $transaction->save();

        // Create Payment Page URL:
        $redirectionUrl = $client->getTransactionPaymentPageService()->paymentPageUrl($spaceId, $pfTransaction->getId());

        $data = [
            'reference'             =>  $pfTransaction->getId(),
            'is_paid'               =>  false,
            'awaiting_confirmation' =>  true,
            'redirect_url'          =>  $redirectionUrl,
            'meta'                  =>  $pfTransaction->getMetaData()
        ];
        return new \DigitalPenguin\Commerce_PFCheckout\Gateways\Transactions\Redirect($order,$data);

    }

    /**
     * Handle the customer returning to the shop, typically only called after returning from a redirect.
     *
     * @param comTransaction $transaction
     * @param array $data
     */
    public function returned(comTransaction $transaction, array $data): Transactions\Order
    {
        $order = $transaction->getOrder();
        $pfTransactionId = $transaction->getProperty('pfTransactionId');

        $client = $this->getClient();
        $pfTransaction = $client->getTransactionService()->read($this->method->getProperty('pfSpaceId'),$pfTransactionId);
        $responseArray = json_decode($pfTransaction,true);

        // Check if authorized - ( AUTHORIZED, FAILED )
        $this->commerce->modx->log(1,'Payment Status is: ' . $responseArray['status']);

        $this->commerce->modx->log(1,print_r($responseArray,true));

        return new \DigitalPenguin\Commerce_PFCheckout\Gateways\Transactions\Order($order,$data);
    }

    /**
     * Define the configuration options for this particular gateway instance.
     *
     * @param comPaymentMethod $method
     * @return Field[]
     */
    public function getGatewayProperties(comPaymentMethod $method) : array
    {

        $fields = [];

        $fields[] = new TextField($this->commerce, [
            'name' => 'properties[pfUserId]',
            'label' => 'User Id',
            'description' => 'Enter your PostFinance user id.',
            'value' => $method->getProperty('pfUserId'),
        ]);

        $fields[] = new TextField($this->commerce, [
            'name' => 'properties[pfSpaceId]',
            'label' => 'Space Id',
            'description' => 'Enter your PostFinance space id.',
            'value' => $method->getProperty('pfSpaceId'),
        ]);

        $fields[] = new PasswordField($this->commerce, [
            'name' => 'properties[pfSecretApiKey]',
            'label' => 'Secret Key',
            'description' => 'Enter your secret API key.',
            'value' => $method->getProperty('pfSecretApiKey'),
        ]);


        return $fields;
    }
}