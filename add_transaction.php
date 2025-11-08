<?php
include 'db_connect.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $customer_id = $_POST['customer_id'];
    $date = $_POST['date'];
    $item = $_POST['item'];
    $qty = $_POST['qty'];
    $rate = $_POST['rate'];
    $deposit = $_POST['deposit'];
    $borrow = $_POST['borrow'];

    $stmt = $conn->prepare("INSERT INTO transactions (customer_id, date, item, qty, rate, deposit, borrow) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdddd", $customer_id, $date, $item, $qty, $rate, $deposit, $borrow);

    if($stmt->execute()){
        echo "<div class='success'>✅ नोंद यशस्वीरित्या जोडली!</div>";
        echo "<script>setTimeout(()=>window.location.href='ledger.php?customer_id=$customer_id',800);</script>";
    } else {
        echo "<div class='error'>❌ त्रुटी: ".$conn->error."</div>";
    }
    $stmt->close();
}
?>
