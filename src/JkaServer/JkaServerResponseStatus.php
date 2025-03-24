<?php declare(strict_types=1);

namespace JkaServerStatus\JkaServer;

enum JkaServerResponseStatus {
    case Timeout;

    /**
     * Network error, other than "timeout".
     */
    case NetworkError;

    case Success;
}
