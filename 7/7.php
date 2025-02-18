<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV["DB_HOST"];
$user = $_ENV["DB_USER"];
$password = $_ENV["DB_PASSWORD"];
$database = $_ENV["DB_NAME"];

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($searchQuery !== '') {
    $sql = "SELECT id, title, content, 
                   MATCH(title, content) AGAINST (?) AS relevance 
            FROM posts 
            WHERE MATCH(title, content) AGAINST (?) 
            ORDER BY relevance DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $searchQuery, $searchQuery);
    $stmt->execute();
    
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<h2>" . htmlspecialchars($row['title']) . "</h2>";
        echo "<p>" . htmlspecialchars($row['content']) . "</p><hr>";
    }
    
    $stmt->close();
}

$conn->close();
