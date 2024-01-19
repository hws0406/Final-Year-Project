<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (isset($_GET['stid'])) {
    $studentId = $_GET['stid'];

    // Retrieve the student details and existing marks for each subject
    $sql = "SELECT s.StudentId, s.StudentName, su.id AS SubjectId, su.SubjectName, COALESCE(r.Marks, '') AS Marks
            FROM tblstudents s
            CROSS JOIN tblsubjects su
            LEFT JOIN tblresults r ON s.StudentId = r.StudentId AND su.id = r.SubjectId
            WHERE s.StudentId = :studentId
            ORDER BY su.id";
            
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentId', $studentId, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_POST['submit'])) {
    $subjectIds = $_POST['subjectIds'];
    $marks = $_POST['marks'];

    // Perform server-side validation and update the student marks in the database
    for ($i = 0; $i < count($subjectIds); $i++) {
        $subjectId = $subjectIds[$i];
        $mark = $marks[$i];

        // Skip the subject if marks are not provided
        if ($mark === '') {
            continue;
        }

        // Validate marks (e.g., check if it is a number within a specific range)

        // Check if the result already exists for the student and subject
        $sql = "SELECT * FROM tblresults WHERE StudentId = :studentId AND SubjectId = :subjectId";
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $query->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);
        $query->execute();
        $rowCount = $query->rowCount();

        if ($rowCount > 0) {
            // Update the existing result with the new marks
            $sql = "UPDATE tblresults SET Marks = :marks WHERE StudentId = :studentId AND SubjectId = :subjectId";
        } else {
            // Insert a new result for the student and subject
            $sql = "INSERT INTO tblresults (StudentId, SubjectId, Marks) VALUES (:studentId, :subjectId, :marks)";
        }

        // Update or insert the result
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $query->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);
        $query->bindParam(':marks', $mark, PDO::PARAM_STR);
        $query->execute();
    }

    $_SESSION['success_msg'] = "Student marks updated successfully";

    // Redirect back to manage-results.php
    header("Location: usermanage-results.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Result</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
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
            background: #d4edda;
            border-left: 4px solid #5cb85c;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap p {
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body class="top-navbar-fixed">
    <div class="main-wrapper">
        <!-- ========== TOP NAVBAR ========== -->
        <?php include('includes/topbar.php');?>
        <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/userleftbar.php');?>
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Edit Result</h2>
                            </div>
                        </div>
                        <!-- /.row -->
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="manage-results.php">Results</a></li>
                                    <li class="active">Edit Result</li>
                                </ul>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.container-fluid -->
                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>Edit Student Result</h5>
                                            </div>
                                        </div>
                                        <?php if(isset($_SESSION['success_msg'])){?>
                                            <div class="alert alert-success succWrap" role="alert">
                                                <p><?php echo htmlentities($_SESSION['success_msg']); ?></p>
                                            </div>
                                        <?php unset($_SESSION['success_msg']); ?>
                                        <?php } ?>
                                        <div class="panel-body">
                                            <form method="post">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Subject</th>
                                                            <th>Marks</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $cnt = 1;
                                                        foreach ($results as $result) {
                                                            ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($cnt); ?></td>
                                                                <td><?php echo htmlentities($result['SubjectName']); ?></td>
                                                                <td>
                                                                    <input type="hidden" name="subjectIds[]" value="<?php echo htmlentities($result['SubjectId']); ?>">
                                                                    <input type="text" class="form-control" name="marks[]" value="<?php echo htmlentities($result['Marks']); ?>" placeholder="N/A or enter marks">
                                                                </td>
                                                            </tr>
                                                            <?php
                                                            $cnt++;
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                                <div class="form-group">
                                                    <button type="submit" name="submit" class="btn btn-primary">Update Marks</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.col-md-6 -->
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
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>

    <!-- ========== THEME JS ========== -->
    <script src="js/main.js"></script>
</body>
</html>
