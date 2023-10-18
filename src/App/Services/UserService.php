<?php

declare(strict_types=1);

namespace App\Services;

use Framework\Database;
use Framework\Exceptions\ValidationException;

class UserService
{
    public function __construct(private Database $db)
    {
    }

    public function isEmailTaken(string $email)
    {
        $emailCount = $this->db->query("SELECT COUNT(*) FROM users WHERE email = :email", ["email" => $email])->count();
        if ($emailCount > 0) throw new ValidationException(["email" => "Email taken."]);
    }

    public function createUser(array $formData)
    {
        $password = password_hash($formData["password"], PASSWORD_BCRYPT, ["cost" => 12]);

        $this->db->query(
            "INSERT INTO users (email, password, age, country, social_media_url) VALUES (:email, :password, :age, :country, :url)",
            [
                "email" => $formData["email"],
                "password" => $password,
                "age" => $formData["age"],
                "country" => $formData["country"],
                "url" => $formData["socialMediaURL"]
            ]
        );
        // After a registration is successful the user should be logged in:
        session_regenerate_id();
        $_SESSION["user"] = $this->db->id();
    }

    public function login(array $formData)
    {
        $user = $this->db->query("SELECT * FROM users WHERE email = :email", ["email" => $formData["email"]])->find();

        $passwordMatch = password_verify($formData["password"], $user["password"] ?? "");

        if (!$user || !$passwordMatch) throw new ValidationException(["password" => ["Invalid email or password"]]);

        session_regenerate_id(); // Changes the id of the cookie every time a user logs in (for added security)

        $_SESSION["user"] = $user["id"]; // Other values can be updated, but the id stays the same, so we use this to identify a logged in user.
    }

    public function logout()
    {
        unset($_SESSION["user"]);
        session_regenerate_id();
    }
}
