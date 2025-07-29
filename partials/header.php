<?php
// partials/header.php

// เริ่ม session หากยังไม่ได้เริ่ม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- การตั้งค่าเมนูและสิทธิ์การเข้าถึง (จาก php_nav_refactor) ---
$navItems = [
    'dashboard' => [
        'label' => 'หน้าหลัก',
        'icon' => 'home',
        'roles' => ['Admin', 'Coordinator', 'HealthStaff', 'User'], 
    ],
    'reports' => [ // <-- เพิ่มส่วนนี้เข้าไป
        'label' => 'รายงาน',
        'icon' => 'bar-chart-2',
        'roles' => ['Admin'], 
    ],
    'shelters' => [
        'label' => 'จัดการข้อมูลศูนย์',
        'icon' => 'building', // ไอคอนตรงกับเมนูหลัก
        'roles' => ['Admin', 'Coordinator', 'HealthStaff'],
    ], 
    'users' => [
        'label' => 'จัดการผู้ใช้งาน',
        'icon' => 'users',
        'roles' => ['Admin'],
    ],
    'settings' => [
        'label' => 'ตั้งค่าระบบ',
        'icon' => 'settings',
        'roles' => ['Admin'],
    ],
];

// --- การจัดการข้อมูลผู้ใช้และหน้าปัจจุบัน ---
$current_page = $_GET['page'] ?? 'dashboard';
$user_role = $_SESSION['role'] ?? 'Guest'; // กำหนดค่าเริ่มต้นเป็น Guest หากยังไม่ได้ login
$user_name = $_SESSION['name'] ?? 'ผู้ใช้ทั่วไป';

// แปลง Role ภาษาอังกฤษเป็นไทย (สามารถเพิ่มได้ตามต้องการ)
$role_display_map = [
    'Admin' => 'ผู้ดูแลระบบ',
    'Coordinator' => 'เจ้าหน้าที่ประสานศูนย์',
    'HealthStaff' => 'เจ้าหน้าที่สาธารณสุข',
    'User' => 'ผู้ใช้ทั่วไป'
];
$display_role = $role_display_map[$user_role] ?? 'N/A';


// --- การดึงข้อมูลการตั้งค่าจากฐานข้อมูล (คงไว้ตามเดิม) ---
require_once 'db_connect.php'; // ตรวจสอบว่า path ถูกต้อง
$menu_settings = [];
try {
    $settings_result = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'menu_%'");
    while($row = $settings_result->fetch_assoc()) {
        $menu_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // จัดการกรณีที่ตาราง settings ไม่มีหรือเชื่อมต่อ DB ไม่ได้
    // error_log($e->getMessage()); 
}


?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบประสานงานศูนย์ช่วยเหลือ</title>
    <!-- เพิ่ม Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        /* สไตล์สำหรับเมนูที่ถูกเลือก */
        .nav-link-active {
            color: #4f46e5; /* สีน้ำเงิน-ม่วง */
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Responsive Navigation Bar -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo / Brand -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="index.php" class="text-xl font-bold text-gray-800">ระบบประสานงานฯ</a>
                </div>
                
                <!-- Desktop Menu (สร้างแบบไดนามิก) -->
                <div class="hidden md:flex items-center space-x-1">
                    <?php foreach ($navItems as $pageKey => $item): ?>
                        <?php if (in_array($user_role, $item['roles'])): ?>
                            <a href="index.php?page=<?= htmlspecialchars($pageKey) ?>" 
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium <?= $current_page === $pageKey ? 'nav-link-active' : '' ?>">
                                <i data-lucide="<?= htmlspecialchars($item['icon']) ?>" class="w-4 h-4 inline-block mr-1"></i> 
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- User Info & Logout -->
                    <div class="flex items-center pl-4">
                        <span class="text-sm text-gray-600 mr-4">
                            สวัสดี, <strong><?= htmlspecialchars($user_name) ?></strong> (<?= htmlspecialchars($display_role) ?>)
                        </span>
                        <a href="logout.php" class="text-red-600 hover:text-red-700 px-3 py-2 rounded-md text-sm font-medium">
                            <i data-lucide="log-out" class="w-4 h-4 inline-block mr-1"></i> ออกจากระบบ
                        </a>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <span class="sr-only">Open main menu</span>
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu (สร้างแบบไดนามิก) -->
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <?php foreach ($navItems as $pageKey => $item): ?>
                    <?php if (in_array($user_role, $item['roles'])): ?>
                        <a href="index.php?page=<?= htmlspecialchars($pageKey) ?>" 
                           class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium <?= $current_page === $pageKey ? 'nav-link-active bg-gray-100' : '' ?>">
                            <i data-lucide="<?= htmlspecialchars($item['icon']) ?>" class="w-5 h-5 inline-block mr-2"></i>
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
             <!-- Mobile User Info & Logout -->
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-5">
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800"><?= htmlspecialchars($user_name) ?></div>
                        <div class="text-sm font-medium text-gray-500"><?= htmlspecialchars($display_role) ?></div>
                    </div>
                </div>
                <div class="mt-3 px-2 space-y-1">
                     <a href="logout.php" class="block text-red-600 hover:text-red-700 px-3 py-2 rounded-md text-base font-medium">
                        <i data-lucide="log-out" class="w-5 h-5 inline-block mr-2"></i>
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <script>
        // ทำให้ Lucide Icons ทำงาน
        lucide.createIcons();

        // Script สำหรับ Mobile Menu Toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
    
    <!-- Main Content จะถูก include ต่อจากนี้ -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
