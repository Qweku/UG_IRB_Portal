<?php
require_once '../../includes/functions/helpers.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $department = executeAssocQuery("SELECT id, department_name, address_line_1, address_line_2, site, department_id, city, state, zip FROM department_groups WHERE id = ?", [$id]);
    if ($department) {
        header('Content-Type: application/json');
        echo json_encode($department[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all departments
$departments = executeAssocQuery("SELECT id, department_name, address_line_1, address_line_2, site, department_id, city, state, zip FROM department_groups ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
           
            <th>Description</th>
            <th>Address Line 1</th>
            <th>Address Line 2</th>
            <th>Site</th>
            <th>Department ID</th>
            <th>City</th>
            <th>State</th>
            <th>Zip</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>';
foreach ( $departments as $row) {
    echo "<tr>";
    echo "
    <td>{$row['department_name']}</td>
    <td>{$row['address_line_1']}</td>
    <td>{$row['address_line_2']}</td>
    <td>{$row['site']}</td>
    <td>{$row['department_id']}</td>
    <td>{$row['city']}</td>
    <td>{$row['state']}</td>
    <td>{$row['zip']}</td>
    <td>
        <button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['department_name']}\")'>
            <i class='fas fa-edit'></i>
        </button>
        <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'>
            <i class='fas fa-trash'></i>
        </button>
    </td>";
    echo "</tr>";
}
echo '</tbody></table></div>';