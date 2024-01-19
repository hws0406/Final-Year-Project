<?php
session_start();
error_reporting(0);
include('includes/config.php');

function calculateTotalAverageGrade($result)
{
    $totalSubjects = 9; // Total number of subjects

    $grades = array(
        'A' => 1,
        'B' => 2,
        'C' => 3,
        'D' => 4,
        'E' => 5,
        'F' => 6
    );

    $totalGradePoints = 0;

    // Calculate total grade points for each subject
    if ($result->BM !== '') {
        $totalGradePoints += $grades[getGrade($result->BM)];
    }
    if ($result->BI !== '') {
        $totalGradePoints += $grades[getGrade($result->BI)];
    }
    if ($result->PI !== '') {
        $totalGradePoints += $grades[getGrade($result->PI)];
    }
    if ($result->SEJ !== '') {
        $totalGradePoints += $grades[getGrade($result->SEJ)];
    }
    if ($result->GEO !== '') {
        $totalGradePoints += $grades[getGrade($result->GEO)];
    }
    if ($result->M3 !== '') {
        $totalGradePoints += $grades[getGrade($result->M3)];
    }
    if ($result->SN !== '') {
        $totalGradePoints += $grades[getGrade($result->SN)];
    }
    if ($result->RBT !== '') {
        $totalGradePoints += $grades[getGrade($result->RBT)];
    }
    if ($result->PM !== '') {
        $totalGradePoints += $grades[getGrade($result->PM)];
    }
    if ($result->PSV !== '') {
        $totalGradePoints += $grades[getGrade($result->PSV)];
    }

    // Calculate TotalAverageGrade
    $totalAverageGrade = $totalGradePoints / $totalSubjects;

    return round($totalAverageGrade, 2);
}

// Get the selected form filter value
$formFilter = isset($_GET['form']) ? $_GET['form'] : '';

// Get the selected class filter value
$classFilter = isset($_GET['class']) ? $_GET['class'] : '';

// Build the SQL query with the form and class filter conditions
$sql = "SELECT s.StudentId, s.StudentName, f.Form, c.ClassName,
        COALESCE(SUM(CASE WHEN su.SubjectName = 'Bahasa Melayu' THEN COALESCE(r.Marks, '') END), '') AS BM,
        COALESCE(SUM(CASE WHEN su.SubjectName = 'Bahasa Inggeris' THEN COALESCE(r.Marks, '') END), '') AS BI,
        COALESCE(SUM(CASE WHEN su.SubjectName = 'Pendidikan Islam' THEN COALESCE(r.Marks, '') END), '') AS PI,
        COALESCE(SUM(CASE WHEN su.SubjectName = 'Sejarah' THEN COALESCE(r.Marks, '') END), '') AS SEJ,
        COALESCE(SUM(CASE WHEN su.SubjectName = 'Geografi' THEN COALESCE(r.Marks, '') END), '') AS GEO,
        COALESCE(SUM(CASE WHEN su.SubjectName = 'Mathematik' THEN COALESCE(r.Marks, '') END), '') AS M3,
        COALESCE(SUM(CASE WHEN su.SubjectName = 'Sains' THEN COALESCE(r.Marks, '') END), '') AS SN,
        COALESCE(SUM(CASE WHEN su.SubjectName = 'Reka Bentuk Teknologi' THEN COALESCE(r.Marks, '') END), '') AS RBT,
        COALESCE(SUM(CASE WHEN su.SubjectName = 'Pendidikan Moral' THEN COALESCE(r.Marks, '') END), '') AS PM,
        COALESCE(SUM(CASE WHEN su.SubjectName = 'Pendidikan Seni Visual' THEN COALESCE(r.Marks, '') END), '') AS PSV,
        COALESCE(SUM(CASE WHEN r.Marks <> 'N/A' THEN r.Marks END), 0) AS TotalMarks,
        COALESCE(SUM(CASE WHEN r.Marks <> 'N/A' THEN r.Marks END) * 100 / (COUNT(DISTINCT su.id) * 100), 0) AS Percentage
        FROM tblstudents s
        LEFT JOIN tblresults r ON r.StudentId = s.StudentId
        LEFT JOIN tblsubjects su ON su.id = r.SubjectId
        LEFT JOIN tblclasses c ON c.id = s.ClassId
        LEFT JOIN tblforms f ON f.id = c.FormId";

// Add the form filter condition if it is not empty
if (!empty($formFilter)) {
    $sql .= " WHERE f.Form = :formFilter";
}

// Add the class filter condition if it is not empty
if (!empty($classFilter)) {
    if (empty($formFilter)) {
        $sql .= " WHERE c.ClassName = :classFilter";
    } else {
        $sql .= " AND c.ClassName = :classFilter";
    }
}

$sql .= " GROUP BY s.StudentId, s.StudentName, f.Form, c.ClassName
          ORDER BY f.Form, c.ClassName";

$query = $dbh->prepare($sql);

// Bind the form filter parameter if it is not empty
if (!empty($formFilter)) {
    $query->bindParam(':formFilter', $formFilter, PDO::PARAM_STR);
}

// Bind the class filter parameter if it is not empty
if (!empty($classFilter)) {
    $query->bindParam(':classFilter', $classFilter, PDO::PARAM_STR);
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
    <title>View Analysis</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/animate-css/animate.min.css">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css">
    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
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
            background: #d4edda; /* Set the desired background color */
            border-left: 4px solid #5cb85c;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap p {
            margin: 0; /* Remove default margin */
            white-space: nowrap; /* Prevent the text from wrapping */
            overflow: hidden; /* Hide any overflow content */
            text-overflow: ellipsis; /* Truncate the text with an ellipsis if it exceeds the width */
        }
        .form-group {
            margin-bottom: 20px;
            display: flex; /* Added */
            align-items: center; /* Added */
        }
        .form-group select {
            flex: 1; /* Added */
            padding: 6px 12px;
            height: 40px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #555;
            background-color: #fff;
            background-image: none;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group label {
            margin-right: 10px; /* Added */
            font-size: 14px;
            color: #777;
        }
        .form-group .reset-button { /* Added */
            margin-left: 10px;
            height: 40px;
            padding: 6px 12px;
        }
    </style>
    <script>
        // Function to hide the success message after a specified duration
        setTimeout(function(){
            var successMsg = document.getElementById('success-message');
            if (successMsg) {
                successMsg.style.display = 'none';
            }
        }, 3000); // Hide the success message after 3 seconds

        function applyFilter(formValue, classValue) {
            // Get the current URL
            var currentURL = window.location.href;

            // Check if the URL already contains a query parameter
            var queryParamIndex = currentURL.indexOf('?');
            var hasQueryParams = queryParamIndex !== -1;

            // Construct the new URL with the form and class filter query parameters
            var newURL;
            if (hasQueryParams) {
                newURL = currentURL.substring(0, queryParamIndex) + '?form=' + formValue + '&class=' + classValue;
            } else {
                newURL = currentURL + '?form=' + formValue + '&class=' + classValue;
            }

            // Redirect to the new URL
            window.location.href = newURL;
        }

        function resetFilters() {
    // Get the current URL
    var currentURL = window.location.href;

    // Remove the form and class filter query parameters from the URL
    var newURL = currentURL.replace(/([&?])form=([^&]*)|class=([^&]*)/gi, '');

    // Remove any trailing '?' or '&' characters from the URL
    newURL = newURL.replace(/[?&]+$/, '');

    // Redirect to the new URL
    window.location.href = newURL;
}

    </script>
</head>
<body class="top-navbar-fixed">
    <div class="main-wrapper">
        <!-- ========== TOP NAVBAR ========== -->
        <?php include('includes/topbar.php');?> 
        <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/leftbar.php');?>  
                <div class="main-page">
                    <div class="container-fluid">
                        <div class="row page-title-div">
                            <div class="col-md-6">
                                <h2 class="title">View Analysis</h2>
                            </div>
                        </div>
                        <!-- /.row -->
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li> Results</li>
                                    <li class="active">View Analysis</li>
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
                                                <h5>Result Analysis</h5>
                                            </div>
                                        </div>
                                        <?php if(isset($_SESSION['success_msg'])){?>
                                        <div class="alert alert-success succWrap" id="success-message" role="alert">
                                            <p><?php echo htmlentities($_SESSION['success_msg']); ?></p>
                                        </div>
                                        <?php 
                                            unset($_SESSION['success_msg']);
                                        } else if(isset($_SESSION['error_msg'])){?>
                                        <div class="alert alert-danger errorWrap" role="alert">
                                            <strong>Oh snap!</strong> <?php echo htmlentities($_SESSION['error_msg']); ?>
                                        </div>
                                        <?php 
                                            unset($_SESSION['error_msg']);
                                        } ?>
                                        <div class="panel-body p-20">
                                            <div class="form-group">
                                                <label for="formFilter">Form:</label>
                                                <select id="formFilter" name="form" onchange="applyFilter(this.value, '<?php echo htmlentities($classFilter); ?>')">
                                                    <option value="">All Forms</option>
                                                    <option value="1"<?php echo ($formFilter == '1') ? ' selected' : ''; ?>>Form 1</option>
                                                    <option value="2"<?php echo ($formFilter == '2') ? ' selected' : ''; ?>>Form 2</option>
                                                    <option value="3"<?php echo ($formFilter == '3') ? ' selected' : ''; ?>>Form 3</option>
                                                    <!-- Add more options for other forms as needed -->
                                                </select>
                                                <label for="classFilter">Class:</label>
                                                <select id="classFilter" name="class" onchange="applyFilter('<?php echo htmlentities($formFilter); ?>', this.value)">
                                                    <option value="">All Classes</option>
                                                    <option value="Arif"<?php echo ($classFilter == 'Arif') ? ' selected' : ''; ?>>Arif</option>
                                                    <option value="Bestari"<?php echo ($classFilter == 'Bestari') ? ' selected' : ''; ?>>Bestari</option>
                                                    <option value="Cemerlang"<?php echo ($classFilter == 'Cemerlang') ? ' selected' : ''; ?>>Cemerlang</option>
                                                    <option value="Dinamik"<?php echo ($classFilter == 'Dinamik') ? ' selected' : ''; ?>>Dinamik</option>
                                                    <option value="Elit"<?php echo ($classFilter == 'Elit') ? ' selected' : ''; ?>>Elit</option>
                                                    <!-- Add more options for other classes as needed -->
                                                </select>
                                                <button class="btn btn-default" onclick="resetFilters()">Reset</button>
                                            </div>
                                            <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">#</th>
                                                        <th rowspan="2">Student Name</th>
                                                        <th rowspan="2">Form</th>
                                                        <th rowspan="2">Class</th>
                                                        <th colspan="2">BM</th>
                                                        <th colspan="2">BI</th>
                                                        <th colspan="2">PI</th>
                                                        <th colspan="2">SEJ</th>
                                                        <th colspan="2">GEO</th>
                                                        <th colspan="2">M3</th>
                                                        <th colspan="2">SN</th>
                                                        <th colspan="2">RBT</th>
                                                        <th colspan="2">PM</th>
                                                        <th colspan="2">PSV</th>
                                                        <th rowspan="2">Total</th>
                                                        <th rowspan="2">%</th>
                                                        <th rowspan="2">GP</th>
                                                    </tr>
                                                    <tr>
                                                        <th>M</th>
                                                        <th>G</th>
                                                        <th>M</th>
                                                        <th>G</th>
                                                        <th>M</th>
                                                        <th>G</th>
                                                        <th>M</th>
                                                        <th>G</th>
                                                        <th>M</th>
                                                        <th>G</th>
                                                        <th>M</th>
                                                        <th>G</th>
                                                        <th>M</th>
                                                        <th>G</th>
                                                        <th>M</th>
                                                        <th>G</th>
                                                        <th>M</th>
                                                        <th>G</th>
                                                        <th>M</th>
                                                        <th>G</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    foreach($results as $result) {
                                                        $totalAverageGrade = calculateTotalAverageGrade($result);
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlentities($cnt);?></td>
                                                            <td><?php echo htmlentities($result->StudentName);?></td>
                                                            <td><?php echo htmlentities($result->Form);?></td>
                                                            <td><?php echo htmlentities($result->ClassName);?></td>
                                                            <td><?php echo ($result->BM !== '') ? htmlentities($result->BM) : '';?></td>
                                                            <td><?php echo ($result->BM !== '') ? getGrade($result->BM) : '';?></td>
                                                            <td><?php echo ($result->BI !== '') ? htmlentities($result->BI) : '';?></td>
                                                            <td><?php echo ($result->BI !== '') ? getGrade($result->BI) : '';?></td>
                                                            <td><?php echo ($result->PI !== '') ? htmlentities($result->PI) : '';?></td>
                                                            <td><?php echo ($result->PI !== '') ? getGrade($result->PI) : '';?></td>
                                                            <td><?php echo ($result->SEJ !== '') ? htmlentities($result->SEJ) : '';?></td>
                                                            <td><?php echo ($result->SEJ !== '') ? getGrade($result->SEJ) : '';?></td>
                                                            <td><?php echo ($result->GEO !== '') ? htmlentities($result->GEO) : '';?></td>
                                                            <td><?php echo ($result->GEO !== '') ? getGrade($result->GEO) : '';?></td>
                                                            <td><?php echo ($result->M3 !== '') ? htmlentities($result->M3) : '';?></td>
                                                            <td><?php echo ($result->M3 !== '') ? getGrade($result->M3) : '';?></td>
                                                            <td><?php echo ($result->SN !== '') ? htmlentities($result->SN) : '';?></td>
                                                            <td><?php echo ($result->SN !== '') ? getGrade($result->SN) : '';?></td>
                                                            <td><?php echo ($result->RBT !== '') ? htmlentities($result->RBT) : '';?></td>
                                                            <td><?php echo ($result->RBT !== '') ? getGrade($result->RBT) : '';?></td>
                                                            <td><?php echo ($result->PM !== '') ? htmlentities($result->PM) : '';?></td>
                                                            <td><?php echo ($result->PM !== '') ? getGrade($result->PM) : '';?></td>
                                                            <td><?php echo ($result->PSV !== '') ? htmlentities($result->PSV) : '';?></td>
                                                            <td><?php echo ($result->PSV !== '') ? getGrade($result->PSV) : '';?></td>
                                                            <td><?php echo number_format($result->TotalMarks);?></td>
                                                            <td><?php echo number_format($result->Percentage, 2);?></td>
                                                            <td><?php echo $totalAverageGrade;?></td>
                                                        </tr>
                                                        <?php
                                                        $cnt++;
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                            <!-- /.col-md-12 -->
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
    <script src="js/iscroll/iscroll.js"></script>

    <!-- ========== PAGE JS FILES ========== -->
    <script src="js/prism/prism.js"></script>
    <script src="js/DataTables/datatables.min.js"></script>

    <!-- ========== THEME JS ========== -->
    <script src="js/main.js"></script>
    <script>
        $(function ($) {
            $('#example').DataTable({
                "lengthMenu": [[25, 50, 75, -1], [25, 50, 75, "All"]],
                "pageLength": 25
            });

            $('#example2').DataTable({
                "scrollY": "300px",
                "scrollCollapse": true,
                "paging": false
            });

            $('#example3').DataTable({
                "lengthMenu": [[25, 50, 75, -1], [25, 50, 75, "All"]],
                "pageLength": 25
            });
        });
    </script>
</body>
</html>

<?php
function getGrade($marks) {
    if ($marks >= 85) {
        return 'A';
    } elseif ($marks >= 75) {
        return 'B';
    } elseif ($marks >= 65) {
        return 'C';
    } elseif ($marks >= 55) {
        return 'D';
    } elseif ($marks >= 45) {
        return 'E';
    } else {
        return 'F';
    }
}
?>
