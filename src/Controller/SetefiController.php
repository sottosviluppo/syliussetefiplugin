<?php

namespace Filcronet\SyliusSetefiPlugin\Controller;

use Filcronet\SyliusSetefiPlugin\Payum\SetefiApi;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SetefiController extends AbstractController
{
    private $api;

    /*public function __construct(CaptureAction $ca)
    {
        dd($ca);
    }*/

    public function resultPayment(Request $request)
    {
        $orderId = $request->query->get('orderId');
        $paymentId = $request->query->get('paymentId');

        $payment = $this->container->get('sylius.repository.payment')->findOneBy(['order' => $orderId]);
        $gatewayConfig = $payment->getMethod()->getGatewayConfig()->getConfig();

        $rawCorrelationId = bin2hex(openssl_random_pseudo_bytes(16));

        $correlationId =  substr($rawCorrelationId, 0, 8);
        $correlationId .= "-";
        $correlationId .=  substr($rawCorrelationId, 8, 4);
        $correlationId .= "-";
        $correlationId .=  substr($rawCorrelationId, 12, 4);
        $correlationId .= "-";
        $correlationId .=  substr($rawCorrelationId, 16, 4);
        $correlationId .= "-";
        $correlationId .=  substr($rawCorrelationId, 20);

        $headers = array(
            "X-Api-Key: " . $gatewayConfig['apiKey'],
            "Content-Type: application/json",
            "Correlation-Id: " . $correlationId,
        );

        $ch = curl_init($gatewayConfig['endpoint'] ."/orders/".$orderId);
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

        return new JsonResponse($resultData);
    }
}
