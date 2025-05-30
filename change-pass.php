<?php
session_start();
include 'instructor_db.php';

$emailError = $currentPasswordError = $newPasswordError = $confirmPasswordError = "";
$showSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $valid = true;

    $stmt = $conn->prepare("SELECT * FROM instructors WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $instructor = $result->fetch_assoc();

    if (!$instructor) {
        $emailError = "Email not found.";
        $valid = false;
    } elseif (!password_verify($current_password, $instructor['password'])) {
        $currentPasswordError = "Current password is incorrect.";
        $valid = false;
    }

    if (strlen($new_password) < 8) {
        $newPasswordError = "New password must be at least 8 characters.";
        $valid = false;
    }

    if ($new_password !== $confirm_password) {
        $confirmPasswordError = "New passwords do not match.";
        $valid = false;
    }

    if ($valid) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE instructors SET password = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $hashed_password, $email);
        if ($update_stmt->execute()) {
            $showSuccess = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            min-height: 100vh;
        }
        .reset-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            color: #800000;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .error {
            color: red;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        button {
            width: 100%;
            background-color: #800000;
            color: white;
            border: none;
            padding: 12px;
            font-size: 1rem;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #a00000;
        }

        @media screen and (max-width: 480px) {
            .reset-box {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="reset-box">
    <h2>Reset Password</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <?php if ($emailError): ?><div class="error"><?= $emailError ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
            <?php if ($currentPasswordError): ?><div class="error"><?= $currentPasswordError ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>New Password (min 8 characters)</label>
            <input type="password" name="new_password" id="new_password" required>
            <div id="newPasswordLengthError" class="error" style="display: none;">New password must be at least 8 characters.</div>
            <?php if ($newPasswordError): ?><div class="error"><?= $newPasswordError ?></div><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>
            <?php if ($confirmPasswordError): ?><div class="error"><?= $confirmPasswordError ?></div><?php endif; ?>
        </div>
        <button type="submit">Update Password</button>
    </form>
</div>

<?php if ($showSuccess): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Password Updated!',
        text: 'Redirecting to login page...',
        timer: 2000,
        showConfirmButton: false
    }).then(() => {
        window.location.href = 'instructor_dashboard.php';
    });
</script>
<?php endif; ?>

<script>
    const newPasswordInput = document.getElementById("new_password");
    const newPasswordError = document.getElementById("newPasswordLengthError");

    newPasswordInput.addEventListener("input", function () {
        if (newPasswordInput.value.length < 8) {
            newPasswordError.style.display = "block";
        } else {
            newPasswordError.style.display = "none";
        }
    });
</script>

</body>
</html>
