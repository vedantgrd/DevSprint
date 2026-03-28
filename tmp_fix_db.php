<?php
$conn = new mysqli("localhost", "root", "", "devsprint");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$res = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'teams' AND COLUMN_NAME = 'hackathon_id' AND TABLE_SCHEMA = 'devsprint'");
if ($res && $res->num_rows > 0) {
    if ($row = $res->fetch_assoc()) {
        $fk = $row['CONSTRAINT_NAME'];
        $conn->query("ALTER TABLE teams DROP FOREIGN KEY $fk");
    }
}
$conn->query("ALTER TABLE teams DROP COLUMN hackathon_id");
echo "Success\n";
?>
