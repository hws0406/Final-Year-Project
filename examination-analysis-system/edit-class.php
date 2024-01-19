<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (isset($_POST['update'])) {
    $classname = $_POST['classname'];
    $form = $_POST['form'];
    $cid = intval($_GET['classid']);

    // Check if the class already exists in the specified form
    $checkSql = "SELECT COUNT(*) as count FROM tblclasses WHERE ClassName = :classname AND Formid = :formid";
    $checkQuery = $dbh->prepare($checkSql);
    $checkQuery->bindParam(':classname', $classname, PDO::PARAM_STR);
    $checkQuery->bindParam(':formid', $form, PDO::PARAM_INT);
    $checkQuery->execute();
    $result = $checkQuery->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        $error = "This Class already exists in the selected Form";
    } else {
        $formId = 0;

        // Check if the form already exists in tblforms
        $checkFormSql = "SELECT Id FROM tblforms WHERE Form = :form";
        $checkFormQuery = $dbh->prepare($checkFormSql);
        $checkFormQuery->bindParam(':form', $form, PDO::PARAM_INT);
        $checkFormQuery->execute();
        $formResult = $checkFormQuery->fetch(PDO::FETCH_ASSOC);

        if ($formResult) {
            // Form already exists, use the existing form ID
            $formId = $formResult['Id'];

            // Check if the class already exists in the same form
            $checkDuplicateClassSql = "SELECT COUNT(*) as count FROM tblclasses WHERE ClassName = :classname AND Formid = :formid AND id != :cid";
            $checkDuplicateClassQuery = $dbh->prepare($checkDuplicateClassSql);
            $checkDuplicateClassQuery->bindParam(':classname', $classname, PDO::PARAM_STR);
            $checkDuplicateClassQuery->bindParam(':formid', $formId, PDO::PARAM_INT);
            $checkDuplicateClassQuery->bindParam(':cid', $cid, PDO::PARAM_INT);
            $checkDuplicateClassQuery->execute();
            $duplicateClassResult = $checkDuplicateClassQuery->fetch(PDO::FETCH_ASSOC);

            if ($duplicateClassResult['count'] > 0) {
                $error = "This Class already exists in the selected Form";
            } else {
                // Update class information in tblclasses
                $updateSql = "UPDATE tblclasses SET ClassName = :classname, Formid = :formid WHERE id = :cid ";
                $updateQuery = $dbh->prepare($updateSql);
                $updateQuery->bindParam(':classname', $classname, PDO::PARAM_STR);
                $updateQuery->bindParam(':formid', $formId, PDO::PARAM_INT);
                $updateQuery->bindParam(':cid', $cid, PDO::PARAM_INT);
                $updateQuery->execute();

                $msg = "Class Information updated successfully";

                // Set success message in session
                $_SESSION['success_msg'] = $msg;

                // Redirect back to manage-classes.php
                header("Location: manage-classes.php");
                exit;
            }
        } else {
            // Form does not exist, insert the form into tblforms
            $formSql = "INSERT INTO tblforms (Form) VALUES (:form)";
            $formQuery = $dbh->prepare($formSql);
            $formQuery->bindParam(':form', $form, PDO::PARAM_INT);
            $formQuery->execute();
            $formId = $dbh->lastInsertId();

            // Update class information in tblclasses
            $updateSql = "UPDATE tblclasses SET ClassName = :classname, Formid = :formid WHERE id = :cid ";
            $updateQuery = $dbh->prepare($updateSql);
            $updateQuery->bindParam(':classname', $classname, PDO::PARAM_STR);
            $updateQuery->bindParam(':formid', $formId, PDO::PARAM_INT);
            $updateQuery->bindParam(':cid', $cid, PDO::PARAM_INT);
            $updateQuery->execute();

            $msg = "Class Information updated successfully";

            // Set success message in session
            $_SESSION['success_msg'] = $msg;

            // Redirect back to manage-classes.php
            header("Location: manage-classes.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Class</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/animate-css/animate.min.css">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css">
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
                            <h2 class="title">Update Class</h2>
                        </div>
                    </div>
                    <!-- /.row -->
                    <div class="row breadcrumb-div">
                        <div class="col-md-6">
                            <ul class="breadcrumb">
                                <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                <li><a href="#">Classes</a></li>
                                <li class="active">Update Class</li>
                            </ul>
                        </div>
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.container-fluid -->

                <section class="section">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-8 col-md-offset-2">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>Update Class Info</h5>
                                        </div>
                                    </div>
                                    <?php if ($msg) { ?>
                                        <div class="alert alert-success left-icon-alert" role="alert">
                                            <strong>Well done!</strong><?php echo htmlentities($msg); ?>
                                        </div>
                                    <?php } else if ($error) { ?>
                                        <div class="alert alert-danger left-icon-alert" role="alert">
                                            <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                    <?php } ?>

                                    <form method="post">
                                        <?php
                                        $cid = intval($_GET['classid']);
                                        $sql = "SELECT tblclasses.id, tblclasses.ClassName, tblforms.Form 
                                                FROM tblclasses 
                                                INNER JOIN tblforms ON tblclasses.Formid = tblforms.Id 
                                                WHERE tblclasses.id = :cid";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':cid', $cid, PDO::PARAM_INT);
                                        $query->execute();
                                        $result = $query->fetch(PDO::FETCH_ASSOC);

                                        if ($result) {
                                            ?>
                                            <div class="form-group has-success">
                                                <label for="form" class="control-label">Form</label>
                                                <div class="">
                                                    <select name="form" class="form-control" required="required" id="form">
                                                        <option value="1" <?php if ($result['Form'] == 1) echo 'selected="selected"'; ?>>1</option>
                                                        <option value="2" <?php if ($result['Form'] == 2) echo 'selected="selected"'; ?>>2</option>
                                                        <option value="3" <?php if ($result['Form'] == 3) echo 'selected="selected"'; ?>>3</option>

                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group has-success">
                                                <label for="success" class="control-label">Class Name</label>
                                                <div class="">
                                                    <select name="classname" class="form-control" required="required" id="success">
                                                        <option value="Arif" <?php if ($result['ClassName'] == 'Arif') echo 'selected="selected"'; ?>>Arif</option>
                                                        <option value="Bestari" <?php if ($result['ClassName'] == 'Bestari') echo 'selected="selected"'; ?>>Bestari</option>
                                                        <option value="Cemerlang" <?php if ($result['ClassName'] == 'Cemerlang') echo 'selected="selected"'; ?>>Cemerlang</option>
                                                        <option value="Dinamik" <?php if ($result['ClassName'] == 'Dinamik') echo 'selected="selected"'; ?>>Dinamik</option>
                                                        <option value="Elit" <?php if ($result['ClassName'] == 'Elit') echo 'selected="selected"'; ?>>Elit</option>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="form-group has-success">
                                            <div class="">
                                                <button type="submit" name="update" class="btn btn-success btn-labeled">Update<span class="btn-label btn-label-right"><i class="fa fa-check"></i></span></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- /.col-md-8 col-md-offset-2 -->
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.container-fluid -->
                </section>
                <!-- /.section -->
            </div>
            <!-- /.main-page -->
        </div>
        <!-- /.content-container -->
    </div>
    <!-- /.content-wrapper -->
</div>
<!-- /.main-wrapper -->

<!-- ========== COMMON JS FILES ========== -->
<script src="js/jquery/jquery-2.2.4.min.js"></script>
<script src="js/jquery-ui/jquery-ui.min.js"></script>
<script src="js/bootstrap/bootstrap.min.js"></script>
<script src="js/pace/pace.min.js"></script>
<script src="js/lobipanel/lobipanel.min.js"></script>
<script src="js/iscroll/iscroll.js"></script>

<!-- ========== PAGE JS FILES ========== -->
<script src="js/prism/prism.js"></script>

<!-- ========== THEME JS ========== -->
<script src="js/main.js"></script>
</body>
</html>
