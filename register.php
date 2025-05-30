<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "qr_attendance");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$qrImage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $gender = $_POST['gender']; 

    if ($name !== '' && in_array($gender, ['Male', 'Female', 'Other'])) {
        $stmt = $conn->prepare("INSERT INTO students(name, gender) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $gender);
        $stmt->execute();
        $id = $conn->insert_id;
        $data = "$id,$name,$gender";
        $qrImage = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($data) . "&size=250x250";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student QR Registration</title>
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
.form-group input[type="text"] {
    width: 100%;
    padding: 10px;
    font-size: 1rem;
    border: 2px solid #ccc;
    border-radius: 6px;
    transition: border-color 0.3s;
}
.form-group input[type="text"]:focus {
    border-color: #FFD700;
    outline: none;
}

.form-group select {
    width: 100%;
    padding: 10px;
    font-size: 1rem;
    border: 2px solid #ccc;
    border-radius: 6px;
    transition: border-color 0.3s;
}
.form-group select:focus {
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

.qr-display {
    margin-top: 30px;
    text-align: center;
}
.qr-display h3 {
    margin-bottom: 12px;
    color: #333;
}
.qr-display img {
    width: 250px;
    height: 250px;
    border: 5px solid #FFD700;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.2);
    animation: popIn 0.5s ease-out;
}

.download-btn {
    background-color: #800000;
    color: #fff;
    border: none;
    padding: 12px 20px;
    font-size: 1rem;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
}
.download-btn:hover {
    background-color: #A40000;
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
<script>
    function downloadQR(studentName) {
        const qrImg = document.getElementById('qrImage');
        if (!qrImg) return;

        const cleanName = studentName.replace(/[^\w\s]/gi, '').replace(/\s+/g, '_');
        const filename = `${cleanName}_qr.png`;

        fetch(qrImg.src)
            .then(res => res.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
            })
            .catch(err => {
                alert('Failed to download QR Code.');
                console.error(err);
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('#registerForm');
        form.addEventListener('submit', (e) => {
            const nameInput = form.querySelector('input[name="name"]');
            if (!nameInput.value.trim()) {
                e.preventDefault();
                alert('Please enter a valid student name.');
                nameInput.focus();
            } else {
                document.querySelector('#submitBtn').textContent = 'Registering...';
            }
        });
    });
</script>

</head>
<body>
<header>
    <div class="header-container">
        <img src="EVSU_logo.png" alt="EVSU Logo">
        <div class="text-group">
            <h1>Eastern Visayas State University</h1>
            <hr class="header-separator">
            <p class="portal-subtitle">Student QR Registration Portal</p>
        </div>
    </div>
</header>


    <main>
        <div class="card">
            <h2>Register Student</h2>
            <form id="registerForm" method="POST">
                <div class="form-group">
                    <label for="name">Student Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g. Juan Dela Cruz" required>
                </div>
                <div class="form-group">
                <select name="gender" id="gender" required>
                    <option value="">Select gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
                <div class="actions">  
                    <button type="button" onclick="location.href='view_attendance.php'">View Records</button>
                    <input type="submit" id="submitBtn" name="register" value="Generate QR">
                </div>
                
            </form>

            <?php if ($qrImage): ?>
                <div class="qr-display">
                    <h3>QR Code for <?= htmlspecialchars($name) ?></h3>
                    <img id="qrImage" src="<?= $qrImage ?>" alt="QR Code">
                    <br><br>
                    <button class="download-btn" onclick="downloadQR('<?= htmlspecialchars($name) ?>')">Download QR Code</button>
                </div>
            <?php endif; ?>
        </div>

    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> EVSU - Ormoc Campus &mdash; All Rights Reserved</p>
    </footer>
</body>
</html>
