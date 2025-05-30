<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "qr_attendance");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['delete_ids'])) {
        $ids = array_map('intval', $_POST['delete_ids']);
        $idsList = implode(",", $ids);
        $conn->query("DELETE FROM students WHERE student_id IN ($idsList)");
        $count = count($ids);
        $message = "$count record(s) deleted successfully.";
    } else {
        $message = "No records selected to delete.";
    }
}


if (isset($_POST['update'])) {
    $id = intval($_POST['student_id']);
    $name = $_POST['name'];
    $stmt = $conn->prepare("UPDATE students SET name = ? WHERE student_id = ?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    $message = "Record for $name updated successfully.";
}

$studentSearch = isset($_GET['student_search']) ? trim($_GET['student_search']) : '';

$studentQuery = "
    SELECT DISTINCT s.* 
    FROM students s
    LEFT JOIN instructors i ON 1 = 1
";

if ($studentSearch !== '') {
    $escaped = $conn->real_escape_string($studentSearch);

    $studentQuery .= " WHERE 
        s.name LIKE '%$escaped%' 
        OR s.student_id LIKE '%$escaped%' 
        OR s.gender LIKE '%$escaped%' 
        OR s.timestamp LIKE '%$escaped%' 
        OR i.subject LIKE '%$escaped%' 
        OR (
            ('$escaped' LIKE 'Present' AND s.timestamp IS NOT NULL AND s.timestamp != '')
            OR ('$escaped' LIKE 'No Record' AND (s.timestamp IS NULL OR s.timestamp = ''))
        )";
}
$studentQuery .= " ORDER BY s.student_id ASC";
$result = $conn->query($studentQuery);


$instructorSearch = isset($_GET['instructor_search']) ? trim($_GET['instructor_search']) : '';
$instructorQuery = "SELECT * FROM instructors";
if ($instructorSearch !== '') {
    $escapedInst = $conn->real_escape_string($instructorSearch);
    $instructorQuery .= " WHERE name LIKE '%$escapedInst%' OR subject LIKE '%$escapedInst%' OR instructor_id LIKE '%$escapedInst%'";
}
$instructorQuery .= " ORDER BY instructor_id ASC";
$instructors = $conn->query($instructorQuery);
?>

<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Student List</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
        }

        .main-header {
            background-color: #800000;
            color: white;
            padding: 10px 30px; 
            text-align: left;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative; 
        }

        .logo-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logout-link {
            margin-left: auto;
            text-decoration: none;
        }

        .logout-icon {
            width: 30px;
            height: 30px;
            cursor: pointer;
            margin-left: 20px;
            transition: transform 0.2s;
        }

        .logout-icon:hover {
            transform: scale(1.1);
        }

        h2 {
            color: #800000;
            font-size: 2rem;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .main-footer {
            background: #f0f0f0;
            text-align: center;
            padding: 12px;
            font-size: 0.9rem;
            color: #333;
            border-top: 1px solid #ccc;
            box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 80%;
            margin: 0 auto 200px;
            border-collapse: collapse;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: white;
        }

        th, td {
            padding: 12px 18px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 16px;
        }

        th {
            background-color: #FFD700;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #ffe9e0;
        }

        input[type="text"] {
            width: 80%;
            padding: 8px;
            font-size: 16px;
            border: 2px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s;
            margin: 5px 0;
        }

        input[type="text"]:focus {
            border-color: #FFD700;
            outline: none;
        }

        input[type="email"] {
            width: 80%;
            padding: 8px;
            font-size: 16px;
            border: 2px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s;
            margin: 5px 0;
        }

        input[type="email"]:focus {
            border-color: #FFD700;
            outline: none;
        }

        button, input[type="submit"] {
            background-color: #800000;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        button:hover, input[type="submit"]:hover {
            background-color: #A40000;
            transform: translateY(-2px);
        }


        .gray-row {
            background-color: #e0e0e0 !important;
            color: #555;
            font-style: italic;
        }

        .table-controls {
            width: 80%;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-controls .search-container form {
            display: flex;
            gap: 20px;
        }

        .table-controls input[type="text"] {
            margin: 0;
            width: auto;
        }
    </style>
    <script>
        function confirmDelete() {
            const checked = document.querySelectorAll('input[name="delete_ids[]"]:checked');
            if (checked.length === 0) {
                alert('Please select at least one record to delete.');
                return false;
            }
            return confirm('Are you sure you want to delete the selected record(s)?');
        }

        function confirmUpdate(name) {
            return confirm('Are you sure you want to update the record for ' + name + '?');
        }
    </script>

    <script>
        window.addEventListener("load", function() {
            if (localStorage.getItem("scrollPosition") !== null) {
                window.scrollTo(0, localStorage.getItem("scrollPosition"));
                localStorage.removeItem("scrollPosition"); 
            }
        });

        document.addEventListener("submit", function() {
            localStorage.setItem("scrollPosition", window.scrollY);
        });
    </script>

</head>

    <header class="main-header">
    <div class="logo-title">
        <h1>ADMIN PORTAL</h1>
        <a href="#" onclick="return confirmLogout();" class="logout-link">
            <img src="logout.png" alt="Logout" class="logout-icon">
        </a>
    </div>
    </header>

    <h2>Student Portal - Attendance Records</h2>

    <?php if ($message): ?>
        <script>alert('<?php echo addslashes($message); ?>');</script>
    <?php endif; ?>

    <div class="table-controls">
        <form method="POST" id="deleteForm" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <input type="submit" name="delete_selected" value="Delete Selected" class="delete-btn" onclick="return confirmDelete()">
        </form>

        <form method="GET" class="search-container" style="display: flex; gap: 10px;">
            <input type="text" name="student_search" placeholder="Search students..." value="<?php echo htmlspecialchars($studentSearch); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <table>
        <tr>
            <th>Select</th>
            <th>ID</th>
            <th>Name</th>
            <th>Gender</th>
            <th>Subjects</th>
            <th>Date & Time</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            $isEmpty = empty($row['timestamp']);
            $date = !$isEmpty ? date('m/d/Y \a\t h:i A', strtotime($row['timestamp'])) : 'No attendance yet';
            $rowClass = $isEmpty ? 'gray-row' : '';

            $studentId = $row['student_id'];
            $subjectList = [];

            $subjectQuery = "SELECT DISTINCT subject FROM instructors";
            $subjectResult = $conn->query($subjectQuery);

            if ($subjectResult && $subjectResult->num_rows > 0) {
                while ($subRow = $subjectResult->fetch_assoc()) {
                    $subjectList[] = $subRow['subject'];
                }
            }

            $subjects = implode(", ", $subjectList);


            $subjectList = array_unique($subjectList);
            $subjects = implode(", ", $subjectList);
            ?>
            <tr class="<?php echo $rowClass; ?>">
                <td><input form="deleteForm" type="checkbox" name="delete_ids[]" value="<?php echo $row['student_id']; ?>"></td>
                <td><?php echo $row['student_id']; ?></td>
                <td>
                    <form method="POST" onsubmit="return confirmUpdate('<?php echo addslashes($row['name']); ?>');">
                        <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                        <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>">
                </td>
                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                <td><?php echo htmlspecialchars($subjects); ?></td> 
                <td><?php echo $date; ?></td>
                <td>
                    <input type="submit" name="update" value="Update">
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php

    if (isset($_POST['add_instructor'])) {
        $iname = $conn->real_escape_string($_POST['instructor_name']);
        $isubj = $conn->real_escape_string($_POST['instructor_subject']);
        $iemail = $conn->real_escape_string($_POST['instructor_email']);

        if (!empty($_POST['instructor_password'])) {
            $password = password_hash($_POST['instructor_password'], PASSWORD_DEFAULT);
        } else {
            $password = ''; 
        }

        if (!empty($iname) && !empty($isubj) && !empty($iemail)) {
            if (!filter_var($iemail, FILTER_VALIDATE_EMAIL)) {
                echo "<script>alert('Invalid email format.'); window.location.href = 'student_list.php';</script>";
                exit;
            }
            $stmt = $conn->prepare("INSERT INTO instructors (name, subject, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $iname, $isubj, $iemail, $password);
            $stmt->execute();

            echo "<script>
                alert('Instructor added successfully.');
                window.location.href = 'student_list.php';
            </script>";
        } else {
            echo "<script>alert('Please fill all fields including email.'); window.location.href = 'student_list.php';</script>";
        }
    }

    if (isset($_POST['update_instructor'])) {
        $iid = intval($_POST['instructor_id']);
        $iname = $conn->real_escape_string($_POST['instructor_name']);
        $isubj = $conn->real_escape_string($_POST['instructor_subject']);
        $iemail = $conn->real_escape_string($_POST['instructor_email']);
        $new_password = $_POST['instructor_password'] ?? '';

        if (!filter_var($iemail, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Invalid email format.'); window.location.href = 'student_list.php#search-results';</script>";
            exit;
        }

        if (!empty($new_password)) {
            $password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $conn->query("UPDATE instructors SET name = '$iname', subject = '$isubj', email = '$iemail', password = '$password_hashed' WHERE instructor_id = $iid");
        } else {
            $conn->query("UPDATE instructors SET name = '$iname', subject = '$isubj', email = '$iemail' WHERE instructor_id = $iid");
        }

        echo "<script>alert('Instructor updated successfully.'); window.location.href = 'student_list.php#search-results';</script>";
    }

    if (isset($_POST['delete_selected_instructors'])) {
        if (!empty($_POST['delete_ids'])) {
            $ids = array_map('intval', $_POST['delete_ids']);
            $idsList = implode(",", $ids);
            $conn->query("DELETE FROM instructors WHERE instructor_id IN ($idsList)");
            $count = count($ids);
            echo "<script>
                alert('Instructor deleted successfully.');
                window.location.href = 'student_list.php';
            </script>";
        } else {
            echo "<script>alert('No instructors selected to delete.');
            location.reload();
            </script>";    
        }
    }
?>

<?php
?>

<h2>Instructor Management</h2>
<form method="POST" onsubmit="return validateInstructorForm();" style="width: 80%; margin: 0 auto 20px;">
    <div style="display: flex; gap: 10px; justify-content: center; align-items: center;">
        <input type="text" name="instructor_name" placeholder="Instructor Name" required
               style="flex: 1; padding: 8px; font-size: 16px; border: 2px solid #ccc; border-radius: 6px;">
        <input type="text" name="instructor_subject" placeholder="Subject" required
               style="flex: 1; padding: 8px; font-size: 16px; border: 2px solid #ccc; border-radius: 6px;">
        <input type="email" name="instructor_email" placeholder="Email" required
               style="flex: 1; padding: 8px; font-size: 16px; border: 2px solid #ccc; border-radius: 6px;">
    <div style="display: flex; flex-direction: column; flex: 1;">
    <input type="password" name="instructor_password" id="instructor_password" placeholder="Enter password" style="padding: 8px; font-size: 16px; border: 2px solid #ccc; border-radius: 6px;">
    <span id="passwordWarning" style="color: red; display: none; font-size: 0.9rem; margin-top: 4px;">
        Password must be at least 8 characters long.
    </span>
    </div>
        <button type="submit" name="add_instructor"
                style="background-color: #800000; color: white; padding: 10px 20px; font-size: 16px; border: none; border-radius: 6px; cursor: pointer; transition: background 0.3s, transform 0.2s;">
            Add Instructor
        </button>
    </div>
</form>

<div id="search-results" style="width: 80%; margin: 0 auto;">
    <form method="POST" id="deleteInstructorsForm">
        <input type="submit" name="delete_selected_instructors" value="Delete Selected"
               onclick="return confirmDeleteInstructors()"
               style="margin-bottom: 10px; background-color: #800000; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr style="background-color: #800000; color: white;">
                <th>Select</th>
                <th>ID</th>
                <th>Name</th>
                <th>Subject</th>
                <th>Email</th>
                <th>Password</th>
                <th>Actions</th>
            </tr>
            <?php
            $instructors = $conn->query($instructorQuery);
            while ($inst = $instructors->fetch_assoc()):
            ?>
                <tr>
                    <td><input type="checkbox" name="delete_ids[]" value="<?php echo $inst['instructor_id']; ?>"></td>
                    <td><?php echo $inst['instructor_id']; ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px; align-items:center;" onsubmit="return confirm('Update instructor <?php echo addslashes($inst['name']); ?>?');">
                            <input type="hidden" name="instructor_id" value="<?php echo $inst['instructor_id']; ?>">
                            <input type="text" name="instructor_name" value="<?php echo htmlspecialchars($inst['name']); ?>" required style="width: 90%;">
                    </td>
                    <td><input type="text" name="instructor_subject" value="<?php echo htmlspecialchars($inst['subject']); ?>" required style="width: 90%;"></td>
                    <td><input type="email" name="instructor_email" value="<?php echo htmlspecialchars($inst['email']); ?>" required style="width: 90%;"></td>
                    <td style="font-family: monospace;">
                    <?php echo substr($inst['password'], 0, 16); ?>
                    </td>
                    <td>
                            <button type="submit" name="update_instructor" style="background-color: #800000; border:none; padding: 6px 12px; color:white; border-radius: 4px; cursor:pointer;">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </form>

    <form method="GET" class="search-container" action="student_list.php#search-results" style="margin-top: 15px; margin-bottom: 30px; width: 100%; display: flex;">
        <input type="text" name="instructor_search" placeholder="Search instructors..." 
               value="<?php echo htmlspecialchars($instructorSearch); ?>" 
               style="flex: 1; padding: 10px; font-size: 16px; border: 2px solid #800000; border-radius: 6px 0 0 6px;">
        <button type="submit"
                style="padding: 10px 20px; font-size: 16px; border: none; background-color: #800000; color: white; border-radius: 0 6px 6px 0; cursor: pointer;">
            Search
        </button>
    </form>
</div>
<script>
    function confirmDeleteInstructors() {
        const checked = document.querySelectorAll('input[name="delete_ids[]"]:checked');
        if (checked.length === 0) {
            alert('Please select at least one instructor to delete.');
            return false;
        }
        return confirm('Are you sure you want to delete the selected instructor(s)?');
    }
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const passwordInput = document.getElementById("instructor_password");
    const warning = document.getElementById("passwordWarning");

    passwordInput.addEventListener("input", function() {
        if (passwordInput.value.length > 0 && passwordInput.value.length < 8) {
            warning.style.display = "inline";
        } else {
            warning.style.display = "none";
        }
    });
});
</script>

<script>
    function validateInstructorForm() {
        const password = document.querySelector('input[name="instructor_password"]').value;
        if (password.length < 8) {
            Swal.fire({
                icon: 'error',
                title: 'Weak Password',
                text: 'Password must be at least 8 characters long!',
                confirmButtonColor: '#800000'
            });
            return false;
        }
        return true;
    }
</script>

<script>
    function confirmLogout() {
            Swal.fire({
                title: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FFD700',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'admin_login.php';
                }
            });
            return false;
    }
</script>

<script>
    window.addEventListener("load", function() {
        if (sessionStorage.getItem('loginSuccess')) {
            Swal.fire({
                icon: 'success',
                title: 'Welcome Admin!',
                text: 'You have logged in successfully.',
                timer: 2000,
                showConfirmButton: false
            });
            sessionStorage.removeItem('loginSuccess');
        }

        if (sessionStorage.getItem('loginError')) {
            Swal.fire({
                icon: 'error',
                title: 'Login Failed!',
                text: 'Invalid username or password.',
                timer: 2500,
                showConfirmButton: false
            });
            sessionStorage.removeItem('loginError');
        }
    });
</script>

    <footer class="main-footer">
        <p>&copy; <?= date('Y') ?> Eastern Visayas State University — Student Records System</p>
    </footer>

</body>
</html>