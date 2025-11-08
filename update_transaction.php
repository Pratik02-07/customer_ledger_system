<?php
include 'db_connect.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id = $_POST['id'];
    $customer_id = $_POST['customer_id'];
    $date = $_POST['date'];
    $item = $_POST['item'];
    $qty = floatval($_POST['qty']);
    $rate = floatval($_POST['rate']);
    $deposit = floatval($_POST['deposit']);

    // ⚙️ Borrow (घेणे) = Qty × Rate
    $borrow = $qty * $rate;

    $stmt = $conn->prepare("UPDATE transactions SET date=?, item=?, qty=?, rate=?, deposit=?, borrow=? WHERE id=?");
    $stmt->bind_param("ssddddi", $date, $item, $qty, $rate, $deposit, $borrow, $id);

    if($stmt->execute()){
        echo "<div style='text-align:center;background:#d4edda;padding:10px;font-size:18px;'>✅ नोंद यशस्वीरित्या अपडेट झाली!</div>";
        echo "<script>setTimeout(()=>window.location.href='ledger.php?customer_id=$customer_id',1200);</script>";
    } else {
        echo "<div style='text-align:center;background:#f8d7da;padding:10px;'>❌ त्रुटी: ".$conn->error."</div>";
    }

    $stmt->close();
}
?>
