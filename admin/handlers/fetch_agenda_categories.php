<?php
require_once '../../includes/functions/helpers.php';

$agendaCategories = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $categoryName = executeAssocQuery("SELECT id, category_name, agenda_class_code, agenda_print FROM agenda_category WHERE id = ?", [$id]);
    if ($categoryName) {
        header('Content-Type: application/json');
        echo json_encode($categoryName[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all agenda categories
$agendaCategories = executeAssocQuery("SELECT id, category_name, agenda_class_code, agenda_print FROM agenda_category ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
            
            <th>Agenda Category</th>
            <th>Agenda Class Code</th>
            <th>Print on Agenda & Minutes As</th>
            <th>Actions</th>
        </tr>
        </thead><tbody>';
foreach ( $agendaCategories as $row) {
    echo "<tr>
    
    <td>{$row['category_name']}</td>
    <td>{$row['agenda_class_code']}</td>
    <td>{$row['agenda_print']}</td>
    <td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['category_name']}\")'><i class='fas fa-edit'></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button>
    </td>
    </tr>";
}
echo '</tbody></table></div>';