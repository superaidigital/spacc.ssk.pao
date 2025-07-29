<?php
// pages/reports.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- API Logic for fetching report data ---
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    
    // Ensure user is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit();
    }
    
    require_once __DIR__ . '/../db_connect.php';

    if (!$conn || $conn->connect_error) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit();
    }

    $response = ['status' => 'error', 'message' => 'Invalid API call'];

    switch ($_GET['api']) {
        
        case 'get_summary':
            $summary_sql = "
                SELECT
                    s.amphoe,
                    COUNT(DISTINCT s.id) AS shelter_count,
                    COALESCE(SUM(CASE WHEN DATE(sl.created_at) < CURDATE() THEN IF(sl.log_type = 'add', sl.change_amount, -sl.change_amount) ELSE 0 END), 0) AS total_occupancy,
                    COALESCE(SUM(CASE WHEN DATE(sl.created_at) = CURDATE() THEN IF(sl.log_type = 'add', sl.change_amount, -sl.change_amount) ELSE 0 END), 0) AS daily_change
                FROM shelters s
                LEFT JOIN shelter_logs sl ON s.id = sl.shelter_id
                WHERE s.amphoe IS NOT NULL AND s.amphoe != '' AND s.type IN ('ศูนย์พักพิง', 'รพ.สต.')
                GROUP BY s.amphoe
                ORDER BY s.amphoe;
            ";
            $result = $conn->query($summary_sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $response = ['status' => 'success', 'data' => $data];
            break;

        case 'get_district_detail':
            if (empty($_GET['amphoe'])) {
                $response = ['status' => 'error', 'message' => 'District not specified'];
                break;
            }
            $amphoe = trim($_GET['amphoe']);
            $detail_sql = "
                SELECT
                    s.id, s.name, s.type, s.tambon,
                    COALESCE(SUM(CASE WHEN DATE(sl.created_at) < CURDATE() THEN IF(sl.log_type = 'add', sl.change_amount, -sl.change_amount) ELSE 0 END), 0) AS current_occupancy,
                    COALESCE(SUM(CASE WHEN DATE(sl.created_at) = CURDATE() THEN IF(sl.log_type = 'add', sl.change_amount, -sl.change_amount) ELSE 0 END), 0) AS daily_change
                FROM shelters s
                LEFT JOIN shelter_logs sl ON s.id = sl.shelter_id
                WHERE s.amphoe = ? AND s.type IN ('ศูนย์พักพิง', 'รพ.สต.')
                GROUP BY s.id, s.name, s.type, s.tambon
                ORDER BY s.name;
            ";
            $stmt = $conn->prepare($detail_sql);
            $stmt->bind_param("s", $amphoe);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $stmt->close();
            $response = ['status' => 'success', 'data' => $data];
            break;
            
        case 'get_shelter_details':
            if (empty($_GET['shelter_id'])) {
                $response = ['status' => 'error', 'message' => 'Shelter ID not specified'];
                break;
            }
            $shelter_id = intval($_GET['shelter_id']);

            // Query 1: Shelter's own info
            $shelter_info_sql = "SELECT name, type, tambon, amphoe FROM shelters WHERE id = ?";
            $stmt_info = $conn->prepare($shelter_info_sql);
            $stmt_info->bind_param("i", $shelter_id);
            $stmt_info->execute();
            $shelter_info = $stmt_info->get_result()->fetch_assoc();
            $stmt_info->close();

            if (!$shelter_info) {
                 $response = ['status' => 'error', 'message' => 'Shelter not found'];
                 break;
            }

            // Query 2: Log Summary (Today's change and Previous total)
            $log_summary_sql = "
                SELECT
                    COALESCE(SUM(CASE WHEN DATE(created_at) < CURDATE() THEN IF(log_type = 'add', change_amount, -change_amount) ELSE 0 END), 0) AS previous_total,
                    COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN IF(log_type = 'add', change_amount, -change_amount) ELSE 0 END), 0) AS daily_change
                FROM shelter_logs
                WHERE shelter_id = ?
            ";
            $stmt_log = $conn->prepare($log_summary_sql);
            $stmt_log->bind_param("i", $shelter_id);
            $stmt_log->execute();
            $log_summary = $stmt_log->get_result()->fetch_assoc();
            $stmt_log->close();


            // Query 3: Graph data (last 7 days)
            $graph_sql = "SELECT report_date, total_patients FROM hospital_daily_reports WHERE shelter_id = ? AND report_date >= CURDATE() - INTERVAL 7 DAY ORDER BY report_date ASC";
            $stmt_graph = $conn->prepare($graph_sql);
            $stmt_graph->bind_param("i", $shelter_id);
            $stmt_graph->execute();
            $graph_result = $stmt_graph->get_result();
            $graph_data = [];
            while($row = $graph_result->fetch_assoc()) {
                $graph_data[] = $row;
            }
            $stmt_graph->close();

            // Query 4: The latest detailed report
            $details_sql = "SELECT * FROM hospital_daily_reports WHERE shelter_id = ? ORDER BY report_date DESC, updated_at DESC LIMIT 1";
            $stmt_details = $conn->prepare($details_sql);
            $stmt_details->bind_param("i", $shelter_id);
            $stmt_details->execute();
            $current_details = $stmt_details->get_result()->fetch_assoc();
            $stmt_details->close();

            $response = [
                'status' => 'success', 
                'data' => [
                    'shelterInfo' => $shelter_info,
                    'logSummary' => $log_summary,
                    'graphData' => $graph_data, 
                    'currentDetails' => $current_details
                ]
            ];
            break;
    }

    echo json_encode($response);
    $conn->close();
    exit();
}
?>

<!-- HTML Structure -->
<div class="space-y-6">
    <div id="report-header" class="flex items-center gap-4">
        <!-- Header will be generated by JavaScript -->
    </div>
    
    <div id="report-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Report cards will be injected here -->
    </div>
</div>

<!-- Shelter Detail Modal -->
<div id="detailModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full justify-center items-center z-50 hidden">
    <div class="relative mx-auto p-4 md:p-6 border w-full max-w-4xl shadow-2xl rounded-2xl bg-gray-50 my-8">
        <div id="modalHeader" class="flex flex-col mb-4">
             <!-- Header, Title, location and summary will be injected here -->
        </div>
        
        <div id="modalContent" class="space-y-6">
            <!-- Loading Indicator -->
            <div id="modalLoading" class="text-center py-16">
                 <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                 <p class="mt-2 text-gray-600">กำลังโหลดข้อมูล...</p>
            </div>

            <!-- Chart and Details (hidden by default) -->
            <div id="modalDataContainer" class="hidden space-y-6">
                <!-- Chart -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h4 class="font-semibold mb-2 text-gray-700">แนวโน้มผู้เข้าพัก/ผู้ป่วย (7 วันล่าสุด)</h4>
                    <canvas id="detailChart"></canvas>
                </div>
                <!-- Details Table -->
                <div class="bg-white p-4 rounded-lg shadow">
                     <h4 class="font-semibold mb-3 text-gray-700">ข้อมูลสรุปปัจจุบัน</h4>
                     <div id="detailTableContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <!-- Detail items will be injected here -->
                     </div>
                </div>
            </div>
             <div id="modalNoData" class="hidden text-center py-16">
                <i data-lucide="inbox" class="h-12 w-12 mx-auto text-gray-400"></i>
                <p class="mt-2 text-gray-600">ไม่พบข้อมูลรายละเอียดของศูนย์นี้</p>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Element References ---
    const reportContainer = document.getElementById('report-container');
    const reportHeader = document.getElementById('report-header');
    const detailModal = document.getElementById('detailModal');
    const modalHeader = document.getElementById('modalHeader');
    const modalContent = document.getElementById('modalContent');
    const modalLoading = document.getElementById('modalLoading');
    const modalDataContainer = document.getElementById('modalDataContainer');
    const modalNoData = document.getElementById('modalNoData');
    const detailTableContainer = document.getElementById('detailTableContainer');

    const API_URL = 'pages/reports.php';
    window.detailChartInstance = null; // To hold the chart instance

    // --- Helper Functions ---
    const showLoading = (message) => {
        reportContainer.innerHTML = `<p class="col-span-full text-center text-gray-500 py-10">${message}</p>`;
    };
    
    // --- Rendering Functions ---
    const renderSummaryView = (data) => {
        reportHeader.innerHTML = `<h1 class="text-3xl font-bold text-gray-800">รายงานสรุปภาพรวม</h1>`;
        if (!data || data.length === 0) {
            reportContainer.innerHTML = '<p class="col-span-full text-center text-gray-500 py-10">ไม่พบข้อมูลสรุปของอำเภอ</p>';
            return;
        }
        reportContainer.innerHTML = data.map(amphoe => `
            <div class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow cursor-pointer" 
                 onclick="window.location.href='index.php?page=reports&view=district&amphoe=${encodeURIComponent(amphoe.amphoe)}'">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-indigo-100 rounded-lg flex-shrink-0"><i data-lucide="map" class="h-8 w-8 text-indigo-600"></i></div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">${amphoe.amphoe}</h3>
                        <p class="text-sm text-gray-500">${amphoe.shelter_count} ศูนย์</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-sm text-gray-500">ยอดวันนี้</p>
                        <p class="text-2xl font-bold ${((parseInt(amphoe.daily_change) || 0) > 0) ? 'text-green-600' : (((parseInt(amphoe.daily_change) || 0) < 0) ? 'text-red-600' : 'text-gray-600')}">${((parseInt(amphoe.daily_change) || 0) > 0) ? '+' : ''}${parseInt(amphoe.daily_change) || 0}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">ยอดสะสม</p>
                        <p class="text-2xl font-bold text-gray-800">${parseInt(amphoe.total_occupancy) || 0}</p>
                    </div>
                </div>
            </div>`).join('');
        lucide.createIcons();
    };

    const renderDistrictDetailView = (data, amphoeName) => {
        reportHeader.innerHTML = `
            <a href="index.php?page=reports" class="p-2 bg-gray-200 rounded-lg text-gray-600 hover:bg-gray-300" title="กลับ"><i data-lucide="arrow-left" class="h-6 w-6"></i></a>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">ศูนย์ในอำเภอ${amphoeName}</h1>
                <p class="text-gray-500">ข้อมูลล่าสุด ณ วันที่ ${new Date().toLocaleDateString('th-TH')}</p>
            </div>`;
        if (!data || data.length === 0) {
            reportContainer.innerHTML = '<p class="col-span-full text-center text-gray-500 py-10">ไม่พบข้อมูลศูนย์ในอำเภอนี้</p>';
            return;
        }
        reportContainer.innerHTML = data.map(shelter => {
            const cumulativeTotal = parseInt(shelter.current_occupancy, 10) || 0;
            const dailyChange = parseInt(shelter.daily_change, 10) || 0;
            const shelterIcon = shelter.type === 'รพ.สต.' ? 'hospital' : 'home';

            return `
            <div class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow cursor-pointer" onclick="openShelterDetailModal(${shelter.id})">
                <div class="flex items-start gap-4 mb-3">
                    <div class="p-3 bg-blue-100 rounded-lg flex-shrink-0">
                        <i data-lucide="${shelterIcon}" class="h-6 w-6 text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">${shelter.name}</h4>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <i data-lucide="map-pin" class="h-3 w-3 mr-1.5 flex-shrink-0"></i>
                            <span>ต.${shelter.tambon || '-'}, อ.${amphoeName}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-sm text-gray-500">ยอดวันนี้</p>
                        <p class="text-xl font-bold ${dailyChange > 0 ? 'text-green-600' : (dailyChange < 0 ? 'text-red-600' : 'text-gray-600')}">${dailyChange > 0 ? '+' : ''}${dailyChange}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">ยอดสะสม</p>
                        <p class="text-xl font-bold text-gray-800">${cumulativeTotal}</p>
                    </div>
                </div>
            </div>`;
        }).join('');
        lucide.createIcons();
    };

    // --- Modal Functions ---
    window.openShelterDetailModal = async (shelterId) => {
        detailModal.classList.remove('hidden');
        detailModal.classList.add('flex');
        modalHeader.innerHTML = ''; // Clear previous header

        modalLoading.style.display = 'block';
        modalDataContainer.style.display = 'none';
        modalNoData.style.display = 'none';
        if (window.detailChartInstance) {
            window.detailChartInstance.destroy();
        }

        try {
            const response = await fetch(`${API_URL}?api=get_shelter_details&shelter_id=${shelterId}`);
            const result = await response.json();

            modalLoading.style.display = 'none';

            if (result.status === 'success') {
                const info = result.data.shelterInfo;
                const logs = result.data.logSummary;
                const shelterIcon = info.type === 'รพ.สต.' ? 'hospital' : 'home';
                
                const dailyChange = parseInt(logs.daily_change) || 0;
                const previousTotal = parseInt(logs.previous_total) || 0;
                const cumulativeTotal = previousTotal + dailyChange;

                modalHeader.innerHTML = `
                    <div class="w-full">
                        <div class="flex justify-between items-start">
                             <div class="flex items-start gap-4">
                                <div class="p-3 bg-blue-100 rounded-lg flex-shrink-0 mt-1">
                                    <i data-lucide="${shelterIcon}" class="h-6 w-6 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-800">${info.name}</h3>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i data-lucide="map-pin" class="h-4 w-4 mr-1.5 flex-shrink-0"></i>
                                        <span>ต.${info.tambon || '-'}, อ.${info.amphoe || '-'}</span>
                                    </div>
                                </div>
                            </div>
                            <button id="closeModalBtn" class="p-2 text-gray-500 hover:text-gray-800 rounded-full hover:bg-gray-200 flex-shrink-0">
                                <i data-lucide="x" class="h-6 w-6"></i>
                            </button>
                        </div>
                        <div class="mt-4 pt-4 border-t-2 border-gray-200 grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-white rounded-lg shadow-inner">
                                <p class="text-sm font-medium text-gray-500">ยอดวันนี้</p>
                                <p class="text-3xl font-bold ${dailyChange > 0 ? 'text-green-600' : (dailyChange < 0 ? 'text-red-600' : 'text-gray-800')}">
                                    ${dailyChange > 0 ? '+' : ''}${dailyChange}
                                </p>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg shadow-inner">
                                <p class="text-sm font-medium text-gray-500">ยอดสะสมทั้งหมด</p>
                                <p class="text-3xl font-bold text-indigo-800">${cumulativeTotal}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                modalHeader.querySelector('#closeModalBtn').addEventListener('click', () => {
                    detailModal.classList.add('hidden');
                    detailModal.classList.remove('flex');
                });

                if(result.data.currentDetails){
                    modalDataContainer.style.display = 'block';
                    renderDetailChart(result.data.graphData);
                    renderDetailTable(result.data.currentDetails);
                } else {
                    modalNoData.style.display = 'block';
                }

            } else {
                modalHeader.innerHTML = `<h3 class="text-2xl font-bold text-red-500">เกิดข้อผิดพลาด</h3>`;
                modalNoData.style.display = 'block';
            }
        } catch (error) {
            modalLoading.style.display = 'none';
            modalNoData.style.display = 'block';
            console.error("Failed to fetch shelter details:", error);
        }
        lucide.createIcons();
    };

    const renderDetailChart = (data) => {
        const ctx = document.getElementById('detailChart').getContext('2d');
        const labels = data.map(d => new Date(d.report_date).toLocaleDateString('th-TH', { day: 'numeric', month: 'short' }));
        const values = data.map(d => d.total_patients);

        window.detailChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'จำนวนรวม',
                    data: values,
                    borderColor: 'rgb(79, 70, 229)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    };
    
    const renderDetailTable = (details) => {
        const detailMapping = [
            { label: 'จำนวนผู้อพยพ', key: 'total_patients', color: 'bg-blue-100 text-blue-800' },
            { label: 'คัดกรองสุขภาพทั่วไป', key: 'male_patients', color: 'bg-gray-100 text-gray-800' },
            { label: 'ผู้สูงอายุ', key: 'elderly_patients', color: 'bg-yellow-100 text-yellow-800' },
            { label: 'มีโรคประจำตัว', key: 'chronic_disease_patients', color: 'bg-purple-100 text-purple-800' },
            { label: 'ผู้พิการ', key: 'disabled_patients', color: 'bg-pink-100 text-pink-800' },
            { label: 'ผู้ป่วยฟอกไต', key: 'kidney_disease_patients', color: 'bg-red-100 text-red-800' },
            { label: 'หญิงตั้งครรภ์', key: 'pregnant_women', color: 'bg-pink-100 text-pink-800' },
            { label: 'เด็ก 0-5 ปี', key: 'child_patients', color: 'bg-green-100 text-green-800' },
            { label: 'ผู้ป่วยซึมเศร้า', key: 'mental_health_patients', color: 'bg-gray-100 text-gray-800' },
            { label: 'ผู้ป่วยเบาหวาน', key: 'diabetes_patients', color: 'bg-red-100 text-red-800' },
            { label: 'ผู้ป่วยทางเดินหายใจ', key: 'other_monitored_diseases', color: 'bg-gray-100 text-gray-800' },
            { label: 'โรคทางเดินอาหาร', key: 'notes', color: 'bg-gray-100 text-gray-800' }
        ];

        detailTableContainer.innerHTML = detailMapping.map(item => `
            <div class="text-center p-3 rounded-lg ${item.color}">
                <p class="text-2xl font-bold">${details[item.key] || 0}</p>
                <p class="text-xs font-medium">${item.label}</p>
            </div>
        `).join('');
    };

    // --- Main Logic ---
    const main = async () => {
        const urlParams = new URLSearchParams(window.location.search);
        const view = urlParams.get('view');
        const amphoe = urlParams.get('amphoe');

        if (view === 'district' && amphoe) {
            showLoading(`กำลังโหลดข้อมูลอำเภอ${amphoe}...`);
            try {
                const response = await fetch(`${API_URL}?api=get_district_detail&amphoe=${encodeURIComponent(amphoe)}`);
                const result = await response.json();
                if (result.status === 'success') renderDistrictDetailView(result.data, amphoe);
                else showLoading(result.message);
            } catch (error) { showLoading('ไม่สามารถโหลดข้อมูลได้'); }
        } else {
            showLoading('กำลังโหลดข้อมูลสรุป...');
             try {
                const response = await fetch(`${API_URL}?api=get_summary`);
                const result = await response.json();
                if (result.status === 'success') renderSummaryView(result.data);
                else showLoading(result.message);
            } catch (error) { showLoading('ไม่สามารถโหลดข้อมูลได้'); }
        }
    };

    main();
});
</script>
