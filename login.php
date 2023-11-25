<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hackathon";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password);

    if($stmt->fetch() && password_verify($input_password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Invalid username or password";
    }

    $stmt->close();
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
<body>
<img class="wave" src="http://github.com/sefyudem/Responsive-Login-Form/blob/master/img/wave.png?raw=true">
<div class="container">
    <div class="img">
        <img id="bg" src="images/bg.png">

    </div>
    <div class="login-content">


        <form action="" method="post">

            <img src="images/login.logo.png">
            <h2 class="title">Welcome</h2>
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
            <a href="signup.php">Create Account ??</a>
            <input type="submit" class="btn" value="Login">

        </form>
    </div>
</div>
<script type="text/javascript" src="js/main.js"></script>
</body>
</html>