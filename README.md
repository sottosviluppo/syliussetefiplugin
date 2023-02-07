<p align="center">
    <a href="https://filcronet.it" target="_blank">
        <img src="https://www.filcronet.it/theme/filcronet/img/filcronet-logo.svg"  alt="filcronet_logo"/>
    </a>
</p>

<h1 align="center">Sylius XPay Plugin</h1>

<p align="center">Plugin to execute payments through the XPay payment gateway via the Hosted Payment Page modality.</p>

## Documentation

For a comprehensive guide on Setefi Payment Gateway please go to Nexi documentation,
there you will find the <a href="https://developer.nexigroup.com/en/servizio-ecommerce/">XPay payment gateway documentation</a>.

## Quickstart Installation

1. Run `composer require sottosviluppo/syliussetefiplugin`.

2. In <b>config/routes.yaml</b> add the following lines:

    ```bash
    filcronet_sylius_setefi_plugin:
      resource: "@FilcronetSyliusSetefiPlugin/Resources/config/routing.yaml"
    ```

The plugin is now installed and ready to use.

## Usage

1. Login into the Admin panel.
2. Go to payment methods and add the new method named XPay.
3. Insert your XPay API endpoint

    ```bash
    Test url: https://stg-ta.nexigroup.com/api/phoenix-0.0/psp/api/v1
    Production url: https://xpay.nexigroup.com/api/phoenix-0.0/psp/api/v1
    ```
    
4. Insert your XPay API key.
5. Fill the rest of the fields and save the payment method.
6. Your E-commerce can now use the XPay Payment Gateway to recive payments.
