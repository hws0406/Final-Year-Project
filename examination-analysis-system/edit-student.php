<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (isset($_GET['stid'])) {
    $studentId = $_GET['stid'];

    // Retrieve the student details
    $sql = "SELECT * FROM tblstudents WHERE StudentId = :studentId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentId', $studentId, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $_SESSION['error_msg'] = "Invalid Student ID";
        header("Location: manage-students.php");
        exit;
    }
}

if (isset($_POST['submit'])) {
    $studentname = $_POST['fullname'];
    $classid = $_POST['class'];
    $form = $_POST['form'];
    $status = 1;

    // Update the student details
    $sql = "UPDATE tblstudents SET StudentName = :studentname, ClassId = :classid, Form = :form WHERE StudentId = :studentId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentname', $studentname, PDO::PARAM_STR);
    $query->bindParam(':classid', $classid, PDO::PARAM_STR);
    $query->bindParam(':form', $form, PDO::PARAM_STR);
    $query->bindParam(':studentId', $studentId, PDO::PARAM_INT);
    $query->execute();

    $_SESSION['success_msg'] = "Student details updated successfully";
    header("Location: manage-students.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Student</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/animate-css/animate.min.css">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css">
    <link rel="stylesheet" href="css/prism/prism.css">
    <link rel="stylesheet" href="css/select2/select2.min.css">
    <link rel="stylesheet" href="css/main.css">
    <script src="js/modernizr/modernizr.min.js"></script>
</head>
<body class="top-navbar-fixed">
<div class="main-wrapper">

    <!-- ========== TOP NAVBAR ========== -->
    <?php include('includes/topbar.php'); ?>

    <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
    <div class="content-wrapper">
        <div class="content-container">

            <!-- ========== LEFT SIDEBAR ========== -->
            <?php include('includes/leftbar.php'); ?>

            <!-- /.left-sidebar -->

            <div class="main-page">
                <div class="container-fluid">
                    <div class="row page-title-div">
                        <div class="col-md-6">
                            <h2 class="title">Edit Student</h2>
                        </div>
                    </div>
                    <!-- /.row -->
                    <div class="row breadcrumb-div">
                        <div class="col-md-6">
                            <ul class="breadcrumb">
                                <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                <li><a href="manage-students.php">Students</a></li>
                                <li class="active">Edit Student</li>
                            </ul>
                        </div>
                    </div>
                    <!-- /.row -->
                </div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5>Edit Student Info</h5>
                                    </div>
                                </div>
                                <?php if (isset($_SESSION['success_msg'])) { ?>
                                    <div class="alert alert-success left-icon-alert" role="alert">
                                        <strong></strong><?php echo htmlentities($_SESSION['success_msg']); ?>
                                    </div>
                                    <?php unset($_SESSION['success_msg']); ?>
                                    <script>
                                        // Remove success message after 3 seconds
                                        setTimeout(function () {
                                            document.querySelector('.alert-success').style.display = 'none';
                                        }, 3000);
                                    </script>
                                <?php } ?>
                                <div class="panel-body">
                                    <form class="form-horizontal" method="post">

                                        <div class="form-group">
                                            <label for="fullname" class="col-sm-2 control-label">Full Name</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="fullname" class="form-control" id="fullname" required="required" value="<?php echo htmlentities($result['StudentName']); ?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="gender" class="col-sm-2 control-label">Gender</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" readonly value="<?php echo htmlentities($result['Gender']); ?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="form" class="col-sm-2 control-label">Form</label>
                                            <div class="col-sm-10">
                                                <select name="form" class="form-control" id="form" required="required">
                                                    <option value="">Select Form</option>
                                                    <?php
                                                    $sql = "SELECT * FROM tblforms";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $forms = $query->fetchAll(PDO::FETCH_OBJ);
                                                    foreach ($forms as $form) {
                                                        $selected = ($result['Form'] === $form->Id) ? 'selected' : '';
                                                        echo '<option value="' . $form->Id . '" ' . $selected . '>' . $form->Form . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="class" class="col-sm-2 control-label">Class</label>
                                            <div class="col-sm-10">
                                                <select name="class" class="form-control" id="class" required="required">
                                                    <option value="">Select Class</option>
                                                    <?php
                                                    $formId = $result['Form'];
                                                    $sql = "SELECT * FROM tblclasses WHERE FormId = :formId ORDER BY ClassName ASC";
                                                    $query = $dbh->prepare($sql);
                                                    $query->bindParam(':formId', $formId, PDO::PARAM_INT);
                                                    $query->execute();
                                                    $classes = $query->fetchAll(PDO::FETCH_OBJ);
                                                    foreach ($classes as $class) {
                                                        $selected = ($result['ClassId'] === $class->id) ? 'selected' : '';
                                                        echo '<option value="' . $class->id . '" ' . $selected . '>' . $class->ClassName . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-10">
                                                <button type="submit" name="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- /.col-md-12 -->
                    </div>
                </div>
            </div>
            <!-- /.main-page -->
        </div>
        <!-- /.content-container -->
    </div>
    <!-- /.content-wrapper -->
</div>

<!-- ========== COMMON JS FILES ========== -->
<script src="js/jquery/jquery-2.2.4.min.js"></script>
<script src="js/bootstrap/bootstrap.min.js"></script>
<script src="js/pace/pace.min.js"></script>
<script src="js/lobipanel/lobipanel.min.js"></script>
<script src="js/iscroll/iscroll.js"></script>
<script src="js/prism/prism.js"></script>
<script src="js/select2/select2.min.js"></script>
<script src="js/main.js"></script>
<script>
    $(function ($) {
        $(".js-states").select2();
        $(".js-states-limit").select2({
            maximumSelectionLength: 2
        });
        $(".js-states-hide").select2({
            minimumResultsForSearch: Infinity
        });

        $('#form').change(function() {
            var formId = $(this).val();
            $.ajax({
                url: 'fetch-classes.php',
                method: 'POST',
                data: { formId: formId },
                success: function(response) {
                    $('#class').html(response);
                }
            });
        });
    });
</script>
</body>
</html>
