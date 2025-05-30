<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "qr_attendance");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$totalQuery = "SELECT COUNT(*) as total FROM students";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalStudents = $totalRow['total'] ?? 1; 

$presentQuery = "SELECT COUNT(*) as present FROM students WHERE timestamp IS NOT NULL AND timestamp != ''";
$presentResult = $conn->query($presentQuery);
$present = $presentResult->fetch_assoc()['present'] ?? 0;

$absent = $totalStudents - $present;

$maleQuery = "SELECT COUNT(*) as male FROM students WHERE gender = 'Male'";
$maleResult = $conn->query($maleQuery);
$male = $maleResult->fetch_assoc()['male'] ?? 0;

$femaleQuery = "SELECT COUNT(*) as female FROM students WHERE gender = 'Female'";
$femaleResult = $conn->query($femaleQuery);
$female = $femaleResult->fetch_assoc()['female'] ?? 0;

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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            padding: 30px 30px; 
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

<body>
<header class="main-header">
    <div class="logo-title" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">INSTRUCTOR DASHBOARD</h1>
        <div style="display: flex; align-items: center; gap: 10px;">

            <a href="scanner.php" class="logout-link" title="Scan attendance">
                <img src="scanner.png" alt="Scan attendance" class="logout-icon" style="filter: brightness(0) saturate(100%) invert(89%) sepia(73%) saturate(749%) hue-rotate(1deg) brightness(103%) contrast(101%);">
            </a>

            <a href="#" onclick="return confirmChangePassword();" class="logout-link" title="Change Password">
                <img src="change-pass.png" alt="Change Password" class="logout-icon" style="filter: brightness(0) saturate(100%) invert(89%) sepia(73%) saturate(749%) hue-rotate(1deg) brightness(103%) contrast(101%);">
            </a>

            <a href="#" onclick="return confirmLogout();" class="logout-link" title="Logout">
                <img src="logout.png" alt="Logout" class="logout-icon" style="filter: brightness(0) saturate(100%) invert(89%) sepia(73%) saturate(749%) hue-rotate(1deg) brightness(103%) contrast(101%);">
            </a>
        </div>
    </div>
</header>


    <h2>Student Portal - Attendance Records</h2>
    <div style="width: 80%; margin: 20px auto; margin-bottom: 40px; display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 220px; background: #fff; border-left: 6px solid #28a745; padding: 8px 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px; color: #28a745;">Present</h3>
            <p><strong><?php echo $present; ?></strong> student(s)</p>
            <p><?php echo round(($present / $totalStudents) * 100, 1); ?>% of total</p>
        </div>
        <div style="flex: 1; min-width: 220px; background: #fff; border-left: 6px solid #dc3545; padding: 8px 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px; color: #dc3545;">Absent</h3>
            <p><strong><?php echo $absent; ?></strong> student(s)</p>
            <p><?php echo round(($absent / $totalStudents) * 100, 1); ?>% of total</p>
        </div>
        <div style="flex: 1; min-width: 220px; background: #fff; border-left: 6px solid #007bff; padding: 8px 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px; color: #007bff;">Male</h3>
            <p><strong><?php echo $male; ?></strong> student(s)</p>
            <p><?php echo round(($male / $totalStudents) * 100, 1); ?>% of total</p>
        </div>
        <div style="flex: 1; min-width: 220px; background: #fff; border-left: 6px solid #e83e8c; padding: 8px 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px; color: #e83e8c;">Female</h3>
            <p><strong><?php echo $female; ?></strong> student(s)</p>
            <p><?php echo round(($female / $totalStudents) * 100, 1); ?>% of total</p>
        </div>
    </div>

    <div class="table-controls" style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
        <form method="GET" class="search-container" style="display: flex; gap: 10px;">
            <input type="text" name="student_search" placeholder="Search students..." value="<?php echo htmlspecialchars($studentSearch); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Gender</th>
            <th>Subjects</th>
            <th>Date & Time</th>
            <th>Status</th>
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
                <td><?php echo $row['student_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                <td><?php echo htmlspecialchars($subjects); ?></td> 
                <td><?php echo $date; ?></td>
                <td><?php echo $isEmpty ? 'No Record' : 'Present'; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

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
                    window.location.href = 'instructor_login.php';
                }
            });
            return false;
        }

        function confirmChangePassword() {
            Swal.fire({
                title: 'Do You Want To Change Password?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FFD700',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'change-pass.php';
                }
            });
            return false;
        }

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