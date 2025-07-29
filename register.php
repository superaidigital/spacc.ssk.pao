<?php
require_once "db_connect.php";

$name = $email = $password = "";
$name_err = $email_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "กรุณากรอกชื่อ-สกุล";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "กรุณากรอกอีเมล";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "อีเมลนี้มีผู้ใช้งานแล้ว";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "มีบางอย่างผิดพลาด กรุณาลองใหม่อีกครั้ง";
            }
            $stmt->close();
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "กรุณากรอกรหัสผ่าน";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    } else {
        $password = trim($_POST["password"]);
    }

    // Check input errors before inserting in database
    if (empty($name_err) && empty($email_err) && empty($password_err)) {
        // By default, new users will have the 'User' role.
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'User')";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $param_name, $param_email, $param_password);
            $param_name = $name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            if ($stmt->execute()) {
                header("location: login.php?registration=success");
            } else {
                echo "มีบางอย่างผิดพลาด กรุณาลองใหม่อีกครั้ง";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">สร้างบัญชีใหม่</h1>
        </div>
        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">ชื่อ-สกุล</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full px-3 py-2 border <?= (!empty($name_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg" value="<?= htmlspecialchars($name); ?>">
                <span class="text-red-500 text-sm"><?= htmlspecialchars($name_err); ?></span>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full px-3 py-2 border <?= (!empty($email_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg" value="<?= htmlspecialchars($email); ?>">
                <span class="text-red-500 text-sm"><?= htmlspecialchars($email_err); ?></span>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full px-3 py-2 border <?= (!empty($password_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg">
                <span class="text-red-500 text-sm"><?= htmlspecialchars($password_err); ?></span>
            </div>
            <div>
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 px-4 rounded-lg hover:bg-blue-700">สมัครสมาชิก</button>
            </div>
        </form>
        <p class="text-center text-sm text-gray-500 mt-6">
            มีบัญชีอยู่แล้ว? <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">เข้าสู่ระบบที่นี่</a>
        </p>
    </div>
</body>
</html>