<?php
include 'db_connect.php';
require_once __DIR__ . '/vendor/autoload.php'; // ✅ mPDF लायब्ररी जोडली



$customer_id = $_GET['customer_id'] ?? 0;

// ग्राहक माहिती
$customerQuery = $conn->query("SELECT name, opening_balance FROM customers WHERE id = $customer_id");
$customer = $customerQuery->fetch_assoc();
$customer_name = $customer['name'] ?? 'Unknown';
$opening_balance = $customer['opening_balance'] ?? 0.00;

// delete नोंद
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM transactions WHERE id = $delete_id");
    header("Location: ledger.php?customer_id=$customer_id");
    exit();
}

// update opening balance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_opening'])) {
    $new_opening = $_POST['new_opening'];
    $stmt = $conn->prepare("UPDATE customers SET opening_balance = ? WHERE id = ?");
    $stmt->bind_param("di", $new_opening, $customer_id);
    $stmt->execute();
    $stmt->close();
    header("Location: ledger.php?customer_id=$customer_id");
    exit();
}

// add entry
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $date = $_POST['date'];
    $item = $_POST['item'];
    $qty = $_POST['qty'];
    $rate = $_POST['rate'];
    $deposit = $_POST['deposit'];
    $borrow = $_POST['borrow'];

    $stmt = $conn->prepare("INSERT INTO transactions (customer_id, date, item, qty, rate, deposit, borrow) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issiddd", $customer_id, $date, $item, $qty, $rate, $deposit, $borrow);
    $stmt->execute();
    $stmt->close();
}

// update entry
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $date = $_POST['date'];
    $item = $_POST['item'];
    $qty = $_POST['qty'];
    $rate = $_POST['rate'];
    $deposit = $_POST['deposit'];
    $borrow = $_POST['borrow'];

    $stmt = $conn->prepare("UPDATE transactions SET date=?, item=?, qty=?, rate=?, deposit=?, borrow=? WHERE id=?");
    $stmt->bind_param("ssddddi", $date, $item, $qty, $rate, $deposit, $borrow, $id);
    $stmt->execute();
    $stmt->close();
}

// PDF Download
if (isset($_GET['export_pdf'])) {
    $transactions = $conn->query("SELECT * FROM transactions WHERE customer_id = $customer_id ORDER BY date ASC");

    $html = "<h2 style='text-align:center;'>$customer_name — Account Ledger</h2>";
    $html .= "<p><b>प्रारंभिक शिल्लक:</b> ₹" . number_format($opening_balance, 2) . "</p>";
    $html .= "<table border='1' width='100%' cellspacing='0' cellpadding='5'>
    <tr>
        <th>तारीख</th>
        <th>आयटम</th>
        <th>Qty</th>
        <th>दर (₹)</th>
        <th>एकूण (₹)</th>
        <th>जमा (₹)</th>
        <th>घेणे (₹)</th>
        <th>शिल्लक (₹)</th>
    </tr>";

    $balance = $opening_balance;
    $totalQty = $totalDeposit = $totalBorrow = $totalItemAmount = 0;

    $html .= "<tr><td colspan='7'><b>प्रारंभिक शिल्लक</b></td><td><b>₹" . number_format($balance, 2) . "</b></td></tr>";

    while ($row = $transactions->fetch_assoc()) {
        $item_total = $row['qty'] * $row['rate'];
        $balance = $balance + ($item_total + $row['deposit']) - $row['borrow'];

        $totalQty += $row['qty'];
        $totalDeposit += $row['deposit'];
        $totalBorrow += $row['borrow'];
        $totalItemAmount += $item_total;

        $html .= "<tr>
            <td>{$row['date']}</td>
            <td>{$row['item']}</td>
            <td>{$row['qty']}</td>
            <td>" . number_format($row['rate'], 2) . "</td>
            <td>" . number_format($item_total, 2) . "</td>
            <td>" . number_format($row['deposit'], 2) . "</td>
            <td>" . number_format($row['borrow'], 2) . "</td>
            <td><b>₹" . number_format($balance, 2) . "</b></td>
        </tr>";
    }

    $html .= "<tr style='background:#f1f1f1;font-weight:bold;'>
        <td colspan='2'>एकूण</td>
        <td>$totalQty</td>
        <td>-</td>
        <td>₹" . number_format($totalItemAmount, 2) . "</td>
        <td>₹" . number_format($totalDeposit, 2) . "</td>
        <td>₹" . number_format($totalBorrow, 2) . "</td>
        <td>₹" . number_format($balance, 2) . "</td>
    </tr></table>";

    // ✅ PDF तयार करा
    $mpdf = new \mPDF('utf-8', 'A4');
    $mpdf->SetFooter("Generated on " . date('d-m-Y H:i') . " | {PAGENO}");
    $mpdf->WriteHTML($html);
    $mpdf->Output("Ledger_$customer_name.pdf", 'D');
    exit;
}

// व्यवहार मिळवा
$transactions = $conn->query("SELECT * FROM transactions WHERE customer_id = $customer_id ORDER BY date ASC");
?>

<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($customer_name); ?> — Account Ledger</title>
<style>
    body {
        font-family: 'Noto Sans Devanagari', sans-serif;
        background-color: #f5f6fa;
        padding: 20px;
    }
    h2 {
        text-align: center;
        background-color: #007bff;
        color: white;
        padding: 10px;
        border-radius: 5px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: white;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }
    th {
        background-color: #000;
        color: white;
    }
    input, button {
        padding: 6px;
        margin: 5px;
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
    .summary {
        background-color: #f1f1f1;
        font-weight: bold;
    }
    #total_display {
        font-weight: bold;
        color: green;
    }
</style>
</head>
<body>

<h2><?php echo htmlspecialchars($customer_name); ?> — Account Ledger</h2>

<!-- ✅ PDF Download बटण -->
<form method="GET" style="margin-bottom:15px;">
    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
    <button type="submit" name="export_pdf" style="background:red;">📄 PDF Download</button>
</form>

<!-- Opening Balance -->
<form method="POST" style="margin-bottom:15px;">
    <label><b>प्रारंभिक शिल्लक (₹):</b></label>
    <input type="number" name="new_opening" step="0.01" value="<?php echo $opening_balance; ?>" required>
    <button type="submit" name="update_opening" style="background:green;">💾 जतन करा</button>
</form>

<!-- बाकी तुझं form आणि table जसंच्या तसं ठेवलं आहे -->
<form method="POST">
    <input type="hidden" name="id" id="edit_id">
    <label>तारीख:</label>
    <input type="date" name="date" id="edit_date" required>
    <label>आयटम:</label>
    <input type="text" name="item" id="edit_item" required>
    <label>Qty:</label>
    <input type="number" name="qty" id="edit_qty" min="1" required oninput="calculateTotal()">
    <label>दर (₹):</label>
    <input type="number" name="rate" id="edit_rate" step="0.01" required oninput="calculateTotal()">
    <span id="total_display">एकूण ₹0.00</span>
    <label>जमा (₹):</label>
    <input type="number" name="deposit" id="edit_deposit" step="0.01" value="0">
    <label>घेणे (₹):</label>
    <input type="number" name="borrow" id="edit_borrow" step="0.01" value="0">
    <button type="submit" name="add">➕ नोंद जोडा</button>
    <button type="submit" name="update" style="background:orange;">✏️ Update</button>
</form>

<table>
    <tr>
        <th>तारीख</th>
        <th>आयटम</th>
        <th>Qty</th>
        <th>दर (₹)</th>
        <th>एकूण (₹)</th>
        <th>जमा (₹)</th>
        <th>घेणे (₹)</th>
        <th>शिल्लक (₹)</th>
        <th>क्रिया</th>
    </tr>

    <?php
    $balance = $opening_balance;
    $totalDeposit = 0;
    $totalBorrow = 0;
    $totalQty = 0;
    $totalItemAmount = 0;

    echo "<tr><td colspan='8'><b>प्रारंभिक शिल्लक</b></td><td><b>₹" . number_format($balance, 2) . "</b></td></tr>";

    if ($transactions && $transactions->num_rows > 0) {
        while ($row = $transactions->fetch_assoc()) {
            $item_total = $row['qty'] * $row['rate'];
            $balance = $balance + ($item_total + $row['deposit']) - $row['borrow'];

            $totalDeposit += $row['deposit'];
            $totalBorrow += $row['borrow'];
            $totalQty += $row['qty'];
            $totalItemAmount += $item_total;

            echo "<tr>";
            echo "<td>{$row['date']}</td>";
            echo "<td>{$row['item']}</td>";
            echo "<td>{$row['qty']}</td>";
            echo "<td>" . number_format($row['rate'], 2) . "</td>";
            echo "<td>" . number_format($item_total, 2) . "</td>";
            echo "<td>" . number_format($row['deposit'], 2) . "</td>";
            echo "<td>" . number_format($row['borrow'], 2) . "</td>";
            echo "<td><b>₹" . number_format($balance, 2) . "</b></td>";
            echo "<td>
                    <button onclick=\"editEntry(" . htmlspecialchars(json_encode($row)) . ")\">✏️ Edit</button>
                    <a href='ledger.php?customer_id=$customer_id&delete_id={$row['id']}' onclick=\"return confirm('तुम्हाला ही नोंद काढून टाकायची आहे का?');\">
                        <button style='background:red;'>🗑️ Delete</button>
                    </a>
                  </td>";
            echo "</tr>";
        }

        echo "<tr class='summary'>
                <td colspan='2'><b>एकूण:</b></td>
                <td><b>$totalQty</b></td>
                <td>-</td>
                <td><b>₹" . number_format($totalItemAmount, 2) . "</b></td>
                <td><b>₹" . number_format($totalDeposit, 2) . "</b></td>
                <td><b>₹" . number_format($totalBorrow, 2) . "</b></td>
                <td><b>₹" . number_format($balance, 2) . "</b></td>
                <td>-</td>
              </tr>";
    } else {
        echo "<tr><td colspan='9' style='color:gray;'>नोंदी उपलब्ध नाहीत.</td></tr>";
    }
    ?>
</table>

<script>
function editEntry(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_date').value = data.date;
    document.getElementById('edit_item').value = data.item;
    document.getElementById('edit_qty').value = data.qty;
    document.getElementById('edit_rate').value = data.rate;
    document.getElementById('edit_deposit').value = data.deposit;
    document.getElementById('edit_borrow').value = data.borrow;
    calculateTotal();
    window.scrollTo(0, 0);
}

// Live Qty × Rate calculation
function calculateTotal() {
    const qty = parseFloat(document.getElementById('edit_qty').value) || 0;
    const rate = parseFloat(document.getElementById('edit_rate').value) || 0;
    const total = qty * rate;
    document.getElementById('total_display').innerText = "एकूण ₹" + total.toFixed(2);
}
</script>

</body>
</html>
