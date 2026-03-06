<?php
include('config.php');
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

$full_name = $email = $password = $cnic = $study_program = $about_me = "";
$full_name_err = $email_err = $password_err = $cnic_err = $display_picture_err = "";
$registration_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["full_name"]))) {
        $full_name_err = "Please enter your full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);

        $sql = "SELECT id FROM users WHERE email=?";
        $stmt = mysqli_prepare($conn,$sql);
        mysqli_stmt_bind_param($stmt,"s",$email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if(mysqli_stmt_num_rows($stmt)>0){
            $email_err="Email already exists";
        }
        mysqli_stmt_close($stmt);
    }

    if(empty(trim($_POST["password"]))){
        $password_err="Enter password";
    } else {
        $password=trim($_POST["password"]);
    }

    if(empty(trim($_POST["cnic"]))){
        $cnic_err="Enter CNIC";
    } else {
        $cnic=trim($_POST["cnic"]);
    }

    $study_program = $_POST['study_program'];
    $about_me = $_POST['about_me'];

    $display_picture = 'uploads/default.png';

    if(isset($_FILES['display_picture']) && $_FILES['display_picture']['error']==0){
        $upload_dir='uploads/';
        if(!is_dir($upload_dir)) mkdir($upload_dir,0755,true);

        $file_name=time().'_'.$_FILES['display_picture']['name'];
        $target_file=$upload_dir.$file_name;

        if(move_uploaded_file($_FILES['display_picture']['tmp_name'],$target_file)){
            $display_picture=$target_file;
        }
    }

    if(empty($full_name_err) && empty($email_err) && empty($password_err) && empty($cnic_err)){

        $hashed_password=password_hash($password,PASSWORD_DEFAULT);

        $sql="INSERT INTO users (full_name,email,display_picture,password,cnic,study_program,about_me,status)
              VALUES (?,?,?,?,?,?,?,'pending')";

        $stmt=mysqli_prepare($conn,$sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sssssss",
            $full_name,
            $email,
            $display_picture,
            $hashed_password,
            $cnic,
            $study_program,
            $about_me
        );

        if(mysqli_stmt_execute($stmt)){


            $verify_link="http://localhost/fall25/safenest/verify.php?email=".$email;

            $mail = new PHPMailer(true);

            try{

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'nasiryt.827@gmail.com';  
                $mail->Password = 'ddgc zhft gohg bjmz';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('nasiryt.827@gmail.com','Account Verification');
                $mail->addAddress($email,$full_name);

                $mail->isHTML(true);
                $mail->Subject='Verify Your Account';

                $mail->Body="
                Hello $full_name,<br><br>
                Click the link below to verify your account:<br><br>
                <a href='$verify_link'>Verify Account</a><br><br>
                Thank you.";

                $mail->send();

                $registration_msg='<div class="alert alert-success text-center">
                Registration successful. Please check your email to verify your account.
                </div>';

            }catch(Exception $e){
                $registration_msg='<div class="alert alert-warning text-center">
                User created but email could not be sent.
                </div>';
            }

        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-4">
    <div class="card mx-auto shadow-sm" style="max-width: 900px;">
        <div class="card-body">
            <h3 class="text-center mb-4">User Registration</h3>
            <?php echo $registration_msg; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $full_name; ?>" required>
                        <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" required>
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Display Picture</label>
                        <input type="file" name="display_picture" class="form-control <?php echo (!empty($display_picture_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $display_picture_err; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>CNIC</label>
                        <input type="text" name="cnic" class="form-control <?php echo (!empty($cnic_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $cnic; ?>" required>
                        <span class="invalid-feedback"><?php echo $cnic_err; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Study Program</label>
                        <select name="study_program" class="form-control" required>
                            <option value="">Select Program</option>
                            <?php 
                            $programs = ['BSCS','BSIT','BSSE','BSDS','BSMGT','BSAI','MCS','MIT','ADPCS','MSCS'];
                            foreach ($programs as $prog) {
                                $selected = ($study_program==$prog)?'selected':'';
                                echo "<option value='$prog' $selected>$prog</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>About Me</label>
                        <textarea name="about_me" class="form-control"><?php echo $about_me; ?></textarea>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <input type="submit" class="btn btn-dark px-4" value="Sign Up">
                    <input type="reset" class="btn btn-secondary px-4" value="Clear">
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>