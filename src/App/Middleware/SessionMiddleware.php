<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;
use App\Exceptions\SessionException;

class SessionMiddleware implements MiddlewareInterface
{
    public function process(callable $next)
    {
        // Check to make sure there are no multiple sessions (sometimes a package or dependency might start a session)
        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new SessionException("Session already active.");
        }
        // Check if data has been sent to the browser because after that starts, a session cannot be created.
        if (headers_sent($filename, $line)) {
            throw new SessionException("Headers already sent. Consider enabling output buffering. Data outputted from {$filename} line {$line}");
        }
        session_start();
        $next();
        session_write_close(); // Tells PHP to write session data and close session. This improves app performance.
    }
}
