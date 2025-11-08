<?php
include 'db_connect.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id = $_POST['id'];
    $customer_id = $_POST['customer_id'];

    $stmt = $conn->prepare("DELETE FROM transactions WHERE id=?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        echo "<div class='success'>✅ नोंद यशस्वीरित्या काढून टाकली!</div>";
        echo "<script>setTimeout(()=>window.location.href='ledger.php?customer_id=$customer_id',800);</script>";
    } else {
        echo "<div class='error'>❌ त्रुटी: ".$conn->error."</div>";
    }

    $stmt->close();
}
?>
