<?php

namespace Filcronet\SyliusSetefiPlugin\Controller;

use Filcronet\SyliusSetefiPlugin\Payum\SetefiApi;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SetefiController extends AbstractController implements ApiAwareInterface
{
    private $api;

    /**
     * @param $api
     */
    public function __construct(SetefiApi $api)
    {
        $this->api = $api;
    }


    public function resultPayment(Request $request)
    {
        $orderId = $request->query->get('orderId');
        $paymentId = $request->query->get('paymentId');

        $apiUrl = $this->api->getEndpoint();
        $apiKey = $this->api->getApiKey();

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
            "X-Api-Key: " . $apiKey,
            "Content-Type: application/json",
            "Correlation-Id: " . $correlationId,
        );

        $ch = curl_init($apiUrl ."/orders/".$orderId);
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

    public function setApi($api): void
    {
        if (!$api instanceof SetefiApi) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . SetefiApi::class);
        }

        $this->api = $api;
    }
}
