<?php

declare(strict_types=1);

namespace App\Services;

use Framework\Database;
use Framework\Exceptions\ValidationException;
use App\Config\Paths;

class ReceiptService
{
    public function __construct(private Database $db)
    {
    }

    public function validateFile(?array $file)
    {
        // Check for no file upload or partial file upload
        if (!$file || $file["error"] !== UPLOAD_ERR_OK) throw new ValidationException([
            "receipt" => ["Failed to upload file"]
        ]);

        // Check file size
        $maxFileSizeMB = 3;
        // The file size key tells us the size in bytes, so this must be converted to MB.
        if ($file["size"] / 1024 / 1024 > $maxFileSizeMB) throw new ValidationException([
            "receipt" => ["File upload is too large"]
        ]);

        // Check filename, only allowing letters, numbers, spaces, period, underscore, and dash
        $originalFileName = $file["name"];
        if (!preg_match('/^[A-Za-z0-9\s._-]+$/', $originalFileName)) throw new ValidationException([
            "receipt" => ["Invalid filename"]
        ]);

        // Check MIME type of file
        $clientMimeType = $file["type"];
        $allowedMimeTypes = ["image/jpeg", "image/png", "application/pdf"];
        if (!in_array($clientMimeType, $allowedMimeTypes)) throw new ValidationException([
            "receipt" => ["Invalid file type"]
        ]);
    }

    public function upload(array $file, int $transaction)
    {
        // Create a new randomized filename so that another file is not accidentally overwritten
        $fileExtension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $newFilename = bin2hex(random_bytes(16)) . "." . $fileExtension;

        // Store file in storage directory
        $uploadPath = Paths::STORAGE_UPLOADS . "/" . $newFilename;
        if (!move_uploaded_file($file["tmp_name"], $uploadPath)) throw new ValidationException([
            "receipt" => ["Failed to upload file - path"]
        ]);

        $this->db->query("
            INSERT INTO receipts (transaction_id, original_filename, storage_filename, media_type)
            VALUES (:transaction_id, :original_filename, :storage_filename, :media_type);
        ", [
            "transaction_id" => $transaction,
            "original_filename" => $file["name"],
            "storage_filename" => $newFilename,
            "media_type" => $file["type"]
        ]);
    }

    public function getReceipt(string $id)
    {
        $receipt = $this->db->query("
            SELECT * FROM receipts WHERE id = :id
        ", ["id" => $id])->find();
        return $receipt;
    }

    public function read(array $receipt)
    {
        // Check to make sure file exists in storage
        $filePath = Paths::STORAGE_UPLOADS . "/" . $receipt["storage_filename"];
        if (!file_exists($filePath)) redirectTo("/");

        // To send data other than plain text or html we need to tell the browser what kind of data we are sending
        header("Content-Disposition: inline;filename={$receipt['original_filename']}");
        header("Content-Type: {$receipt['media_type']}");

        readfile($filePath);
    }

    public function delete(array $receipt)
    {
        // Delete file from storage
        $filePath = Paths::STORAGE_UPLOADS . "/" . $receipt["storage_filename"];
        unlink($filePath);
        // Delete record from database
        $this->db->query("DELETE FROM receipts WHERE id = :id", ["id" => $receipt["id"]]);
    }
}
