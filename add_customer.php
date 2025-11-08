<?php
include 'db_connect.php';

// ग्राहक add करण्यासाठी फॉर्म सबमिट झाल्यावर
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $opening_balance = $_POST['opening_balance'] ?? 0.00; // नवीन शिल्लक फील्ड

    if ($name != '') {
        $stmt = $conn->prepare("INSERT INTO customers (name, opening_balance) VALUES (?, ?)");
        $stmt->bind_param("sd", $name, $opening_balance);

        if ($stmt->execute()) {
            echo "<p style='color:green;'>✅ ग्राहक जोडला गेला: <b>$name</b> (शिल्लक ₹$opening_balance)</p>";
        } else {
            echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color:red;'>⚠️ कृपया ग्राहकाचे नाव भरा!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <title>ग्राहक जोडा</title>
    <style>
        body {
            font-family: 'Noto Sans Devanagari', sans-serif;
            margin: 40px;
            background-color: #f4f4f4;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, button {
            width: 100%;
            margin: 8px 0;
            padding: 10px;
            font-size: 16px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<h2>➕ नवीन ग्राहक जोडा</h2>

<form method="POST">
    <label>ग्राहकाचे नाव:</label>
    <input type="text" name="name" placeholder="उदा. Akshay Hirugade" required>

    <label>प्रारंभिक शिल्लक (₹):</label>
    <input type="number" step="0.01" name="opening_balance" placeholder="उदा. 5000" value="0.00">

    <button type="submit">ग्राहक जोडा</button>
</form>

</body>
</html>
