<?php
session_start();
include('includes/config.php');

$filterForm = isset($_GET['form']) ? $_GET['form'] : '';
$filterClass = isset($_GET['class']) ? $_GET['class'] : '';

$sql = "SELECT s.StudentId, s.StudentName, f.Form, c.ClassName,
    COALESCE(SUM(CASE WHEN su.SubjectName = 'Bahasa Melayu' THEN r.Marks ELSE NULL END), 'N/A') AS BM,
    COALESCE(SUM(CASE WHEN su.SubjectName = 'Bahasa Inggeris' THEN r.Marks ELSE NULL END), 'N/A') AS BI,
    COALESCE(SUM(CASE WHEN su.SubjectName = 'Pendidikan Islam' THEN r.Marks ELSE NULL END), 'N/A') AS PI,
    COALESCE(SUM(CASE WHEN su.SubjectName = 'Sejarah' THEN r.Marks ELSE NULL END), 'N/A') AS SEJ,
    COALESCE(SUM(CASE WHEN su.SubjectName = 'Geografi' THEN r.Marks ELSE NULL END), 'N/A') AS GEO,
    COALESCE(SUM(CASE WHEN su.SubjectName = 'Mathematik' THEN r.Marks ELSE NULL END), 'N/A') AS M3,
    COALESCE(SUM(CASE WHEN su.SubjectName = 'Sains' THEN r.Marks ELSE NULL END), 'N/A') AS SN,
    COALESCE(SUM(CASE WHEN su.SubjectName = 'Reka Bentuk Teknologi' THEN r.Marks ELSE NULL END), 'N/A') AS RBT,
    COALESCE(SUM(CASE WHEN su.SubjectName = 'Pendidikan Moral' THEN r.Marks ELSE NULL END), 'N/A') AS PM,
    COALESCE(SUM(CASE WHEN su.SubjectName = 'Pendidikan Seni Visual' THEN r.Marks ELSE NULL END), 'N/A') AS PSV
    FROM tblstudents s
    LEFT JOIN tblresults r ON r.StudentId = s.StudentId
    LEFT JOIN tblsubjects su ON su.id = r.SubjectId
    LEFT JOIN tblforms f ON f.Id = s.Form
    LEFT JOIN tblclasses c ON c.id = s.ClassId";

if (!empty($filterForm)) {
    $sql .= " WHERE f.Form = :form";
}

if (!empty($filterClass)) {
    $sql .= !empty($filterForm) ? " AND c.ClassName = :class" : " WHERE c.ClassName = :class";
}

$sql .= " GROUP BY s.StudentId, s.StudentName, f.Form, c.ClassName
    ORDER BY f.Form, c.ClassName";

$query = $dbh->prepare($sql);

if (!empty($filterForm)) {
    $query->bindParam(':form', $filterForm, PDO::PARAM_STR);
}

if (!empty($filterClass)) {
    $query->bindParam(':class', $filterClass, PDO::PARAM_STR);
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
    <title>Manage Results</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/animate-css/animate.min.css">
    <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css">
    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
    <link rel="stylesheet" href="css/main.css">
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
                                <h2 class="title">Manage Results</h2>
                            </div>
                        </div>
                        <!-- /.row -->
                        <div class="row breadcrumb-div">
                            <div class="col-md-6">
                                <ul class="breadcrumb">
                                    <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                    <li> Results</li>
                                    <li class="active">Manage Results</li>
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
                                                <h5>View Students Result</h5>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-3">
                                                    <select class="form-control" id="formFilter">
                                                        <option value="">All Form</option>
                                                        <option value="1">Form 1</option>
                                                        <option value="2">Form 2</option>
                                                        <option value="3">Form 3</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-control" id="classFilter">
                                                        <option value="">All Class</option>
                                                        <option value="Arif">Arif</option>
                                                        <option value="Bestari">Bestari</option>
                                                        <option value="Cemerlang">Cemerlang</option>
                                                        <option value="Dinamik">Dinamik</option>
                                                        <option value="Elit">Elit</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <button class="btn btn-primary" id="filterBtn">Filter</button>
                                                    <button class="btn btn-default" id="resetBtn">Reset</button>
                                                </div>
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
                                            <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Student Name</th>
                                                        <th>Form</th>
                                                        <th>Class</th>
                                                        <th>BM</th>
                                                        <th>BI</th>
                                                        <th>PI</th>
                                                        <th>SEJ</th>
                                                        <th>GEO</th>
                                                        <th>M3</th>
                                                        <th>SN</th>
                                                        <th>RBT</th>
                                                        <th>PM</th>
                                                        <th>PSV</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $cnt = 1;
                                                    if ($query->rowCount() > 0) {
                                                        foreach ($results as $result) {
                                                            ?>
                                                            <tr>
                                                                <td><?php echo htmlentities($cnt);?></td>
                                                                <td><?php echo htmlentities($result->StudentName);?></td>
                                                                <td><?php echo htmlentities($result->Form);?></td>
                                                                <td><?php echo htmlentities($result->ClassName);?></td>
                                                                <td><?php echo htmlentities($result->BM !== 'N/A' ? $result->BM : ''); ?></td>
                                                                <td><?php echo htmlentities($result->BI !== 'N/A' ? $result->BI : ''); ?></td>
                                                                <td><?php echo htmlentities($result->PI !== 'N/A' ? $result->PI : ''); ?></td>
                                                                <td><?php echo htmlentities($result->SEJ !== 'N/A' ? $result->SEJ : ''); ?></td>
                                                                <td><?php echo htmlentities($result->GEO !== 'N/A' ? $result->GEO : ''); ?></td>
                                                                <td><?php echo htmlentities($result->M3 !== 'N/A' ? $result->M3 : ''); ?></td>
                                                                <td><?php echo htmlentities($result->SN !== 'N/A' ? $result->SN : ''); ?></td>
                                                                <td><?php echo htmlentities($result->RBT !== 'N/A' ? $result->RBT : ''); ?></td>
                                                                <td><?php echo htmlentities($result->PM !== 'N/A' ? $result->PM : ''); ?></td>
                                                                <td><?php echo htmlentities($result->PSV !== 'N/A' ? $result->PSV : ''); ?></td>
                                                                <td>
                                                                    <a href="edit-result.php?stid=<?php echo htmlentities($result->StudentId);?>"><i class="fa fa-edit" title="Edit Record"></i></a>
                                                                    <a href="#" class="delete" data-id="<?php echo htmlentities($result->StudentId); ?>"><i class="fa fa-trash" title="Delete Record"></i></a>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                            $cnt++;
                                                        }
                                                    } else {
                                                        ?>
                                                        <tr>
                                                            <td colspan="15" style="text-align: center;">No results found</td>
                                                        </tr>
                                                        <?php
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

    <!-- ========== COMMON JS FILES ========== -->
    <script src="js/jquery/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap/bootstrap.min.js"></script>
    <script src="js/pace/pace.min.js"></script>
    <script src="js/lobipanel/lobipanel.min.js"></script>
    <script src="js/iscroll/iscroll.js"></script>

    <!-- ========== PAGE JS FILES ========== -->
    <script src="js/DataTables/datatables.min.js"></script>

    <!-- ========== THEME JS ========== -->
    <script src="js/main.js"></script>
    <script>
    $(function ($) {
        var table = $('#example').DataTable({
            "lengthMenu": [[25, 50, 75, -1], [25, 50, 75, "All"]],
            "pageLength": 25
        });

        $('#filterBtn').click(function() {
            var formFilter = $('#formFilter').val();
            var classFilter = $('#classFilter').val();

            if (formFilter !== '' || classFilter !== '') {
                var url = 'manage-results.php?';

                if (formFilter !== '') {
                    url += 'form=' + formFilter;
                }

                if (classFilter !== '') {
                    if (formFilter !== '') {
                        url += '&';
                    }
                    url += 'class=' + classFilter;
                }

                window.location.href = url;
            }
        });

        $('#resetBtn').click(function() {
            $('#formFilter').val('');
            $('#classFilter').val('');

            window.location.href = 'manage-results.php';
        });

        // Delete button click event
        $('.delete').click(function () {
            var studentId = $(this).data('id');
            if (confirm('Are you sure you want to delete this record?')) {
                // Redirect to delete page with studentId parameter
                window.location.href = 'delete-result.php?stid=' + studentId;
            }
        });
    });
    </script>
</body>
</html>
