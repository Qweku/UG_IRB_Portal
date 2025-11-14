<?php 
require_once '../../includes/config/database.php';

$vulnerables = [];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Fetch vulnerable options
    $stmt = $conn->prepare("SELECT id, population_type FROM vulnerable_populations ORDER BY id ASC");
    $stmt->execute();
    $vulnerables = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching vulnerables: " . $e->getMessage());
}
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Name</th><th>Actions</th></tr></thead><tbody>';
foreach ( $vulnerables as $row) {
    echo "<tr><td>{$row['population_type']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['population_type']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}   
echo '</tbody></table></div>';