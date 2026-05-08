<?php
    header("Content-Type: application/json; charset=UTF-8");
    include 'db.php';

    $data = json_decode(file_get_contents("php://input"), true);

    if ($data) {
        $id = $data['id'];

        $sql = "UPDATE posts SET views = views + 1 WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["message" => "조회수 +1"]);
        } else {
            echo json_encode(["message" => "에러 : " . $conn->error]);
        }
    };
?>