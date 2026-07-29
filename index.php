<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lacso - Food Wastage Management System</title>
<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<script>
tailwind.config = {
theme: {
extend: {
colors: {
primary: '#28a745',
secondary: '#ffc107'
},
borderRadius: {
'none': '0px',
'sm': '4px',
DEFAULT: '8px',
'md': '12px',
'lg': '16px',
'xl': '20px',
'2xl': '24px',
'3xl': '32px',
'full': '9999px',
'button': '8px'
}
}
}
}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
<style>
@keyframes slideUp {
from {
opacity: 0;
transform: translateY(20px);
}
to {
opacity: 1;
transform: translateY(0);
}
}
.hero-image {
transition: transform 0.5s ease-in-out;
}
.hero-image:hover {
transform: scale(1.05);
}
@keyframes float {
0%, 100% { transform: translateY(0px); }
50% { transform: translateY(-10px); }
}
.float-animation {
animation: float 3s ease-in-out infinite;
}
* {
cursor: none !important;
font-family: 'Poppins', sans-serif;
}
.cursor {
position: fixed;
top: 0;
left: 0;
width: 20px;
height: 20px;
background: linear-gradient(45deg, #28a745, #ffc107);
border-radius: 50%;
pointer-events: none;
z-index: 9999;
transition: all 0.1s ease;
mix-blend-mode: difference;
}
.cursor-hover {
transform: scale(1.5);
background: linear-gradient(45deg, #ffc107, #28a745);
}
.cursor-click {
transform: scale(0.8);
background: #ff4757;
}

/* Dark Mode Overrides */
.dark-mode {
background: #1a1a1a;
color: #ffffff;
}
.dark-mode .bg-white { background-color: #2d2d2d !important; color: #ffffff !important;}
.dark-mode .text-gray-900 { color: #ffffff !important; }
.dark-mode .text-gray-700 { color: #e0e0e0 !important; }
.dark-mode .text-gray-600 { color: #bdbdbd !important; }
.dark-mode .bg-gray-50 { background-color: #242424 !important; }
.dark-mode .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.2) !important; }
.dark-mode nav.bg-white { background-color: #1a1a1a !important; box-shadow: 0 1px 3px 0 rgba(255, 255, 255, 0.1), 0 1px 2px 0 rgba(255, 255, 255, 0.06) !important; }
.dark-mode .testimonial-card, .dark-mode .step-card { border: 1px solid #3a3a3a; }
.dark-mode .badge-card { box-shadow: none !important; }

/* Existing Styles */
.step-card { transition: all 0.3s ease; }
.step-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(40, 167, 69, 0.2); }
.ripple { position: relative; overflow: hidden; }
.ripple::before {
content: '';
position: absolute;
top: 50%;
left: 50%;
width: 0;
height: 0;
border-radius: 50%;
background: rgba(255,255,255,0.5);
transition: width 0.6s, height 0.6s;
transform: translate(-50%, -50%);
}
.ripple:active::before {
width: 300px;
height: 300px;
}
</style>
</head>
<body class="transition-colors duration-500" id="body">
<div class="cursor" id="cursor"></div>

<div id="homepage" class="page-transition active">

<nav class="bg-white shadow-lg fixed w-full top-0 z-50 transition-colors duration-500">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <div class="flex items-center">
        <h1 class="text-2xl font-bold text-primary font-['Pacifico']">Lacso</h1>
      </div>
      <div class="hidden md:block">
        <div class="ml-10 flex items-center space-x-4">
          <a href="#home" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">Home</a>
          <a href="#about" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">About</a>
          <a href="#how" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">How It Works</a>
          <a href="volunteer.php" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">Volunteer</a>
          <a href="#blog" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">Blog</a>
          <a href="#contact" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors">Contact</a>
          <button id="darkModeToggle" class="text-gray-700 hover:text-primary p-2 text-xl transition-colors">
            <i id="darkModeIcon" class="ri-moon-line"></i>
          </button>
          <a href="login.php" class="bg-primary text-white px-4 py-2 !rounded-button hover:bg-green-600 transition-colors whitespace-nowrap ripple">Login</a>
        </div>
      </div>
    </div>
  </div>
</nav>

<section id="home" class="min-h-screen flex flex-col relative overflow-hidden bg-gray-900 pt-16">
	<div class="absolute inset-0 bg-black bg-opacity-50 z-10"></div>
	<div class="relative h-screen overflow-hidden" id="heroSlider">
	<div class="absolute inset-0 transition-opacity duration-1000 opacity-0 slide active-slide">
	<img src="https://readdy.ai/api/search-image?query=emotional%20portrait%20of%20hungry%20homeless%20children%20receiving%20food%20aid%2C%20soft%20natural%20lighting%2C%20hopeful%20expressions%2C%20cinematic%20composition%2C%20professional%20photography%2C%208k%20quality&width=1920&height=1080&seq=hero001&orientation=landscape"
	alt="Helping Children" class="w-full h-full object-cover object-center">
	<div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-60"></div>
	</div>
	<div class="absolute inset-0 transition-opacity duration-1000 opacity-0 slide">
	<img src="https://readdy.ai/api/search-image?query=diverse%20group%20of%20volunteers%20working%20together%20in%20food%20bank%2C%20sorting%20and%20packaging%20donations%2C%20bright%20modern%20facility%2C%20teamwork%20and%20community%20spirit%2C%20professional%20photography%2C%208k%20quality&width=1920&height=1080&seq=hero002&orientation=landscape"
	alt="Volunteer Work" class="w-full h-full object-cover">
	<div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-60"></div>
	</div>
	<div class="absolute inset-0 transition-opacity duration-1000 opacity-0 slide">
	<img src="https://readdy.ai/api/search-image?query=heartwarming%20scene%20of%20community%20food%20distribution%20event%2C%20people%20receiving%20meals%2C%20gratitude%20and%20hope%2C%20warm%20evening%20lighting%2C%20emotional%20impact%2C%20professional%20photography%2C%208k%20quality"
	alt="Community Impact" class="w-full h-full object-cover">
	<div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-60"></div>
	</div>
	<div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-20 flex space-x-3">
	<button class="w-3 h-3 rounded-full bg-white bg-opacity-50 hover:bg-opacity-100 transition-all duration-300 slider-dot" data-index="0"></button>
	<button class="w-3 h-3 rounded-full bg-white bg-opacity-50 hover:bg-opacity-100 transition-all duration-300 slider-dot" data-index="1"></button>
	<button class="w-3 h-3 rounded-full bg-white bg-opacity-50 hover:bg-opacity-100 transition-all duration-300 slider-dot" data-index="2"></button>
	</div>
	</div>
	<div class="absolute inset-0 z-20 flex items-center justify-center">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center" id="heroContent">
	<h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight text-white">
	Save Food, Save Lives<br>
	<span class="text-secondary">Join Lacso Today</span>
	</h1>
	<p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto text-white opacity-80">
	Together, we can reduce food waste and feed the hungry. Every donation makes a difference in someone's life.
	</p>
	<div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
	<button id="donateBtn" class="bg-primary text-white px-8 py-4 !rounded-button text-lg font-semibold hover:bg-green-600 transition-all float-animation ripple whitespace-nowrap hover:scale-105 transform duration-300" onclick="window.location.href='login.php'">
	Donate Food 🍱
	</button>
	<button href="Volunteer.php" class="bg-transparent border-2 border-white text-white px-8 py-4 !rounded-button text-lg font-semibold hover:bg-white hover:text-gray-900 transition-all ripple whitespace-nowrap hover:scale-105 transform duration-300"onclick="window.location.href='Volunteer.php'">
	Become a Volunteer
	</button>
	</div>
	</div>
	</div>
</section>

 <section id="how" class="py-20 bg-white transition-colors duration-500">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-16">
      <h2 class="text-4xl font-bold text-gray-900 mb-3" id="howTitle">How It Works</h2>
      <p class="text-lg text-gray-600" id="howSubtitle">Join our community in three simple steps</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8" id="stepContainer">
      <div class="step-card bg-white p-6 rounded-2xl shadow-lg text-center transition-colors duration-500">
        <div class="w-14 h-14 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ri-user-add-line text-xl text-white"></i>
        </div>
        <h3 class="text-xl font-bold mb-2">Step 1: Register</h3>
        <p class="text-gray-600">Create your account and join our community.</p>
      </div>
      <div class="step-card bg-white p-6 rounded-2xl shadow-lg text-center transition-colors duration-500">
        <div class="w-14 h-14 bg-secondary rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ri-hand-heart-line text-xl text-white"></i>
        </div>
        <h3 class="text-xl font-bold mb-2">Step 2: Donate/Volunteer</h3>
        <p class="text-gray-600">Share food or give your time to help others.</p>
      </div>
      <div class="step-card bg-white p-6 rounded-2xl shadow-lg text-center transition-colors duration-500">
        <div class="w-14 h-14 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ri-line-chart-line text-xl text-white"></i>
        </div>
        <h3 class="text-xl font-bold mb-2">Step 3: Track Impact</h3>
        <p class="text-gray-600">See the difference you make in real time.</p>
      </div>
    </div>
  </div>
</section>

<section id="about" class="py-20 bg-gray-50 transition-colors duration-500">
  <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-8 items-center">
    <div id="aboutText">
      <h2 class="text-4xl font-bold text-gray-900 mb-4">About Lacso</h2>
      <p class="text-lg text-gray-600 mb-4">
        Lacso connects food donors with those in need.
      </p>
      <p class="text-lg text-gray-600 mb-6">
        Restaurants, stores, and individuals can donate surplus food to communities.
      </p>
    </div>

    <div class="relative" id="aboutImageContainer">
      <img src="images/p3.jpeg"
           alt="About Lacso"
           class="rounded-2xl shadow-xl object-cover w-full h-96 transform transition-transform duration-500" id="aboutImg">
      
      <a href="login.php" class="absolute -bottom-4 -right-4 w-28 h-28 bg-primary rounded-full flex items-center justify-center text-white text-xl font-bold animate-bounce shadow-xl cursor-pointer hover:bg-green-600 transition-colors" id="joinUsButton">
        Join Us!
      </a>
    </div>

  </div>
</section>

<section class="py-20 bg-white transition-colors duration-500" id="badgesSection">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-16">
<h2 class="text-4xl font-bold text-gray-900 mb-4">Achievement Badges</h2>
<p class="text-xl text-gray-600 max-w-3xl mx-auto">
Earn badges as you contribute to reducing food waste and helping your community
</p>
</div>
<div class="grid md:grid-cols-3 gap-8" id="badgeContainer">
<div class="badge-card bg-gradient-to-br from-yellow-400 to-orange-500 p-8 rounded-2xl text-white text-center shadow-lg">
<div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6">
<i class="ri-sword-line text-3xl"></i>
</div>
<h3 class="text-2xl font-bold mb-4">Hunger Warrior</h3>
<p class="opacity-90">
Fight hunger by donating 50+ meals to those in need
</p>
</div>
<div class="badge-card bg-gradient-to-br from-green-400 to-blue-500 p-8 rounded-2xl text-white text-center shadow-lg">
<div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6">
<i class="ri-shield-star-line text-3xl"></i>
</div>
<h3 class="text-2xl font-bold mb-4">Food Hero</h3>
<p class="opacity-90">
Save 100+ meals from going to waste
</p>
</div>
<div class="badge-card bg-gradient-to-br from-purple-400 to-pink-500 p-8 rounded-2xl text-white text-center shadow-lg">
<div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6">
<i class="ri-trophy-line text-3xl"></i>
</div>
<h3 class="text-2xl font-bold mb-4">Zero Waste Champ</h3>
<p class="opacity-90">
Achieve zero food waste for 30 consecutive days
</p>
</div>
</div>
</div>
</section>

<section class="py-20 bg-gray-50 transition-colors duration-500" id="testimonialsSection">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-16">
<h2 class="text-4xl font-bold text-gray-900 mb-4">What Our Community Says</h2>
<p class="text-xl text-gray-600 max-w-3xl mx-auto">
Hear from donors and volunteers who are making a difference
</p>
</div>
<div class="grid md:grid-cols-3 gap-8" id="testimonialContainer">
<div class="testimonial-card bg-white p-8 rounded-2xl shadow-lg transition-colors duration-500">
<div class="flex items-center mb-6">
<img src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20a%20smiling%20restaurant%20owner%2C%20middle-aged%20man%20with%20chef%20uniform%2C%20warm%20lighting%2C%20confident%20expression%2C%20food%20service%20background&width=100&height=100&seq=test001&orientation=squarish"
alt="Michael Chen" class="w-12 h-12 rounded-full object-cover mr-4">
<div>
<h4 class="font-semibold text-gray-900">Michael Chen</h4>
<p class="text-gray-600 text-sm">Restaurant Owner</p>
</div>
</div>
<p class="text-gray-600 italic">
"Lacso has transformed how we handle surplus food. Instead of throwing away perfectly good meals,
we now help feed families in our community. It's incredibly rewarding!"
</p>
<div class="flex text-yellow-400 mt-4">
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
</div>
</div>
<div class="testimonial-card bg-white p-8 rounded-2xl shadow-lg transition-colors duration-500">
<div class="flex items-center mb-6">
<img src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20a%20smiling%20volunteer%20woman%2C%20young%20adult%20with%20volunteer%20t-shirt%2C%20bright%20lighting%2C%20friendly%20expression%2C%20community%20service%20background&width=100&height=100&seq=test002&orientation=squarish"
alt="Sarah Johnson" class="w-12 h-12 rounded-full object-cover mr-4">
<div>
<h4 class="font-semibold text-gray-900">Sarah Johnson</h4>
<p class="text-gray-600 text-sm">Volunteer</p>
</div>
</div>
<p class="text-gray-600 italic">
"Being part of Lacso has been life-changing. Seeing the direct impact of our efforts in reducing
food waste while helping those in need gives me so much purpose."
</p>
<div class="flex text-yellow-400 mt-4">
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
</div>
</div>
<div class="testimonial-card bg-white p-8 rounded-2xl shadow-lg transition-colors duration-500">
<div class="flex items-center mb-6">
<img src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20a%20smiling%20grocery%20store%20manager%2C%20mature%20woman%20with%20business%20attire%2C%20professional%20lighting%2C%20confident%20expression%2C%20retail%20background&width=100&height=100&seq=test003&orientation=squarish"
alt="David Rodriguez" class="w-12 h-12 rounded-full object-cover mr-4">
<div>
<h4 class="font-semibold text-gray-900">Emily Davis</h4>
<p class="text-gray-600 text-sm">Grocery Store Manager</p>
</div>
</div>
<p class="text-gray-600 italic">
"The platform is incredibly user-friendly. We can easily schedule pickups and track our donations.
It's amazing to see how much food we've saved from going to landfills."
</p>
<div class="flex text-yellow-400 mt-4">
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
</div>
</div>
</div>
</div>
</section>
<section id="blog" class="py-20 bg-white transition-colors duration-500">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-16">
<h2 class="text-4xl font-bold text-gray-900 mb-4">Latest Blog Posts</h2>
<p class="text-xl text-gray-600 max-w-3xl mx-auto">
Stay updated with our latest news, success stories, and food waste reduction tips
</p>
</div>
<div class="grid md:grid-cols-3 gap-8" id="blogContainer">
<div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
<div class="relative h-48 overflow-hidden">
<img src="https://readdy.ai/api/search-image?query=fresh%20organic%20vegetables%20and%20fruits%20being%20sorted%20and%20packed%20by%20volunteers%20in%20a%20modern%20food%20bank%2C%20bright%20natural%20lighting%2C%20professional%20food%20photography%2C%20sustainability%20theme&width=600&height=400&seq=blog001&orientation=landscape"
alt="Blog Post 1" class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-500">
<div class="absolute top-4 left-4 bg-primary text-white px-3 py-1 rounded-full text-sm">Food Waste</div>
</div>
<div class="p-6">
<h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-primary transition-colors">
10 Creative Ways to Reduce Food Waste in Your Restaurant
</h3>
<p class="text-gray-600 mb-4">
Learn innovative strategies to minimize food waste while maximizing profits and helping the community.
</p>
<div class="flex items-center justify-between">
<div class="flex items-center">
<img src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20a%20female%20food%20sustainability%20expert%2C%20warm%20lighting%2C%20friendly%20smile&width=40&height=40&seq=author001&orientation=squarish"
alt="Author" class="w-8 h-8 rounded-full mr-2">
<span class="text-sm text-gray-500">Emma Thompson</span>
</div>
<span class="text-sm text-gray-500">July 25, 2025</span>
</div>
</div>
</div>
<div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
<div class="relative h-48 overflow-hidden">
<img src="https://readdy.ai/api/search-image?query=diverse%20group%20of%20volunteers%20working%20together%20in%20community%20kitchen%2C%20preparing%20meals%2C%20teamwork%2C%20bright%20modern%20facility&width=600&height=400&seq=blog002&orientation=landscape"
alt="Blog Post 2" class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-500">
<div class="absolute top-4 left-4 bg-secondary text-white px-3 py-1 rounded-full text-sm">Community</div>
</div>
<div class="p-6">
<h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-primary transition-colors">
Building Strong Communities Through Food Donation
</h3>
<p class="text-gray-600 mb-4">
Discover how local food donation programs are strengthening community bonds and fighting hunger.
</p>
<div class="flex items-center justify-between">
<div class="flex items-center">
<img src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20a%20male%20community%20organizer%2C%20warm%20lighting%2C%20confident%20expression&width=40&height=40&seq=author002&orientation=squarish"
alt="Author" class="w-8 h-8 rounded-full mr-2">
<span class="text-sm text-gray-500">Marcus Chen</span>
</div>
<span class="text-sm text-gray-500">July 24, 2025</span>
</div>
</div>
</div>
<div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
<div class="relative h-48 overflow-hidden">
<img src="https://readdy.ai/api/search-image?query=modern%20food%20delivery%20volunteer%20using%20smartphone%20app%2C%20technology%20in%20food%20rescue%2C%20urban%20setting&width=600&height=400&seq=blog003&orientation=landscape"
alt="Blog Post 3" class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-500">
<div class="absolute top-4 left-4 bg-purple-500 text-white px-3 py-1 rounded-full text-sm">Technology</div>
</div>
<div class="p-6">
<h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-primary transition-colors">
How Technology is Revolutionizing Food Rescue
</h3>
<p class="text-gray-600 mb-4">
Explore the latest technological innovations making food rescue more efficient and accessible.
</p>
<div class="flex items-center justify-between">
<div class="flex items-center">
<img src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20a%20female%20tech%20expert%2C%20warm%20lighting%2C%20professional%20attire&width=40&height=40&seq=author003&orientation=squarish"
alt="Author" class="w-8 h-8 rounded-full mr-2">
<span class="text-sm text-gray-500">Sarah Johnson</span>
</div>
<span class="text-sm text-gray-500">July 23, 2025</span>
</div>
</div>
</div>
</div>
<div class="text-center mt-12">
<button class="bg-primary text-white px-8 py-3 !rounded-button font-semibold hover:bg-green-600 transition-colors ripple whitespace-nowrap">
View All Posts <i class="ri-arrow-right-line ml-2"></i>
</button>
</div>
</div>
</section>

<footer class="bg-gray-900 text-white py-12" id="contact">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid md:grid-cols-4 gap-8">
      <div>
        <h3 class="text-2xl font-bold mb-4 font-['Pacifico']">Lacso</h3>
        <p class="text-gray-400 mb-4">Reducing food waste and feeding the hungry.</p>
      </div>
      <div>
        <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
        <ul class="space-y-2">
          <li><a href="#about" class="text-gray-400 hover:text-white transition-colors">About</a></li>
          <li><a href="#how" class="text-gray-400 hover:text-white transition-colors">How It Works</a></li>
          <li><a href="#blog" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
          <li><a href="#contact" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-lg font-semibold mb-4">Get Involved</h4>
        <ul class="space-y-2">
          <li><a href="volunteer.php" class="text-gray-400 hover:text-white transition-colors">Volunteer</a></li>
          <li><a href="login.php" class="text-gray-400 hover:text-white transition-colors">Donate Food</a></li>
          <li><a href="LACSO-Admin-Panel.php" class="text-gray-400 hover:text-white transition-colors">Admin Panel</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
        <p class="text-gray-400 mb-2">Email: support@lacso.org</p>
        <p class="text-gray-400 mb-4">Phone: +1 (555) 123-4567</p>
        <div class="flex space-x-4">
          <a href="#" class="text-gray-400 hover:text-primary"><i class="ri-facebook-fill text-xl"></i></a>
          <a href="#" class="text-gray-400 hover:text-primary"><i class="ri-twitter-fill text-xl"></i></a>
          <a href="#" class="text-gray-400 hover:text-primary"><i class="ri-instagram-line text-xl"></i></a>
        </div>
      </div>
    </div>
    <div class="mt-8 pt-6 border-t border-gray-800 text-center">
      <p class="text-gray-500 text-sm">&copy; 2025 Lacso. All rights reserved.</p>
    </div>
  </div>
</footer>

</div>

<script>
// --- Custom Cursor Logic ---
const cursor = document.getElementById("cursor");
document.addEventListener("mousemove", e => {
  cursor.style.left = e.clientX + "px";
  cursor.style.top = e.clientY + "px";
});
document.addEventListener("mousedown", () => {
  cursor.classList.add("cursor-click");
});
document.addEventListener("mouseup", () => {
  cursor.classList.remove("cursor-click");
});
document.querySelectorAll("button, a").forEach(el => {
  el.addEventListener("mouseenter", () => cursor.classList.add("cursor-hover"));
  el.addEventListener("mouseleave", () => cursor.classList.remove("cursor-hover"));
});

// --- Dark Mode Toggle Logic ---
const body = document.getElementById('body');
const darkModeToggle = document.getElementById('darkModeToggle');
const darkModeIcon = document.getElementById('darkModeIcon');

darkModeToggle.addEventListener('click', () => {
  body.classList.toggle('dark-mode');
  
  // Toggle icon between moon and sun
  if (body.classList.contains('dark-mode')) {
    darkModeIcon.classList.remove('ri-moon-line');
    darkModeIcon.classList.add('ri-sun-line');
  } else {
    darkModeIcon.classList.remove('ri-sun-line');
    darkModeIcon.classList.add('ri-moon-line');
  }
});

// --- Hero Slider Logic ---
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.slider-dot');
let currentSlide = 0;

function showSlide(index) {
  slides.forEach((slide, i) => {
    slide.classList.remove('opacity-100');
    slide.classList.add('opacity-0');
    dots[i].classList.remove('bg-opacity-100');
    dots[i].classList.add('bg-opacity-50');
  });

  slides[index].classList.remove('opacity-0');
  slides[index].classList.add('opacity-100');
  dots[index].classList.remove('bg-opacity-50');
  dots[index].classList.add('bg-opacity-100');
  currentSlide = index;
}

showSlide(0);

let slideInterval = setInterval(() => {
  currentSlide = (currentSlide + 1) % slides.length;
  showSlide(currentSlide);
}, 5000); 

dots.forEach(dot => {
  dot.addEventListener('click', function() {
    clearInterval(slideInterval); 
    showSlide(parseInt(this.dataset.index));
    slideInterval = setInterval(() => {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    }, 5000);
  });
});

// --- GSAP Animations ---

gsap.registerPlugin(ScrollTrigger);

// 1. Hero Content Animation
gsap.from("#heroContent h1", {
  duration: 1,
  y: 50,
  opacity: 0,
  ease: "power2.out"
});
gsap.from("#heroContent p", {
  duration: 1,
  y: 50,
  opacity: 0,
  ease: "power2.out",
  delay: 0.3
});
gsap.from("#heroContent button", {
  duration: 1,
  y: 50,
  opacity: 0,
  ease: "power2.out",
  delay: 0.6,
  stagger: 0.2
});


// 2. How It Works Section Animation
gsap.from("#howTitle, #howSubtitle", {
  scrollTrigger: {
    trigger: "#how",
    start: "top 80%",
  },
  y: 50,
  opacity: 0,
  duration: 0.8,
  ease: "power2.out",
  stagger: 0.1
});

gsap.from("#stepContainer > .step-card", {
  scrollTrigger: {
    trigger: "#stepContainer",
    start: "top 80%",
  },
  scale: 0.8,
  opacity: 0,
  duration: 1,
  ease: "back.out(1.7)",
  stagger: 0.3
});

// 3. About Section Animation
gsap.from("#aboutText", {
  scrollTrigger: {
    trigger: "#about",
    start: "top 80%",
  },
  x: -100,
  opacity: 0,
  duration: 1
});

gsap.from("#aboutImageContainer img", {
  scrollTrigger: {
    trigger: "#about",
    start: "top 80%",
  },
  scale: 0.8,
  opacity: 0,
  duration: 1.2,
  ease: "power3.out",
  delay: 0.2
});

// 4. Badges Section Animation
gsap.from("#badgeContainer > .badge-card", {
  scrollTrigger: {
    trigger: "#badgesSection",
    start: "top 80%",
  },
  y: 100,
  rotation: -15,
  opacity: 0,
  duration: 1.2,
  ease: "elastic.out(1, 0.3)",
  stagger: 0.2
});

// 5. Testimonials Section Animation
gsap.from("#testimonialContainer > .testimonial-card", {
  scrollTrigger: {
    trigger: "#testimonialsSection",
    start: "top 80%",
  },
  y: 50,
  opacity: 0,
  duration: 0.8,
  ease: "power1.out",
  stagger: 0.3
});

// 6. Blog Section Animation
gsap.from("#blogContainer > div", {
  scrollTrigger: {
    trigger: "#blogContainer",
    start: "top 80%",
  },
  y: 50,
  opacity: 0,
  duration: 0.8,
  ease: "power2.out",
  stagger: 0.2
});
</script>

</body>
</html>