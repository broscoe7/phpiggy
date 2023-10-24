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

    public function editView(array $params)
    {
        $transaction = $this->transactionService->getUserTransaction($params["transaction"]);
        if (!$transaction) redirectTo("/");
        echo $this->view->render("transactions/edit.php", ["transaction" => $transaction]);
    }

    public function edit(array $params)
    {
        // Check if transaction exists and user can edit it
        $transaction = $this->transactionService->getUserTransaction($params["transaction"]);
        if (!$transaction) redirectTo("/");

        // Validate form
        $this->validatorService->validateTransaction($_POST);

        // Edit the transaction in database
        $this->transactionService->update($_POST, $transaction["id"]);
        redirectTo($_SERVER["HTTP_REFERER"]);
    }

    public function delete(array $params)
    {
        $this->transactionService->delete((int) $params["transaction"]);
        redirectTo("/");
    }
}
