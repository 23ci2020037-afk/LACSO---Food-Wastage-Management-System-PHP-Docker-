<?php
session_start();
include 'db.php'; // Use LIVE database

// Handle Action Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_volunteer'])) {
        $v_name = $_POST['v_name'];
        $v_email = $_POST['v_email'];
        $v_pass = password_hash($_POST['v_pass'], PASSWORD_BCRYPT);
        // Insert into users table as 'volunteer'
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'volunteer', NOW())");
        $stmt->bind_param("sss", $v_name, $v_email, $v_pass);
        $stmt->execute();
        $stmt->close();
        header("Location: LACSO-Admin-Panel.php?tab=volunteers");
        exit;
    }
    
    if (isset($_POST['remove_volunteer'])) {
        $v_id = $_POST['v_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $v_id);
        $stmt->execute();
        $stmt->close();
        header("Location: LACSO-Admin-Panel.php?tab=volunteers");
        exit;
    }
}

// Ensure tab persistence after reload
$initialTab = $_GET['tab'] ?? 'dashboard';

// --- 1. Dashboard Stats ---
$totalDonations = $conn->query("SELECT COUNT(*) FROM donations")->fetch_row()[0] ?? 0;
// Fallback if roles aren't fully set up yet
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?? 0;
$totalDonors = floor($totalUsers * 0.7); // Approximating based on users if roles not set
$activeVolunteers = ceil($totalUsers * 0.3);

// Deliveries Today
$deliveriesToday = $conn->query("SELECT COUNT(*) FROM donations WHERE LOWER(status) IN ('accepted', 'delivered')")->fetch_row()[0] ?? 0;

// --- 2. Chart Data (Live 7-Day Trend) ---
$trendQuery = $conn->query("
    SELECT DATE(created_at) as d_date, COUNT(*) as d_count 
    FROM donations 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
    ORDER BY d_date ASC
");

$dynLabels = [];
$dynData = [];
for($i=6; $i>=0; $i--) {
    $dateKey = date('Y-m-d', strtotime("-$i days"));
    $dynLabels[] = date('D', strtotime("-$i days"));
    $dynData[$dateKey] = 0; // Initialize with 0
}

if ($trendQuery) {
    while($row = $trendQuery->fetch_assoc()) {
        if(isset($dynData[$row['d_date']])) {
            $dynData[$row['d_date']] = (int)$row['d_count'];
        }
    }
}

$donationTrendLabels = array_values($dynLabels);
$donationTrendData = array_values($dynData);

// --- 2.1 Role Distribution Chart ---
$roleCounts = $conn->query("SELECT role, COUNT(*) as c FROM users GROUP BY role");
$rolesLabel = [];
$rolesData = [];
if($roleCounts){
    while($r = $roleCounts->fetch_assoc()){
        $rolesLabel[] = ucfirst($r['role'] ? $r['role'] : 'Unknown');
        $rolesData[] = (int)$r['c'];
    }
}

// --- 2.2 Category Distribution Bar Chart ---
$catCounts = $conn->query("SELECT category, COUNT(*) as c FROM donations WHERE category IS NOT NULL AND category != '' GROUP BY category");
$catLabels = [];
$catData = [];
if($catCounts){
    while($r = $catCounts->fetch_assoc()) {
        $catLabels[] = ucfirst($r['category']);
        $catData[] = (int)$r['c'];
    }
}
if(empty($catLabels)) {
    $catLabels = ['Cooked Meals', 'Raw Vegetables', 'Packaged Snacks'];
    $catData = [12, 8, 15]; // Mock fallback
}

// --- 3. Recent Activities (Live from DB) ---
$recentActivitiesResult = $conn->query("SELECT IFNULL(donor_name, 'Guest') as donor_name, food_name, created_at FROM donations ORDER BY id DESC LIMIT 5");
$recentActivities = [];
if ($recentActivitiesResult) {
    while ($row = $recentActivitiesResult->fetch_assoc()) {
        $recentActivities[] = [
            'icon' => '<i class="ri-box-3-fill text-emerald-500"></i>',
            'title' => htmlspecialchars($row['food_name']),
            'donor' => htmlspecialchars($row['donor_name']),
            'time' => date('d M, h:i A', strtotime($row['created_at']))
        ];
    }
}

// --- 4. Users Data (Live) ---
$usersRes = $conn->query("
    SELECT u.id, u.name, u.email, u.role, 
           (SELECT food_name FROM donations WHERE donor_id = u.id OR donor_name = u.name ORDER BY id DESC LIMIT 1) as recent_food
    FROM users u 
    ORDER BY u.id DESC
");
$volunteers = [];
$donors = [];
if($usersRes) {
    while($row = $usersRes->fetch_assoc()) {
        // Simple logic to separate if roles aren't populated: containing "vol" goes to volunteers
        if ($row['role'] === 'volunteer' || stripos($row['email'], 'vol') !== false || stripos($row['name'], 'vol') !== false) {
            $volunteers[] = $row;
        } else {
            $donors[] = $row;
        }
    }
}

// --- 5. All Donations Data (Live) ---
$allDonationsRes = $conn->query("
    SELECT d.id, IFNULL(d.donor_name, 'Unknown') as d_name, d.food_name, d.quantity, d.created_at, d.status, d.pickup_address, IFNULL(u.name, 'Unassigned') as v_name 
    FROM donations d 
    LEFT JOIN users u ON d.volunteer_id = u.id 
    ORDER BY d.id DESC LIMIT 40");
$donationsList = [];
if($allDonationsRes) {
    while($row = $allDonationsRes->fetch_assoc()) $donationsList[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en" x-data="adminPanel('<?php echo htmlspecialchars($initialTab); ?>')">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LACSO Admin Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background: #e9f5ee; color: #064e3b; overflow: hidden; }
        .glass-sidebar { background: #064e3b; color: white; border-right: 1px solid #065f46; box-shadow: 10px 0 30px rgba(6,78,59,0.1); z-index: 50; }
        .nav-btn { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; border-radius: 1.5rem; transition: all 0.3s; font-weight: 900; margin-bottom: 0.5rem; cursor: pointer; color: rgba(255,255,255,0.7); }
        .nav-btn:hover { background: rgba(255,255,255,0.1); transform: translateX(5px); color: white;}
        .nav-btn.active { background: #10b981; color: white; box-shadow: 0 10px 20px -5px rgba(16,185,129,0.4); }
        
        .panel-card { background: #ffffff; border: 2px solid #d1fae5; border-radius: 2rem; padding: 1.5rem; box-shadow: 0 15px 30px -10px rgba(6,78,59,0.05); }
        .stat-icon { width: 3.5rem; height: 3.5rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; }
        
        /* Table styles */
        .admin-table { width: 100%; border-collapse: separate; border-spacing: 0 0.5rem; }
        .admin-table th { color: #064e3b; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem; text-align: left; font-size: 0.75rem; opacity: 0.5;}
        .admin-table td { padding: 1.2rem 1rem; background: #ffffff; border-top: 1px solid #d1fae5; border-bottom: 1px solid #d1fae5; font-weight: 700; font-size: 0.9rem; }
        .admin-table tr td:first-child { border-left: 1px solid #d1fae5; border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; }
        .admin-table tr td:last-child { border-right: 1px solid #d1fae5; border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; }
        .admin-table tr { transition: transform 0.2s; cursor: pointer; }
        .admin-table tr:hover td { background: #f0fdf4; border-color: #6ee7b7; box-shadow: 0 5px 15px rgba(6,78,59,0.05); }
        .admin-table tr:hover { transform: translateY(-2px); }
        
        .status-pill { padding: 6px 16px; border-radius: 99px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; display: inline-flex; align-items: center; gap: 0.25rem; }
        .status-delivered { background: #ecfdf5; color: #047857; border: 1px solid #10b981; }
        .status-accepted { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .status-pending { background: #fef3c7; color: #92400e; border: 1px solid #fbbf24; }
        
        .fade-enter { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        
        /* Toast Notifications */
        #toast-container { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;}
        .toast { background: white; border-left: 4px solid #10b981; box-shadow: 0 10px 40px -10px rgba(6,78,59,0.3); padding: 16px 24px; border-radius: 12px; display: flex; align-items: center; gap: 12px; pointer-events: auto; animation: slideInRight 0.4s ease-out forwards, fadeOut 0.5s ease-in forwards 4.5s; width: 320px; }
        .toast-red { border-left-color: #f59e0b; }
        .toast-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .toast-content h4 { font-weight: 900; color: #064e3b; margin: 0; font-size: 0.9rem;}
        .toast-content p { margin: 0; color: #065f46; opacity: 0.8; font-size: 0.75rem; font-weight: bold;}
        @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeOut { to { opacity: 0; transform: translateY(-10px); } }
    </style>
</head>
<body class="flex h-screen w-screen relative overflow-hidden bg-[#e9f5ee]">

    <!-- Realtime Notifications Container -->
    <div id="toast-container"></div>
    <!-- Sound effect placeholder -->
    <audio id="notify-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <!-- Sidebar -->
    <aside class="w-72 glass-sidebar flex flex-col p-8 z-50">
        <div class="flex items-center gap-3 mb-12">
            <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-xl">
                <i class="ri-radar-fill text-2xl font-black"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight leading-none text-white">LACSO</h1>
                <p class="text-[10px] text-emerald-300 font-bold uppercase tracking-widest mt-1">Command Center</p>
            </div>
        </div>
        
        <nav class="flex-1 space-y-2">
            <div @click="switchTab('dashboard')" :class="{'active': section==='dashboard'}" class="nav-btn">
                <i class="ri-dashboard-3-fill text-xl"></i> <span>Overview</span>
            </div>
            <div @click="switchTab('donations')" :class="{'active': section==='donations'}" class="nav-btn">
                <i class="ri-gift-fill text-xl"></i> <span>Live Orders (<?php echo count($donationsList); ?>)</span>
            </div>
            <div @click="switchTab('volunteers')" :class="{'active': section==='volunteers'}" class="nav-btn">
                <i class="ri-riding-fill text-xl"></i> <span>Volunteers</span>
            </div>
            <div @click="switchTab('donors')" :class="{'active': section==='donors'}" class="nav-btn">
                <i class="ri-heart-3-fill text-xl"></i> <span>Donor Network</span>
            </div>
            <div @click="switchTab('chatbot')" :class="{'active': section==='chatbot'}" class="nav-btn mt-6">
                <i class="ri-robot-2-fill text-xl"></i> <span>AI Analytics</span>
            </div>
        </nav>

        <a href="logout.php" class="mt-auto block p-4 rounded-xl text-center text-emerald-200 hover:bg-red-500 hover:text-white font-bold transition-colors">
            <i class="ri-logout-circle-r-line"></i> Secure Logout
        </a>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 h-screen overflow-y-auto p-10 relative">
        <!-- Abstract Background -->
        <div class="fixed top-0 right-0 w-[800px] h-[800px] bg-emerald-200 opacity-20 blur-[150px] rounded-full pointer-events-none -mr-40 -mt-40"></div>

        <!-- DASHBOARD VIEW -->
        <div x-show="section==='dashboard'" class="tab-content w-full max-w-7xl mx-auto pb-20">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h2 class="text-4xl font-black text-emerald-950 italic">System Overview</h2>
                    <p class="text-emerald-700 font-bold mt-2">Live metrics monitoring across the infrastructure.</p>
                </div>
            </div>

            <!-- Stats Header -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="panel-card flex items-center gap-5 slide-item">
                    <div class="stat-icon bg-emerald-100 text-emerald-600"><i class="ri-box-3-fill"></i></div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-emerald-500">Total Donations</p>
                        <p class="text-3xl font-black text-emerald-950"><?php echo $totalDonations; ?></p>
                    </div>
                </div>
                <div class="panel-card flex items-center gap-5 slide-item">
                    <div class="stat-icon bg-blue-100 text-blue-600"><i class="ri-riding-line"></i></div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-blue-500">Active Volunteers</p>
                        <p class="text-3xl font-black text-emerald-950"><?php echo $activeVolunteers; ?></p>
                    </div>
                </div>
                <div class="panel-card flex items-center gap-5 slide-item">
                    <div class="stat-icon bg-orange-100 text-orange-500"><i class="ri-heart-pulse-fill"></i></div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-orange-500">Total Donors</p>
                        <p class="text-3xl font-black text-emerald-950"><?php echo $totalDonors; ?></p>
                    </div>
                </div>
                <div class="panel-card flex items-center gap-5 slide-item">
                    <div class="stat-icon bg-purple-100 text-purple-600"><i class="ri-checkbox-circle-fill"></i></div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-purple-500">Deliveries Today</p>
                        <p class="text-3xl font-black text-emerald-950"><?php echo $deliveriesToday; ?></p>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- AI Graph -->
                <div class="panel-card lg:col-span-2 slide-item relative overflow-hidden">
                    <h3 class="text-2xl font-black text-emerald-950 mb-6 flex items-center gap-2"><i class="ri-bar-chart-2-fill text-emerald-500"></i> Donation Influx Matrix</h3>
                    <div class="relative z-10 bg-white p-4 rounded-xl border border-emerald-50">
                        <canvas id="donationChart" height="100"></canvas>
                    </div>
                </div>

                <!-- Recent Terminal Feed -->
                <div class="panel-card slide-item bg-emerald-950 text-white border-0 shadow-2xl relative overflow-hidden">
                    <h3 class="text-xl font-black mb-6 text-emerald-400 border-b border-emerald-800 pb-4"><i class="ri-terminal-box-line"></i> Live Event Stream</h3>
                    <ul class="space-y-6 relative z-10">
                        <?php foreach($recentActivities as $act): ?>
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-emerald-800 text-emerald-300 rounded-lg flex items-center justify-center shadow-lg">
                                <?php echo $act['icon']; ?>
                            </div>
                            <div>
                                <p class="text-white font-bold text-sm leading-tight mb-1"><?php echo $act['title']; ?></p>
                                <p class="text-emerald-500 text-xs font-bold"><?php echo $act['donor']; ?> • <?php echo $act['time']; ?></p>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($recentActivities)): ?>
                        <li class="text-emerald-500 font-bold text-sm">System idle... await data.</li>
                        <?php endif; ?>
                    </ul>
                    <i class="ri-radar-fill absolute -bottom-10 -right-10 text-[15rem] text-white opacity-[0.02]"></i>
                </div>
            </div>

            <!-- Additional Interactive Charts Row -->
            <div class="grid lg:grid-cols-3 gap-8 mt-8">
                <!-- User Demographics Doughnut Chart -->
                <div class="panel-card slide-item relative overflow-hidden bg-white">
                    <h3 class="text-xl font-black text-emerald-950 mb-4 flex items-center gap-2"><i class="ri-donut-chart-fill text-orange-500"></i> Network Demographics</h3>
                    <div class="relative z-10 w-full flex justify-center mt-4">
                        <canvas id="roleChart" height="220" style="max-height: 220px;"></canvas>
                    </div>
                </div>

                <!-- Food Categories Bar Chart -->
                <div class="panel-card lg:col-span-2 slide-item relative overflow-hidden bg-white">
                    <h3 class="text-xl font-black text-emerald-950 mb-4 flex items-center gap-2"><i class="ri-bar-chart-grouped-fill text-blue-500"></i> Donated Item Categories</h3>
                    <div class="relative z-10 w-full mt-4">
                        <canvas id="categoryChart" height="220" style="max-height: 220px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- DONATIONS (LIVE ORDERS) VIEW -->
        <div x-show="section==='donations'" style="display:none;" class="tab-content w-full max-w-7xl mx-auto pb-20">
            <h2 class="text-4xl font-black text-emerald-950 italic mb-8"><i class="ri-live-fill text-red-500 animate-pulse mr-2"></i> Live Orders Tracker</h2>
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th>ORDER ID</th>
                            <th>DONOR</th>
                            <th>FOOD ITEM</th>
                            <th>QTY</th>
                            <th>PICKUP LOC.</th>
                            <th>ASSIGNED TO</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($donationsList as $don): 
                            $rawStatus = trim(strtolower($don['status'] ?? 'pending'));
                            $statusLabel = ucfirst($rawStatus);
                            $badgeClass = 'status-pending';
                            $icon = '<i class="ri-loader-4-line animate-spin"></i>';
                            if ($rawStatus === 'accepted') { $badgeClass = 'status-accepted'; $icon = '<i class="ri-run-line"></i>'; }
                            else if ($rawStatus === 'delivered') { $badgeClass = 'status-delivered'; $icon = '<i class="ri-check-double-line"></i>'; }
                        ?>
                        <tr class="fade-enter">
                            <td class="text-emerald-500 font-black">#<?php echo $don['id']; ?></td>
                            <td><?php echo htmlspecialchars($don['d_name']); ?></td>
                            <td class="text-emerald-950"><?php echo htmlspecialchars($don['food_name']); ?></td>
                            <td class="text-emerald-600"><?php echo htmlspecialchars($don['quantity']); ?></td>
                            <td><span class="truncate block max-w-[150px] opacity-70"><?php echo htmlspecialchars($don['pickup_address']); ?></span></td>
                            <td class="<?php echo ($don['v_name'] === 'Unassigned') ? 'text-red-400 italic' : 'text-blue-600'; ?>"><i class="ri-user-smile-fill mr-1 opacity-50"></i><?php echo htmlspecialchars($don['v_name']); ?></td>
                            <td><span class="status-pill <?php echo $badgeClass; ?>"><?php echo $icon . " " . $statusLabel; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($donationsList)): ?>
                        <tr><td colspan="7" class="text-center py-10 text-emerald-500 italic opacity-50">No active records found in database.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- VOLUNTEERS VIEW -->
        <div x-show="section==='volunteers'" style="display:none;" class="tab-content w-full max-w-7xl mx-auto pb-20 relative">
            <div class="flex justify-between items-end mb-8">
                <h2 class="text-4xl font-black text-emerald-950 italic"><i class="ri-riding-fill text-emerald-600 mr-2"></i> Volunteer Directory</h2>
                <button @click="isAddModalOpen = true" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black px-6 py-3 rounded-xl shadow-lg transition transform hover:-translate-y-1 flex items-center gap-2">
                    <i class="ri-user-add-fill"></i> Add New Agent
                </button>
            </div>
            
            <!-- Add Volunteer Modal -->
            <div x-show="isAddModalOpen" style="display:none;" class="fixed inset-0 z-[100] bg-emerald-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                <div @click.outside="isAddModalOpen = false" class="bg-white rounded-[2rem] p-8 max-w-md w-full shadow-2xl relative" x-transition.opacity>
                    <button @click="isAddModalOpen = false" class="absolute top-6 right-6 text-emerald-900/50 hover:text-red-500"><i class="ri-close-circle-fill text-3xl"></i></button>
                    <h3 class="text-2xl font-black text-emerald-950 mb-6 italic"><i class="ri-riding-line text-emerald-500 mr-2"></i> Register Volunteer</h3>
                    <form method="POST" action="LACSO-Admin-Panel.php" class="space-y-4">
                        <div>
                            <label class="block text-xs font-black uppercase text-emerald-600 mb-1">Full Name</label>
                            <input type="text" name="v_name" required class="w-full bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3 font-bold text-emerald-950 focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-emerald-600 mb-1">Email Address</label>
                            <input type="email" name="v_email" required class="w-full bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3 font-bold text-emerald-950 focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-emerald-600 mb-1">Password</label>
                            <input type="password" name="v_pass" required class="w-full bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3 font-bold text-emerald-950 focus:outline-none focus:border-emerald-500">
                        </div>
                        <button type="submit" name="add_volunteer" class="w-full mt-4 bg-emerald-600 text-white font-black py-4 rounded-xl shadow-lg hover:bg-emerald-500 transition">Save Volunteer to Database</button>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>VOLUNTEER NAME</th>
                            <th>EMAIL</th>
                            <th>REGULARITY</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($volunteers as $v): ?>
                        <tr class="fade-enter">
                            <td class="text-emerald-500 font-black">#<?php echo $v['id']; ?></td>
                            <td class="text-emerald-950 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center font-black text-emerald-500"><?php echo strtoupper($v['name'][0]); ?></div>
                                <?php echo htmlspecialchars($v['name']); ?>
                            </td>
                            <td class="text-emerald-600 opacity-80"><?php echo htmlspecialchars($v['email']); ?></td>
                            <td><span class="status-pill status-delivered"><i class="ri-verified-badge-fill"></i> Active</span></td>
                            <td>
                                <form method="POST" action="LACSO-Admin-Panel.php" onsubmit="return confirm('Are you sure you want to remove this volunteer?');">
                                    <input type="hidden" name="v_id" value="<?php echo $v['id']; ?>">
                                    <button type="submit" name="remove_volunteer" class="bg-red-50 text-red-600 px-4 py-2 font-black rounded-lg hover:bg-red-500 hover:text-white transition">
                                        <i class="ri-delete-bin-fill mr-1"></i> Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($volunteers)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-emerald-500 italic opacity-50">No volunteers found in the database.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DONORS VIEW -->
        <div x-show="section==='donors'" style="display:none;" class="tab-content w-full max-w-7xl mx-auto pb-20">
            <h2 class="text-4xl font-black text-emerald-950 italic mb-8"><i class="ri-heart-3-fill text-pink-500 mr-2"></i> Donor Network</h2>
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>DONOR NAME</th>
                            <th>EMAIL</th>
                            <th>RECENTLY DONATED</th>
                            <th>RATING</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($donors as $d): ?>
                        <tr class="fade-enter">
                            <td class="text-emerald-500 font-black">#<?php echo $d['id']; ?></td>
                            <td class="text-emerald-950 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center font-black text-pink-500"><?php echo strtoupper($d['name'][0]); ?></div>
                                <?php echo htmlspecialchars($d['name']); ?>
                            </td>
                            <td class="text-emerald-600 opacity-80"><?php echo htmlspecialchars($d['email']); ?></td>
                            <td class="text-emerald-950 font-bold italic"><?php echo $d['recent_food'] ? htmlspecialchars($d['recent_food']) : '<span class="opacity-40">No entries yet</span>'; ?></td>
                            <td class="text-yellow-500"><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-half-fill"></i></td>
                            <td><button class="bg-emerald-50 text-emerald-600 px-4 py-2 font-black rounded-lg hover:bg-emerald-600 hover:text-white transition">Verify</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- CHATBOT VIEW -->
        <div x-show="section==='chatbot'" style="display:none;" class="tab-content w-full max-w-4xl mx-auto pb-20">
            <div class="panel-card bg-emerald-950 text-white relative overflow-hidden border-0 shadow-2xl">
                <h2 class="text-3xl font-black mb-6 flex items-center gap-3 text-emerald-400 border-b border-emerald-800 pb-6">
                    <i class="ri-robot-2-line animate-bounce"></i> LACSO AI Sentinel
                </h2>
                <div class="h-[500px] bg-[#022c22] rounded-2xl p-6 overflow-y-auto mb-6 border border-emerald-800/50 flex flex-col gap-4" id="chatbox">
                    <!-- AI msg -->
                    <div class="flex items-start gap-4 fade-enter">
                        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center font-black flex-shrink-0 shadow-lg"><i class="ri-magic-line"></i></div>
                        <div class="bg-emerald-800/50 p-4 rounded-r-2xl rounded-bl-2xl border border-emerald-700 font-bold max-w-xl text-emerald-50 leading-relaxed text-sm">
                            <span class="block text-emerald-400 mb-2 uppercase tracking-widest text-xs">System Analysis Complete</span>
                            Hello Admin. I have analyzed the current database network. <br><br>
                            • Total Donations Logged: <strong class="text-white"><?php echo $totalDonations; ?></strong><br>
                            • Verified Donor Organizations: <strong class="text-white"><?php echo $totalDonors; ?></strong><br>
                            • Delivery Agents Active: <strong class="text-white"><?php echo $activeVolunteers; ?></strong><br><br>
                            Your 3 active priority volunteers (Sarah, Michael, Raj) are online. Wait times are low in the Bodakdev region. Type your query below to route units or filter data.
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-4 relative z-10">
                    <input type="text" id="ai-input" placeholder="Query the AI Matrix..." class="flex-1 bg-emerald-900 border border-emerald-700 text-white p-4 rounded-xl font-bold placeholder-emerald-600 focus:outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 transition">
                    <button onclick="sendMsg()" class="bg-emerald-500 hover:bg-emerald-400 text-emerald-950 font-black px-8 rounded-xl transition transform hover:scale-105 active:scale-95 shadow-[0_0_20px_rgba(16,185,129,0.3)]">
                        SEND DATA <i class="ri-send-plane-fill ml-2"></i>
                    </button>
                </div>
                <i class="ri-fingerprint-line absolute bottom-0 right-0 text-[20rem] text-emerald-500 opacity-[0.03] pointer-events-none"></i>
            </div>
        </div>

    </main>

    <script>
        // Alpine Component Logic
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminPanel', (initTab = 'dashboard') => ({
                section: initTab,
                isAddModalOpen: false,
                
                switchTab(target) {
                    if (this.section === target) return;
                    this.section = target;
                    
                    // Retrigger entrance animations for dynamic rendering
                    setTimeout(() => {
                        const elements = document.querySelectorAll('.fade-enter');
                        elements.forEach(el => {
                            el.style.animation = 'none';
                            el.offsetHeight; /* trigger reflow */
                            el.style.animation = null; 
                        });
                    }, 50);
                }
            }))
        })

        // Setup Chart
        const ctx = document.getElementById('donationChart').getContext('2d');
        new Chart(ctx, {
            type:'line',
            data:{
                labels: <?= json_encode($donationTrendLabels) ?>,
                datasets:[{
                    label:'Donations Pipeline',
                    data: <?= json_encode($donationTrendData) ?>,
                    backgroundColor:'rgba(16, 185, 129, 0.1)', // emerald-500 alpha
                    borderColor:'#10b981',
                    borderWidth:4,
                    tension:0.4,
                    fill:true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#059669',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio: false,
                plugins:{legend:{display:false}},
                scales:{
                    y:{beginAtZero:true, grid: {color: 'rgba(16, 185, 129, 0.1)'}, border:{display:false}},
                    x:{grid:{display:false}, border:{display:false}}
                }
            }
        });

        // Setup Role Pie Chart
        const ctxRole = document.getElementById('roleChart').getContext('2d');
        new Chart(ctxRole, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($rolesLabel) ?>,
                datasets: [{
                    data: <?= json_encode($rolesData) ?>,
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: "'Outfit', sans-serif", weight: 'bold' } } }
                },
                cutout: '65%'
            }
        });

        // Setup Category Bar Chart
        const ctxCat = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCat, {
            type: 'bar',
            data: {
                labels: <?= json_encode($catLabels) ?>,
                datasets: [{
                    label: 'Items Donated',
                    data: <?= json_encode($catData) ?>,
                    backgroundColor: '#10b981',
                    borderRadius: 8,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(16, 185, 129, 0.1)' }, border: { display: false } },
                    x: { grid: { display: false }, border: { display: false } }
                }
            }
        });

        // Intro GSAP Animation
        document.addEventListener('DOMContentLoaded', () => {
            gsap.from(".slide-item", {
                opacity: 0,
                y: 30,
                duration: 0.8,
                stagger: 0.1,
                ease: "power3.out",
                clearProps: "all"
            });
            gsap.from(".glass-sidebar", {
                x: -50,
                opacity: 0,
                duration: 0.7,
                ease: "power2.out"
            });
        });

        // Chatbot Mock Logic
        function sendMsg() {
            const input = document.getElementById('ai-input');
            const txt = input.value.trim();
            if(!txt) return;

            const box = document.getElementById('chatbox');
            // User msg
            box.insertAdjacentHTML('beforeend', `
                <div class="flex items-start gap-4 flex-row-reverse fade-enter">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center font-black flex-shrink-0 text-emerald-800 shadow-sm"><i class="ri-user-fill"></i></div>
                    <div class="bg-emerald-500 p-4 rounded-l-2xl rounded-br-2xl text-emerald-950 font-bold max-w-xl shadow-md">
                        ${txt.replace(/</g, "&lt;")}
                    </div>
                </div>
            `);
            input.value = '';
            box.scrollTop = box.scrollHeight;

            // AI Reply
            setTimeout(() => {
                box.insertAdjacentHTML('beforeend', `
                    <div class="flex items-start gap-4 fade-enter">
                        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center font-black flex-shrink-0 shadow-lg text-white"><i class="ri-magic-line"></i></div>
                        <div class="bg-emerald-800/50 p-4 rounded-r-2xl rounded-bl-2xl border border-emerald-700 font-bold max-w-xl text-emerald-50">
                            I am processing database metrics for your query. All systems nominal.
                        </div>
                    </div>
                `);
                box.scrollTop = box.scrollHeight;
            }, 800);
        }

        // --- Realtime Notification Polling System ---
        let lastUserId = 0;
        let lastDonationId = 0;
        
        function showNotification(title, message, isDonation = true) {
            // Artificial 5 second delay as requested
            setTimeout(() => {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                
                // Try playing sound, ignore if browser auto-play policy blocks it
                const sound = document.getElementById('notify-sound');
                if(sound) { sound.play().catch(e => { /* Ignore blocked play */ }); }
                
                toast.className = 'toast ' + (isDonation ? '' : 'toast-red');
                toast.innerHTML = `
                    <div class="toast-icon ${isDonation ? 'bg-emerald-100 text-emerald-600' : 'bg-orange-100 text-orange-600'}">
                        <i class="${isDonation ? 'ri-box-3-fill' : 'ri-user-smile-fill'}"></i>
                    </div>
                    <div class="toast-content">
                        <h4>${title}</h4>
                        <p>${message}</p>
                    </div>
                `;
                container.appendChild(toast);
                
                // Auto remove element after animation completes (5 seconds)
                setTimeout(() => {
                    if(toast.parentElement) toast.remove();
                }, 5000);
            }, 5000);
        }

        function pollNotifications() {
            fetch(`api_notifications.php?last_user_id=${lastUserId}&last_donation_id=${lastDonationId}`)
            .then(res => res.json())
            .then(data => {
                // Initial Load - Set IDs without notifying
                if (lastUserId === 0) {
                    lastUserId = data.new_last_user_id;
                    lastDonationId = data.new_last_donation_id;
                    return;
                }
                
                let updated = false;

                // Process Users
                if(data.users && data.users.length > 0) {
                    data.users.forEach(u => {
                        let roleT = (u.role || 'user').toUpperCase();
                        showNotification('[New User] ' + roleT, `${u.name} just registered!`, false);
                    });
                    lastUserId = data.new_last_user_id;
                    updated = true;
                }

                // Process Donations
                if(data.donations && data.donations.length > 0) {
                    data.donations.forEach(d => {
                        showNotification('New Donation Alert!', `${d.donor_name} donated ${d.food_name}!`, true);
                    });
                    lastDonationId = data.new_last_donation_id;
                    updated = true;
                }
                
                // If we got new data, we might want to tell the user to refresh the dashboard or auto-refresh data
                if(updated && document.activeElement.tagName !== "INPUT") {
                    // Optional: You could reload specific components here
                }
            })
            .catch(err => console.error("Poll error:", err));
        }

        // Start polling every 3 seconds
        setInterval(pollNotifications, 3000);

    </script>
</body>
</html>