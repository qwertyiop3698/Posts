<?php
include "./db.php";
include "./auth.php";

json_headers();

try {
    ensure_owner_columns($conn);
    $user = require_user($conn);

    $data = json_decode(file_get_contents("php://input"), true);
    $postId = (int) ($data["post_id"] ?? 0);
    $content = trim($data["content"] ?? "");

    if ($postId <= 0 || $content === "") {
        echo json_encode(["success" => false, "message" => "게시물과 댓글 내용이 필요합니다."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("요청 준비 실패: " . $conn->error);
    }

    $stmt->bind_param("iis", $postId, $user["id"], $content);
    $stmt->execute();
    $commentId = $conn->insert_id;
    $stmt->close();

    echo json_encode([
        "success" => true,
        "message" => "댓글이 등록되었습니다.",
        "id" => (int) $commentId,
        "user_id" => $user["id"],
        "username" => $user["username"],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
