<?php
session_start();
include 'db.php';

// Only volunteers allowed
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='volunteer'){
    header("Location: login.php");
    exit;
}

$volunteerName = $_SESSION['user_name'];

// Handle AJAX requests
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
    $donationId = intval($_POST['donation_id']);
    $action = $_POST['action'];

    if($action === 'accept'){
        $v_id = $_SESSION['user_id'];
        $v_name = $_SESSION['user_name'] ?? 'Volunteer 1';
        $stmt = $conn->prepare("UPDATE donations SET status='Accepted', volunteer_id=?, volunteer_name=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("isi", $v_id, $v_name, $donationId);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("UPDATE donations SET status='Accepted', volunteer_id=? WHERE id=?");
            $stmt->bind_param("ii", $v_id, $donationId);
            $stmt->execute();
        }
        echo "success";
        exit;
    }

    if($action === 'delete'){
        $stmt = $conn->prepare("DELETE FROM donations WHERE id=?");
        $stmt->bind_param("i", $donationId);
        if($stmt->execute()){
            echo "success";
        } else {
            echo "error: ".$stmt->error;
        }
        exit;
    }
}

// Auto-ensure required columns exist in donations table to prevent SQL errors
@$conn->query("ALTER TABLE donations ADD COLUMN user_id INT DEFAULT 0");
@$conn->query("ALTER TABLE donations ADD COLUMN donor_id INT DEFAULT 0");
@$conn->query("ALTER TABLE donations ADD COLUMN donor_name VARCHAR(100) DEFAULT 'Donor'");
@$conn->query("ALTER TABLE donations ADD COLUMN volunteer_name VARCHAR(100) DEFAULT NULL");

$hasUserId = $conn->query("SHOW COLUMNS FROM donations LIKE 'user_id'")->num_rows > 0;
$hasDonorId = $conn->query("SHOW COLUMNS FROM donations LIKE 'donor_id'")->num_rows > 0;

$joinCond = "1=1";
if ($hasUserId && $hasDonorId) {
    $joinCond = "(d.user_id = u.id OR d.donor_id = u.id)";
} else if ($hasUserId) {
    $joinCond = "d.user_id = u.id";
} else if ($hasDonorId) {
    $joinCond = "d.donor_id = u.id";
}

// Fetch donations for volunteer
$sql = "SELECT d.id, IFNULL(d.donor_name, u.name) AS donor_name, d.food_name, d.quantity, d.status, d.created_at, d.pickup_address, d.drop_address, d.category, d.serves
        FROM donations d
        LEFT JOIN users u ON $joinCond
        ORDER BY d.created_at DESC";
$donationsResult = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard | LACSO</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #e9f5ee; /* Slightly darker green background to make cards pop */
            color: #064e3b;
        }
        .glass-nav {
            background: #ffffff;
            border-bottom: 2px solid #d1fae5;
            backdrop-filter: none;
        }
        .donor-card, .reward-card {
            background: #ffffff !important;
            border: 2px solid #d1fae5 !important;
            transition: all 0.3s ease;
            opacity: 1 !important; /* Ensure full opacity */
        }
        .donor-card:hover, .reward-card:hover {
            transform: translateY(-5px);
            border-color: #10b981 !important;
            box-shadow: 0 15px 30px -10px rgba(6, 78, 59, 0.1);
        }
        .status-badge {
            padding: 6px 16px;
            border-radius: 99px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-accepted { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }

        @keyframes pulse-soft {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        .animate-pulse-soft { animation: pulse-soft 3s infinite; }
    </style>
</head>
<body class="min-h-screen">

    <!-- Navigation -->
    <nav class="glass-nav sticky top-0 z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="ri-hand-heart-fill text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-emerald-900 border-none">LACSO</h1>
            </div>
            
            <div class="hidden md:flex items-center gap-8 font-medium">
                <a href="#" class="text-emerald-700 hover:text-emerald-500 transition-colors">Dashboard</a>
                <a href="mytask.html" class="text-emerald-700 hover:text-emerald-500 transition-colors">Tasks</a>
                <a href="History.html" class="text-emerald-700 hover:text-emerald-500 transition-colors">Log</a>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-emerald-900"><?php echo htmlspecialchars($volunteerName); ?></p>
                    <p class="text-[10px] uppercase tracking-widest text-emerald-600 font-semibold leading-none">Verified Volunteer</p>
                </div>
                <div class="w-10 h-10 rounded-full border-2 border-emerald-200 overflow-hidden shadow-sm">
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?php echo urlencode($volunteerName); ?>" alt="avatar">
                </div>
                <a href="logout.php" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Logout">
                    <i class="ri-logout-box-r-line text-xl"></i>
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">
        
        <!-- Welcome Header -->
        <header class="mb-12 relative overflow-hidden bg-emerald-900 rounded-[2rem] p-10 text-white shadow-2xl animate-pulse-soft">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-center md:text-left">
                    <p class="text-3xl font-black text-white"><?php echo htmlspecialchars($volunteerName); ?></p>
                    <p class="text-emerald-100 opacity-80 max-w-md">Your contribution today can help feed dozens of families. Ready to make an impact?</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl text-center border border-white/10">
                        <p class="text-2xl font-bold">12</p>
                        <p class="text-[10px] uppercase opacity-70">Pickups Done</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl text-center border border-white/10">
                        <p class="text-2xl font-bold text-emerald-400">8.4k</p>
                        <p class="text-[10px] uppercase opacity-70">Points</p>
                    </div>
                </div>
            </div>
            <!-- Abstract background shape -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500 opacity-20 blur-[100px] rounded-full -mr-20 -mt-20"></div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-emerald-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center">
                    <i class="ri-history-line text-xl"></i>
                </div>
                <div>
                    <p class="text-emerald-900/50 text-xs font-bold uppercase tracking-wider">Quick Task</p>
                    <p class="text-lg font-bold">Manage Schedule</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-emerald-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">
                    <i class="ri-award-line text-xl"></i>
                </div>
                <div>
                    <p class="text-emerald-900/50 text-xs font-bold uppercase tracking-wider">Achievement</p>
                    <p class="text-lg font-bold">Get Certificate</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-emerald-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center">
                    <i class="ri-team-line text-xl"></i>
                </div>
                <div>
                    <p class="text-emerald-900/50 text-xs font-bold uppercase tracking-wider">Community</p>
                    <p class="text-lg font-bold">Leaderboard</p>
                </div>
            </div>
        </div>

        <!-- Rewards & Recognition Section -->
        <section class="mb-12">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-2xl font-bold text-emerald-900">Rewards & Recognition 🎖️</h3>
                    <p class="text-sm text-emerald-600/70">Milestones achieved this month.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Monthly Certification -->
                <div class="reward-card bg-gradient-to-br from-emerald-600 to-emerald-800 p-8 rounded-[2rem] text-white shadow-xl relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                                <i class="ri-medal-fill text-xl"></i>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest opacity-80">Monthly Achiever</span>
                        </div>
                        <h4 class="text-2xl font-bold mb-2">Certification of Honor</h4>
                        <p class="text-emerald-100/70 text-sm mb-6 max-w-sm">Congratulations! You've successfully completed the monthly food safety and delivery program.</p>
                        <button onclick="generateCertificate()" class="bg-white text-emerald-900 font-bold px-6 py-3 rounded-xl hover:bg-emerald-50 transition flex items-center gap-2 group-hover:scale-105">
                            <i class="ri-download-cloud-fill"></i> Download Certificate
                        </button>
                    </div>
                    <!-- Decorative back icon -->
                    <i class="ri-file-shield-2-line absolute -bottom-10 -right-10 text-[12rem] opacity-5"></i>
                </div>

                <!-- Monthly Prizes -->
                <div class="reward-card bg-white p-8 rounded-[2rem] border border-emerald-100 shadow-sm relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center">
                                <i class="ri-gift-fill text-xl"></i>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-emerald-400">Current Prize</span>
                        </div>
                        <h4 class="text-2xl font-bold text-emerald-900 mb-1">Silver Food Hero Badge</h4>
                        <p class="text-emerald-600/70 text-sm">Unlocked at 10 successful deliveries. You are only 2 away!</p>
                    </div>

                    <div class="mt-6 flex items-center justify-between bg-emerald-50 p-4 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <i class="ri-coupon-2-fill text-2xl text-emerald-600"></i>
                            <p class="text-xs font-bold text-emerald-900">Food Voucher ₹500<br><span class="opacity-50">Pending Unlock</span></p>
                        </div>
                        <div class="w-16 bg-emerald-200 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-600 h-full w-[80%]"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Donations Feed Section -->
        <section>
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-2xl font-bold text-emerald-900">Available Donations</h3>
                    <p class="text-sm text-emerald-600/70">Find food pickups nearby and accept to start delivery.</p>
                </div>
                <div class="flex gap-2">
                    <button class="bg-white p-2 border border-emerald-100 rounded-lg hover:bg-emerald-50 text-emerald-600 transition-colors">
                        <i class="ri-filter-2-line"></i>
                    </button>
                    <button class="bg-white p-2 border border-emerald-100 rounded-lg hover:bg-emerald-50 text-emerald-600 transition-colors" onclick="window.location.reload()">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="donationsList">
                <?php if($donationsResult->num_rows > 0): ?>
                    <?php while($row = $donationsResult->fetch_assoc()): 
                        $rawStatus = $row['status'] ?? 'pending';
                        $status = ucfirst(strtolower($rawStatus));
                        $isAccepted = ($status === 'Accepted');
                    ?>
                        <article id="donation-<?php echo $row['id']; ?>" class="donor-card bg-white p-6 rounded-[2rem] border border-emerald-50 shadow-sm flex flex-col sm:flex-row gap-6">
                            <!-- Left: Food Info -->
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="status-badge <?php echo $isAccepted ? 'status-accepted' : 'status-pending'; ?>">
                                        <?php echo $status; ?>
                                    </span>
                                    <span class="text-[10px] text-emerald-300 font-bold uppercase tracking-tighter">ID #<?php echo $row['id']; ?></span>
                                </div>
                                <h4 class="text-2xl font-black text-emerald-950 mb-1"><?php echo htmlspecialchars($row['food_name']); ?></h4>
                                <div class="flex gap-4 text-xs font-semibold text-emerald-600 mb-4">
                                    <span class="bg-emerald-50 px-2 py-1 rounded-md"><i class="ri-scales-line mr-1"></i><?php echo htmlspecialchars($row['quantity']); ?></span>
                                    <span class="bg-emerald-50 px-2 py-1 rounded-md"><i class="ri-group-line mr-1"></i><?php echo htmlspecialchars($row['serves'] ?? 'N/A'); ?></span>
                                    <span class="bg-emerald-50 px-2 py-1 rounded-md capitalize"><i class="ri-leaf-line mr-1"></i><?php echo htmlspecialchars($row['category'] ?? 'N/A'); ?></span>
                                </div>
                                
                                <div class="space-y-2 mt-4 text-sm">
                                    <div class="flex items-start gap-2">
                                        <i class="ri-map-pin-user-fill text-emerald-400 mt-0.5"></i>
                                        <div>
                                            <p class="text-[10px] uppercase font-black text-emerald-400 leading-none mb-1">Pickup Address</p>
                                            <p class="text-emerald-950 font-bold"><?php echo htmlspecialchars($row['pickup_address'] ?? 'Address not specified'); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <i class="ri-community-fill text-blue-400 mt-0.5"></i>
                                        <div>
                                            <p class="text-[10px] uppercase font-black text-blue-400 leading-none mb-1">Drop Address</p>
                                            <p class="text-emerald-950 font-bold"><?php echo htmlspecialchars($row['drop_address'] ?? 'Nearby NGO / Center'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Actions & Donor -->
                            <div class="sm:w-48 flex flex-col justify-between border-t sm:border-t-0 sm:border-l border-emerald-50 pt-6 sm:pt-0 sm:pl-6">
                                <div class="mb-6">
                                    <p class="text-[10px] uppercase font-bold text-emerald-300 mb-2">Donor Details</p>
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-xs">
                                            <?php echo strtoupper(substr($row['donor_name'] ?? 'G', 0, 1)); ?>
                                        </div>
                                        <p class="font-bold text-emerald-900 text-sm"><?php echo htmlspecialchars($row['donor_name'] ?? 'Guest Donor'); ?></p>
                                    </div>
                                    <?php if (!empty($row['donor_phone'])): ?>
                                    <div class="flex items-center gap-2 text-emerald-600">
                                        <i class="ri-phone-fill text-xs"></i>
                                        <p class="text-xs font-bold"><?php echo htmlspecialchars($row['donor_phone']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="space-y-2">
                                    <button onclick="acceptDonation(<?php echo $row['id']; ?>)" 
                                            id="accept-btn-<?php echo $row['id']; ?>"
                                            class="w-full bg-emerald-600 text-white font-bold py-3 rounded-xl hover:bg-emerald-700 transition shadow-md <?php echo $isAccepted ? 'opacity-50 pointer-events-none' : ''; ?>">
                                        Accept Task
                                    </button>
                                    <button onclick="deleteDonation(<?php echo $row['id']; ?>)"
                                            class="w-full text-red-500 font-bold py-2 text-xs hover:text-red-700 transition">
                                        Remove Listing
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="lg:col-span-2 bg-white rounded-3xl p-20 text-center border-2 border-dashed border-emerald-100">
                        <i class="ri-ghost-smile-line text-6xl text-emerald-100 mb-4 inline-block"></i>
                        <h4 class="text-xl font-bold text-emerald-900">No donations found!</h4>
                        <p class="text-emerald-600/60">Wait for donors to list new food items.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- Notification -->
    <div id="notification" class="fixed bottom-8 right-8 translate-y-20 opacity-0 bg-emerald-900 text-white px-8 py-4 rounded-2xl shadow-2xl flex items-center gap-3 transition-all duration-500 z-[100]">
        <i class="ri-checkbox-circle-fill text-emerald-400 text-xl"></i>
        <p id="notif-text" class="font-bold"></p>
    </div>

    <script>
        let trackingInterval = null;

        function showNotification(text) {
            const notif = document.getElementById('notification');
            const inner = document.getElementById('notif-text');
            inner.innerText = text;
            notif.classList.remove('translate-y-32', 'opacity-0');
            setTimeout(() => {
                notif.classList.add('translate-y-32', 'opacity-0');
            }, 3500);
        }

        async function acceptDonation(id) {
            const btn = document.getElementById(`accept-btn-${id}`);
            if (btn) btn.innerText = "Accepting...";

            try {
                const response = await fetch("", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=accept&donation_id=${id}`
                });
                
                const result = await response.text();
                if (result.trim() === "success") {
                    showNotification("Order Accepted! Track your journey.");
                    if(btn) {
                        btn.classList.add('opacity-40', 'pointer-events-none');
                        btn.innerText = "Task Ongoing";
                    }
                    const card = document.getElementById(`donation-${id}`);
                    if (card) {
                        const badge = card.querySelector('.status-badge');
                        if (badge) {
                            badge.innerText = "Accepted";
                            badge.classList.replace('status-pending', 'status-accepted');
                        }
                    }
                    startLiveTracking(id);
                } else {
                    alert("System Error: " + result);
                    if(btn) btn.innerText = "Accept Task";
                }
            } catch (error) { 
                console.error(error); 
                if(btn) btn.innerText = "Accept Task";
            }
        }

        function startLiveTracking(donationId) {
            if (trackingInterval) clearInterval(trackingInterval);
            
            console.log("Starting Live Tracking for Donation #" + donationId);
            
            trackingInterval = setInterval(() => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(position => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        // Send to update_location.php
                        fetch("update_location.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `donation_id=${donationId}&lat=${lat}&lng=${lng}`
                        }).then(r => r.text()).then(txt => console.log("Location Sync: " + txt));
                    }, err => console.error("Tracking Error:", err), {
                        enableHighAccuracy: true
                    });
                }
            }, 10000); // Update every 10 seconds
        }

        async function deleteDonation(id) {
            if(!confirm("Are you sure you want to remove this listing?")) return;
            
            try {
                const response = await fetch("", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=delete&donation_id=${id}`
                });
                
                const result = await response.text();
                if (result.trim() === "success") {
                    gsap.to(`#donation-${id}`, {
                        opacity: 0,
                        x: 50,
                        duration: 0.5,
                        onComplete: () => document.getElementById(`donation-${id}`).remove()
                    });
                    showNotification("🗑️ Listing removed from your feed.");
                }
            } catch (error) { console.error(error); }
        }

        // Dashboard animations on load
        window.addEventListener('load', () => {
            gsap.from(".donor-card, .reward-card", {
                opacity: 0,
                y: 20,
                stagger: 0.1,
                duration: 0.8,
                ease: "power2.out"
            });
        });

        // Certificate generation logic
        function generateCertificate() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'landscape',
                unit: 'px',
                format: [600, 450]
            });

            const user = "<?php echo htmlspecialchars($volunteerName); ?>";
            const date = new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

            // Background simple border
            doc.setDrawColor(5, 150, 105);
            doc.setLineWidth(10);
            doc.rect(20, 20, 560, 410);

            // Text
            doc.setTextColor(5, 150, 105);
            doc.setFontSize(40);
            doc.text("LACSO HERO", 300, 100, { align: 'center' });
            
            doc.setTextColor(30, 41, 59);
            doc.setFontSize(22);
            doc.text("Certificate of Honor", 300, 140, { align: 'center' });

            doc.setFontSize(16);
            doc.text("This is to certify that", 300, 190, { align: 'center' });

            doc.setFontSize(32);
            doc.setTextColor(5, 150, 105);
            doc.text(user, 300, 240, { align: 'center' });

            doc.setTextColor(30, 41, 59);
            doc.setFontSize(14);
            doc.text("has shown exceptional commitment in reducing food waste", 300, 280, { align: 'center' });
            doc.text("during the month of " + date, 300, 305, { align: 'center' });

            doc.setFontSize(12);
            doc.text("Signed: LACSO Management Team", 480, 400, { align: 'right' });

            doc.save(`${user.replace(/\s+/g, '_')}_Monthly_Certificate.pdf`);
            showNotification("📄 Certificate generated successfully!");
        }
    </script>
</body>
</html>
