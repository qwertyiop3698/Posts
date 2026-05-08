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

    if (strlen($username) < 3 || strlen($username) > 50) {
        echo json_encode(["success" => false, "message" => "아이디는 3자 이상 50자 이하로 입력해주세요."]);
        exit;
    }

    if (strlen($password) < 4) {
        echo json_encode(["success" => false, "message" => "비밀번호는 4자 이상 입력해주세요."]);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $token = make_token();
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, api_token) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("요청 준비 실패: " . $conn->error);
    }

    $stmt->bind_param("sss", $username, $passwordHash, $token);

    if (!$stmt->execute()) {
        if ($conn->errno === 1062) {
            echo json_encode(["success" => false, "message" => "이미 사용 중인 아이디입니다."]);
            exit;
        }

        throw new Exception("회원가입 실패: " . $conn->error);
    }

    $userId = $conn->insert_id;
    $stmt->close();

    echo json_encode([
        "success" => true,
        "message" => "회원가입이 완료되었습니다.",
        "user" => [
            "id" => (int) $userId,
            "username" => $username,
            "token" => $token,
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
