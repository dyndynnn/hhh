<?php
include 'admin_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data'];
    list($id, $name) = explode(",", $data);

    $stmt = $conn->prepare("UPDATE students SET timestamp = NOW() WHERE student_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "Attendance recorded for $name";
}
?>
