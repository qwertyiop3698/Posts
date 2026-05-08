<?php
include "./db.php";
include "./auth.php";

json_headers();

try {
    ensure_owner_columns($conn);
    $user = require_user($conn);

    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $imageName = "";

    if ($title === "" || $content === "") {
        echo json_encode(["success" => false, "message" => "제목과 내용이 필요합니다."]);
        exit;
    }

    if (isset($_FILES["image"]) && isset($_FILES["image"]["name"]) && is_array($_FILES["image"]["name"])) {
        for ($i = 0; $i < count($_FILES["image"]["name"]); $i++) {
            if ($_FILES["image"]["error"][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $originalName = basename($_FILES["image"]["name"][$i]);
            $safeName = time() . "_" . substr(make_token(), 0, 8) . "_" . preg_replace("/[^A-Za-z0-9._-]/", "_", $originalName);
            $targetFile = __DIR__ . "/uploads/" . $safeName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"][$i], $targetFile)) {
                $imageName = $imageName === "" ? $safeName : $imageName . "," . $safeName;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, image_path) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("요청 준비 실패: " . $conn->error);
    }

    $stmt->bind_param("isss", $user["id"], $title, $content, $imageName);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => true, "message" => "저장 완료."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
