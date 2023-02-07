<?php

declare(strict_types=1);

namespace Filcronet\SyliusSetefiPlugin\Payum;

use Filcronet\SyliusSetefiPlugin\Payum\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class SetefiPaymentGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'setefi_payment',
            'payum.factory_title' => 'XPay Gateway',
            'payum.action.status' => new StatusAction(),
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            return new SetefiApi($config['endpoint'], $config['apiKey']);
        };
    }
}
