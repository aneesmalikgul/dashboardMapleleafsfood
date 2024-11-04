<?php include 'layouts/config.php'; ?>
<?php include 'layouts/main.php'; ?>

<?php
session_start();

// Initialize a variable to store error message
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnLogin'])) {
    if (isset($_POST['email']) && isset($_POST['password']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        try {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            $sql = "SELECT * FROM users WHERE useremail = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement.");
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result) {
                throw new Exception("Failed to get result.");
            }

            $user = $result->fetch_assoc();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['useremail'] = $user['useremail'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['userFullName'] = $user['first_name'] . " " . $user['last_name'];
                $_SESSION['userrole'] = $user['role_id'];
                $_SESSION["loggedin"] = true;

                // Set success message
                $_SESSION['message'][] = array("type" => "success", "content" => "Login successful!");

                header("Location: index.php");
                exit();
            } else {
                $error_message = "Invalid email or password.";
                $_SESSION['message'][] = array("type" => "error", "content" => $error_message);
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
            $_SESSION['message'][] = array("type" => "error", "content" => $error_message);
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
            if (isset($conn)) {
                $conn->close();
            }
        }
    } else {
        $error_message = "Email and password are required.";
        $_SESSION['message'][] = array("type" => "error", "content" => $error_message);
    }
}
?>


<head>
    <title>Log In | Maple Leafs Food</title>
    <?php include 'layouts/title-meta.php'; ?>

    <?php include 'layouts/head-css.php'; ?>
</head>

<body class="authentication-bg position-relative">

    <?php include 'layouts/background.php'; ?>

    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">

                        <!-- Logo -->
                        <div class="card-header py-3 text-center bg-white">
                            <a href="index.php">
                                <span><img src="assets/images/logo.svg" alt="logo" height="90"></span>
                                <!-- <h3>Care Way Medical Lab</h3> -->
                            </a>
                        </div>

                        <div class="card-body p-3">

                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">Sign In</h4>
                                <p class="text-muted mb-4">Enter your email address and password to access admin panel.</p>
                            </div>

                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

                                <div class="mb-3">
                                    <label for="emailaddress" class="form-label">Email address</label>
                                    <input class="form-control" type="email" id="emailaddress" name="email" required="" placeholder="Enter your email">
                                </div>

                                <div class="mb-3">
                                    <a href="auth-recoverpw.php" class="text-muted float-end fs-12">Forgot your password?</a>
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password">
                                        <div class="input-group-text" data-password="false">
                                            <span class="password-eye"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="checkbox-signin" checked>
                                        <label class="form-check-label" for="checkbox-signin">Remember me</label>
                                    </div>
                                </div>

                                <div class="mb-3 mb-0 text-center">
                                    <input class="btn btn-primary" name="btnLogin" id="btnLogin" type="submit">
                                </div>

                            </form>
                        </div> <!-- end card-body -->
                    </div>
                    <!-- end card -->

                    <div class="row mt-2">
                        <div class="col-12 text-center">
                            <p class="text-muted bg-body">Don't have an account? <a href="auth-register.php" class="text-muted ms-1 link-offset-3 text-decoration-underline"><b>Sign Up</b></a></p>
                        </div> <!-- end col -->
                    </div>
                    <!-- end row -->

                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->

    <footer class="footer footer-alt fw-medium">
        <span class="bg-body">
            <script>
                document.write(new Date().getFullYear())
            </script> © Maple Leafs Food
        </span>
    </footer>
    <?php include 'layouts/footer-scripts.php'; ?>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

</body>

</html>