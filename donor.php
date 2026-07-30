<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Redirect volunteers and admins to their respective dashboards
if (isset($_SESSION['role']) && $_SESSION['role'] === 'volunteer') {
    header("Location: voluntersssbtn.php");
    exit;
} elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: LACSO-Admin-Panel.php");
    exit;
}

$donor_id = $_SESSION['user_id'];

// Fetch actual Donor profile name from database
$userName = $_SESSION['user_name'] ?? 'Donor';
try {
    $resUserCheck = $conn->query("SELECT name, role FROM users WHERE id = $donor_id");
    if ($resUserCheck && $userDataRow = $resUserCheck->fetch_assoc()) {
        if (strtolower($userDataRow['role']) === 'volunteer') {
            header("Location: voluntersssbtn.php");
            exit;
        }
        $userName = $userDataRow['name'];
    }
} catch (Throwable $e) {}

// Auto-repair donations & users schema safely if columns are missing
try { $conn->query("ALTER TABLE donations ADD COLUMN user_id INT DEFAULT 0"); } catch (Throwable $e) {}
try { $conn->query("ALTER TABLE donations ADD COLUMN donor_id INT DEFAULT 0"); } catch (Throwable $e) {}
try { $conn->query("ALTER TABLE donations ADD COLUMN donor_name VARCHAR(100) DEFAULT 'Donor'"); } catch (Throwable $e) {}
try { $conn->query("ALTER TABLE donations ADD COLUMN volunteer_name VARCHAR(100) DEFAULT NULL"); } catch (Throwable $e) {}
try { $conn->query("ALTER TABLE users ADD COLUMN points INT DEFAULT 0"); } catch (Throwable $e) {}
try { $conn->query("ALTER TABLE users ADD COLUMN co2_saved DECIMAL(10,2) DEFAULT 0.00"); } catch (Throwable $e) {}

$escapedUser = $conn->real_escape_string($userName);
$whereClause = "(donor_id = $donor_id OR user_id = $donor_id OR LOWER(donor_name) = LOWER('$escapedUser'))";

// Stats (Strictly for logged-in donor)
$totalDonations = 0;
try {
    $res = $conn->query("SELECT COUNT(*) as total FROM donations WHERE $whereClause");
    if ($res && $row = $res->fetch_assoc()) {
        $totalDonations = (int)$row['total'];
    }
} catch (Throwable $e) {}

$peopleFed = $totalDonations > 0 ? $totalDonations * 15 : 0;
$foodSaved = $totalDonations > 0 ? number_format($totalDonations * 2.5, 2) : "0.00";
$userPoints = 0;

try {
    $hasPoints = $conn->query("SHOW COLUMNS FROM users LIKE 'points'")->num_rows > 0;
    if ($hasPoints) {
        $resUser = $conn->query("SELECT points, co2_saved FROM users WHERE id = $donor_id");
        if ($resUser && $userData = $resUser->fetch_assoc()) {
            $userPoints = (int)($userData['points'] ?? 0);
            if (!empty($userData['co2_saved']) && (float)$userData['co2_saved'] > 0) {
                $foodSaved = number_format($userData['co2_saved'], 2);
            }
        }
    }
} catch (Throwable $e) {}

// Fetch Donations (Strictly for logged-in donor)
$hasVolId = $conn->query("SHOW COLUMNS FROM donations LIKE 'volunteer_id'")->num_rows > 0;
if ($hasVolId) {
    $sql = "SELECT d.*, IFNULL(d.volunteer_name, IFNULL(u.name, 'Volunteer 2')) AS volunteer_name 
            FROM donations d 
            LEFT JOIN users u ON d.volunteer_id = u.id 
            WHERE $whereClause 
            ORDER BY d.id DESC";
} else {
    $sql = "SELECT d.*, 'Volunteer 2' AS volunteer_name 
            FROM donations d 
            WHERE $whereClause 
            ORDER BY d.id DESC";
}
$donations = [];
try {
    $donationsResult = $conn->query($sql);
    if ($donationsResult && $donationsResult->num_rows > 0) {
        while($row = $donationsResult->fetch_assoc()) $donations[] = $row;
    }
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard | LACSO</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #e9f5ee; /* Back to Emerald-tinted background */
            color: #064e3b;
        }
        .main-nav {
            background: #ffffff;
            border-bottom: 2px solid #d1fae5;
        }
        .stat-card, .history-card {
            background: #ffffff !important;
            border: 2px solid #d1fae5 !important;
            transition: all 0.3s ease;
        }
        .stat-card:hover, .history-card:hover {
            transform: translateY(-5px);
            border-color: #10b981 !important;
            box-shadow: 0 15px 30px -10px rgba(6, 78, 59, 0.1);
        }
        .btn-primary {
            background: #065f46; /* Back to Emerald primary */
            color: white;
            box-shadow: 0 8px 0 #064e3b;
            transition: all 0.1s;
        }
        .btn-primary:active {
            transform: translateY(4px);
            box-shadow: 0 4px 0 #064e3b;
        }
        .status-pill {
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-accepted { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .status-delivered { background: #ecfdf5; color: #047857; border: 1px solid #10b981; }

        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 0.5; }
            100% { transform: scale(1.3); opacity: 0; }
        }
        .live-pulse { position: relative; }
        .live-pulse::after {
            content: '';
            position: absolute;
            inset: -4px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse-ring 1.5s infinite;
        }
    </style>
</head>
<body class="min-h-screen">

    <!-- Navigation -->
    <nav class="main-nav sticky top-0 z-50 px-6 py-4 shadow-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="ri-hand-heart-fill text-xl"></i>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-emerald-900">LACSO</h1>
            </div>
            
            <div class="hidden md:flex items-center gap-8 font-bold">
                <a href="#" class="text-emerald-900 border-b-2 border-emerald-600">Home</a>
                <a href="Donationfrom.php" class="text-emerald-700 hover:text-emerald-900 transition-colors">Donate Now</a>
                <a href="History.php" class="text-emerald-700 hover:text-emerald-900 transition-colors">Logs</a>
                <a href="#" class="text-emerald-700 hover:text-emerald-900 transition-colors">Profile</a>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-emerald-900"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="text-[10px] uppercase font-bold text-emerald-500"><?php echo ($userPoints > 100) ? 'Gold Donor' : 'Silver Donor'; ?></p>
                </div>
                <a href="logout.php" class="w-10 h-10 rounded-full border-2 border-red-100 flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors">
                    <i class="ri-logout-box-r-line font-bold"></i>
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">
        
        <!-- Welcome Section -->
        <header class="mb-12 relative overflow-hidden bg-emerald-900 rounded-[2.5rem] p-12 text-white shadow-2xl">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="text-center md:text-left">
                    <h2 class="text-4xl md:text-5xl font-black mb-3 italic">Hi, <?php echo htmlspecialchars($userName); ?>! 👋</h2>
                    <p class="text-emerald-100 text-lg opacity-90 max-w-xl font-medium leading-relaxed">Your generosity fuels our mission to eliminate food waste and hunger. Thank you for your service.</p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="Donationfrom.php" class="btn-primary px-8 py-4 rounded-2xl font-black flex items-center gap-3 text-lg">
                            <i class="ri-add-circle-fill text-2xl"></i> Start New Donation
                        </a>
                        <div class="bg-white/10 backdrop-blur px-6 py-4 rounded-2xl border border-white/10 flex items-center gap-3">
                            <i class="ri-vip-crown-2-fill text-yellow-400 text-2xl"></i>
                            <span class="font-black"><?php echo $userPoints; ?> Impact Points</span>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:block w-64 h-64 bg-white/5 rounded-full border border-white/10 flex items-center justify-center p-8">
                    <i class="ri-earth-line text-[8rem] text-emerald-400 opacity-20"></i>
                </div>
            </div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500 opacity-20 blur-[120px] rounded-full -mr-32 -mt-32"></div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16">
            <div class="stat-card p-6 rounded-3xl flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center">
                    <i class="ri-box-3-fill text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-black text-emerald-400 mb-0.5">Total Contributions</p>
                    <p class="text-2xl font-black text-emerald-950 counter" data-target="<?php echo $totalDonations; ?>"><?php echo $totalDonations; ?></p>
                </div>
            </div>
            <div class="stat-card p-6 rounded-3xl flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">
                    <i class="ri-leaf-fill text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-black text-blue-400 mb-0.5">CO2 Emissions Saved (KG)</p>
                    <p class="text-2xl font-black text-indigo-950 counter" data-target="<?php echo $foodSaved; ?>"><?php echo $foodSaved; ?></p>
                </div>
            </div>
            <div class="stat-card p-6 rounded-3xl flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center">
                    <i class="ri-team-fill text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-black text-orange-400 mb-0.5">Lives Impacted</p>
                    <p class="text-2xl font-black text-emerald-950 counter" data-target="<?php echo $peopleFed; ?>"><?php echo $peopleFed; ?></p>
                </div>
            </div>
            <div class="stat-card p-6 rounded-3xl flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center">
                    <i class="ri-bar-chart-fill text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-black text-purple-400 mb-0.5">Community Rank</p>
                    <p class="text-2xl font-black text-emerald-950">#42</p>
                </div>
            </div>
        </div>

        <!-- Latest Status & Tracking Section -->
        <div class="grid lg:grid-cols-3 gap-8 mb-16">
            <div class="lg:col-span-2 history-card p-10 rounded-[2.5rem] relative overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-black text-emerald-950 flex items-center gap-3 italic">
                        <i class="ri-radar-line text-emerald-600 animate-pulse"></i> Live Order Monitor
                    </h3>
                    <?php if (!empty($donations)): ?>
                        <span class="text-xs font-black text-emerald-400 uppercase tracking-widest bg-emerald-50 px-4 py-1 rounded-full">Active Record</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($donations)): 
                    $latest = $donations[0]; 
                    $statusRaw = strtolower(trim($latest['status'] ?? 'pending'));
                    $isAccepted = (strpos($statusRaw, 'accept') !== false);
                    $isDelivered = (strpos($statusRaw, 'deliver') !== false || strpos($statusRaw, 'collect') !== false);
                    $isPending = (!$isAccepted && !$isDelivered);
                    $displayStatus = $isAccepted ? 'Accepted' : ($isDelivered ? 'Delivered' : 'Pending');
                    $volName = !empty($latest['volunteer_name']) ? $latest['volunteer_name'] : 'Volunteer 2';
                ?>
                    <div class="space-y-8">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div class="flex-1">
                                <p class="text-xs font-black text-emerald-400 mb-1 uppercase tracking-widest">Ongoing Donation #<?php echo $latest['id']; ?></p>
                                <h4 class="text-4xl font-black text-emerald-950 mb-3"><?php echo htmlspecialchars($latest['food_name']); ?></h4>
                                <div class="flex items-center gap-2">
                                    <span class="status-pill <?php 
                                        echo ($isAccepted ? 'status-accepted' : ($isDelivered ? 'status-delivered' : 'status-pending')); 
                                    ?>">
                                        <?php echo $displayStatus; ?>
                                    </span>
                                    <?php if ($isPending): ?>
                                        <span class="text-[10px] font-bold text-emerald-600 flex items-center gap-1">
                                            <i class="ri-loader-4-line animate-spin"></i> Searching for courier...
                                        </span>
                                    <?php elseif ($isAccepted): ?>
                                        <span class="text-[10px] font-bold text-emerald-600 flex items-center gap-1">
                                            <i class="ri-checkbox-circle-fill text-emerald-500"></i> Assigned to <?php echo htmlspecialchars($volName); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($isAccepted): ?>
                                <!-- High Visibility Tracking Card -->
                                <button onclick="location.href='tracking.php?id=<?php echo $latest['id']; ?>'" 
                                        class="w-full md:w-auto bg-emerald-600 p-8 rounded-[2rem] text-white shadow-[0_20px_40px_-10px_rgba(5,150,105,0.4)] flex items-center gap-6 group hover:bg-emerald-700 transition-all transform hover:-translate-y-1">
                                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center live-pulse">
                                        <i class="ri-map-pin-pulse-fill text-3xl"></i>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-[10px] uppercase font-black text-emerald-200 mb-1">Live Tracking</p>
                                        <p class="font-black text-2xl">Track Now <i class="ri-arrow-right-s-line ml-1"></i></p>
                                    </div>
                                </button>
                            <?php elseif ($isPending): ?>
                                <div class="w-full md:w-auto bg-emerald-50 border-2 border-emerald-100 p-8 rounded-[2rem] text-emerald-900 border-dashed text-center flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm">
                                        <i class="ri-user-search-fill text-emerald-600 text-xl"></i>
                                    </div>
                                    <p class="text-sm font-black opacity-60">Awaiting Volunteer<br>Acceptance...</p>
                                </div>
                            <?php elseif ($isDelivered): ?>
                                <div class="w-full md:w-auto bg-emerald-50 border-2 border-emerald-200 p-8 rounded-[2rem] text-emerald-900 text-center flex flex-col items-center gap-3 shadow-sm">
                                    <div class="w-12 h-12 bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-lg">
                                        <i class="ri-checkbox-circle-fill text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-black opacity-80">Order Delivered Successfully!</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Progress Line -->
                        <div class="relative pt-8 pb-4">
                            <div class="h-2 bg-emerald-100 rounded-full w-full">
                                <div class="h-full bg-emerald-600 rounded-full shadow-[0_0_15px_rgba(16,185,129,0.5)] transition-all duration-1000" 
                                     style="width: <?php echo ($isDelivered ? '100' : ($isAccepted ? '50' : '5')); ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center mt-6">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-4 h-4 rounded-full bg-emerald-600 border-4 border-white shadow-md"></div>
                                    <span class="text-[10px] font-black text-emerald-400 uppercase">Received</span>
                                </div>
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-4 h-4 rounded-full <?php echo $isAccepted || $isDelivered ? 'bg-emerald-600' : 'bg-emerald-200'; ?> border-4 border-white shadow-md"></div>
                                    <span class="text-[10px] font-black text-emerald-400 uppercase">Accepted</span>
                                </div>
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-4 h-4 rounded-full <?php echo $isDelivered ? 'bg-emerald-600' : 'bg-emerald-200'; ?> border-4 border-white shadow-md"></div>
                                    <span class="text-[10px] font-black text-emerald-400 uppercase">Delivered</span>
                                </div>
                            </div>
                        </div>

                        <?php if ($isAccepted): ?>
                            <div class="p-6 bg-emerald-50 rounded-3xl border-2 border-emerald-200 flex items-center justify-between shadow-md">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center text-white text-2xl shadow-md live-pulse">
                                        <i class="ri-truck-fill"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] uppercase font-black text-emerald-600 tracking-wider">Pickup Partner En Route</p>
                                        <p class="font-black text-xl text-emerald-950">
                                            🚚 <span class="text-emerald-700"><?php echo htmlspecialchars($latest['volunteer_name'] ?? 'Volunteer 1'); ?></span> is coming for pickup!
                                        </p>
                                        <p class="text-xs text-emerald-600 font-bold mt-1">Status: Accepted & Out for Delivery</p>
                                    </div>
                                </div>
                                <button onclick="location.href='tracking.php?id=<?php echo $latest['id']; ?>'" class="w-12 h-12 bg-emerald-900 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-emerald-800 transition" title="Track Map">
                                    <i class="ri-map-pin-2-fill text-xl"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="p-8 bg-emerald-50/70 rounded-3xl border-2 border-emerald-200 text-center space-y-4">
                        <div class="w-16 h-16 bg-emerald-600 text-white rounded-full flex items-center justify-center mx-auto text-3xl shadow-lg live-pulse">
                            <i class="ri-radar-line"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-black text-emerald-950">Live Order Monitor Standby</h4>
                            <p class="text-sm font-medium text-emerald-700 mt-1">Submit your first food donation to see real-time volunteer tracking and live status updates here!</p>
                        </div>
                        <a href="Donationfrom.php" class="inline-flex items-center gap-2 btn-primary px-6 py-3 rounded-2xl font-black text-sm">
                            <i class="ri-add-circle-fill text-lg"></i> Donate Food Now
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ad/Action Side -->
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-orange-400 to-orange-600 p-8 rounded-[2.5rem] text-white shadow-xl relative overflow-hidden group">
                    <h5 class="text-2xl font-black mb-2 italic">Earn Badges! 🏆</h5>
                    <p class="text-orange-50 font-medium mb-6 opacity-90 leading-snug">Reach 10 donations to unlock the "Silver Angel" badge.</p>
                    <div class="w-full bg-white/20 h-2 rounded-full mb-8 overflow-hidden">
                        <div class="bg-white h-full" style="width: 30%"></div>
                    </div>
                    <button class="w-full bg-white text-orange-600 font-black py-4 rounded-2xl shadow-lg group-hover:scale-105 transition-transform">
                        Explore Rewards
                    </button>
                    <i class="ri-service-fill absolute -bottom-10 -right-10 text-[10rem] opacity-5"></i>
                </div>
            </div>
        </div>

        <!-- History Section as Modern Feed -->
        <section class="mb-12">
            <div class="flex items-center gap-3 mb-10">
                <div class="w-2 h-8 bg-emerald-600 rounded-full"></div>
                <h3 class="text-3xl font-black text-emerald-950 italic">Recent Contributions</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($donations as $donation): 
                    $dStatus = ucfirst(strtolower($donation['status']));
                ?>
                <div class="history-card p-6 rounded-[2rem] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="status-pill <?php 
                                echo ($dStatus === 'Accepted' ? 'status-accepted' : ($dStatus === 'Delivered' ? 'status-delivered' : 'status-pending')); 
                            ?>"><?php echo $dStatus; ?></span>
                            <span class="text-[10px] font-bold text-emerald-300">#<?php echo $donation['id']; ?></span>
                        </div>
                        <h5 class="text-2xl font-black text-emerald-950 mb-1"><?php echo htmlspecialchars($donation['food_name']); ?></h5>
                        <p class="text-emerald-600 font-bold italic"><?php echo htmlspecialchars($donation['quantity']); ?> KG</p>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t-2 border-emerald-50 flex items-center justify-between">
                        <div class="flex items-center gap-3 text-emerald-900/40 text-xs font-bold">
                            <i class="ri-calendar-event-line"></i>
                            <span><?php echo $donation['accepted_at'] ? date('d M', strtotime($donation['accepted_at'])) : 'Waiting'; ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                             <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                                 <i class="ri-eye-line font-bold"></i>
                             </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <footer class="max-w-7xl mx-auto px-6 py-10 text-center border-t-2 border-emerald-50">
        <p class="text-emerald-950/40 text-sm font-black uppercase tracking-widest italic">© <?php echo date("Y"); ?> LACSO Food Management • Serving Kindness</p>
    </footer>

    <script>
        // Safely Animate Counters
        function animateCounter(el) {
            const rawVal = el.getAttribute('data-target');
            const target = parseInt(rawVal) || 0;
            const obj = { count: 0 };
            
            if (typeof gsap !== 'undefined') {
                gsap.to(obj, {
                    count: target,
                    duration: 2.5,
                    ease: "expo.out",
                    onUpdate: () => {
                        el.textContent = Math.round(obj.count).toLocaleString();
                    }
                });
            } else {
                el.textContent = target.toLocaleString(); // Fallback if GSAP fails
            }
        }

            // Poll every 5 seconds to auto-refresh status if pending
            const isPending = document.querySelector('.status-pending');
            if (isPending) {
                setInterval(() => {
                    fetch(window.location.href)
                        .then(r => r.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const newDoc = parser.parseFromString(html, 'text/html');
                            const newMonitor = newDoc.querySelector('.history-card');
                            const currentMonitor = document.querySelector('.history-card');
                            if (newMonitor && currentMonitor && newMonitor.innerHTML !== currentMonitor.innerHTML) {
                                currentMonitor.innerHTML = newMonitor.innerHTML;
                            }
                        })
                        .catch(e => console.error("Poll error", e));
                }, 5000);
            }
        // Web Audio Sound Chime Generator for Donor
        function playDonorCelebrationSound() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const now = ctx.currentTime;

                const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
                notes.forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, now + i * 0.12);
                    gain.gain.setValueAtTime(0.25, now + i * 0.12);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + i * 0.12 + 0.6);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(now + i * 0.12);
                    osc.stop(now + i * 0.12 + 0.6);
                });
            } catch (e) {}
        }

        function showDonorToast(msg) {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed top-6 right-6 z-[9999] flex flex-col gap-3 max-w-md';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = 'bg-emerald-900 text-white font-bold px-6 py-4 rounded-2xl shadow-2xl border-2 border-emerald-400 flex items-center gap-3 animate-bounce';
            toast.innerHTML = `<i class="ri-checkbox-circle-fill text-2xl text-emerald-400"></i> <span>${msg}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 6000);
        }

        let donorUserId = <?php echo (int)$donor_id; ?>;
        let knownAcceptedIds = new Set();
        let firstPollDone = false;

        async function pollDonorNotifications() {
            try {
                const res = await fetch(`api_notifications.php?donor_user_id=${donorUserId}`);
                const data = await res.json();

                if (data.accepted && data.accepted.length > 0) {
                    let newlyAccepted = false;
                    data.accepted.forEach(item => {
                        const key = item.id + '_' + item.status;
                        if (!knownAcceptedIds.has(key)) {
                            knownAcceptedIds.add(key);
                            if (firstPollDone) {
                                newlyAccepted = true;
                                playDonorCelebrationSound();
                                showDonorToast(`🎉 Great news! Volunteer ${item.volunteer_name || '2'} accepted your pickup for "${item.food_name}"!`);
                            }
                        }
                    });
                    if (newlyAccepted) {
                        setTimeout(() => window.location.reload(), 2000);
                    }
                }
                firstPollDone = true;
            } catch (e) {}
        }

        setInterval(pollDonorNotifications, 3000);
        pollDonorNotifications();
    </script>
</body>
</html>