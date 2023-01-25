<?php

namespace Filcronet\SyliusSetefiPlugin\Payum\Action;

use Filcronet\SyliusSetefiPlugin\Payum\SetefiApi;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Payum\Core\Request\Capture;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class CaptureAction extends AbstractController implements ActionInterface, ApiAwareInterface
{
    private $client;
    private $api;

    public function __construct(Client $client)
    {
        $this->client = $client;
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

        try {
            // Protocollo XML Hosted 3DSecure - Inizializzazione

            $merchantDomain = 'http://localhost/en_US/order/thank-you';

            $setefiPaymentGatewayDomain = $this->api->getEndpoint();
            $terminalId = $this->api->getTerminalId();
            $terminalPassword = $this->api->getTerminalPassword();

            $parameters = array(
                'id' => $terminalId,
                'password' => $terminalPassword,
                'operationType' => 'initialize',
                'amount' => $this->getDivideBy($payment->getAmount()),
                'currencyCode' => $this->getCurrencyCode($payment->getCurrencyCode()),
                'language' => 'ITA',
                'responseToMerchantUrl' => $merchantDomain,
                'recoveryUrl' => $merchantDomain,
                'merchantOrderId' => $payment->getOrder()->getId(),
            );

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

            $setefiPaymentUrl = "$paymentUrl?PaymentID=$paymentId";
            $this->redirect($setefiPaymentUrl);
        } catch (RequestException $exception){
            $response = $exception->getResponse();
        } finally {
            $payment->setDetails(['status' => $response->getStatusCode()]);
        }
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
