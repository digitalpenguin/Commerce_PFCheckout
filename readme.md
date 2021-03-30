PostFinance Checkout Integration for Commerce on MODX
=

https://checkout.postfinance.ch/en-us/doc

_"PostFinance Checkout is the most practical all-in-one payment solution for Swiss online shops – a contractual partner, a plug-in and five payment options for your customers: PostFinance Card, PostFinance e-finance, TWINT, Visa and Mastercard."_

Developed by Murray Wood at [Digital Penguin Ltd.](https://www.digitalpenguin.hk "Digital Penguin - Hong Kong")

**Thanks to Sebastian G. Marinescu from [xvanced](https://xvanced.com) for sponsoring the development of this module.


Requirements
-

Commerce_PFCheckout requires at least MODX 2.6.5 and PHP 7.1 or higher. Commerce by modmore should be at least version 1.1.4. You also need to have an PostFinance Checkout account which provides the `space id`, `user id` and `secret key`.

Installation
-

Install via the MODX package manager. The listing name is "PostFinance Checkout for Commerce". The package namespace is Commerce_PFCheckout.

Setup the Module
-

Once installed, navigate to Commerce in the MODX manager. Select the Configuration tab and then click on Modules. Find Commerce_PFCheckout in the module list and click on it to make a window pop up where you can enable the module for Test Mode, Live Mode, or both.

Now the module is enabled, you can click on the Payment Methods tab on the left. Then click Add a Payment Method. Select PostFinance Checkout from the Gateway dropdown box and then give it a name e.g. PostFinance Checkout. Next, click on the availability tab and enable it for test or live modes and then click save.

After saving, you'll see an extra PostFinance Checkout tab appears at the top of the window. Here you can enter your PostFinance Checkout API credentials: `Space id`, `User id` and the `Secret key`

Congratulations! PostFinance Checkout should now appear as a payment method a customer can use during checkout.

Customize the Markup
-

On the payment page of the checkout process in Commerce, the PostFinance Checkout payment method is
displayed according to a default twig template located in your MODX install at:
```core/components/commerce_pfcheckout/templates/frontend/gateways/postfinancegateway.twig```

There is a system setting in the commerce_pfcheckout namespace called `commerce_pfcheckout.payment_template`.
The default value is ```frontend/gateways/postfinancegateway.twig```

If you would like to customise the default template, don't edit the file directly as it will
be overwritten in future upgrades. Instead, create a new file in the same location with a different name 
e.g. `mycustomtemplate.twig` and update the system setting's value.

For example: ```frontend/gateways/mycustomtemplate.twig```. The module will then use your custom template 
automatically.

Setup in the PostFinance Checkout Portal
-

Besides the template, most configuration takes place inside PostFinance Checkout's merchant portal.
You need to setup your which payment methods you want to accept, and you can customise the look
of the payment page the customer is redirected to.

You will also need to get your three authentication details here: 
1. Space id
2. User id
3. Secret Key

Login here: https://checkout.postfinance.ch/user/login

Testing
=

https://developer.postfinancepayments.ch/documentation/testcases/
