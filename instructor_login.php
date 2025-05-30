<?php
session_start();
include 'instructor_db.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM instructors WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $instructor = $result->fetch_assoc();

    if ($instructor && password_verify($password, $instructor['password'])) {
        $_SESSION['instructor_id'] = $instructor['instructor_id'];
        $_SESSION['instructor_email'] = $instructor['email'];
        $success = true; 
    } else {
        $error = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('EVSU.png') no-repeat center center/cover;
    color: #333;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

header {
    background-color: #800000;
    color: white;
    padding: 20px 30px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}

.header-container {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
}

.header-container img {
    height: 70px;
    flex-shrink: 0;
}

.text-group {
    text-align: center;
}

.text-group h1 {
    font-size: 2rem;
    margin-bottom: 5px;
}

.header-separator {
    width: 100%;
    height: 3px;
    background-color: #FFD700;
    border: none;
    margin: 4px 0 10px;
}

.portal-subtitle {
    font-size: 1rem;
    color: #f8f8f8;
    letter-spacing: 0.5px;
}

main {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
}

.card {
    background: #fff;
    padding: 35px 30px;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 400px;
    animation: fadeIn 0.8s ease-in-out;
}

.card h2 {
    margin-bottom: 25px;
    font-size: 1.7rem;
    color: #800000;
    border-bottom: 2px solid #ccc;
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 18px;
    text-align: left;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}
.form-group input[type="email"] {
    width: 100%;
    padding: 10px;
    font-size: 1rem;
    border: 2px solid #ccc;
    border-radius: 6px;
    transition: border-color 0.3s;
}
.form-group input[type="email"]:focus {
    border-color: #FFD700;
    outline: none;
}

.form-group input[type="password"] {
    width: 100%;
    padding: 10px;
    font-size: 1rem;
    border: 2px solid #ccc;
    border-radius: 6px;
    transition: border-color 0.3s;
}
.form-group input[type="password"]:focus {
    border-color: #FFD700;
    outline: none;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
.actions input[type="submit"],
.actions button {
    flex: 1 1 48%;
    padding: 12px;
    font-size: 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    color: #fff;
    transition: transform 0.2s, background 0.3s;
}
.actions input[type="submit"] {
    background: #800000;
}
.actions button {
    background: #555;
}
.actions input[type="submit"]:hover {
    background: #A40000;
    transform: translateY(-2px);
}
.actions button:hover {
    background: #000;
    transform: translateY(-2px);
}

footer {
    text-align: center;
    padding: 15px;
    background: #f5f5f5;
    font-size: 0.9rem;
    color: #333;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.1);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes popIn {
    0% { transform: scale(0.6); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
    </style>
</head>
<body>
<header>
    <div class="header-container">
        <img src="EVSU_logo.png" alt="EVSU Logo">
        <div class="text-group">
            <h1>Eastern Visayas State University</h1>
            <hr class="header-separator">
            <p class="portal-subtitle">Instructor Login Portal</p>
        </div>
    </div>
</header>
<main>
    <div class="card">
        <h2>Instructor Login</h2>
        <?php if ($error): ?>
            <p style="color: red; text-align: center;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="actions">
                <input type="submit" value="Login">
            </div>
            <div style="text-align: center; margin-top: 10px;">
                <a href="forgot_password.php" style="color: #800000; font-weight: bold;">Change Password?</a>
            </div>
        </form>

    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($success): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Login Successful!',
        showConfirmButton: true,
        confirmButtonText: 'Continue'
    }).then(() => {
        window.location.href = 'instructor_dashboard.php';
    });
</script>
<?php endif; ?>

<footer>
    <p>&copy; <?= date('Y') ?> EVSU - Ormoc Campus — All Rights Reserved</p>
</footer>
</body>
</html>
