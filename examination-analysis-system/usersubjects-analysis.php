<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Function to get the grade based on marks
function getGrade($marks) {
    if ($marks >= 85) {
        return 'A';
    } elseif ($marks >= 70) {
        return 'B';
    } elseif ($marks >= 60) {
        return 'C';
    } elseif ($marks >= 50) {
        return 'D';
    } elseif ($marks >= 40) {
        return 'E';
    } else {
        return 'F';
    }
}

// Function to calculate percentage
function calculatePercentage($count, $total) {
    if ($total > 0) {
        return round(($count / $total) * 100, 2);
    }
    return 0;
}

$filterForm = isset($_GET['form']) ? $_GET['form'] : '';

$sql = "SELECT su.id AS SubjectId, su.SubjectName AS SubjectName,
    COUNT(CASE WHEN r.marks >= 85 THEN 1 END) AS A,
    COUNT(CASE WHEN r.marks >= 70 AND r.marks < 85 THEN 1 END) AS B,
    COUNT(CASE WHEN r.marks >= 60 AND r.marks < 70 THEN 1 END) AS C,
    COUNT(CASE WHEN r.marks >= 50 AND r.marks < 60 THEN 1 END) AS D,
    COUNT(CASE WHEN r.marks >= 40 AND r.marks < 50 THEN 1 END) AS E,
    COUNT(CASE WHEN r.marks < 40 THEN 1 END) AS F,
    COUNT(*) AS Total
    FROM tblsubjects su
    LEFT JOIN tblresults r ON su.id = r.SubjectId
    LEFT JOIN tblstudents s ON s.StudentId = r.StudentId
    LEFT JOIN tblforms f ON f.Id = s.Form
    WHERE r.marks IS NOT NULL";

if (!empty($filterForm)) {
    $sql .= " AND f.Form = :form";
}

$sql .= " GROUP BY su.id, su.SubjectName
    ORDER BY su.id ASC";

$query = $dbh->prepare($sql);

if (!empty($filterForm)) {
    $query->bindParam(':form', $filterForm, PDO::PARAM_STR);
}

$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subject Analysis</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/animate-css/animate.min.css">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css">
    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
    <link rel="stylesheet" href="css/main.css">
    <script src="js/modernizr/modernizr.min.js"></script>
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
                                <h2 class="title">Subject Analysis</h2>
                            </div>
                        </div>
                        <!-- /.row -->
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="userdashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li> Results</li>
                                    <li class="active">Subject Analysis</li>
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
                                    <?php
                                    if ($query->rowCount() > 0) {
                                        ?>
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>Subject Analysis</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body p-20">
                                                <div class="form-group">
                                                    <label for="formFilter">View by Form:</label>
                                                    <select class="form-control" id="formFilter" name="formFilter">
                                                        <option value="">All Forms</option>
                                                        <option value="1" <?php if ($filterForm == "1") echo "selected"; ?>>Form 1</option>
                                                        <option value="2" <?php if ($filterForm == "2") echo "selected"; ?>>Form 2</option>
                                                        <option value="3" <?php if ($filterForm == "3") echo "selected"; ?>>Form 3</option>
                                                    </select>
                                                </div>
                                                <table class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th rowspan="2">#</th>
                                                            <th rowspan="2">Subject</th>
                                                            <th colspan="2">A</th>
                                                            <th colspan="2">B</th>
                                                            <th colspan="2">C</th>
                                                            <th colspan="2">D</th>
                                                            <th colspan="2">E</th>
                                                            <th colspan="2">F</th>
                                                            <th colspan="2">Menguasai</th> <!-- New column for Menguasai -->
                                                            <th colspan="2">Tidak Menguasai</th> <!-- New column for Tidak Menguasai -->
                                                            <th rowspan="2">GPMP</th> <!-- New column for TotalAverageGrade -->
                                                        </tr>
                                                        <tr>
                                                            <th>No</th>
                                                            <th>%</th>
                                                            <th>No</th>
                                                            <th>%</th>
                                                            <th>No</th>
                                                            <th>%</th>
                                                            <th>No</th>
                                                            <th>%</th>
                                                            <th>No</th>
                                                            <th>%</th>
                                                            <th>No</th>
                                                            <th>%</th>
                                                            <th>No</th>
                                                            <th>%</th>
                                                            <th>No</th>
                                                            <th>%</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $i = 1;
                                                        foreach ($results as $result) {
                                                            $totalGradePoints = ($result->A * 1) + ($result->B * 2) + ($result->C * 3) + ($result->D * 4) + ($result->E * 5) + ($result->F * 6);
                                                            $totalCounts = $result->A + $result->B + $result->C + $result->D + $result->E + $result->F;
                                                            $totalAverageGrade = $totalGradePoints / $totalCounts;
                                                            ?>
                                                            <tr>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo htmlentities($result->SubjectName); ?></td>
                                                                <td><?php echo htmlentities($result->A); ?></td>
                                                                <td><?php echo number_format(calculatePercentage($result->A, $result->Total), 2); ?></td>
                                                                <td><?php echo htmlentities($result->B); ?></td>
                                                                <td><?php echo number_format(calculatePercentage($result->B, $result->Total), 2); ?></td>
                                                                <td><?php echo htmlentities($result->C); ?></td>
                                                                <td><?php echo number_format(calculatePercentage($result->C, $result->Total), 2); ?></td>
                                                                <td><?php echo htmlentities($result->D); ?></td>
                                                                <td><?php echo number_format(calculatePercentage($result->D, $result->Total), 2); ?></td>
                                                                <td><?php echo htmlentities($result->E); ?></td>
                                                                <td><?php echo number_format(calculatePercentage($result->E, $result->Total), 2); ?></td>
                                                                <td><?php echo htmlentities($result->F); ?></td>
                                                                <td><?php echo number_format(calculatePercentage($result->F, $result->Total), 2); ?></td>
                                                                <td><?php echo htmlentities($result->A + $result->B + $result->C + $result->D + $result->E); ?></td> <!-- Menguasai counts -->
                                                                <td><?php echo number_format(calculatePercentage($result->A + $result->B + $result->C + $result->D + $result->E, $result->Total), 2); ?></td> <!-- Menguasai % -->
                                                                <td><?php echo htmlentities($result->F); ?></td> <!-- Tidak Menguasai counts -->
                                                                <td><?php echo number_format(calculatePercentage($result->F, $result->Total), 2); ?></td> <!-- Tidak Menguasai % -->
                                                                <td><?php echo number_format($totalAverageGrade, 2); ?></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <?php
                                    } else {
                                        echo "<p>No subjects found.</p>";
                                    }
                                    ?>
                                </div>
                            </div>
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
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/DataTables/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.table').DataTable({
                columnDefs: [{
                    type: 'num-fmt',
                    targets: '_all',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            // Check if the data represents a percentage column (ends with '%')
                            if (typeof data === 'string' && data.endsWith('%')) {
                                return parseFloat(data).toFixed(2) + '%';
                            }
                            return data;
                        }
                        return data;
                    }
                }]
            });

            $('#formFilter').change(function() {
                var formFilter = $(this).val();

                if (formFilter !== '') {
                    window.location.href = 'usersubjects-analysis.php?form=' + formFilter;
                } else {
                    window.location.href = 'usersubjects-analysis.php';
                }
            });
        });
    </script>
</body>
</html>
