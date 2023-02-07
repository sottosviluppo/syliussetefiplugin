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
}
