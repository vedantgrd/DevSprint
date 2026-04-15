<?php
include '../includes/db_connect.php';

$q = $_REQUEST["q"];

$hint = "";

if ($q !== "") {
    $q = strtolower($q);
    $len = strlen($q);
    $sql = "SELECT name FROM products WHERE name LIKE ?";
    if ($stmt = $conn->prepare($sql)) {
        $param = "%" . $q . "%";
        $stmt->bind_param("s", $param);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $productName = htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8');
            $hint .= "<div class='result-item' onclick='selectProduct(\"" . $productName . "\")'>" . $productName . "</div>";
        }
        $stmt->close();
    }
}

echo $hint === "" ? "no suggestion" : $hint;

$conn->close();
?>
