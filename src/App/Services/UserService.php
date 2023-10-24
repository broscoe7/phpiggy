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
        // Alternative: 
        // session_destroy();
        // This deletes all session data, but you might not want that if you are tracking users.

        // session_regenerate_id(); Changes id of cookie.

        // Instead of just changing the cookie id, this completely wipes out the old cookie and creates a new one. For more secure sites log out the user completely this way.
        $params = session_get_cookie_params(); // Allows us to enter in currently existing values
        setcookie(
            "PHPSESSID", // Cookie name
            "", // Value
            time() - 2000, // Expiration date: this sets the expiration date to "now," destroying the cookie
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
}
