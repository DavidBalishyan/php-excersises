<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV["DB_HOST"];
$user = $_ENV["DB_USER"];
$password = $_ENV["DB_PASSWORD"];
$dbname = $_ENV["DB_NAME"];


$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conn ERR: " . $conn->connect_error);
}

// SQL հարց՝ ջնջելու 30 օրից ավելի ոչ ակտիվ օգտատերերին
$sql = "DELETE FROM users WHERE last_active < NOW() - INTERVAL 30 DAY";

if ($conn->query($sql) === TRUE) {
    echo "DELETED!\n";
} else {
    echo "Conn ERR " . $conn->error . "\n";
}

// Փակել միացումը
$conn->close();
