<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$userName = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Food Donation Form</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
</head>
<body class="bg-green-600 flex items-center justify-center min-h-screen relative">

  <div class="bg-green-500 p-6 rounded-2xl shadow-lg w-full max-w-sm animate-fadeIn">
    <h1 class="text-2xl font-bold text-white text-center mb-2">Donate Food</h1>
    <p class="text-white text-center mb-4 text-sm">Fill details of food you want to donate</p>

    <form id="donationForm" class="space-y-3">

      <!-- Food Name -->
      <div>
        <label class="block text-white mb-1 text-sm">Food Name</label>
        <select class="w-full px-3 py-2 rounded-lg bg-green-400 text-white text-sm focus:outline-none">
          <option>Select Food</option>
          <option>Rice</option>
          <option>Bread</option>
          <option>Curry</option>
          <option>Vegetables</option>
          <option>Fruits</option>
          <option>Packaged Food</option>
        </select>
      </div>

      <!-- Quantity -->
      <div>
        <label class="block text-white mb-1 text-sm">Quantity</label>
        <select class="w-full px-3 py-2 rounded-lg bg-green-400 text-white text-sm focus:outline-none">
          <option>Select Quantity</option>
          <option>1 Kg</option>
          <option>5 Kg</option>
          <option>10 Kg</option>
          <option>20 Packets</option>
          <option>50 Packets</option>
        </select>
      </div>

      <!-- Serves -->
      <div>
        <label class="block text-white mb-1 text-sm">Serves (approx people)</label>
        <select class="w-full px-3 py-2 rounded-lg bg-green-400 text-white text-sm focus:outline-none">
          <option>Select</option>
          <option>10 People</option>
          <option>20 People</option>
          <option>50 People</option>
          <option>100 People</option>
        </select>
      </div>

      <!-- Expiry -->
      <div>
        <label class="block text-white mb-1 text-sm">Best Before (Expiry)</label>
        <select class="w-full px-3 py-2 rounded-lg bg-green-400 text-white text-sm focus:outline-none">
          <option>Select Expiry</option>
          <option>1 Day</option>
          <option>2 Days</option>
          <option>3 Days</option>
          <option>1 Week</option>
        </select>
      </div>

      <!-- Category -->
      <div>
        <label class="block text-white mb-1 text-sm">Category</label>
        <select class="w-full px-3 py-2 rounded-lg bg-green-400 text-white text-sm focus:outline-none">
          <option>Select Category</option>
          <option>Veg</option>
          <option>Non-Veg</option>
          <option>Packaged</option>
        </select>
      </div>

      <!-- Image Upload -->
      <div>
        <label class="block text-white mb-1 text-sm">Upload Food Image</label>
        <input type="file"
          class="w-full text-white text-sm file:mr-3 file:py-1 file:px-3
                 file:rounded-lg file:border-0
                 file:text-xs file:font-semibold
                 file:bg-white file:text-green-600
                 hover:file:bg-green-100" />
      </div>

      <!-- Notes -->
      <div>
        <label class="block text-white mb-1 text-sm">Additional Notes</label>
        <select class="w-full px-3 py-2 rounded-lg bg-green-400 text-white text-sm focus:outline-none">
          <option>Select Notes</option>
          <option>Freshly Cooked</option>
          <option>Leftovers</option>
          <option>Packaged (Sealed)</option>
          <option>Frozen</option>
        </select>
      </div>

      <!-- Submit -->
      <button type="submit"
        class="w-full bg-white text-green-600 font-bold py-2 rounded-lg hover:bg-green-200 transition text-sm">
        Donate Now
      </button>

    </form>

    <!-- Back -->
    <div class="text-center mt-3">
      <a href="donor.html" 
         class="text-white text-xs hover:underline inline-block transition-transform duration-300 hover:-translate-y-1 hover:scale-105">
        ← Back to Home
      </a>
    </div>
  </div>

  <!-- Notification -->
  <div id="notification" 
       class="hidden fixed top-4 right-4 bg-white text-green-600 px-4 py-2 rounded-lg shadow-lg text-sm font-semibold animate-slideIn">
    ✅ Food donation submitted successfully!
  </div>

  <style>
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }
    .animate-fadeIn {
      animation: fadeIn 0.6s ease-out;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateX(100%); }
      to { opacity: 1; transform: translateX(0); }
    }
    .animate-slideIn {
      animation: slideIn 0.5s ease-out;
    }
  </style>

  <script>
    const form = document.getElementById("donationForm");
    const notification = document.getElementById("notification");

    form.addEventListener("submit", function(e) {
      e.preventDefault(); // form submit stop
      notification.classList.remove("hidden");

      // 3 sec me hide
      setTimeout(() => {
        notification.classList.add("hidden");
      }, 3000);
    });
  </script>

</body>
</html>
