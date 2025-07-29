<?php
define('BASE_PATH', __DIR__); // เพิ่มบรรทัดนี้ไว้บนสุด

// ตั้งค่า error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL);

// เริ่ม session อย่างปลอดภัย
if (session_status() === PHP_SESSION_NONE) {
    $session_opts = [
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict'
    ];
    session_start($session_opts);
}

// ตรวจสอบการล็อกอิน
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "db_connect.php";
require_once "includes/functions.php";

try {
    // ตรวจสอบการเชื่อมต่อฐานข้อมูล
    if (!$conn || $conn->connect_error) {
        throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
    }

    // ดึงการตั้งค่าระบบ
    $settings_sql = "SELECT setting_key, setting_value FROM settings";
    $settings_result = $conn->prepare($settings_sql);
    if (!$settings_result || !$settings_result->execute()) {
        throw new Exception("ไม่สามารถดึงการตั้งค่าระบบได้");
    }

    $settings = [];
    $result = $settings_result->get_result();
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    // ตรวจสอบสถานะระบบ
    if (($settings['system_status'] ?? '1') != '1' && $_SESSION['role'] !== 'Admin') {
        $page = 'system_disabled';
    } else {
        // ดึงและตรวจสอบสิทธิ์การใช้งาน (FIX: Changed $_SESSION['id'] to $_SESSION['user_id'])
        $permissions = getUserPermissions($conn, $_SESSION['user_id'], $_SESSION['role']);
        if (!$permissions) {
            throw new Exception("ไม่สามารถดึงข้อมูลสิทธิ์การใช้งานได้");
        }
        $_SESSION['permissions'] = $permissions;

        // ตรวจสอบและ sanitize หน้าที่เรียก
        $page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_SANITIZE_STRING) : 'dashboard';
        if (!in_array($page, $permissions['allowed_pages'])) {
            $page = 'dashboard';
        }

        // ตรวจสอบไฟล์หน้าที่เรียก
        if (!file_exists("pages/{$page}.php")) {
            throw new Exception("ไม่พบหน้าที่ต้องการ");
        }
    }

    // แสดงผลหน้าเว็บ
    include 'partials/header.php';

    if ($page === 'system_disabled') {
        require_once 'pages/system_disabled.php';
    } else {
        require_once "pages/{$page}.php";
    }

    include 'partials/footer.php';

} catch (Exception $e) {
    error_log("Error in index.php: " . $e->getMessage());
    include 'partials/header.php';
    require_once 'pages/error.php';
    include 'partials/footer.php';
}
?>
