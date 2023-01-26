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
        $data=["message"=>"OK"];
        return new JsonResponse($data);
    }
}
