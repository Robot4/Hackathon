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
<html>
<head>
    <title>Reccetes</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a81368914c.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/login.css" />

</head>
<body bgcolor="#28bc89">
<img class="wave" src="http://github.com/sefyudem/Responsive-Login-Form/blob/master/img/wave.png?raw=true">
<div class="container">
    <div class="img">
        <img id="bg" src="images/pngwing.com.png">

    </div>
    <div class="login-content">


        <form action="" method="post">

            <img src="images/login.logo.png">
            <h2 class="title">BIENVENUE</h2>
            <?php
            if(isset($error_message)) {
                echo "<p style='color: red;'>$error_message</p>";
            } elseif (isset($success_message)) {
                echo "<p style='color: green;'>$success_message</p>";
            }
            ?>
            <div class="input-div one">
                <div class="i">
                    <i class="fas fa-user"></i>
                </div>
                <div class="div">
                    <h5>Username</h5>
                    <input type="text" class="input" id="username" name="username" required><br>

                </div>
            </div>
            <div class="input-div pass">
                <div class="i">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="div">
                    <h5>Password</h5>
                    <input type="password" class="input" id="password" name="password" required><br>

                </div>
            </div>
            <a href="login.php">Vous avez déjà un compte?</a>
            <input type="submit" class="btn" value="Créer un compte">

        </form>
    </div>
</div>
<script type="text/javascript" src="js/main.js"></script>
</body>
</html>