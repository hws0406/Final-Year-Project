<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(isset($_POST['submit'])) {
    $classname = $_POST['classname'];
    $form = $_POST['form'];

    // Check if the class already exists in the specified form
    $checkSql = "SELECT COUNT(*) as count FROM tblclasses WHERE ClassName = :classname AND Formid = :formid";
    $checkQuery = $dbh->prepare($checkSql);
    $checkQuery->bindParam(':classname', $classname, PDO::PARAM_STR);
    $checkQuery->bindParam(':formid', $form, PDO::PARAM_INT);
    $checkQuery->execute();
    $result = $checkQuery->fetch(PDO::FETCH_ASSOC);

    if($result['count'] > 0) {
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
            $checkDuplicateClassSql = "SELECT COUNT(*) as count FROM tblclasses WHERE ClassName = :classname AND Formid = :formid";
            $checkDuplicateClassQuery = $dbh->prepare($checkDuplicateClassSql);
            $checkDuplicateClassQuery->bindParam(':classname', $classname, PDO::PARAM_STR);
            $checkDuplicateClassQuery->bindParam(':formid', $formId, PDO::PARAM_INT);
            $checkDuplicateClassQuery->execute();
            $duplicateClassResult = $checkDuplicateClassQuery->fetch(PDO::FETCH_ASSOC);

            if($duplicateClassResult['count'] > 0) {
                $error = "This Class already exists in the selected Form";
            } else {
                // Insert class name into tblclasses
                $classSql = "INSERT INTO tblclasses (ClassName, Formid) VALUES (:classname, :formid)";
                $classQuery = $dbh->prepare($classSql);
                $classQuery->bindParam(':classname', $classname, PDO::PARAM_STR);
                $classQuery->bindParam(':formid', $formId, PDO::PARAM_INT);
                $classQuery->execute();

                $lastInsertId = $dbh->lastInsertId();
                if($lastInsertId) {
                    $_SESSION['msg'] = "Class created successfully";
                    header("Location: manage-classes.php");
                    exit;
                } else {
                    $error = "Something went wrong. Please try again";
                }
            }
        } else {
            // Form does not exist, insert the form into tblforms
            $formSql = "INSERT INTO tblforms (Form) VALUES (:form)";
            $formQuery = $dbh->prepare($formSql);
            $formQuery->bindParam(':form', $form, PDO::PARAM_INT);
            $formQuery->execute();
            $formId = $dbh->lastInsertId();

            // Insert class name into tblclasses
            $classSql = "INSERT INTO tblclasses (ClassName, Formid) VALUES (:classname, :formid)";
            $classQuery = $dbh->prepare($classSql);
            $classQuery->bindParam(':classname', $classname, PDO::PARAM_STR);
            $classQuery->bindParam(':formid', $formId, PDO::PARAM_INT);
            $classQuery->execute();

            $lastInsertId = $dbh->lastInsertId();
            if($lastInsertId) {
                $_SESSION['msg'] = "Class created successfully";
                header("Location: manage-classes.php");
                exit;
            } else {
                $error = "Something went wrong. Please try again";
            }
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
    <title>Create Class</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/animate-css/animate.min.css">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css">
    <link rel="stylesheet" href="css/main.css">
    <script src="js/modernizr/modernizr.min.js"></script>
    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #dd3d36;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #5cb85c;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
    </style>
</head>
<body class="top-navbar-fixed">
    <div class="main-wrapper">
        <!-- ========== TOP NAVBAR ========== -->
        <?php include('includes/topbar.php');?>
        <!-----End Top bar>
        <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/leftbar.php');?>
                <!-- /.left-sidebar -->

                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Create Student Class</h2>
                            </div>
                        </div>
                        <!-- /.row -->
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="#">Classes</a></li>
                                    <li class="active">Create Class</li>
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
                                                <h5>Create Student Class</h5>
                                            </div>
                                        </div>
                                        <?php if($error) { ?>
                                            <div class="alert alert-danger left-icon-alert" role="alert">
                                                <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                            </div>
                                        <?php } ?>

                                        <div class="panel-body">
                                            <form method="post">
                                                <div class="form-group has-success">
                                                    <label for="form" class="control-label">Form</label>
                                                    <div class="">
                                                        <select name="form" class="form-control" required="required" id="form">
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group has-success">
                                                    <label for="success" class="control-label">Class Name</label>
                                                    <div class="">
                                                        <select name="classname" class="form-control" required="required" id="success">
                                                            <option value="Arif">Arif</option>
                                                            <option value="Bestari">Bestari</option>
                                                            <option value="Cemerlang">Cemerlang</option>
                                                            <option value="Dinamik">Dinamik</option>
                                                            <option value="Elit">Elit</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group has-success">
                                                    <div class="">
                                                        <button type="submit" name="submit" class="btn btn-success btn-labeled">Submit<span class="btn-label btn-label-right"><i class="fa fa-check"></i></span></button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
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

    <!-- ========== ADD custom.js FILE BELOW WITH YOUR CHANGES ========== -->
</body>
</html>
