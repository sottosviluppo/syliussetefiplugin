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
        $method = $request->getMethod();
        $parameters = json_decode($request->getContent(), true);
        $data=["params"=>$params, "get" => $paramsGet, "method" => $method, "body"=>$parameters];
        return new JsonResponse($data);
    }
}
