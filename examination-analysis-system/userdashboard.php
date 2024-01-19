<?php
session_start();
error_reporting(0);
include('includes/config.php');
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Examination Analysis System</title>
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <link rel="stylesheet" href="css/animate-css/animate.min.css">
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css">
        <link rel="stylesheet" href="css/toastr/toastr.min.css">
        <link rel="stylesheet" href="css/icheck/skins/line/blue.css">
        <link rel="stylesheet" href="css/icheck/skins/line/red.css">
        <link rel="stylesheet" href="css/icheck/skins/line/green.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="js/modernizr/modernizr.min.js"></script>
    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">
            <?php include('includes/topbar.php'); ?>
            <div class="content-wrapper">
                <div class="content-container">
                    <?php include('includes/userleftbar.php'); ?>
                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-sm-6">
                                    <h2 class="title">Dashboard</h2>
                                </div>
                            </div>
                            <section class="section">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                            <a class="dashboard-stat bg-primary" href="manage-students.php">
                                                <?php
                                                $sql1 = "SELECT StudentId FROM tblstudents";
                                                $query1 = $dbh->prepare($sql1);
                                                $query1->execute();
                                                $totalstudents = $query1->rowCount();
                                                ?>
                                                <span class="number counter"><?php echo htmlentities($totalstudents); ?></span>
                                                <span class="name">Students</span>
                                                <span class="bg-icon"><i class="fa fa-users"></i></span>
                                            </a>
                                            <!-- /.dashboard-stat -->
                                        </div>
                                        <!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-12 -->

                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                            <a class="dashboard-stat bg-danger" href="manage-subjects.php">
                                                <?php
                                                $sql = "SELECT id FROM tblsubjects";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $totalsubjects = $query->rowCount();
                                                ?>
                                                <span class="number counter"><?php echo htmlentities($totalsubjects); ?></span>
                                                <span class="name">Subjects</span>
                                                <span class="bg-icon"><i class="fa fa-ticket"></i></span>
                                            </a>
                                            <!-- /.dashboard-stat -->
                                        </div>
                                        <!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-12 -->

                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                            <a class="dashboard-stat bg-warning" href="manage-classes.php">
                                                <?php
                                                $sql2 = "SELECT id FROM tblclasses";
                                                $query2 = $dbh->prepare($sql2);
                                                $query2->execute();
                                                $totalclasses = $query2->rowCount();
                                                ?>
                                                <span class="number counter"><?php echo htmlentities($totalclasses); ?></span>
                                                <span class="name">Classes</span>
                                                <span class="bg-icon"><i class="fa fa-bank"></i></span>
                                            </a>
                                            <!-- /.dashboard-stat -->
                                        </div>
                                        <!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-12 -->
                                    </div>
                                    <!-- /.row -->
                                </div>
                                <!-- /.container-fluid -->
                            </section>
                            <!-- /.section -->
                        </div>
                        <!-- /.container-fluid -->
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
        <script src="js/waypoint/waypoints.min.js"></script>
        <script src="js/counterUp/jquery.counterup.min.js"></script>
        <script src="js/amcharts/amcharts.js"></script>
        <script src="js/amcharts/serial.js"></script>
        <script src="js/amcharts/plugins/export/export.min.js"></script>
        <link rel="stylesheet" href="js/amcharts/plugins/export/export.css" type="text/css" media="all" />
        <script src="js/amcharts/themes/light.js"></script>
        <script src="js/toastr/toastr.min.js"></script>
        <script src="js/icheck/icheck.min.js"></script>

        <!-- ========== THEME JS ========== -->
        <script src="js/main.js"></script>
        <script src="js/production-chart.js"></script>
        <script src="js/traffic-chart.js"></script>
        <script src="js/task-list.js"></script>
        <script>
            $(function () {
                // Counter for dashboard stats
                $('.counter').counterUp({
                    delay: 10,
                    time: 1000
                });
            });
        </script>
    </body>
</html>
