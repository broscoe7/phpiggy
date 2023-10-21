<?php

declare(strict_types=1);

namespace App\Services;

use Framework\Database;

class TransactionService
{
    public function __construct(private Database $db)
    {
    }
    public function create(array $formData)
    {
        // The db will expect date and time, but the form will only provide date
        $formattedDate = "{$formData['date']} 00:00:00";
        $this->db->query("
            INSERT INTO transactions (user_id, description, amount, date) VALUES (:user_id, :description, :amount, :date);
        ", [
            "user_id" => $_SESSION["user"],
            "description" => $formData["description"],
            "amount" => $formData["amount"],
            "date" => $formattedDate
        ]);
    }
    public function getUserTransactions()
    {
        $searchTerm = addcslashes($_GET["s"] ?? "", "%_");
        $transactions = $this->db->query(
            "SELECT *, DATE_FORMAT(date, '%Y-%m-%d') AS formatted_date
            FROM transactions 
            WHERE user_id = :user_id 
            AND description LIKE :description",
            [
                "user_id" => $_SESSION["user"],
                "description" => "%{$searchTerm}%"
            ]
        )->findAll();
        return $transactions;
    }
}