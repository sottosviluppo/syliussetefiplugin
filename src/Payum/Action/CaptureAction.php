<?php

declare(strict_types=1);

namespace Filcronet\SyliusSetefiPlugin\Payum\Action;

use Filcronet\SyliusSetefiPlugin\Payum\SetefiApi;
use Filcronet\SyliusSetefiPlugin\Services\SetefiManager;
use GuzzleHttp\Client;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpRedirect;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Payum\Core\Request\Capture;
use Symfony\Component\HttpFoundation\RequestStack;

final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    private $api;
    private $rs;
    private $client;
    private $logger;
    private $sm;

    public function __construct(Client $client, RequestStack $rs, LoggerInterface $logger, SetefiManager $sm)
    {
        $this->client = $client;
        $this->rs = $rs;
        $this->logger = $logger;
        $this->sm = $sm;
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

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        // Protocollo XML Hosted 3DSecure - Inizializzazione
        $merchantDomain = $this->rs->getMainRequest()->getSchemeAndHttpHost().'/'.$this->rs->getMainRequest()->getLocale().'/setefi/result/payment?orderId='.$payment->getOrder()->getId().'&';
        $apiUrl = $this->api->getEndpoint();
        $apiKey = $this->api->getApiKey();

        $requestBodyData = array(
            "order" => array(
                "orderId"=>$payment->getOrder()->getId(),
                "amount"=>$payment->getAmount(),
                "currency"=>$payment->getCurrencyCode(),
            ),
            "paymentSession" => array(
                "actionType"=>"PAY",
                "amount"=>$payment->getAmount(),
                "recurrence" =>array(
                    "action" =>"NO_RECURRING",
                ),
                "exemptions" =>"NO_PREFERENCE",
                'language' => $this->getLocaleCode($this->rs->getMainRequest()->getLocale()),
                "resultUrl"=>$merchantDomain,
                "cancelUrl"=>$merchantDomain,
                "notificationUrl"=>$merchantDomain,
            ),
        );
        $headers = array(
            "X-Api-Key: " . $apiKey,
            "Content-Type: application/json",
            "Correlation-Id: " . $this->sm->generateCorralationId(),
        );

        $ch = curl_init($apiUrl ."/orders/hpp");
        $payload = json_encode($requestBodyData);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resultJson = curl_exec($ch);

        if (curl_errno($ch)) {
            die("curl error: " . curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code != 200) {
            die("invalid http status code: ".print_r($http_code, true));
        }

        curl_close($ch);

        $resultData = json_decode($resultJson);

        $setefiPaymentPageUrl = $resultData->hostedPage;

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
