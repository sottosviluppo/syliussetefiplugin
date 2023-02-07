<?php

namespace Filcronet\SyliusSetefiPlugin\Services;

class SetefiManager
{
    public function checkPayment($paymentId, $lastOperation){
        $result = [
            'result' => 'KO',
            'lastOperation' => $lastOperation
        ];

        if ($paymentId === $lastOperation['operationId'] && $lastOperation['operationResult'] === 'EXECUTED'){
            $result = [
                'result' => 'OK',
                'lastOperation' => $lastOperation
            ];
        }
        return $result;
    }

    public function generateCorralationId(){
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

        return $correlationId;
    }
}
