<!DOCTYPE html>
<html>
<head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: #f2f2f2;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh; 
        }

        .main-header {
            background-color: #800000;
            color: white;
            width: 100%;
            padding: 20px 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px; 
            text-align:center;
            position: relative;
        }

        .back-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 50px; 
            color: yellow;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .back-icon:hover {
            color: #FFD700;
        }

        .yellow-line {
            height: 2px;
            background-color: yellow;
            width: 100%; 
            margin: 5px auto;
        }

        .logo-title {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-title img {
            height: 60px;
            width: 60px;
            object-fit: contain;
        }

        .logo-title h1 {
            margin: 0;
            font-size: 1.8rem;
            letter-spacing: 0.5px;
        }

        .subtext {
            margin: 5px 0 0;
            font-size: 1rem;
            color: #f5f5f5;
        }

        .main-footer {
            width: 100%;
            background: #f0f0f0;
            text-align: center;
            padding: 15px;
            font-size: 1rem;
            color: #333;
            border-top: 1px solid #ccc;
            margin-top: auto; 
            box-shadow: 0 -2px 6px rgba(0,0,0,0.1);
        }

        h2 {
            margin-top: 30px;
            color: #800000;
            font-size: 2rem;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        form#deleteForm {
            margin-bottom: 20px;
        }

        button, input[type="submit"] {
            background-color: #800000;
            color: #fff;
            border: none;
            padding: 10px 16px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        button:hover, input[type="submit"]:hover {
            background-color: #A40000;
            transform: translateY(-2px);
        }

        table {
            width: 95%;
            max-width: 1000px;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 30px;
        }

        th, td {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            font-size: 16px;
        }

        th {
            background-color: #FFD700;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #ffe9e0;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            border: 2px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            border-color: #FFD700;
            outline: none;
        }

    </style>
</head>
<body>
   <header class="main-header">
    <a href="register.php" class="back-icon">&#8592;</a>
        <div class="logo-title">
            <img src="EVSU_logo.png" alt="EVSU Logo">
            <div>
                <h1>EVSU OC- Student Records</h1>
                <div class="yellow-line"></div>
                <p class="subtext">Manage, Update, and Track Attendance Records</p>
            </div>
        </div>
    </header>

    <h2>Student Attendance Record</h2>

    <?php
        $conn = new mysqli("localhost", "root", "", "qr_attendance");
        $result = $conn->query("SELECT * FROM students WHERE timestamp IS NOT NULL ORDER BY timestamp DESC");

        echo "<table><tr><th>ID</th><th>Name</th><th>Date & Time</th></tr>";
        while ($row = $result->fetch_assoc()) {
            if ($row['timestamp']) {
                $formattedDate = date("m/d/Y", strtotime($row['timestamp'])) . " at " . date("h:i A", strtotime($row['timestamp']));
            } else {
                $formattedDate = ' ';
            }

            echo "<tr><td>{$row['student_id']}</td><td>{$row['name']}</td><td>$formattedDate</td></tr>";
        }
        echo "</table>";
    ?>

    <footer class="main-footer">
        <p>&copy; <?= date('Y') ?> Eastern Visayas State University — Student Records System</p>
    </footer>
</body>
</html>
