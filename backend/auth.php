<?php
function json_headers() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Content-Type: application/json; charset=UTF-8");

    if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
        http_response_code(204);
        exit;
    }
}

function column_exists($conn, $table, $column) {
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return (int) $count > 0;
}

function ensure_users_table($conn) {
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            api_token VARCHAR(128) NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";

    if (!$conn->query($sql)) {
        throw new Exception("사용자 테이블 오류: " . $conn->error);
    }

    if (!column_exists($conn, "users", "api_token")) {
        if (!$conn->query("ALTER TABLE users ADD COLUMN api_token VARCHAR(128) NULL UNIQUE AFTER password_hash")) {
            throw new Exception("사용자 토큰 컬럼 오류: " . $conn->error);
        }
    }
}

function ensure_owner_columns($conn) {
    ensure_users_table($conn);

    if (!column_exists($conn, "posts", "user_id")) {
        if (!$conn->query("ALTER TABLE posts ADD COLUMN user_id INT NULL AFTER id")) {
            throw new Exception("게시물 작성자 컬럼 오류: " . $conn->error);
        }
    }

    if (!column_exists($conn, "comments", "user_id")) {
        if (!$conn->query("ALTER TABLE comments ADD COLUMN user_id INT NULL AFTER post_id")) {
            throw new Exception("댓글 작성자 컬럼 오류: " . $conn->error);
        }
    }
}

function make_token() {
    if (function_exists("random_bytes")) {
        return bin2hex(random_bytes(32));
    }

    return sha1(uniqid("", true) . mt_rand());
}

function get_bearer_token() {
    $header = $_SERVER["HTTP_AUTHORIZATION"] ?? $_SERVER["REDIRECT_HTTP_AUTHORIZATION"] ?? "";

    if ($header === "" && function_exists("apache_request_headers")) {
        $headers = apache_request_headers();
        $header = $headers["Authorization"] ?? $headers["authorization"] ?? "";
    }

    if (preg_match('/Bearer\s+(.+)/', $header, $matches)) {
        return trim($matches[1]);
    }

    if (isset($_POST["token"])) {
        return trim($_POST["token"]);
    }

    $raw = file_get_contents("php://input");
    if ($raw !== "") {
        $data = json_decode($raw, true);
        if (isset($data["token"])) {
            return trim($data["token"]);
        }
    }

    return "";
}

function require_user($conn) {
    ensure_users_table($conn);

    $token = get_bearer_token();
    if ($token === "") {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "로그인이 필요합니다."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, username FROM users WHERE api_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($id, $username);

    if (!$stmt->fetch()) {
        $stmt->close();
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "로그인이 만료되었습니다. 다시 로그인해주세요."]);
        exit;
    }

    $stmt->close();

    return [
        "id" => (int) $id,
        "username" => $username,
    ];
}
?>
