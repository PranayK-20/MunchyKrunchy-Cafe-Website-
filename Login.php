<?php
include('Connection.php');
if(isset($_POST['Login'])) {
    $manager = $_POST['manager'];
    $password = $_POST['password'];

    $query = "SELECT * FROM login WHERE manager_id = '$manager'";
    $result = mysqli_query($conn, $query);
    if(!$result){
        echo "Error!: {$conn->error}";
    } else {
        if($result->num_rows > 0) {
            $row = mysqli_fetch_assoc($result);
            if($row['password'] == $password){
                header("Location: Manager/ManagerDashboard.php");
                exit();
            }else {
              echo "<script>alert('Invalid Password!');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Manager Login</title>
            <style type="text/css">
                body {
                    margin: 0;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, #100e16 0%, #2e1f26 40%, #3f2a3e 100%);
                    color: #f1e4d1;
                    font-family: 'Poppins', sans-serif;
                }
                .form{
                    width: 360px;
                    background-color: rgba(20, 18, 28, 0.95);
                    color: #f1e4d1;
                    padding: 36px 36px;
                    padding-right: 48px;
                    margin: 0 auto;
                    border-radius: 24px;
                    box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(200, 135, 64, 0.18);
                }
                .form label{
                    display: block;
                    margin-bottom: 12px;
                    font-size: 0.95rem;
                    letter-spacing: 0.02em;
                }
                .form input[type="text"],
                .form input[type="password"]{
                    width: 100%;
                    padding: 12px 14px;
                    margin-bottom: 18px;
                    border-radius: 14px;
                    border: 1px solid rgba(255,255,255,0.12);
                    background: rgba(255,255,255,0.05);
                    color: #f1e4d1;
                    outline: none;
                    transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
                }
                .form input[type="text"]:focus,
                .form input[type="password"]:focus{
                    border-color: #c87740;
                    box-shadow: 0 0 0 4px rgba(200, 135, 64, 0.12);
                    transform: translateY(-1px);
                }
                .login{
                    background-color: #c87740;
                    color: #221413;
                    width: 100%;
                    box-sizing: border-box;
                    padding: 12px 14px;
                    border: none;
                    border-radius: 14px;
                    cursor: pointer;
                    font-weight: 700;
                    letter-spacing: 0.02em;
                    transition: background-color 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
                }
                .login:hover{
                    background-color: #d18a47;
                    box-shadow: 0 18px 30px rgba(200, 135, 64, 0.24);
                    transform: translateY(-2px);
                }
                .login:active{
                    background-color: #a0572a;
                    transform: translateY(0);
                }
            </style>
    </head>
    <body>
        <form class = "form" method = "POST">
            <label for = "manager">Manager:</label>
            <input type="text" id = "manager" name="manager" required>
            <br>
            <label for = "password">Password:</label>
            <input type = "password" id = "password" name="password" required>
            <br>
            <input class = "login" type="submit" name = "Login" value="Login">
        </form>
    </body>
</html>