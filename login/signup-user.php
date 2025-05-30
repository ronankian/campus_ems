<?php require_once "controllerUserData.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Signup Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<style>
    html,
    body {
        background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
            url('/campus_ems/assets/images/events-bg.jpg') no-repeat center center;
        background-size: cover;
        min-height: 100vh;
        font-family: 'Poppins', sans-serif;
    }
</style>


<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
                <form action="signup-user.php" method="POST" autocomplete="">
                    <h2 class="text-center">Signup Form</h2>
                    <p class="text-center">It's quick and easy.</p>
                    <?php
                    if (count($errors) == 1) {
                        ?>
                        <div class="alert alert-danger text-center">
                            <?php
                            foreach ($errors as $showerror) {
                                echo $showerror;
                            }
                            ?>
                        </div>
                        <?php
                    } elseif (count($errors) > 1) {
                        ?>
                        <div class="alert alert-danger">
                            <?php
                            foreach ($errors as $showerror) {
                                ?>
                                <li><?php echo $showerror; ?></li>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="form-group">
                        <input class="form-control" type="text" name="firstname" placeholder="First Name" required
                            value="<?php echo isset($firstname) ? $firstname : '' ?>">
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="text" name="lastname" placeholder="Last Name" required
                            value="<?php echo isset($lastname) ? $lastname : '' ?>">
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="text" name="username" placeholder="Username" required
                            value="<?php echo isset($username) ? $username : '' ?>">
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="email" name="email" placeholder="Email Address" required
                            value="<?php echo $email ?>">
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="password" name="cpassword" placeholder="Confirm password"
                            required>
                    </div>
                    <div class="form-group">
                        <input class="form-control button" type="submit" name="signup" value="Signup">
                    </div>
                    <div class="link login-link text-center">Already a member? <a href="login-user.php">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>