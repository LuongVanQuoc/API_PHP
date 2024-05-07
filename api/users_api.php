<?php
include "../connect.php";

// Kiểm tra xem request có phải là GET không
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Kiểm tra đường dẫn yêu cầu
    header('Content-Type: application/json');
    if ((isset($_GET['id']))) {
        $username = $_GET['id'];
        getUserById($conn, $username);
    } elseif ((isset($_GET['username'])) && (isset($_GET['password']))) {
        $username = $_GET['username'];
        $password = $_GET['password'];
        login($conn, $username, $password);
    } elseif ((isset($_GET['keyword']))) {
        $keyword = $_GET['keyword'];

        searchByKeyword($conn, $keyword);
    } elseif ((isset($_GET['list-user']))) {
        getListUserAndRole($conn);
    } else {
        getAllUsers($conn);
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/x-www-form-urlencoded');
    if ((isset($_GET['create-user']))) {

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON);

        $username = $input->username;
        $password = $input->password;
        $email = $input->email;
        $phone = $input->phone;

        register($conn, $username, $password, $email, $phone);
    } elseif ((isset($_GET['create-staff']))) {
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON);

        $username = $input->username;
        $name = $input->name;
        $email = $input->email;
        $phone = $input->phone;

        if (empty($username) || empty($name) || empty($phone) || empty($email)) {
            $response = array('message' => 'Trống thông tin.');
            echo json_encode($response);
        } else {
            createAccount($conn, $username, $name, $email, $phone, 2);
        }
    } elseif ((isset($_GET['create-shipper']))) {
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON);

        $username = $input->username;
        $name = $input->name;
        $email = $input->email;
        $phone = $input->phone;

        if (empty($username) || empty($name) || empty($phone) || empty($email)) {
            $response = array('message' => 'Trống thông tin.');
            echo json_encode($response);
        } else {
            createAccount($conn, $username, $name, $email, $phone, 3);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    if ((isset($_GET['username']))) {
        $username = $_GET['username'];

        restoreUser($conn, $username);
    }
    if ((isset($_GET['update-user']))) {
        $username = $_GET['update-user'];

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON);

        $name = $input->name;
        $phone = $input->phone;
        $email = $input->email;

        updateInfo($conn, $username, $name, $phone, $email);
    }
    if ((isset($_GET['id'])) && (isset($_GET['currentPass'])) && (isset($_GET['newPass']))) {
        $username = $_GET['id'];
        $curPass = $_GET['currentPass'];
        $newPass = $_GET['newPass'];

        chanePass($conn, $username, $curPass, $newPass);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    if ((isset($_GET['username']))) {
        $username = $_GET['username'];

        shutdownUser($conn, $username);
    }
}

function getAllUsers($conn)
{
    try {
        $conn->exec("SET NAMES 'utf8'");
        $result = $conn->query("SELECT * FROM users WHERE role = 1 OR role = 2 OR role = 3 ORDER BY active DESC, role ASC")->fetchAll(PDO::FETCH_ASSOC);
        // Trả về kết quả dưới dạng JSON
        header('Content-Type: application/json');
        echo json_encode($result);
    } catch (PDOException $e) {
        // Xử lý lỗi nếu có
        echo json_encode(['error' => 'Lỗi truy vấn: ' . $e->getMessage()]);
    }
}

function getListUserAndRole($conn)
{
    try {
        $conn->exec("SET NAMES 'utf8'");
        $result = $conn->query("SELECT * FROM users u INNER JOIN roles r ON u.role = r.id
                                WHERE u.role = 1 OR u.role = 2 OR u.role = 3 
                                ORDER BY active DESC, role ASC")->fetchAll(PDO::FETCH_ASSOC);
        // Trả về kết quả dưới dạng JSON
        header('Content-Type: application/json');
        echo json_encode($result);
    } catch (PDOException $e) {
        // Xử lý lỗi nếu có
        echo json_encode(['error' => 'Lỗi truy vấn: ' . $e->getMessage()]);
    }
}

function getUserById($conn, $id)
{
    try {
        $conn->exec("SET NAMES 'utf8'");
        $username = $conn->quote($id);
        $result = $conn->query("SELECT * FROM users WHERE username = " . $username)->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            // Nếu có kết quả, trả về dữ liệu JSON và thông báo thành công
            header('Content-Type: application/json');
            echo json_encode($result);
            //echo "Thông tin tài khoản";
        } else {
            // Nếu không có kết quả, trả về thông báo lỗi
            header('Content-Type: application/json');
            echo json_encode(array("message" => "Không tìm thấy thông tin tài khoản."));
        }
    } catch (PDOException $e) {
        // Xử lý lỗi nếu có
        echo json_encode(['error' => 'Lỗi truy vấn: ' . $e->getMessage()]);
    }
}

function createAccount($conn, $username, $name, $email, $phone, $role)
{
    $username = $conn->quote($username);
    $name = $conn->quote($name);
    $phone = $conn->quote($phone);
    $email = $conn->quote($email);
    $role = $conn->quote($role);

    $conn->query("INSERT INTO users (username, name,  pass, phone, email, image, role, active) 
                    VALUES ($username, $name, 123456, $phone, $email, 'default_image.jpg', $role, 1)");
    $response = array('message' => 'Tạo tài khoản thành công!');
    echo json_encode($response);
}

function updateInfo($conn, $id, $infoName, $infoPhone, $infoEmail)
{
    $username = $conn->quote($id);
    $name = $conn->quote($infoName);
    $phone = $conn->quote($infoPhone);
    $email = $conn->quote($infoEmail);

    $sql = ("UPDATE users SET name = $name, phone = $phone, email = $email WHERE username = $username");

    $conn->query($sql);

    $response = array('message' => 'Cập nhật thành công.');
    echo json_encode($response);
}

function chanePass($conn, $id, $currentPass, $newPass)
{
    $currentPass = strval($currentPass);

    $stmt = $conn->prepare("SELECT pass FROM users WHERE username = :username");
    $stmt->execute(array(':username' => $id));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($currentPass === $result['pass']) {
        $hashedNewPassword = password_hash($newPass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET pass = :newPassword WHERE username = :username");
        $stmt->execute(array(':newPassword' => $hashedNewPassword, ':username' => $id));

        echo "Cập nhật mật khẩu thành công!";
    } else {
        echo "Mật khẩu hiện tại không đúng!";
    }
}

function login($conn, $id, $password)
{
    $username = $conn->quote($id);
    $pass = $conn->quote($password);
    $result = $conn->query("SELECT * FROM users WHERE username = $username AND pass = $pass")->fetch(PDO::FETCH_ASSOC);
    // Kiểm tra kết quả trả về
    if ($result) {
        if ($result['active'] === 0) {
            // Nếu active false thì báo lỗi
            header('Content-Type: application/json');
            http_response_code(400); // Bad request
            echo json_encode(array("message" => "Tài khoản đã bị khóa!"));
        } else {
            // Nếu có kết quả, trả về dữ liệu JSON và thông báo thành công
            header('Content-Type: application/json');
            echo json_encode(array("message" => "Đăng nhập thành công!"));
        }
    } else {
        // Nếu không có kết quả, trả về thông báo lỗi
        header('Content-Type: application/json');
        echo json_encode(array("message" => "Tên người dùng hoặc mật khẩu không chính xác."));
    }
}

function register($conn, $regName, $regPass, $regEmail, $regPhone)
{
    $username = $conn->quote($regName);
    $password = $conn->quote($regPass);
    $phone = $conn->quote($regPhone);
    $email = $conn->quote($regEmail);

    if (empty($username) || empty($password) || empty($phone) || empty($email)) {
        $response = array('message' => 'Thông tin trống!');
        echo json_encode($response);
    } else {
        $result = $conn->query("SELECT * FROM users WHERE username = $username")->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $response = array('message' => 'Tên đăng nhập đã tồn tại!');
            http_response_code(400); // Bad request
            echo json_encode($response);
        } else {
            $sql = ("INSERT INTO users (username, pass, phone, email, image, role, active) 
                    VALUES ($username, $password, $phone, $email, 'default_image.jpg', 1, 1)");
            // Kiểm tra kết quả trả về
            $conn->query($sql);

            $response = array('message' => 'Đăng ký thành công.');
            echo json_encode($response);
        }
    }
}

function shutdownUser($conn, $id)
{
    $username = $conn->quote($id);

    $result = $conn->query("SELECT * FROM users WHERE username = $username")->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $sql = ("UPDATE users SET active = 0 WHERE username = $username");

        $conn->query($sql);

        $response = array('message' => 'Ngưng hoạt động thành công!');
        echo json_encode($response);
    } else {
        echo "Không tìm thấy tài khoản";
    }
}

function restoreUser($conn, $id)
{
    $username = $conn->quote($id);

    $result = $conn->query("SELECT * FROM users WHERE username = $username")->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $sql = ("UPDATE users SET active = 1 WHERE username = $username");

        $conn->query($sql);

        $response = array('message' => 'Khôi phục hoạt động thành công.');
        echo json_encode($response);
    } else {
        echo "Không tìm thấy tài khoản";
    }
}

function searchByKeyword($conn, $keyword)
{
    $result = $conn->query("SELECT * FROM users WHERE username LIKE '%$keyword%' OR name LIKE '%$keyword%' OR email LIKE '%keyword%'")->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode($result);
    } else {
        $response = array('message' => 'Không tìm thấy tài khoản chứa từ khóa.');
        echo json_encode($response);
    }
}
