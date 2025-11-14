<?php 

require_once '../../includes/config/database.php';
$sites = [];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Fetch site options
    $stmt = $conn->prepare("SELECT id, site_name FROM sites ORDER BY id ASC");
    $stmt->execute();
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching sites: " . $e->getMessage());
}
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Name</th><th>Actions</th></tr></thead><tbody>';
foreach ( $sites as $row) {
    echo "<tr><td>{$row['site_name']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['site_name']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}
echo '</tbody></table></div>';