<?php declare(strict_types=1);

namespace JkaServerStatus\JkaServer;

class JkaServerResponse
{
    public readonly bool $isError;
    public readonly bool $isTimeout;
    public readonly string $data;

    public function __construct(bool $isError, bool $isTimeout, string $data = "")
    {
        $this->isError = $isError;
        $this->isTimeout = $isTimeout;
        $this->data = $data;
    }
}
