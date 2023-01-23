<?php

declare(strict_types=1);

namespace Filcronet\SyliusSetefiPlugin\Payum;

final class SetefiApi
{
    /** @var string */
    private $terminalId;
    /** @var string */
    private $terminalPassword;
    /** @var string */
    private $endpoint;

    public function __construct(string $endpoint, string $terminalId, string $terminalPassword)
    {
        $this->terminalId = $terminalId;
        $this->terminalPassword = $terminalPassword;
        $this->endpoint = $endpoint;

    }

    public function getTerminalId(): string
    {
        return $this->terminalId;
    }

    public function getTerminalPassword(): string
    {
        return $this->terminalPassword;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }
}
