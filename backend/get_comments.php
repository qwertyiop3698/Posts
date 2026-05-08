<?php
include "./db.php";
include "./auth.php";

json_headers();

try {
    ensure_owner_columns($conn);

    $postId = (int) ($_GET["post_id"] ?? 0);
    if ($postId <= 0) {
        echo json_encode([]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT comments.id, comments.post_id, comments.user_id, comments.content, comments.created_at, users.username
        FROM comments
        LEFT JOIN users ON comments.user_id = users.id
        WHERE comments.post_id = ?
        ORDER BY comments.id DESC
    ");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->bind_result($id, $savedPostId, $userId, $content, $createdAt, $username);
    $comments = array();

    while ($stmt->fetch()) {
        $comments[] = [
            "id" => $id,
            "post_id" => $savedPostId,
            "user_id" => $userId,
            "content" => $content,
            "created_at" => $createdAt,
            "username" => $username,
        ];
    }

    $stmt->close();
    echo json_encode($comments);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
