<?php
// pages/users.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// This check is for direct page access.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    // We create a simple HTML response for direct access denial
    // because the main layout (header/footer) isn't included yet.
    if (!isset($_GET['api'])) {
        echo '<!DOCTYPE html><html lang="th"><head><title>ไม่มีสิทธิ์เข้าถึง</title><script src="https://cdn.tailwindcss.com"></script><link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap" rel="stylesheet"><style>body { font-family: \'Sarabun\', sans-serif; }</style></head><body class="bg-gray-100 flex items-center justify-center h-screen"><div class="text-center"><h1 class="text-2xl font-bold">ไม่มีสิทธิ์เข้าถึง</h1><p>คุณไม่มีสิทธิ์ในการเข้าถึงหน้านี้</p><a href="../index.php" class="text-blue-600 hover:underline">กลับหน้าหลัก</a></div></body></html>';
        exit();
    }
}


// --- API Logic ---
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    
    // API-specific authorization check. This is crucial.
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
        echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์ในการดำเนินการ']);
        exit();
    }
    
    require '../db_connect.php'; 
    $data = json_decode(file_get_contents('php://input'), true);
    $logged_in_user_id = $_SESSION['user_id'];
    
    switch ($_GET['api']) {
        case 'get_data':
            $users = [];
            $shelters = [];
            $user_sql = "
                SELECT u.*, s.name AS shelter_name,
                GROUP_CONCAT(hs.shelter_id) as health_shelter_ids
                FROM users u 
                LEFT JOIN shelters s ON u.assigned_shelter_id = s.id
                LEFT JOIN healthstaff_shelters hs ON u.id = hs.user_id
                GROUP BY u.id
                ORDER BY u.name ASC
            ";
            $user_result = $conn->query($user_sql);
            while($row = $user_result->fetch_assoc()) {
                $users[] = $row;
            }
            $shelter_result = $conn->query("SELECT id, name FROM shelters ORDER BY name ASC");
             while($row = $shelter_result->fetch_assoc()) {
                $shelters[] = $row;
            }
            echo json_encode(['status' => 'success', 'users' => $users, 'shelters' => $shelters]);
            break;
        case 'add_user':
            $conn->begin_transaction();
            try {
                $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $assigned_shelter = ($data['role'] === 'Coordinator' && !empty($data['assigned_shelter_id'])) 
                    ? intval($data['assigned_shelter_id']) 
                    : NULL;
                
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status, assigned_shelter_id) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $data['name'], $data['email'], $password_hash, 
                                 $data['role'], $data['status'], $assigned_shelter);
                
                if ($stmt->execute()) {
                    $new_user_id = $stmt->insert_id;
                    
                    // จัดการศูนย์ที่รับผิดชอบสำหรับเจ้าหน้าที่สาธารณสุข
                    if ($data['role'] === 'HealthStaff' && !empty($data['multi_shelters'])) {
                        $shelter_stmt = $conn->prepare("INSERT INTO healthstaff_shelters 
                                                      (user_id, shelter_id) VALUES (?, ?)");
                        foreach ($data['multi_shelters'] as $shelter_id) {
                            $shelter_stmt->bind_param("ii", $new_user_id, $shelter_id);
                            $shelter_stmt->execute();
                        }
                        $shelter_stmt->close();
                    }
                    
                    $conn->commit();
                    echo json_encode(['status' => 'success', 'message' => 'เพิ่มผู้ใช้งานสำเร็จ']);
                }
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
            }
            $stmt->close();
            break;
        case 'edit_user':
            $conn->begin_transaction();
            try {
                $user_id_to_edit = intval($data['id']);
                if ($user_id_to_edit === $logged_in_user_id) {
                    if ($data['role'] !== 'Admin' || $data['status'] !== 'Active') {
                        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเปลี่ยนบทบาทหรือสถานะของบัญชีตัวเองได้']);
                        exit();
                    }
                }

                $query_parts = [];
                $params = [];
                $types = '';

                $fields_to_update = ['name', 'email', 'role', 'status'];
                foreach ($fields_to_update as $field) {
                    $query_parts[] = "$field = ?";
                    $params[] = $data[$field];
                    $types .= 's';
                }

                if ($data['role'] === 'Coordinator' && !empty($data['assigned_shelter_id'])) {
                    $query_parts[] = "assigned_shelter_id = ?";
                    $params[] = intval($data['assigned_shelter_id']);
                    $types .= 'i';
                } else {
                    $query_parts[] = "assigned_shelter_id = NULL";
                }
                
                if (!empty($data['password'])) {
                    $query_parts[] = "password = ?";
                    $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
                    $types .= 's';
                }

                $params[] = $user_id_to_edit;
                $types .= 'i';

                $sql = "UPDATE users SET " . implode(', ', $query_parts) . " WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);

                if ($stmt->execute()) {
                    // จัดการศูนย์ที่รับผิดชอบสำหรับเจ้าหน้าที่สาธารณสุข
                    if ($data['role'] === 'HealthStaff') {
                        // ลบข้อมูลเดิม
                        $conn->query("DELETE FROM healthstaff_shelters WHERE user_id = " . $user_id_to_edit);
                        
                        // เพิ่มข้อมูลใหม่
                        if (!empty($data['multi_shelters'])) {
                            $shelter_stmt = $conn->prepare("INSERT INTO healthstaff_shelters 
                                                          (user_id, shelter_id) VALUES (?, ?)");
                            foreach ($data['multi_shelters'] as $shelter_id) {
                                $shelter_stmt->bind_param("ii", $user_id_to_edit, $shelter_id);
                                $shelter_stmt->execute();
                            }
                            $shelter_stmt->close();
                        }
                    }
                    
                    $conn->commit();
                    echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ']);
                }
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
            }
            break;
        case 'delete_user':
            $user_id_to_delete = intval($data['id']);
            if ($user_id_to_delete === $logged_in_user_id) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบบัญชีของตัวเองได้']);
                exit();
            }
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id_to_delete);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'ลบผู้ใช้งานสำเร็จ']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ลบผู้ใช้งานไม่สำเร็จ: ' . $stmt->error]);
            }
            $stmt->close();
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid API call']);
            break;
    }
    $conn->close();
    exit();
}
?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800">จัดการผู้ใช้งาน</h1>
        <button id="addUserBtn" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 flex items-center gap-2">
            <i data-lucide="plus"></i><span>เพิ่มผู้ใช้งาน</span>
        </button>
    </div>
    <div class="bg-white rounded-xl shadow-md overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ-สกุล</th>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">อีเมล</th>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">บทบาท</th>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ศูนย์ที่รับผิดชอบ</th>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                   <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                </tr>
            </thead>
            <tbody id="userTableBody" class="divide-y divide-gray-200">
                <tr><td colspan="6" class="text-center p-8 text-gray-500">กำลังโหลดข้อมูล...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full justify-center items-center z-50 hidden">
    <div class="relative mx-auto p-8 border w-full max-w-lg shadow-lg rounded-2xl bg-white">
        <button id="closeUserModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><i data-lucide="x" class="h-6 w-6"></i></button>
        <h3 id="userModalTitle" class="text-2xl leading-6 font-bold text-gray-900 mb-6"></h3>
        <form id="userForm" class="space-y-4">
            <input type="hidden" id="userId" name="id">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">ชื่อ-สกุล</label>
                <input type="text" id="name" name="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
                <input type="email" id="email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน</label>
                <input type="password" id="password" name="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="เว้นว่างไว้หากไม่ต้องการเปลี่ยน">
            </div>
            <div class="grid grid-cols-2 gap-4">
                 <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">บทบาท</label>
                    <select id="role" name="role" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        <option value="Admin">ผู้ดูแลระบบ</option>
                        <option value="Coordinator">เจ้าหน้าที่ประสานศูนย์</option>
                        <option value="HealthStaff">เจ้าหน้าที่สาธารณสุข</option>
                        <option value="User">ผู้ใช้ทั่วไป</option>
                    </select>
                </div>
                 <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">สถานะ</label>
                    <select id="status" name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        <option value="Active">เปิดใช้งาน</option>
                        <option value="Inactive">ปิดใช้งาน</option>
                    </select>
                </div>
            </div>
            <div id="shelterAssignmentContainer" class="hidden">
                <label for="assigned_shelter_id" class="block text-sm font-medium text-gray-700">ศูนย์ที่รับผิดชอบ</label>
                <select id="assigned_shelter_id" name="assigned_shelter_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">-- ไม่กำหนด --</option>
                </select>
            </div>
            <div id="multiShelterContainer" class="hidden">
                <label class="block text-sm font-medium text-gray-700">ศูนย์ที่รับผิดชอบ (เลือกได้หลายศูนย์)</label>
                <p class="select-tooltip">กด Ctrl (Windows) หรือ Command (Mac) เพื่อเลือกหลายรายการ</p>
                <select id="multi_shelters" name="multi_shelters[]" multiple 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg">
                </select>
                <p class="select-tooltip text-red-600" id="multiShelterError" style="display: none;">
                    กรุณาเลือกอย่างน้อย 1 ศูนย์
                </p>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                 <button type="button" id="cancelUserModal" class="px-6 py-2.5 bg-gray-200 rounded-lg hover:bg-gray-300">ยกเลิก</button>
                 <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">บันทึก</button>
            </div>
        </form>
    </div>
</div>
<style>
    select[multiple] {
        height: auto;
        min-height: 120px;
        padding: 8px;
    }
    select[multiple] option {
        padding: 8px;
        margin: 2px 0;
        border-radius: 4px;
        cursor: pointer;
    }
    select[multiple] option:checked {
        background-color: #3b82f6 !important;
        color: white;
    }
    /* เพิ่ม tooltip สำหรับวิธีใช้ */
    .select-tooltip {
        font-size: 0.75rem;
        color: #6B7280;
        margin-top: 0.25rem;
    }
</style>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const API_URL = 'pages/users.php';
    let allUsers = [];
    let allShelters = [];
    const loggedInUserId = <?= $_SESSION['user_id'] ?? 'null' ?>;
    const userTableBody = document.getElementById('userTableBody');
    const addUserBtn = document.getElementById('addUserBtn');
    const userModal = document.getElementById('userModal');
    const userForm = document.getElementById('userForm');
    const userModalTitle = document.getElementById('userModalTitle');
    const roleSelect = document.getElementById('role');
    const shelterAssignmentContainer = document.getElementById('shelterAssignmentContainer');
    const multiShelterContainer = document.getElementById('multiShelterContainer');
    const assignedShelterSelect = document.getElementById('assigned_shelter_id');
    const showAlert = (icon, title, text = '') => Swal.fire({ icon, title, text, confirmButtonColor: '#2563EB' });
    const closeUserModal = () => userModal.classList.add('hidden');
    function renderUsers() {
        if (!userTableBody) return;
        const roleDisplay = { 
            'Admin': 'ผู้ดูแลระบบ', 
            'Coordinator': 'เจ้าหน้าที่ประสานศูนย์', 
            'HealthStaff': 'เจ้าหน้าที่สาธารณสุข',
            'User': 'ผู้ใช้ทั่วไป' 
        };
        if (allUsers.length === 0) {
            userTableBody.innerHTML = '<tr><td colspan="6" class="text-center p-8 text-gray-500">ไม่พบข้อมูลผู้ใช้งาน</td></tr>';
            return;
        }
        userTableBody.innerHTML = allUsers.map(user => {
            let shelterInfo = '-';
            if (user.role === 'Coordinator') {
                shelterInfo = user.shelter_name || '-';
            } else if (user.role === 'HealthStaff' && user.health_shelter_ids) {
                const shelterIds = user.health_shelter_ids.split(',');
                const shelterNames = shelterIds.map(id => 
                    allShelters.find(s => s.id == id)?.name
                ).filter(Boolean);
                shelterInfo = shelterNames.join('<br>') || '-';
            }

            return `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">${user.name || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-500">${user.email || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                    ${roleDisplay[user.role] || user.role}
                </td>
                <td class="px-6 py-4 whitespace-normal text-gray-500">${shelterInfo}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${user.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${user.status === 'Active' ? 'เปิดใช้งาน' : 'ปิดใช้งาน'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="edit-btn text-blue-600 hover:text-blue-900" 
                            data-user='${JSON.stringify(user)}'>แก้ไข</button>
                    <button class="delete-btn text-red-600 hover:text-red-900 ml-4" 
                            data-id="${user.id}" 
                            data-name="${user.name}">ลบ</button>
                </td>
            </tr>`;
        }).join('');
    }
    function populateShelterDropdowns() {
        // สำหรับ Coordinator
        assignedShelterSelect.innerHTML = '<option value="">-- ไม่กำหนด --</option>';
        
        // สำหรับ HealthStaff
        const multiShelterSelect = document.getElementById('multi_shelters');
        multiShelterSelect.innerHTML = ''; // เคลียร์ตัวเลือกเดิม
        
        allShelters.forEach(shelter => {
            // เพิ่มตัวเลือกสำหรับ Coordinator
            assignedShelterSelect.add(new Option(shelter.name, shelter.id));
            
            // เพิ่มตัวเลือกสำหรับ HealthStaff
            multiShelterSelect.add(new Option(shelter.name, shelter.id));
        });
    }
    async function mainFetch() {
        try {
            const response = await fetch(`${API_URL}?api=get_data`);
            const result = await response.json();
            if (result.status === 'success') {
                allUsers = result.users;
                allShelters = result.shelters;
                renderUsers();
                populateShelterDropdowns();
            } else { showAlert('error', 'เกิดข้อผิดพลาด', result.message); }
        } catch (error) { console.error('Fetch error:', error); showAlert('error', 'การเชื่อมต่อล้มเหลว', 'ไม่สามารถดึงข้อมูลจากเซิร์ฟเวอร์ได้'); }
    }
    async function submitForm(url, data) {
        // แปลงข้อมูล multi-select เป็น array
        if (data.role === 'HealthStaff') {
            const multiSelect = document.getElementById('multi_shelters');
            data.multi_shelters = Array.from(multiSelect.selectedOptions).map(opt => opt.value);
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.status === 'success') {
                mainFetch();
                return { success: true, message: result.message };
            } else {
                return { success: false, message: result.message };
            }
        } catch (error) {
            console.error('Submit error:', error);
            return { success: false, message: 'การเชื่อมต่อล้มเหลว' };
        }
    }
    function openUserModal(user = null) {
        userForm.reset();
        shelterAssignmentContainer.classList.add('hidden');
        multiShelterContainer.classList.add('hidden');
        
        // เรียกใช้ฟังก์ชันที่เปลี่ยนชื่อใหม่
        populateShelterDropdowns();
        
        if (user) {
            userModalTitle.textContent = 'แก้ไขข้อมูลผู้ใช้งาน';
            Object.keys(user).forEach(key => {
                const input = userForm.elements[key];
                if (input) {
                    if (key === 'assigned_shelter_id') {
                        assignedShelterSelect.value = user[key] || '';
                    } else if (key === 'health_shelter_ids' && user[key]) {
                        // แยก ID ของศูนย์ที่เลือกและตั้งค่า multi-select
                        const selectedIds = user[key].split(',');
                        const multiSelect = document.getElementById('multi_shelters');
                        Array.from(multiSelect.options).forEach(option => {
                            option.selected = selectedIds.includes(option.value);
                        });
                    } else {
                        input.value = user[key];
                    }
                }
            });
            userForm.elements.password.placeholder = "เว้นว่างไว้หากไม่ต้องการเปลี่ยน";
            userForm.elements.password.required = false;
            if (user.role === 'Coordinator') {
                shelterAssignmentContainer.classList.remove('hidden');
            }
            if (user.role === 'HealthStaff') {
                multiShelterContainer.classList.remove('hidden');
            }
            if (user.id == loggedInUserId) {
                document.getElementById('role').disabled = true;
                document.getElementById('status').disabled = true;
            }
        } else {
            userModalTitle.textContent = 'เพิ่มผู้ใช้งานใหม่';
            userForm.elements.password.placeholder = "";
            userForm.elements.password.required = true;
        }
        userModal.classList.remove('hidden');
    }
    addUserBtn.addEventListener('click', () => openUserModal());
    document.getElementById('closeUserModal').addEventListener('click', closeUserModal);
    document.getElementById('cancelUserModal').addEventListener('click', closeUserModal);
    roleSelect.addEventListener('change', () => {
        if (roleSelect.value === 'Coordinator') {
            shelterAssignmentContainer.classList.remove('hidden');
            multiShelterContainer.classList.add('hidden');
        } else if (roleSelect.value === 'HealthStaff') {
            shelterAssignmentContainer.classList.add('hidden');
            multiShelterContainer.classList.remove('hidden');
        } else {
            shelterAssignmentContainer.classList.add('hidden');
            multiShelterContainer.classList.add('hidden');
        }
    });
    userTableBody.addEventListener('click', e => {
        if (e.target.classList.contains('edit-btn')) {
            const userData = JSON.parse(e.target.dataset.user);
            openUserModal(userData);
        }
        if (e.target.classList.contains('delete-btn')) {
            const id = e.target.dataset.id;
            const name = e.target.dataset.name;
            if (id == loggedInUserId) {
                 showAlert('error', 'ไม่สามารถลบตัวเองได้');
                 return;
            }
            Swal.fire({
                title: 'ยืนยันการลบ?', text: `คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้ "${name}"?`, icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6b7280',
                confirmButtonText: 'ใช่, ลบเลย!', cancelButtonText: 'ยกเลิก'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const res = await submitForm(`${API_URL}?api=delete_user`, { id });
                    showAlert(res.success ? 'success' : 'error', res.success ? 'ลบสำเร็จ' : res.message);
                }
            });
        }
    });
    userForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target).entries());
        
        // ตรวจสอบการเลือกศูนย์สำหรับเจ้าหน้าที่สาธารณสุข
        if (data.role === 'HealthStaff') {
            const multiSelect = document.getElementById('multi_shelters');
            const selectedShelters = Array.from(multiSelect.selectedOptions);
            
            if (selectedShelters.length === 0) {
                document.getElementById('multiShelterError').style.display = 'block';
                return;
            } else {
                document.getElementById('multiShelterError').style.display = 'none';
            }
        }

        const url = data.id ? `${API_URL}?api=edit_user` : `${API_URL}?api=add_user`;
        const result = await submitForm(url, data);
        
        if (result.success) {
            closeUserModal();
            showAlert('success', result.message);
        } else {
            showAlert('error', 'เกิดข้อผิดพลาด', result.message);
        }
    });
    mainFetch();
});
</script>