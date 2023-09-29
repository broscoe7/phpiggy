<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;
use Framework\Exceptions\ValidationException;

class ValidationExceptionMiddleware implements MiddlewareInterface
{

    public function process(callable $next)
    {
        try {
            $next();
        } catch (ValidationException $e) {
            $_SESSION["errors"] = $e->errors; // Saves the error so it isn't lost after the page redirect.

            // Delete sensitive information from the original form submission so that it is not sent to the template
            $oldFormData = $_POST;
            $excludedFields = ["password", "confirmPassword"];
            $formattedFormData = array_diff_key($oldFormData, array_flip($excludedFields));

            $_SESSION["oldFormData"] = $formattedFormData; // Save originally submitted data so it can be repopulated upon redirect.
            $referer = $_SERVER["HTTP_REFERER"]; // Since there are multiple forms on the page, this redirects to the form the info has been sent from. This superglobal is only available after a form has been submitted.
            redirectTo($referer);
        }
    }
}
