<?php
include "./db.php";
include "./auth.php";

json_headers();

try {
    ensure_owner_columns($conn);
    $user = require_user($conn);
    $transactionStarted = false;

    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int) ($data["id"] ?? 0);

    if ($id <= 0) {
        echo json_encode(["success" => false, "message" => "게시물 ID가 필요합니다."]);
        exit;
    }

    $owner = null;
    $check = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
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
        echo json_encode(["success" => false, "message" => "작성자만 게시물을 삭제할 수 있습니다."]);
        exit;
    }

    $conn->begin_transaction();
    $transactionStarted = true;

    $deleteComments = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
    $deleteComments->bind_param("i", $id);
    $deleteComments->execute();
    $deleteComments->close();

    $deletePost = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $deletePost->bind_param("ii", $id, $user["id"]);
    $deletePost->execute();
    $deletePost->close();

    $conn->commit();

    echo json_encode(["success" => true, "message" => "게시물과 댓글이 삭제되었습니다."]);
} catch (Exception $e) {
    if (isset($transactionStarted) && $transactionStarted) {
        $conn->rollback();
    }

    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
