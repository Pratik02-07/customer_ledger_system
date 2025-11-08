<?php
include 'db_connect.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $customer_id = $_GET['customer_id'];
    $result = $conn->query("SELECT * FROM transactions WHERE id=$id");
    $row = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>नोंद बदला</title>
<style>
body {
    font-family: 'Noto Sans Devanagari', Arial, sans-serif;
    background: #f7f9fb;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
form {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
input {
    margin: 8px 0;
    padding: 8px;
    width: 100%;
}
button {
    padding: 10px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    background: #0056b3;
}
</style>
</head>
<body>

<form method="POST" action="update_transaction.php">
    <h2>नोंद बदला</h2>
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">

    तारीख: <input type="date" name="date" value="<?php echo $row['date']; ?>" required>
    आयटम: <input type="text" name="item" value="<?php echo $row['item']; ?>" required>
    Qty: <input type="number" step="0.01" name="qty" value="<?php echo $row['qty']; ?>" required>
    दर (₹): <input type="number" step="0.01" name="rate" value="<?php echo $row['rate']; ?>" required>
    जमा (₹): <input type="number" step="0.01" name="deposit" value="<?php echo $row['deposit']; ?>">
    घेणे (₹): <input type="number" step="0.01" name="borrow" value="<?php echo $row['borrow']; ?>">

    <button type="submit">💾 Save Changes</button>
</form>

</body>
</html>
