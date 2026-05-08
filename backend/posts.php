<?php
include "./db.php";
include "./auth.php";

json_headers();

try {
    ensure_owner_columns($conn);

    $sql = "
        SELECT posts.*, users.username
        FROM posts
        LEFT JOIN users ON posts.user_id = users.id
        ORDER BY posts.id DESC
    ";
    $result = mysqli_query($conn, $sql);
    $data = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
