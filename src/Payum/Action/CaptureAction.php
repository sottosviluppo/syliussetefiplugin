<?php

declare(strict_types=1);

namespace Filcronet\SyliusSetefiPlugin\Payum\Action;

use Filcronet\SyliusSetefiPlugin\Payum\SetefiApi;
use GuzzleHttp\Client;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpRedirect;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Payum\Core\Request\Capture;
use Symfony\Component\HttpFoundation\RequestStack;

final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    private $client;
    private $api;
    private $rs;

    public function __construct(Client $client, RequestStack $rs)
    {
        $this->client = $client;
        $this->rs = $rs;
    }

    public function getLocaleCode($locale): string
    {
        $codes = array(
            'it_IT' => 'ITA',
            'en_US' => 'USA',
            'en_GB' => 'USA',
            'fr_FR' => 'FRA',
            'de_DE' => 'DEU',
            'ru_RU' => 'RUS',
            'es_ES' => 'SPA',
            'pt_PT' => 'POR'
        );

        if (!array_key_exists($locale, $codes)) {
            return 'ITA';
        }
        return $codes[$locale];
    }

    public function getCurrencyCode($iso): string
    {
        $codes = array(
            'CHF' => '756',
            'EUR' => '978',
            'GBP' => '826',
            'USD' => '840',
        );

        if (!array_key_exists($iso, $codes)) {
            return '978';
        }
        return $codes[$iso];
    }

    private function getDivideBy($orderAmount): float|int
    {
        $divideBy = 100;
        return $orderAmount/$divideBy;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        // Protocollo XML Hosted 3DSecure - Inizializzazione
        $merchantDomain = $this->rs->getMainRequest()->getSchemeAndHttpHost().'/'.$this->rs->getMainRequest()->getLocale().'/setefi/result/payment';

        $setefiPaymentGatewayDomain = $this->api->getEndpoint();
        $terminalId = $this->api->getTerminalId();
        $terminalPassword = $this->api->getTerminalPassword();

        $parameters = array(
            'id' => $terminalId,
            'password' => $terminalPassword,
            'operationType' => 'initialize',
            'amount' => $this->getDivideBy($payment->getAmount()),
            'currencyCode' => $this->getCurrencyCode($payment->getCurrencyCode()),
            'language' => $this->getLocaleCode($this->rs->getMainRequest()->getLocale()),
            'responseToMerchantUrl' => $merchantDomain,
            'merchantOrderId' => $payment->getOrder()->getId(),
        );

        dd($parameters);

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $setefiPaymentGatewayDomain);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($curlHandle, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
        $xmlResponse = curl_exec($curlHandle);
        curl_close($curlHandle);

        $response = new \SimpleXMLElement($xmlResponse);
        $paymentId = $response->paymentid;
        $paymentUrl = $response->hostedpageurl;
        $securityToken = $response->securitytoken;

        $setefiPaymentPageUrl = "$paymentUrl?PaymentID=$paymentId";
        throw new HttpRedirect($setefiPaymentPageUrl);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
            ;
    }

    public function setApi($api): void
    {
        if (!$api instanceof SetefiApi) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . SetefiApi::class);
        }

        $this->api = $api;
    }
}
