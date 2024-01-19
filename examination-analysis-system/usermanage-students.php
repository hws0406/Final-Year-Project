<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Check if success message is set in session
if (isset($_SESSION['success_msg'])) {
    $msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// Delete Student Record
if (isset($_GET['stid'])) {
    $studentId = $_GET['stid'];

    // Perform the delete operation based on the student ID
    $sql = "DELETE FROM tblstudents WHERE StudentId = :studentId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentId', $studentId, PDO::PARAM_INT);
    $query->execute();

    // Check if the delete operation was successful
    if ($query->rowCount() > 0) {
        $_SESSION['success_msg'] = "Student deleted successfully";
    } else {
        $_SESSION['error_msg'] = "Failed to delete student";
    }

    // Redirect to manage-students.php
    header("Location: usermanage-students.php");
    exit;
}

// Retrieve the filter values from the form submission
$formFilter = isset($_GET['form']) ? $_GET['form'] : '';
$classFilter = isset($_GET['class']) ? $_GET['class'] : '';

$sql = "SELECT tblstudents.StudentId, tblstudents.StudentName, tblstudents.Gender, tblforms.Form, tblclasses.ClassName 
        FROM tblstudents 
        JOIN tblclasses ON tblclasses.id = tblstudents.ClassId 
        JOIN tblforms ON tblforms.Id = tblstudents.Form";

// Add filter conditions to the query if a filter option is selected
if (!empty($formFilter) && !empty($classFilter)) {
    $sql .= " WHERE tblforms.Form = :form AND tblclasses.ClassName = :class";
} elseif (!empty($formFilter)) {
    $sql .= " WHERE tblforms.Form = :form";
} elseif (!empty($classFilter)) {
    $sql .= " WHERE tblclasses.ClassName = :class";
}

$sql .= " ORDER BY tblforms.Form ASC, tblclasses.ClassName ASC, tblstudents.StudentName ASC";

$query = $dbh->prepare($sql);

// Bind filter values to the query parameters
if (!empty($formFilter) && !empty($classFilter)) {
    $query->bindParam(':form', $formFilter, PDO::PARAM_STR);
    $query->bindParam(':class', $classFilter, PDO::PARAM_STR);
} elseif (!empty($formFilter)) {
    $query->bindParam(':form', $formFilter, PDO::PARAM_STR);
} elseif (!empty($classFilter)) {
    $query->bindParam(':class', $classFilter, PDO::PARAM_STR);
}

$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
$cnt = 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Students</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/animate-css/animate.min.css">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css">
    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css" />
    <link rel="stylesheet" href="css/main.css" media="screen">
    <script src="js/modernizr/modernizr.min.js"></script>
    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #dd3d36;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
        }

        .succWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #5cb85c;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
        }

        .filter-form {
            display: flex;
            align-items: center;
        }

        .filter-form .form-group {
            margin-right: 10px;
        }

        .filter-btns {
            display: flex;
            align-items: center;
            margin-left: 10px;
        }
    </style>
</head>

<body class="top-navbar-fixed">
    <div class="main-wrapper">

        <!-- ========== TOP NAVBAR ========== -->
        <?php include('includes/topbar.php'); ?>
        <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/userleftbar.php'); ?>

                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">Manage Students</h2>
                            </div>
                        </div>

                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="userdashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li> Students</li>
                                    <li class="active">Manage Students</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <section class="section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>View Students Info</h5>
                                            </div>
                                        </div>
                                        <?php if ($msg) { ?>
                                            <div class="alert alert-success left-icon-alert" role="alert">
                                                <strong></strong><?php echo htmlentities($msg); ?>
                                            </div>
                                            <script>
                                                // Remove success message after 3 seconds
                                                setTimeout(function() {
                                                    document.querySelector('.alert-success').style.display = 'none';
                                                }, 3000);
                                            </script>
                                        <?php } else if ($error) { ?>
                                            <div class="alert alert-danger left-icon-alert" role="alert">
                                                <strong></strong> <?php echo htmlentities($error); ?>
                                            </div>
                                        <?php } ?>
                                        <div class="panel-body p-20">
                                            <form method="GET" action="" class="filter-form">
                                                <div class="form-group">
                                                    <label for="formSelect">Form:</label>
                                                    <select class="form-control" id="formSelect" name="form">
                                                        <option value="">All Forms</option>
                                                        <option value="1">Form 1</option>
                                                        <option value="2">Form 2</option>
                                                        <option value="3">Form 3</option>
                                                        <option value="4">Form 4</option>
                                                        <option value="5">Form 5</option>
                                                        <!-- Add more options for different forms if needed -->
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="classSelect">Class:</label>
                                                    <select class="form-control" id="classSelect" name="class">
                                                        <option value="">All Classes</option>
                                                        <option value="Arif">Arif</option>
                                                        <option value="Bestari">Bestari</option>
                                                        <option value="Cemerlang">Cemerlang</option>
                                                        <option value="Dinamik">Dinamik</option>
                                                        <option value="Elit">Elit</option>
                                                        <!-- Add more options for different classes if needed -->
                                                    </select>
                                                </div>
                                                <div class="filter-btns">
                                                    <button type="submit" class="btn btn-primary">Filter</button>
                                                    <button type="button" class="btn btn-default" onclick="resetForm()">Reset</button>
                                                </div>
                                            </form>
                                            <br>
                                            <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Student Name</th>
                                                        <th>Gender</th>
                                                        <th>Form</th>
                                                        <th>Class Name</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if ($query->rowCount() > 0) {
                                                        foreach ($results as $result) {
                                                            ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($cnt); ?></td>
                                                                <td><?php echo htmlentities($result->StudentName); ?></td>
                                                                <td><?php echo htmlentities($result->Gender); ?></td>
                                                                <td><?php echo htmlentities($result->Form); ?></td>
                                                                <td><?php echo htmlentities($result->ClassName); ?></td>
                                                                <td>
                                                                    <a href="useredit-student.php?stid=<?php echo htmlentities($result->StudentId); ?>"><i class="fa fa-edit" title="Edit Record"></i></a>
                                                                    <a href="usermanage-students.php?stid=<?php echo htmlentities($result->StudentId); ?>" onclick="return confirm('Are you sure you want to delete this student?');"><i class="fa fa-trash" title="Delete Record"></i></a>
                                                                </td>
                                                            </tr>
                                                    <?php
                                                            $cnt++;
                                                        }
                                                    } else {
                                                        echo '<tr><td colspan="6">No records found</td></tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

    </div>

    <!-- Common and page JS files -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>
    <script src="js/prism/prism.js"></script>
    <script src="js/DataTables/datatables.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Function to reset the form
        function resetForm() {
            document.getElementById("formSelect").selectedIndex = 0;
            document.getElementById("classSelect").selectedIndex = 0;
            
            // Submit the form to remove the filter parameters from the URL
            document.querySelector('.filter-form').submit();
        }

        $(function($) {
            $('#example').DataTable({
                "lengthMenu": [
                    [25, 50, 75, -1],
                    [25, 50, 75, "All"]
                ],
                "pageLength": 25
            });

            $('#example2').DataTable({
                "scrollY": "300px",
                "scrollCollapse": true,
                "paging": false
            });

            $('#example3').DataTable({
                "lengthMenu": [
                    [25, 50, 75, -1],
                    [25, 50, 75, "All"]
                ],
                "pageLength": 25
            });
        });
    </script>
</body>

</html>