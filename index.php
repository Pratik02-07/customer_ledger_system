<?php
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>ग्राहक खाते प्रणाली (Customer Ledger System)</title>
<style>
body {
    font-family: 'Noto Sans Devanagari', Arial, sans-serif;
    background: #f2f4f7;
    margin: 0;
    padding: 0;
}
h2 {
    background: #007bff;
    color: white;
    text-align: center;
    padding: 15px;
    margin: 0;
}
.container {
    width: 80%;
    margin: 20px auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
}
input[type="text"], input[type="search"] {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    width: 70%;
    font-size: 16px;
}
button, .btn {
    padding: 8px 15px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    color: white;
}
.btn-add { background: #28a745; }
.btn-edit { background: #ffc107; color: black; }
.btn-delete { background: #dc3545; }
.btn-view { background: #007bff; }
.btn:hover { opacity: 0.9; }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
th {
    background: #007bff;
    color: white;
}
.success, .error {
    padding: 10px;
    margin: 10px 0;
    border-radius: 6px;
    font-weight: bold;
}
.success { background: #d4edda; color: #155724; }
.error { background: #f8d7da; color: #721c24; }
.search-box {
    margin-bottom: 15px;
    text-align: right;
}
</style>
</head>
<body>

<h2>ग्राहक खाते प्रणाली (Customer Ledger System)</h2>

<div class="container">

    <div class="search-box">
        <input type="search" id="searchInput" placeholder="🔍 ग्राहक शोधा...">
    </div>

    <table id="customerTable">
        <tr>
            <th>क्रमांक</th>
            <th>ग्राहकाचे नाव</th>
            <th>क्रिया</th>
        </tr>
        <?php
        $res = $conn->query("SELECT * FROM customers ORDER BY id DESC");
        if($res && $res->num_rows > 0){
            $no = 1;
            while($r = $res->fetch_assoc()){
                echo "<tr>
                        <td>{$no}</td>
                        <td>{$r['name']}</td>
                        <td>
                            <a href='ledger.php?customer_id={$r['id']}' class='btn btn-view'>🔍 पहा</a>
                            <a href='index.php?edit={$r['id']}' class='btn btn-edit'>✏️ Edit</a>
                            <a href='index.php?delete={$r['id']}' class='btn btn-delete' onclick=\"return confirm('❌ ग्राहक हटवायचा आहे का?');\">🗑 Delete</a>
                        </td>
                      </tr>";
                $no++;
            }
        } else {
            echo "<tr><td colspan='3' style='color:gray;'>कोणतेही ग्राहक नाहीत.</td></tr>";
        }
        ?>
    </table>

    <hr>

    <?php
    // 🟡 EDIT MODE (ग्राहक edit साठी)
    if(isset($_GET['edit'])){
        $edit_id = $_GET['edit'];
        $edit_query = $conn->query("SELECT * FROM customers WHERE id=$edit_id");
        if($edit_query && $edit_query->num_rows > 0){
            $data = $edit_query->fetch_assoc();
            ?>
            <h3>✏️ ग्राहक नाव संपादित करा</h3>
            <form method="POST">
                <input type="hidden" name="update_id" value="<?php echo $data['id']; ?>">
                <input type="text" name="update_name" value="<?php echo $data['name']; ?>" required>
                <button type="submit" name="update_customer" class="btn btn-edit">Update</button>
                <a href="index.php" class="btn btn-view">रद्द करा</a>
            </form>
            <?php
        }
    }

    // 🟢 UPDATE CUSTOMER (नाव database मध्ये update होईल)
    if(isset($_POST['update_customer'])){
        $id = $_POST['update_id'];
        $new_name = trim($_POST['update_name']);
        if($new_name != ''){
            $stmt = $conn->prepare("UPDATE customers SET name=? WHERE id=?");
            $stmt->bind_param("si", $new_name, $id);
            if($stmt->execute()){
                echo "<div class='success'>✅ ग्राहक नाव यशस्वीरित्या बदलले!</div>";
                echo "<script>setTimeout(()=>window.location.href='index.php',1000);</script>";
            } else {
                echo "<div class='error'>❌ Error: ".$conn->error."</div>";
            }
            $stmt->close();
        }
    }

    // 🟢 ADD CUSTOMER
    if(isset($_POST['add_customer'])){
        $name = trim($_POST['name']);
        if($name != ''){
            $stmt = $conn->prepare("INSERT INTO customers (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            if($stmt->execute()){
                echo "<div class='success'>✅ ग्राहक जोडला गेला: $name</div>";
                echo "<script>setTimeout(()=>window.location.href='index.php',1000);</script>";
            } else {
                echo "<div class='error'>❌ Error: ".$conn->error."</div>";
            }
            $stmt->close();
        }
    }

    // 🗑 DELETE CUSTOMER
    if(isset($_GET['delete'])){
        $id = $_GET['delete'];
        $conn->query("DELETE FROM customers WHERE id=$id");
        echo "<div class='error'>🗑 ग्राहक हटवला गेला!</div>";
        echo "<script>setTimeout(()=>window.location.href='index.php',1000);</script>";
    }
    ?>

    <h3>➕ नवीन ग्राहक जोडा</h3>
    <form method="POST">
        <input type="text" name="name" placeholder="ग्राहकाचं नाव लिहा" required>
        <button type="submit" name="add_customer" class="btn btn-add">Save Customer</button>
    </form>

</div>

<script>
// 🔍 Search Filter
document.getElementById("searchInput").addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll("#customerTable tr");
    rows.forEach((row, i) => {
        if (i === 0) return;
        const name = row.cells[1]?.textContent.toLowerCase();
        row.style.display = name.includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>
