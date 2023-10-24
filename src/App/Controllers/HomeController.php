<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\TemplateEngine;
use App\Services\TransactionService;

class HomeController
{
    public function __construct(private TemplateEngine $view, private TransactionService $transactionService)
    {
    }
    public function home(): void
    {
        $page = $_GET["p"] ?? 1;
        $page = (int) $page;
        $length = 3; // The number of transactions to be shown per page
        $offset = ($page - 1) * $length;
        $searchTerm = $_GET["s"] ?? null;
        [$transactions, $count] = $this->transactionService->getUserTransactions($length, $offset);
        $lastPage = ceil($count / $length);
        $pages = $lastPage ? range(1, $lastPage) : []; // Create array of page numbers
        $pageLinks = array_map(
            fn ($pageNum) => http_build_query([
                "p" => $pageNum,
                "s" => $searchTerm
            ]),
            $pages
        ); // Create array of page links from page numbers array
        echo $this->view->render("index.php", [
            "transactions" => $transactions,
            "currentPage" => $page,
            "previousPageQuery" => http_build_query([ // Method will ignore any null values
                "p" => $page - 1,
                "s" => $searchTerm,
            ]),
            "lastPage" => $lastPage,
            "nextPageQuery" => http_build_query([
                "p" => $page + 1,
                "s" => $searchTerm
            ]),
            "pageLinks" => $pageLinks,
            "searchTerm" => $searchTerm
        ]); // This is how we pass data to the template engine
    }
}
