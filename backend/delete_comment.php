<?php
include "./db.php";
include "./auth.php";

json_headers();

try {
    ensure_owner_columns($conn);
    $user = require_user($conn);

    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int) ($data["id"] ?? 0);

    if ($id <= 0) {
        echo json_encode(["success" => false, "message" => "댓글 ID가 필요합니다."]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        throw new Exception("요청 준비 실패: " . $conn->error);
    }

    $stmt->bind_param("ii", $id, $user["id"]);
    $stmt->execute();

    if ($stmt->affected_rows < 1) {
        $stmt->close();
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "작성자만 댓글을 삭제할 수 있습니다."]);
        exit;
    }

    $stmt->close();

    echo json_encode(["success" => true, "message" => "댓글이 삭제되었습니다."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
