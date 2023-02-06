<?php

namespace Filcronet\SyliusSetefiPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SetefiController extends AbstractController
{
    /*public function resultPayment()
    {
        $merchantDomain = 'http://127.0.0.1';

        $paymentId = $_POST['paymentid'];
        $result = array();
        $result['result'] = $_POST['result'];
        $result['authorizationCode'] = $_POST['authorizationcode'];
        $result['rrn'] = $_POST['rrn'];
        $result['merchantOrderId'] = $_POST['merchantorderid'];
        $result['responsecode'] = $_POST['responsecode'];
        $result['threeDSecure'] = $_POST["threedsecure"];
        $result['maskedPan'] = $_POST["maskedpan"];
        $result['cardCountry'] = $_POST["cardcountry"];
        $result['customField'] = $_POST["customfield"];
        $result['securityToken'] = $_POST["securitytoken"];

        session_id($paymentId);
        session_start();
        $_SESSION['payment-result'] = $result;

        $resultPageUrl = $merchantDomain . "/result.php?paymentid=" . $paymentId;

        echo $resultPageUrl;
    }*/
    public function resultPayment(Request $request): Response
    {
        $params = $request->request->all();
        $paramsGet = $request->query->all();
        $method = $request->getMethod();
        $parameters = json_decode($request->getContent(), true);
        $data=["params"=>$params, "get" => $paramsGet, "method" => $method, "body"=>$parameters];
        return new JsonResponse($data);
    }
}
