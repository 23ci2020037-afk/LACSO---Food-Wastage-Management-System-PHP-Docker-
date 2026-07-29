<?php
session_start();
include 'db.php';

$donation_id = $_GET['id'] ?? 0;

// Fetch basics
$stmt = $conn->prepare("SELECT d.*, u.name as volunteer_name FROM donations d LEFT JOIN users u ON d.volunteer_id = u.id WHERE d.id = ?");
$stmt->bind_param("i", $donation_id);
$stmt->execute();
$donation = $stmt->get_result()->fetch_assoc();

if (!$donation) {
    die("Donation not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Donation | LACSO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f0fdf4; }
        #map { height: 400px; border-radius: 2rem; border: 4px solid white; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1); }
        .tracking-card { backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.9); }
        .pulse { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        /* Hide the routing text instructions */
        .leaflet-routing-container { display: none !important; }
    </style>
</head>
<body class="p-6">
    <div class="max-w-2xl mx-auto">
        <header class="flex items-center justify-between mb-8">
            <a href="donor.php" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm hover:bg-emerald-50 transition-colors">
                <i class="ri-arrow-left-line text-emerald-600"></i>
            </a>
            <h1 class="text-xl font-black text-emerald-900">Track Volunteer Route</h1>
            <div class="w-10"></div>
        </header>

        <div id="map" class="mb-8"></div>

        <div class="tracking-card p-8 rounded-[2.5rem] shadow-xl border border-white">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center text-white text-3xl pulse">
                    <i class="ri-route-fill"></i>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-black text-emerald-400 tracking-widest">Live Route Status</p>
                    <h2 class="text-2xl font-black text-emerald-900" id="status-text">
                        <?php echo ucfirst($donation['status']); ?>
                    </h2>
                </div>
            </div>

            <div class="space-y-6 pt-6 border-t border-emerald-50">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <i class="ri-user-location-fill"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-emerald-300">Volunteer En Route</p>
                        <p class="text-lg font-bold text-emerald-950"><?php echo htmlspecialchars($donation['volunteer_name'] ?? 'Assigning...'); ?></p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                        <i class="ri-home-heart-fill"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-blue-300">Pickup Location</p>
                        <p class="text-lg font-bold text-emerald-950"><?php echo htmlspecialchars($donation['pickup_address'] ?? 'Your Default Address'); ?></p>
                    </div>
                </div>
            </div>

            <button onclick="window.location.reload()" class="w-full mt-8 bg-emerald-900 text-white font-black py-4 rounded-2xl hover:bg-emerald-800 transition shadow-lg flex items-center justify-center gap-2">
                <i class="ri-refresh-line"></i> Refresh Route Data
            </button>
        </div>
    </div>

    <script>
        const donationId = <?php echo $donation_id; ?>;
        let map, marker, routingControl;
        
        // Initial coordinates
        let lat = <?php echo (float)($donation['volunteer_lat'] ?: 23.0225); ?>;
        let lng = <?php echo (float)($donation['volunteer_lng'] ?: 72.5714); ?>;

        // Mock Pickup Location (approx 2-3km away for demo demonstration)
        const pickupLat = lat + 0.015;
        const pickupLng = lng - 0.010;

        function initMap() {
            map = L.map('map').setView([lat, lng], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const scooterIcon = L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/1048/1048329.png', // Delivery Scooter
                iconSize: [45, 45],
                iconAnchor: [22.5, 22.5]
            });

            const pickupIcon = L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/2558/2558066.png', // House/Pickup
                iconSize: [45, 45],
                iconAnchor: [22.5, 45]
            });

            // Create markers
            L.marker([pickupLat, pickupLng], {icon: pickupIcon}).addTo(map).bindPopup("<b>Pickup Location</b>");
            marker = L.marker([lat, lng], {icon: scooterIcon}).addTo(map).bindPopup("<b>Volunteer</b>");

            // Initialize Routing Machine to draw the path
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(lat, lng), // Start (Volunteer)
                    L.latLng(pickupLat, pickupLng) // End (Donor)
                ],
                routeWhileDragging: false,
                addWaypoints: false,
                show: false, // Hides the turn-by-turn text box
                createMarker: function() { return null; }, // Hide default routing markers
                lineOptions: {
                    styles: [{color: '#059669', opacity: 0.8, weight: 6, dashArray: '10, 10'}] // Emerald dashed line
                }
            }).addTo(map);
        }

        async function updateTracking() {
            try {
                const res = await fetch(`api_get_tracking.php?id=${donationId}`);
                const data = await res.json();
                
                if (data.success) {
                    const newPos = L.latLng(data.lat, data.lng);
                    
                    // Update Marker
                    marker.setLatLng(newPos);
                    
                    // Update Route Line dynamically
                    routingControl.setWaypoints([
                        newPos, 
                        L.latLng(pickupLat, pickupLng)
                    ]);

                    map.panTo(newPos);
                    document.getElementById('status-text').innerText = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                }
            } catch (e) { console.error("Update failed", e); }
        }

        initMap();
        // Poll for updates every 10 seconds
        setInterval(updateTracking, 10000);
    </script>
</body>
</html>
