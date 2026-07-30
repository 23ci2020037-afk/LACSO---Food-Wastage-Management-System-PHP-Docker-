<?php
session_start();
include 'db.php';

// Admin credentials
$ADMIN_EMAIL = 'admin';
$ADMIN_PASSWORD = 'admin';

// Allowed volunteers
$ALLOWED_VOLUNTEERS = [
    'volunteer1',
    'volunteer2',
    'volunteer3'
];

$VOLUNTEER_PAGE = 'voluntersssbtn.php';
$DONOR_PAGE = 'donor.php';
$ADMIN_PAGE = 'LACSO-Admin-Panel.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'login') {
        $input = trim($_POST['email']);
        $password = trim($_POST['password']);

        if ($input === '' || $password === '') {
            $error = "Enter username/email and password.";
        } else {
            // Auto-ensure default test accounts exist in DB safely
            try {
                // Insert default volunteer accounts if missing
                $defaultUsers = [
                    ['Volunteer 1', 'volunteer1', 'vol123', 'volunteer'],
                    ['Volunteer 2', 'volunteer2', 'vol123', 'volunteer'],
                    ['Volunteer 3', 'volunteer3', 'vol123', 'volunteer'],
                    ['Admin', 'admin', 'admin', 'admin'],
                    ['Care Foundation NGO', 'ngo', 'ngo123', 'ngo'],
                    ['Hope Orphanage NGO', 'ngo1', 'ngo123', 'ngo']
                ];
                foreach ($defaultUsers as $u) {
                    $uName = $u[0];
                    $uEmail = $u[1];
                    $uPass = $u[2];
                    $uRole = $u[3];
                    $chk = $conn->prepare("SELECT id FROM users WHERE email=?");
                    if ($chk) {
                        $chk->bind_param("s", $uEmail);
                        $chk->execute();
                        $res = $chk->get_result();
                        if ($res->num_rows === 0) {
                            $ins = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                            $ins->bind_param("ssss", $uName, $uEmail, $uPass, $uRole);
                            $ins->execute();
                        }
                    }
                }
            } catch (Throwable $e) {
                // Suppress DB auto-creation errors if schema is different
            }

            // Direct Admin bypass check
            if (($input === 'admin' || strtolower($input) === 'admin@lacso.org') && $password === 'admin') {
                $_SESSION['user_id'] = 0;
                $_SESSION['user_name'] = 'Admin';
                $_SESSION['role'] = 'admin';
                session_write_close();
                header("Location: $ADMIN_PAGE");
                exit;
            }

            // Direct Volunteer bypass / fallback check for default volunteer accounts
            $lowerInput = strtolower($input);
            $defaultVolunteers = [
                'volunteer1'   => 'Volunteer 1',
                'volunteer2'   => 'Volunteer 2',
                'volunteer3'   => 'Volunteer 3',
                'volunteer'    => 'Volunteer 1',
                'volunter'     => 'Volunteer 1',
                'volunteer 1'  => 'Volunteer 1',
                'volunteer 2'  => 'Volunteer 2',
                'volunteer 3'  => 'Volunteer 3',
            ];

            if (isset($defaultVolunteers[$lowerInput]) && ($password === 'vol123' || $password === 'volunteer')) {
                $vName = $defaultVolunteers[$lowerInput];
                $vId = 1;
                try {
                    $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email)=LOWER(?) OR LOWER(name)=LOWER(?)");
                    if ($stmt) {
                        $stmt->bind_param("ss", $lowerInput, $vName);
                        $stmt->execute();
                        $r = $stmt->get_result();
                        if ($row = $r->fetch_assoc()) {
                            $vId = (int)$row['id'];
                        }
                    }
                } catch (Throwable $t) {}

                $_SESSION['user_id'] = $vId;
                $_SESSION['user_name'] = $vName;
                $_SESSION['role'] = 'volunteer';
                session_write_close();
                header("Location: $VOLUNTEER_PAGE");
                exit;
            }

            // Direct NGO bypass / fallback check for default NGO accounts
            $defaultNgos = [
                'ngo'           => 'Care Foundation NGO',
                'ngo1'          => 'Hope Orphanage NGO',
                'ngo@lacso.org' => 'Care Foundation NGO',
            ];

            if (isset($defaultNgos[$lowerInput]) && ($password === 'ngo123' || $password === 'ngo')) {
                $ngoName = $defaultNgos[$lowerInput];
                $ngoId = 10;
                try {
                    $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email)=LOWER(?) OR LOWER(name)=LOWER(?)");
                    if ($stmt) {
                        $stmt->bind_param("ss", $lowerInput, $ngoName);
                        $stmt->execute();
                        $r = $stmt->get_result();
                        if ($row = $r->fetch_assoc()) {
                            $ngoId = (int)$row['id'];
                        }
                    }
                } catch (Throwable $t) {}

                $_SESSION['user_id'] = $ngoId;
                $_SESSION['user_name'] = $ngoName;
                $_SESSION['role'] = 'ngo';
                session_write_close();
                header("Location: ngo.php");
                exit;
            }

            // Check user in DB by email OR name (case-insensitive)
            try {
                $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE LOWER(email)=LOWER(?) OR LOWER(name)=LOWER(?)");
                if ($stmt) {
                    $stmt->bind_param("ss", $input, $input);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();

                    if ($user) {
                        $role = strtolower($user['role']);
                        $isPassValid = password_verify($password, $user['password']) || 
                                       $password === $user['password'] || 
                                       ($role === 'volunteer' && ($password === 'vol123' || $password === 'volunteer'));

                        if ($isPassValid) {
                            $_SESSION['user_id'] = (int)$user['id'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['role'] = $role;
                            session_write_close();

                            if ($role === 'volunteer') {
                                header("Location: $VOLUNTEER_PAGE");
                                exit;
                            } elseif ($role === 'donor') {
                                header("Location: $DONOR_PAGE");
                                exit;
                            } elseif ($role === 'ngo') {
                                header("Location: ngo.php");
                                exit;
                            } elseif ($role === 'admin') {
                                header("Location: $ADMIN_PAGE");
                                exit;
                            } else {
                                $error = "Invalid user role.";
                            }
                        } else {
                            $error = "Invalid email or password.";
                        }
                    } else {
                        $error = "Invalid email or password.";
                    }
                } else {
                    $error = "Database query failed.";
                }
            } catch (Throwable $t) {
                $error = "Database error: " . $t->getMessage();
            }
        }
    } elseif ($action === 'signup') {
        // Signup logic
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = strtolower($_POST['role']);

        if ($name === '' || $email === '' || $password === '' || !in_array($role, ['donor','ngo'])) {
            $error = "All fields are required.";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Email already registered.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)");
                $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
                if ($stmt->execute()) {
                    $error = "Account created successfully. You can now login.";
                } else {
                    $error = "Signup failed. Try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Lacso - Login & Sign Up</title>
  <style>
    body{margin:0;font-family:"Poppins",sans-serif;background:#28a745;display:flex;justify-content:center;align-items:center;min-height:100vh;}
    .container{width:380px;background:rgba(255,255,255,0.15);padding:25px;border-radius:20px;backdrop-filter:blur(10px);box-shadow:0px 8px 20px rgba(0,0,0,0.2);}
    .title{font-size:28px;font-weight:bold;text-align:center;color:white;margin-bottom:5px;}
    .subtitle{font-size:14px;text-align:center;color:white;margin-bottom:20px;}
    .tabs{display:flex;justify-content:space-between;background:rgba(255,255,255,0.2);border-radius:25px;padding:5px;margin-bottom:20px;}
    .tabs button{flex:1;border:none;background:transparent;color:white;font-size:16px;padding:10px;border-radius:20px;cursor:pointer;transition:.3s;}
    .tabs button.active{background:rgba(255,255,255,0.4);font-weight:bold;}
    .card{display:none;}
    .card.active{display:block;}
    label{font-size:14px;font-weight:bold;color:white;margin-top:10px;display:block;}
    input{width:100%;padding:10px;margin:8px 0 15px;border:none;border-radius:10px;outline:none;font-size:14px;}
    .btn{width:100%;padding:12px;border:none;border-radius:10px;font-size:16px;cursor:pointer;font-weight:bold;background:white;color:#28a745;transition:.3s;}
    .btn:hover{background:#f1f1f1;}
    .radio-group{margin:10px 0;}
    .radio-group label{color:white;font-weight:normal;display:block;margin-bottom:5px;}
    .error{color:yellow;text-align:center;margin-bottom:10px;}
    .note{color:#e0ffe0;font-size:12px;text-align:center;margin-top:8px;}
  </style>
</head>
<body>
  <div class="container">
    <div class="title">Lacso</div>
    <div class="subtitle">Join our mission to reduce food waste</div>

    <?php if(!empty($error)) echo '<div class="error">'.htmlspecialchars($error).'</div>'; ?>

    <div class="tabs">
      <button id="loginTab" class="active">Login</button>
      <button id="signupTab">Sign Up</button>
    </div>

    <!-- Login Form -->
    <div class="card active" id="loginForm">
      <form method="POST">
        <input type="hidden" name="action" value="login">
        <label>Email / Username</label>
        <input type="text" name="email" placeholder="Enter your email or username" required>
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>
        <button type="submit" class="btn">Login</button>
      </form>
      <div class="note" style="margin-top:15px; padding:10px; background:rgba(255,255,255,0.15); border-radius:10px; text-align:left; font-size:13px; line-height:1.6;">
        🔑 <strong>Admin:</strong> <code>admin</code> / <code>admin</code><br>
        🚴 <strong>Volunteers:</strong> <code>volunteer1</code> | <code>volunteer2</code> | <code>volunteer3</code> (Pass: <code>vol123</code>)<br>
        🏢 <strong>NGO Partner:</strong> <code>ngo</code> | <code>ngo1</code> (Pass: <code>ngo123</code>)
      </div>
    </div>

    <!-- Signup Form -->
    <div class="card" id="signupForm">
      <form method="POST">
        <input type="hidden" name="action" value="signup">
        <label>Full Name</label>
        <input type="text" name="name" placeholder="Enter your full name" required>
        <label>Email / Username</label>
        <input type="text" name="email" placeholder="Enter your email or username" required>
        <label>Password</label>
        <input type="password" name="password" placeholder="Create a password" required>
        <div class="radio-group">
          <label>I want to:</label>
          <label><input type="radio" name="role" value="donor" required> Donate food</label>
          <label><input type="radio" name="role" value="ngo" required> Register as NGO/Receiver</label>
          <div style="font-size:11px; opacity:0.8; margin-top:4px; color:#d1fae5;">* Note: Volunteers are registered directly by the Admin.</div>
        </div>
        <button type="submit" class="btn">Create Account</button>
      </form>
    </div>
  </div>

  <script>
    const loginTab=document.getElementById("loginTab");
    const signupTab=document.getElementById("signupTab");
    const loginForm=document.getElementById("loginForm");
    const signupForm=document.getElementById("signupForm");

    loginTab.onclick=()=>{
      loginTab.classList.add("active");
      signupTab.classList.remove("active");
      loginForm.classList.add("active");
      signupForm.classList.remove("active");
    };

    signupTab.onclick=()=>{
      signupTab.classList.add("active");
      loginTab.classList.remove("active");
      signupForm.classList.add("active");
      loginForm.classList.remove("active");
    };
  </script>
</body>
</html>
