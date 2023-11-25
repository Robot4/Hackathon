<?php
include "db.php"; // Include your database connection file

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Hash the password before storing it in the database
    $hashed_password = password_hash($input_password, PASSWORD_DEFAULT);

    // Check if the username is already taken
    $check_username_query = "SELECT id FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_username_query);
    $check_stmt->bind_param("s", $input_username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $error_message = "Username already taken. Please choose a different one.";
    } else {
        // Insert the new user into the database
        $insert_user_query = "INSERT INTO users (username, password) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_user_query);
        $insert_stmt->bind_param("ss", $input_username, $hashed_password);

        if ($insert_stmt->execute()) {
            $success_message = "Account created successfully. You can now login.";
            header("Location: index.php");  // Redirect to index.php
        } else {
            $error_message = "Error creating account. Please try again.";
        }

        $insert_stmt->close();
    }

    $check_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
</head>
<body>
<form action="" method="post">

    <h2>Create Account</h2>

    <?php
    if(isset($error_message)) {
        echo "<p style='color: red;'>$error_message</p>";
    } elseif (isset($success_message)) {
        echo "<p style='color: green;'>$success_message</p>";
    }
    ?>

    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br>

    <input type="submit" value="Create Account">

</form>
</body>
</html>
