# 🥗 LACSO - Food Wastage Management System

LACSO is a full-stack web application designed to eliminate food waste and reduce hunger by connecting surplus food donors (restaurants, hotels, and individuals) with verified volunteers and NGOs/Receivers.

---

## ⚡ Quick Start with Docker (Recommended - 1 Command Setup)

Run this application on **ANY laptop/PC** without manually installing XAMPP, PHP, MySQL, or importing database tables!

### Prerequisites
- Install [Docker Desktop](https://www.docker.com/products/docker-desktop/) on your system.

### Running the App
1. **Clone the repository**:
   ```bash
   git clone https://github.com/YOUR_USERNAME/website_final_pages.git
   cd website_final_pages
   ```

2. **Start the containers** (Automatically sets up PHP, MySQL & imports database tables):
   ```bash
   docker compose up -d
   ```

3. **Access the application**:
   - 🌐 **Web App**: [http://localhost:8000](http://localhost:8000)
   - 🗄️ **phpMyAdmin (Database GUI)**: [http://localhost:8080](http://localhost:8080)

4. **Stop the containers** when finished:
   ```bash
   docker compose down
   ```

---

## 🛠️ Traditional Setup (XAMPP / WAMP)

If you prefer using XAMPP:
1. Move the repository folder into `C:\xampp\htdocs\website_final_pages`.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`) and import `database_setup.sql`.
3. Open browser: `http://localhost/website_final_pages/index.php`.

---

## 🌟 Key Features

### 🍱 1. Donor Portal (`donor.php` & `Donationfrom.php`)
- Easy food donation listing (Food Item, Quantity, Expiry, Pickup/Drop Address, Category, and Image upload).
- Live Order Monitor with status tracking (**Received ➔ Accepted ➔ Out for Delivery ➔ Delivered**).
- Real-time notification banner (**"🚚 Volunteer X is coming for pickup!"**).
- Impact dashboard (Total contributions, CO2 emissions saved, lives impacted, and impact points).

### 🚴 2. Volunteer Dashboard (`voluntersssbtn.php`)
- Available donations feed with quick **"Accept Task"** action.
- Live Geolocation tracking integration ([update_location.php](file:///c:/xampp/htdocs/website_final_pages/update_location.php)).
- PDF Certificate Generator for monthly milestones (built with `jsPDF`).
- Rewards and badges progress system.

### 🏢 3. NGO / Receiver Portal (`ngo.php`)
- View local surplus food items in nearby zones.
- Quick claim supply requests for orphanages and community centers.

### 🛡️ 4. Admin Command Center (`LACSO-Admin-Panel.php`)
- Real-time sound & visual toast alerts for new users and donations ([api_notifications.php](file:///c:/xampp/htdocs/website_final_pages/api_notifications.php)).
- Live Analytics Charts using Chart.js (Donation Influx Matrix, Network Demographics, Item Categories).
- Volunteer Directory Management (Add/Remove volunteers).
- Integrated **LACSO AI Sentinel** Chatbot module.

---

## 🔑 Default Testing Credentials

| Role | Username / Email | Password | Dashboard Page |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin` | `LACSO-Admin-Panel.php` |
| **Volunteer 1** | `volunteer1` | `vol123` | `voluntersssbtn.php` |
| **Volunteer 2** | `volunteer2` | `vol123` | `voluntersssbtn.php` |
| **Volunteer 3** | `volunteer3` | `vol123` | `voluntersssbtn.php` |
| **New Users** | Sign Up via Login page | *(User defined)* | Role-specific dashboard |

---

## 📄 License
Distributed under the MIT License.
