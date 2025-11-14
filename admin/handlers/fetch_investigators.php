<?php 
require_once '../../includes/config/database.php';

$investigators = [];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Fetch benefit options
    $stmt = $conn->prepare("SELECT id, specialty_name FROM investigator ORDER BY id ASC");
    $stmt->execute();
    $investigators = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching investigators: " . $e->getMessage());
}
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
           
            <th>Name</th>
            <th>Actions</th>
        </tr>
        </thead><tbody>';
foreach ( $investigators as $row) {
    echo "<tr>
    
    <td>{$row['specialty_name']}</td>
    <td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['specialty_name']}\")'><i class='fas fa-edit'></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button>
    </td>
    </tr>";
}
echo '</tbody></table></div>';