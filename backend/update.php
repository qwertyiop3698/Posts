<?php
include "./db.php";
include "./auth.php";

json_headers();

try {
    ensure_owner_columns($conn);
    $user = require_user($conn);

    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int) ($data["id"] ?? 0);
    $title = trim($data["title"] ?? "");
    $content = trim($data["content"] ?? "");

    if ($id <= 0 || $title === "" || $content === "") {
        echo json_encode(["success" => false, "message" => "게시물, 제목, 내용이 필요합니다."]);
        exit;
    }

    $owner = null;
    $check = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    if (!$check) {
        throw new Exception("요청 준비 실패: " . $conn->error);
    }

    $check->bind_param("i", $id);
    $check->execute();
    $check->bind_result($owner);

    if (!$check->fetch()) {
        $check->close();
        echo json_encode(["success" => false, "message" => "게시물을 찾을 수 없습니다."]);
        exit;
    }

    $check->close();

    if ((int) $owner !== $user["id"]) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "작성자만 게시물을 수정할 수 있습니다."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        throw new Exception("요청 준비 실패: " . $conn->error);
    }

    $stmt->bind_param("ssii", $title, $content, $id, $user["id"]);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => true, "message" => "게시물이 수정되었습니다."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
