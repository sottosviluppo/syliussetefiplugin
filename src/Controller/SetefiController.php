<?php

namespace Filcronet\SyliusSetefiPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SetefiController extends AbstractController
{
    public function resultPayment(Request $request): Response
    {
        $params = $request->request->all();
        $paramsGet = $request->query->all();
        $data=["params"=>$params, "get" => $paramsGet];
        return new JsonResponse($data);
    }
}
