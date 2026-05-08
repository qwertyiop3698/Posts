<?php
include "./db.php";
include "./auth.php";

json_headers();

try {
    ensure_users_table($conn);

    $data = json_decode(file_get_contents("php://input"), true);
    $username = trim($data["username"] ?? "");
    $password = $data["password"] ?? "";

    if ($username === "" || $password === "") {
        echo json_encode(["success" => false, "message" => "아이디와 비밀번호를 입력해주세요."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    if (!$stmt) {
        throw new Exception("요청 준비 실패: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $savedUsername, $passwordHash);

    if (!$stmt->fetch()) {
        $stmt->close();
        echo json_encode(["success" => false, "message" => "아이디 또는 비밀번호가 올바르지 않습니다."]);
        exit;
    }

    $stmt->close();

    if (!password_verify($password, $passwordHash)) {
        echo json_encode(["success" => false, "message" => "아이디 또는 비밀번호가 올바르지 않습니다."]);
        exit;
    }

    $token = make_token();
    $update = $conn->prepare("UPDATE users SET api_token = ? WHERE id = ?");
    $update->bind_param("si", $token, $id);
    $update->execute();
    $update->close();

    echo json_encode([
        "success" => true,
        "message" => "로그인되었습니다.",
        "user" => [
            "id" => (int) $id,
            "username" => $savedUsername,
            "token" => $token,
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
