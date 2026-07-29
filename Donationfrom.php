<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Donation Form | LACSO</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #064e3b 0%, #059669 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        .input-field {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            transition: all 0.3s ease;
        }

        .input-field:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #34d399;
            box-shadow: 0 0 0 2px rgba(52, 211, 153, 0.2);
            outline: none;
        }

        .label-style {
            color: #d1fae5;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            display: block;
        }

        select option {
            background: #065f46;
            color: white;
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.1);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <div class="glass-card w-full max-w-lg rounded-3xl p-8 transform transition-all duration-500">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2 tracking-tight">Donate Food</h1>
            <p class="text-emerald-100 text-sm opacity-80">Your kindness can fill someone's empty plate today.</p>
        </div>

        <form action="save_donation.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Donor Name -->
                <div>
                    <label class="label-style">Donor Name</label>
                    <input type="text" name="donor_name" placeholder="Your full name" required
                        class="input-field w-full px-4 py-3 rounded-xl placeholder-emerald-100 placeholder-opacity-30">
                </div>

                <!-- Phone Number -->
                <div>
                    <label class="label-style">Phone Number</label>
                    <input type="tel" name="donor_phone" placeholder="Your contact number" required
                        class="input-field w-full px-4 py-3 rounded-xl placeholder-emerald-100 placeholder-opacity-30">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Food Name -->
                <div>
                    <label class="label-style">Food Item</label>
                    <select name="food_name" required class="input-field w-full px-4 py-3 rounded-xl appearance-none">
                        <option value="">Select Food</option>
                        <option value="Rice">Rice</option>
                        <option value="Bread">Bread</option>
                        <option value="Curry">Curry</option>
                        <option value="Vegetables">Vegetables</option>
                        <option value="Fruits">Fruits</option>
                        <option value="Packaged Food">Packaged Food</option>
                    </select>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="label-style">Quantity</label>
                    <select name="quantity" required class="input-field w-full px-4 py-3 rounded-xl appearance-none">
                        <option value="">Select Quantity</option>
                        <option value="1 Kg">1 Kg</option>
                        <option value="5 Kg">5 Kg</option>
                        <option value="10 Kg">10 Kg</option>
                        <option value="20 Packets">20 Packets</option>
                        <option value="50 Packets">50 Packets</option>
                    </select>
                </div>

                <!-- Serves -->
                <div>
                    <label class="label-style">Serves (Approx)</label>
                    <select name="serves" required class="input-field w-full px-4 py-3 rounded-xl appearance-none">
                        <option value="">Select</option>
                        <option value="10 People">10 People</option>
                        <option value="20 People">20 People</option>
                        <option value="50 People">50 People</option>
                        <option value="100 People+">100 People+</option>
                    </select>
                </div>

                <!-- Expiry -->
                <div>
                    <label class="label-style">Best Before</label>
                    <select name="expiry" required class="input-field w-full px-4 py-3 rounded-xl appearance-none">
                        <option value="">Select Expiry</option>
                        <option value="1 Day">1 Day</option>
                        <option value="2 Days">2 Days</option>
                        <option value="3 Days">3 Days</option>
                        <option value="1 Week">1 Week</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Category -->
                <div>
                    <label class="label-style">Category</label>
                    <select name="category" required class="input-field w-full px-4 py-3 rounded-xl appearance-none">
                        <option value="">Select Category</option>
                        <option value="Veg">Veg</option>
                        <option value="Non-Veg">Non-Veg</option>
                        <option value="Packaged">Packaged</option>
                    </select>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="label-style">Food Image</label>
                    <input type="file" name="food_image" class="w-full text-sm text-emerald-100 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-500 file:text-white hover:file:bg-emerald-600 transition-all" />
                </div>
            </div>

            <!-- New: Pickup Address -->
            <div>
                <label class="label-style">Pickup Address</label>
                <input type="text" name="pickup_address" placeholder="Enter full address for pickup" required
                    class="input-field w-full px-4 py-3 rounded-xl placeholder-emerald-100 placeholder-opacity-30">
            </div>

            <!-- New: Drop Address -->
            <div>
                <label class="label-style">Drop Address (Optional)</label>
                <input type="text" name="drop_address" placeholder="e.g. Community Centre, Mother Teresa NGO"
                    class="input-field w-full px-4 py-3 rounded-xl placeholder-emerald-100 placeholder-opacity-30">
            </div>

            <!-- Notes -->
            <div>
                <label class="label-style">Additional Notes</label>
                <textarea name="notes" rows="2" placeholder="Any special instructions?"
                    class="input-field w-full px-4 py-3 rounded-xl placeholder-emerald-100 placeholder-opacity-30 resize-none"></textarea>
            </div>

            <!-- Submit -->
            <div class="pt-4">
                <button type="submit"
                    class="w-full bg-emerald-400 hover:bg-emerald-300 text-emerald-950 font-bold py-4 rounded-2xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Confirm Donation
                </button>
            </div>
        </form>

        <div class="text-center mt-6">
            <a href="donor.php" class="text-emerald-200 text-sm hover:text-white transition-colors flex items-center justify-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

</body>
</html>
l>