<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV["DB_HOST"];
$user = $_ENV["DB_USER"];
$password = $_ENV["DB_PASSWORD"];
$dbname = $_ENV["DB_NAME"];


$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conn ERR: " . $conn->connect_error);
}

$posts_per_page = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;


$offset = ($page - 1) * $posts_per_page;


$total_posts_query = "SELECT COUNT(*) AS total FROM posts";
$result = $conn->query($total_posts_query);
$total_posts = $result->fetch_assoc()['total'];


$total_pages = ceil($total_posts / $posts_per_page);


$sql = "SELECT * FROM posts ORDER BY created_at DESC LIMIT $posts_per_page OFFSET $offset";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="UTF-8">
    <title>Pagination</title>
</head>
<body>
    <h1>Posts</h1>
    
    <?php while ($row = $result->fetch_assoc()): ?>
        <div>
            <h2><?= htmlspecialchars($row['title']) ?></h2>
            <p><?= htmlspecialchars($row['content']) ?></p>
            <small>Published: <?= $row['created_at'] ?></small>
        </div>
        <hr>
    <?php endwhile; ?>


    <div>
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">Previous</a>
        <?php endif; ?>

        <span>Էջ <?= $page ?> / <?= $total_pages ?></span>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>">Next</a>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
$conn->close();
?>
