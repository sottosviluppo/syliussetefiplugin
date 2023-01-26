<?php

namespace Filcronet\SyliusSetefiPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetefiController extends AbstractController
{
    #[Route('setefi/result/payment', name: 'setefi_result_payment', methods: ['POST'])]
    public function resultPayment(): Response
    {

        $data=["message"=>"OK"];
        return new JsonResponse($data);
    }
}
