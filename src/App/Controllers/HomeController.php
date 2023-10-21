<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\TemplateEngine;
use APP\Services\TransactionService;

class HomeController
{
    public function __construct(private TemplateEngine $view, private TransactionService $transactionService)
    {
    }
    public function home(): void
    {
        $transactions = $this->transactionService->getUserTransactions();
        echo $this->view->render("index.php", ["transactions" => $transactions]); // This is how we pass data to the template engine
    }
}
