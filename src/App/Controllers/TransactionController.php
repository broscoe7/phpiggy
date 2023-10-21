<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\TemplateEngine;
use App\Services\{ValidatorService, TransactionService};

class TransactionController
{
    public function __construct(private TemplateEngine $view, private ValidatorService $validatorService, private TransactionService $transactionService)
    {
    }

    public function createView()
    {
        echo $this->view->render("transactions/create.php");
    }

    public function create()
    {
        $this->validatorService->validateTransaction($_POST);
        // Even though $_POST is available everywhere we send it as an argument here to keep the validator service flexible. In the future, we might want to use the same service for other kinds of data like a json, which would not be available globally.
        $this->transactionService->create($_POST);
        redirectTo("/");
    }
}
