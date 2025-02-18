<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV["DB_HOST"];
$username = $_ENV["DB_USER"];
$password = $_ENV["DB_PASSWORD"];
$dbname = $_ENV["DB_NAME"];


$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT users.id AS user_id, users.name, users.email, 
               posts.id AS post_id, posts.title, posts.content 
        FROM users 
        INNER JOIN posts ON users.id = posts.user_id 
        ORDER BY users.id ASC, posts.id DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Users & Posts</h2>";
    
    $current_user = null;
    
    while ($row = $result->fetch_assoc()) {
        if ($current_user !== $row["user_id"]) {
            if ($current_user !== null) {
                echo "<hr>"; 
            }
            echo "<h3>User: " . htmlspecialchars($row["name"]) . " (" . htmlspecialchars($row["email"]) . ")</h3>";
            $current_user = $row["user_id"];
        }

        echo "<p><strong>Post:</strong> " . htmlspecialchars($row["title"]) . "</p>";
        echo "<p>" . nl2br(htmlspecialchars($row["content"])) . "</p>";
    }
} else {
    echo "No data was found.";
}

    
$conn->close();
