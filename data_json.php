<?php
header('Content-Type: application/json');
include 'koneksi.php';

$data = [];
$q = mysqli_query($conn, "SELECT * FROM books ORDER BY id ASC");
while ($row = mysqli_fetch_assoc($q)) {
    $data[] = $row;
}
echo json_encode($data, JSON_PRETTY_PRINT);
?>