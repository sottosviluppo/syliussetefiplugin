<?php

declare(strict_types=1);

namespace Filcronet\SyliusSetefiPlugin\Payum;

final class SetefiApi
{
    private $apiKey;
    private $endpoint;

    public function __construct(string $endpoint, string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->endpoint = $endpoint;

    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }
}
