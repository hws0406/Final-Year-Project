<?php
session_start();
error_reporting(0);

if (isset($_POST['login'])) {
    include 'includes/config.php';
    $id = $_POST['id'];
    $password = ($_POST['password']);
    $sqllogin = "SELECT * FROM userprofile WHERE id = '$id' AND password = '$password'";
    $stmt = $dbh->prepare($sqllogin);
    $stmt->execute();
    $user = $stmt->fetch();
    
    if($user){
        if($user["user_type"]=="user"){
          session_start();
          $_SESSION["sessionid"] = session_id();
          $_SESSION["id"] = $id;
          echo "<script>alert('Login Successful');</script>";
          echo "<script>window.location.replace('userdashboard.php')</script>";

        }else if($user["user_type"]=="admin"){
          session_start();
          $_SESSION["sessionid"] = session_id();
          $_SESSION["id"] = $id;
          echo "<script>alert('Login Successful');</script>";
          echo "<script>window.location.replace('dashboard.php')</script>";
        }
      }else{
        echo "<script>alert('Invalid ID or Password');</script>";
      }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <script src="../js/login.js" defer></script>

    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 300px;
            padding: 16px;
            background-color: white;
            margin: 0 auto;
            margin-top: 100px;
            border: 1px solid black;
            border-radius: 4px;
        }
        input[type=text], input[type=password] {
            display: inline-block;
            border: none;
            background: ;
        }
        input[type=text]:focus, input[type=password]:focus {
            background-color: #ddd;
            outline: none;
        }
        .btn {
            background-color:grey;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
            opacity: 0.9;
        }
        .btn:hover {
            opacity:1;
        }
    </style>
</head>

<body>
    <header class="w3-header w3-center w3-padding-32">
        <h3>Examination Analysis System</h3>
    </header>
    <div style="display:flex; justify-content: center">
        <div class="w3-container w3-card w3-padding-30 w3-margin">
            <form name="loginForm" action="login.php" method="post">
                <p>
                    <label><b>Id</b></label>
                    <input class="w3-input w3-round w3-border" type="text" name="id" id="idid" placeholder="Your Id" required>
                </p>
                <p>
                    <label><b>Password</b></label>
                    <input class="w3-input w3-round w3-border" type="password" name="password" id="idpassword" placeholder="Your Password" required>
                </p>
                <p>
                    <input class="w3-check" name="rememberme" type="checkbox" id="idremember" onclick="rememberMe()"><label> Remember Me</label>
                </p>
                <p>
                    <button class="w3-btn w3-grey w3-round w3-block" type="submit" name="login" value="login">Login</button>
                </p>
            </form>
        </div>
    </div>

    <footer class="fixedFooter w3-footer w3-center w3-bottom">
        <p>© 2023 Examination Analysis System</p>
    </footer>

</body>
</html>
