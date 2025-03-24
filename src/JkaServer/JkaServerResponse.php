<?php declare(strict_types=1);

namespace JkaServerStatus\JkaServer;

class JkaServerResponse
{
    public readonly JkaServerResponseStatus $status;
    public readonly string $data;

    public function __construct(JkaServerResponseStatus $status, string $data = "")
    {
        $this->status = $status;
        $this->data = $data;
    }
}
