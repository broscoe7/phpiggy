<?php

declare(strict_types=1);

namespace Framework;

use PDO, PDOException, PDOStatement;

class Database
{
    private PDO $connection; // Without this the connection will be lost after the construct function ends. Marking it private forces us to define methods in this class if we want to access the built-in PDO methods externally.
    private PDOStatement $stmt; // This allows us to work with prepared statements in the query function.

    public function __construct(string $driver, array $config, string $username, string $password)
    {
        $config = http_build_query(data: $config, arg_separator: ";"); // The DSN format requires a semicolon separator but the http_build_query uses an amperstand by default
        $dsn = "{$driver}:{$config}";
        // An uncaught error could leak important information about the database in the command line. This prevents that kind of leak.
        try {
            $this->connection = new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            die("Unable to connect to database");
        }
    }

    public function query(string $query, array $params = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]): Database
    {
        $this->stmt = $this->connection->prepare($query);
        $this->stmt->execute($params);
        return $this;
    }

    public function count()
    {
        return $this->stmt->fetchColumn(); // Fetchcolumn() returns a single column from a results array (default index is zero, the first row in the results array, which in this case is the only value we need)
    }

    public function find()
    {
        return $this->stmt->fetch();
    }

    public function id()
    {
        return $this->connection->lastInsertId(); // Returns the id of the last inserted row.
    }
}
