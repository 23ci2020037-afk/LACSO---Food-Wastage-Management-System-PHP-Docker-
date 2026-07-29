<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ngo') {
    header("Location: login.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? 'NGO Partner';
$ngo_id = $_SESSION['user_id'];

// Fetch available pending donations that haven't been assigned an NGO yet
// If there isn't a target_ngo_id column natively, this gracefully falls back.
$hasNgoColumn = $conn->query("SHOW COLUMNS FROM donations LIKE 'target_ngo_id'")->num_rows > 0;

$sql = "SELECT * FROM donations WHERE lower(status) = 'pending'";
if ($hasNgoColumn) {
    $sql .= " AND (target_ngo_id IS NULL OR target_ngo_id = $ngo_id)";
}
$sql .= " ORDER BY id DESC LIMIT 10";

$donationsResult = $conn->query($sql);
$availableFood = [];
if ($donationsResult) {
    while($row = $donationsResult->fetch_assoc()) $availableFood[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGO Dashboard | LACSO</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #e9f5ee; color: #064e3b; }
        .nav-bar { background: #ffffff; border-bottom: 2px solid #d1fae5; }
        .food-card { transition: all 0.3s ease; }
        .food-card:hover { transform: translateY(-5px); border-color: #10b981; box-shadow: 0 15px 30px -10px rgba(6, 78, 59, 0.1); }
        .btn-claim { background: #065f46; color: white; transition: all 0.2s; box-shadow: 0 4px 0 #064e3b; }
        .btn-claim:active { transform: translateY(2px); box-shadow: 0 2px 0 #064e3b; }
    </style>
</head>
<body class="min-h-screen">
    <nav class="nav-bar sticky top-0 z-50 px-6 py-4 shadow-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="ri-community-fill text-xl"></i>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-emerald-900">LACSO<span class="text-xs text-emerald-500 ml-1 uppercase">NGO</span></h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="font-bold text-emerald-900 hidden sm:block"><?php echo htmlspecialchars($userName); ?></span>
                <a href="logout.php" class="w-10 h-10 rounded-full border-2 border-red-100 flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors">
                    <i class="ri-logout-box-r-line font-bold"></i>
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">
        <header class="mb-12 relative overflow-hidden bg-emerald-900 rounded-[2.5rem] p-12 text-white shadow-2xl">
            <div class="relative z-10">
                <h2 class="text-4xl md:text-5xl font-black mb-3 italic">Hello, <?php echo htmlspecialchars($userName); ?> 🏢</h2>
                <p class="text-emerald-100 text-lg opacity-90 max-w-xl font-medium">View surplus food in your connected zones and request a supply drop directly to your orphanage or community center.</p>
                <button onclick="alert('Support Request Sent to Admin! They will assign the next available food batch to your address.')" class="mt-6 bg-white text-emerald-900 font-black px-6 py-3 rounded-xl shadow-lg hover:bg-emerald-50 transition">
                    <i class="ri-megaphone-fill"></i> Request Daily Supply
                </button>
            </div>
            <i class="ri-building-4-line absolute top-0 right-10 text-[15rem] text-emerald-400 opacity-20 -mt-10"></i>
        </header>

        <section>
            <h3 class="text-3xl font-black text-emerald-950 italic mb-8"><i class="ri-map-pin-2-fill text-emerald-500"></i> Local Surplus Available</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($availableFood)): ?>
                    <div class="col-span-full p-10 bg-emerald-50 rounded-3xl border-2 border-emerald-100 border-dashed text-center text-emerald-700 font-bold italic">
                        No pending surplus food in your area right now. Rest assured our volunteers are working hard!
                    </div>
                <?php else: ?>
                    <?php foreach ($availableFood as $food): ?>
                        <div class="bg-white p-6 rounded-3xl border-2 border-emerald-100 food-card">
                            <div class="flex justify-between items-start mb-4">
                                <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full uppercase"><?php echo htmlspecialchars($food['category'] ?? 'General'); ?></span>
                                <span class="text-xs text-emerald-400 font-bold"><i class="ri-time-line"></i> <?php echo date('H:i', strtotime($food['created_at'])); ?></span>
                            </div>
                            <h4 class="text-2xl font-black text-emerald-950 mb-1"><?php echo htmlspecialchars($food['food_name']); ?></h4>
                            <p class="text-emerald-600 font-bold mb-4"><i class="ri-scales-3-line"></i> <?php echo htmlspecialchars($food['quantity']); ?> KG</p>
                            
                            <div class="bg-emerald-50 rounded-xl p-3 mb-6">
                                <p class="text-xs font-bold text-emerald-800"><i class="ri-user-heart-fill"></i> Donor: <?php echo htmlspecialchars($food['donor_name']); ?></p>
                                <p class="text-xs font-bold text-emerald-800 mt-1"><i class="ri-map-pin-fill"></i> Origin: <?php echo htmlspecialchars($food['pickup_address']); ?></p>
                            </div>

                            <form method="POST" action="login.php" onsubmit="event.preventDefault(); alert('Food Claimed! A volunteer will be routed to your NGO address.'); this.closest('.food-card').style.display='none';">
                                <button type="submit" class="w-full btn-claim py-3 rounded-xl font-bold flex items-center justify-center gap-2">
                                    Claim Supply <i class="ri-arrow-right-circle-fill"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
