<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

// Get current active advertisements
$query = "SELECT id, title, banner_url 
          FROM advertisements 
          WHERE status = 'active' 
          AND start_date <= NOW() 
          AND end_date >= NOW() 
          ORDER BY RAND() 
          LIMIT 3";

$result = $conn->query($query);
$ads = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ads[] = [
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'banner_url' => htmlspecialchars($row['banner_url'])
        ];
    }
}

echo json_encode($ads);
$conn->close();
