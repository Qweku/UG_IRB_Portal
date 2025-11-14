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
        $stmt = $conn->prepare("SELECT id, expedite_cite, expedite_description FROM expedited_codes WHERE id = ?");
        $stmt->execute([$id]);
        $expedite = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($expedite) {
            header('Content-Type: application/json');
            echo json_encode($expedite);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
        exit;
    }

    // Fetch exempt options
    $stmt = $conn->prepare("SELECT id, expedite_cite, expedite_description FROM expedited_codes ORDER BY id ASC");
    $stmt->execute();
    $expedites = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching expedites: " . $e->getMessage());
}
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Cite</th><th>Description</th><th>Actions</th></tr></thead><tbody>';
foreach ( $expedites as $row) {
    echo "<tr><td>{$row['expedite_cite']}</td><td>{$row['expedite_description']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['expedite_cite']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}   
echo '</tbody></table></div>';