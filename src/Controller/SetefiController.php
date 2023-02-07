<?php

namespace Filcronet\SyliusSetefiPlugin\Controller;

use Filcronet\SyliusSetefiPlugin\Services\SetefiManager;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SetefiController extends AbstractController
{
    private $sm;

    public function __construct(SetefiManager $sm)
    {
        $this->sm = $sm;
    }

    public function resultPayment(Request $request)
    {
        $resultPayment = false;
        $orderId = $request->query->get('orderId');
        $paymentId = $request->query->get('paymentid');

        if ($orderId && $paymentId){
            $payment = $this->container->get('sylius.repository.payment')->findOneBy(['order' => $orderId]);
            $gatewayConfig = $payment->getMethod()->getGatewayConfig()->getConfig();
            if ($payment && $gatewayConfig){
                $headers = array(
                    "X-Api-Key: " . $gatewayConfig['apiKey'],
                    "Content-Type: application/json",
                    "Correlation-Id: " . $this->sm->generateCorralationId(),
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

                $resultData = json_decode($resultJson, true);
                $lastOperation = end($resultData['operations']);

                $result = $this->sm->checkPayment($paymentId, $lastOperation);

                if ($result['result']=='OK'){
                    $resultPayment = true;
                    $payment->setState(PaymentInterface::STATE_COMPLETED);
                    $payment->getOrder()->setPaymentState(OrderPaymentStates::STATE_PAID);
                    $payment->setDetails(['paymentId' => $lastOperation['operationId'], 'operationType' => $lastOperation['operationType'], 'operationResult' => $lastOperation['operationResult']]);
                } else {
                    //$payment->setState(PaymentInterface::STATE_FAILED);
                    $payment->setDetails(['paymentId' => $lastOperation['operationId'], 'operationType' => $lastOperation['operationType'], 'operationResult' => $lastOperation['operationResult']]);
                }
                $manager = $this->container->get('sylius.manager.payment');
                $manager->persist($payment);
                $manager->flush();
            }
        }

        return $this->redirect($this->generateUrl('sylius_shop_order_thank_you', array('resultPayment' => $resultPayment, 'orderId' => $orderId)));
        //return new JsonResponse($resultData);
    }
}
