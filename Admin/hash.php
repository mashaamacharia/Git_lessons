<?php
include('connection.php');

$query = "SELECT id, password FROM admins"; 
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $admin_id = $row['id'];
    $plain_text_password = $row['password']; // This is the old plain text password
    $hashed_password = password_hash($plain_text_password, PASSWORD_DEFAULT);

    $update_query = "UPDATE admins SET password = '$hashed_password' WHERE id = $admin_id";
    mysqli_query($conn, $update_query);
}

echo "All passwords have been hashed successfully!";
?>
