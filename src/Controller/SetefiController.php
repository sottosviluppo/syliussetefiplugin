<?php

namespace Filcronet\SyliusSetefiPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetefiController extends AbstractController
{
    public function resultPayment(): Response
    {
        $paymentId = $_POST['paymentid'];
        dd($_POST);
        $data=["message"=>$paymentId];
        return new JsonResponse($data);
    }
}
