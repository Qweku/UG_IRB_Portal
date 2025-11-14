<?php
require_once '../../includes/config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT id, exempt_cite, exempt_description FROM exempt_codes WHERE id = ?");
        $stmt->execute([$id]);
        $exempt = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($exempt) {
            header('Content-Type: application/json');
            echo json_encode($exempt);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
        exit;
    }

    // Fetch exempt options
    $stmt = $conn->prepare("SELECT id, exempt_cite, exempt_description FROM exempt_codes ORDER BY id ASC");
    $stmt->execute();
    $exempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching exempts: " . $e->getMessage());
}
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Cite</th><th>Description</th><th>Actions</th></tr></thead><tbody>';
foreach ( $exempts as $row) {
    echo "<tr><td>{$row['exempt_cite']}</td><td>{$row['exempt_description']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['exempt_cite']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}   
echo '</tbody></table></div>';