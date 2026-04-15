<?php
$conn = new mysqli("localhost", "root", "", "devsprint");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$res = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'teams' AND COLUMN_NAME = 'hackathon_id' AND TABLE_SCHEMA = 'devsprint' AND REFERENCED_TABLE_NAME IS NOT NULL");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $fk = $row['CONSTRAINT_NAME'];
        if (!$conn->query("ALTER TABLE teams DROP FOREIGN KEY `$fk`")) echo "Failed to drop FK $fk: " . $conn->error . "\n";
    }
}

if (!$conn->query("ALTER TABLE teams DROP COLUMN hackathon_id")) echo "Failed to drop column: " . $conn->error . "\n";
else echo "Dropped column successfully\n";
?>
