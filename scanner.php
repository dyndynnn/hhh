<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
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
            position: relative; 
        }

        .back-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 30px; 
            color: yellow;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
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
        }

        .logo-title img {
            width: 50px; 
            height: 50px; 
            margin-right: 15px;
        }

        .logo-title h1 {
            font-size: 1.6rem;
            color: white;
        }

        .logo-title p {
            font-size: 0.9rem;
            color: white;
            margin-top: 5px;
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
            color: #800000;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        #reader {
            width: 450px;  
            height: 500px; 
            margin-bottom: 30px; 
            border: 4px solid #800000;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            display: inline-block; 
            vertical-align: middle;  
        }

        #scanned-data {
            display: none;
            text-align: center;
            background-color: #ffffff;
            border: 2px solid #ccc;
            padding: 10px;  
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            height: auto;  
            max-width: 450px;
            margin-top: 7px;
            margin-bottom: 30px;
        }

        #scanned-data p {
            font-size: 1.4rem;
            color: #333;
            font-weight: bold;
            margin-bottom: 5px; 
        }

        #scanned-data img {
            margin-top: 20px;  
            max-width: 100%;
            height: auto;  
            margin-bottom: -50px;  
        }

        button {
            background-color: #800000;
            color: white;
            padding: 12px 24px;  
            font-size: 18px; 
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            margin-top: 20px; 
        }

        button:hover {
            background-color: #A40000;
            transform: translateY(-2px);
        }

        button:focus {
            outline: none;
        }

    </style>
</head>
<body>
   <header class="main-header">
   <a href="instructor_dashboard.php" class="back-icon">&#8592;</a>
        <div class="logo-title">
            <img src="EVSU_logo.png" alt="EVSU Logo">
            <div>
                <h1>EVSU OC- Student Records</h1>
                <div class="yellow-line"></div>
                <p class="subtext">Manage, Update, and Track Attendance Records</p>
            </div>
        </div>
    </header>

    <h2>Scan QR Code</h2>

    <div id="reader"></div>

    <div id="scanned-data">
        <p><strong>Scanned:</strong> <span id="qr-content"></span></p>
        <button onclick="submitAttendance()">Submit Attendance</button>
    </div>

    <script>
        let scannedText = "";

        function onScanSuccess(decodedText, decodedResult) {
            scannedText = decodedText;
            let parts = decodedText.split(",");
            let nameOnly = parts[1];
            document.getElementById("qr-content").innerText = nameOnly;
            document.getElementById("scanned-data").style.display = "block";
        }

        function submitAttendance() {
            fetch("log_attendance.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "data=" + encodeURIComponent(scannedText)
            })
            .then(res => res.text())
            .then(response => {
                alert(response);
                location.href = "scanner.php";
            })
            .catch(console.error);
        }

        const html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
    </script>

    <footer class="main-footer">
        <p>&copy; <?= date('Y') ?> Eastern Visayas State University — Student Records System</p>
    </footer>
</body>
</html>
