<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Volunteer Page</title>
  <style>
    /* Global Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      line-height: 1.6;
      color: #333;
    }

    a { text-decoration: none; }

    /* Hero Section */
    .hero {
      position: relative;
      background-image: url('images/volunter.jpg'); /* Replace with your image path */
      background-size: cover;
      background-position: center;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: white;
      padding: 0 20px;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background-color: rgba(0,0,0,0.5);
      z-index: 0;
    }

    .hero h1, .hero h2, .hero p, .hero .btn {
      position: relative;
      z-index: 1;
    }

    .hero h1 {
      font-size: 4rem;
      margin-bottom: 20px;
    }

    .hero h2 {
      font-size: 2.5rem;
      color: yellow;
      margin-bottom: 15px;
    }

    .hero p {
      font-size: 1.2rem;
      max-width: 600px;
      margin-bottom: 30px;
    }

    .hero .btn {
      background-color: #28a745;
      color: white;
      padding: 15px 30px;
      font-size: 1.2rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .hero .btn:hover {
      background-color: #218838;
    }

    /* Stats Section */
    .stats {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      background-color: #f8f9fa;
      padding: 50px 20px;
      text-align: center;
    }

    .stat-item {
      flex: 1 1 200px;
      margin: 20px;
    }

    .stat-item h3 {
      font-size: 2.5rem;
      color: #28a745;
      margin-bottom: 10px;
    }

    .stat-item p {
      font-size: 1.2rem;
    }

    /* Volunteer Opportunities Section */
    .volunteer-section {
      padding: 60px 20px;
      text-align: center;
      background-color: #fff;
    }

    .volunteer-section h2 {
      font-size: 3rem;
      margin-bottom: 15px;
      color: #333;
    }

    .volunteer-section p {
      font-size: 1.2rem;
      margin-bottom: 40px;
      color: #666;
    }

    .volunteer-cards {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 30px;
    }

    .card {
      background-color: #f8f9fa;
      border-radius: 15px;
      padding: 30px 20px;
      width: 300px;
      text-align: left;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .card h3 {
      font-size: 1.5rem;
      margin-bottom: 15px;
      color: #28a745;
    }

    .card p {
      font-size: 1rem;
      margin-bottom: 20px;
      color: #555;
    }

    .card a {
      color: #28a745;
      font-weight: bold;
      text-decoration: none;
      transition: color 0.3s;
    }

    .card a:hover {
      color: #218838;
    }

    /* FAQ Section */
    .faq-section {
      padding: 60px 20px;
      background-color: #f8f9fa;
      text-align: center;
    }

    .faq-section h2 {
      font-size: 3rem;
      margin-bottom: 15px;
      color: #333;
    }

    .faq-section p {
      font-size: 1.2rem;
      margin-bottom: 40px;
      color: #666;
    }

    .faq-cards {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 20px;
    }

    .faq-card {
      background-color: #fff;
      border-radius: 15px;
      padding: 30px 25px;
      width: 700px;
      text-align: left;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .faq-card h3 {
      font-size: 1.5rem;
      margin-bottom: 15px;
      color: #28a745;
    }

    .faq-card p {
      font-size: 1rem;
      color: #555;
    }

    /* CTA Footer Section */
    .cta-footer {
      background-color: #28a745;
      color: white;
      padding: 80px 20px;
      text-align: center;
    }

    .cta-footer h2 {
      font-size: 3rem;
      margin-bottom: 20px;
    }

    .cta-footer p {
      font-size: 1.3rem;
      margin-bottom: 30px;
    }

    .cta-footer a {
      background-color: white;
      color: #28a745;
      padding: 15px 35px;
      font-size: 1.2rem;
      border-radius: 8px;
      font-weight: bold;
      transition: background-color 0.3s;
      display: inline-block;
    }

    .cta-footer a:hover {
      background-color: #f1f1f1;
      color: #218838;
    }

    /* Responsive Styles */
    @media (max-width: 1024px) {
      .faq-card { width: 380px; }
    }

    @media (max-width: 768px) {
      .hero h1 { font-size: 3rem; }
      .hero h2 { font-size: 2rem; }
      .hero p { font-size: 1rem; }
      .hero .btn { font-size: 1rem; padding: 12px 25px; }
      .stat-item h3 { font-size: 2rem; }
      .stat-item p { font-size: 1rem; }
      .volunteer-section h2 { font-size: 2.2rem; }
      .volunteer-section p { font-size: 1rem; }
      .card { width: 100%; max-width: 350px; margin: auto; }
      .faq-card { width: 100%; max-width: 350px; margin: auto; }
      .cta-footer h2 { font-size: 2rem; }
      .cta-footer p { font-size: 1rem; }
      .cta-footer a { font-size: 1rem; padding: 12px 25px; }
    }
  </style>
</head>
<body>

  <!-- Hero Section -->
  <div class="hero">
    <h1>Make a Difference</h1>
    <h2>Become a Volunteer</h2>
    <p>Join our community of food heroes and help us create a world without food waste.</p>
    <!-- Button with link -->
    <a href="login.php" class="btn">Start Volunteer Today</a>
  </div>

  <!-- Stats Section -->
  <div class="stats">
    <div class="stat-item">
      <h3>1,500</h3>
      <p>Active Volunteers</p>
    </div>
    <div class="stat-item">
      <h3>25,000</h3>
      <p>Hours Contributed</p>
    </div>
    <div class="stat-item">
      <h3>50,000</h3>
      <p>Meals Distributed</p>
    </div>
    <div class="stat-item">
      <h3>100</h3>
      <p>Partner Organizations</p>
    </div>
  </div>

  <!-- Volunteer Opportunities Section -->
  <div class="volunteer-section">
    <h2>Volunteer Opportunities</h2>
    <p>Choose from various roles that match your skills and availability.</p>
    <div class="volunteer-cards">
      <div class="card">
        <h3>Food Pickup & Delivery</h3>
        <p>Help collect surplus food from donors and deliver it to those in need. Perfect for people with vehicles.</p>
        <a href="#">Learn More</a>
      </div>
      <div class="card">
        <h3>Food Bank Assistant</h3>
        <p>Sort, package, and distribute food at our distribution centers. Great for team players.</p>
        <a href="#">Learn More</a>
      </div>
      <div class="card">
        <h3>Community Coordinator</h3>
        <p>Organize food drives and coordinate with local organizations. Ideal for natural leaders.</p>
        <a href="#">Learn More</a>
      </div>
    </div>
  </div>

  <!-- FAQ Section -->
  <div class="faq-section">
    <h2>Frequently Asked Questions</h2>
    <p>Everything you need to know about volunteering with Lacso</p>
    <div class="faq-cards">
      <div class="faq-card">
        <h3>How much time do I need to commit?</h3>
        <p>You can volunteer as little as 2 hours per week. We offer flexible schedules to accommodate your availability.</p>
      </div>
      <div class="faq-card">
        <h3>Do I need any special skills?</h3>
        <p>No special skills required! We provide all necessary training. Just bring your enthusiasm and willingness to help.</p>
      </div>
      <div class="faq-card">
        <h3>Is there an age requirement?</h3>
        <p>Volunteers must be 16 or older. Those under 18 need parental consent and must be accompanied by an adult.</p>
      </div>
      <div class="faq-card">
        <h3>What safety measures are in place?</h3>
        <p>We follow strict health and safety protocols, provide necessary equipment, and ensure all volunteers are properly trained.</p>
      </div>
    </div>
  </div>

  <!-- CTA Footer Section -->
  <div class="cta-footer">
    <h2>Ready to Make a Difference?</h2>
    <p>Join our community of volunteers and help us create a world without food waste.</p>
    <!-- Button with link -->
    <a href="login.php">Start Volunteer Today</a>
  </div>

</body>
</html>
