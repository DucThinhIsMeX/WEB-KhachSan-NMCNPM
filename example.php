<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

// Lấy danh sách phòng
$stmt = $db->query("SELECT r.*, rt.name as type_name, rt.price 
                    FROM rooms r 
                    JOIN room_types rt ON r.room_type_id = rt.id");
$rooms = $stmt->fetchAll();

foreach ($rooms as $room) {
    echo "Phòng " . $room['room_number'] . " - " . $room['type_name'] . " - " . number_format($room['price']) . "đ<br>";
}
?>
