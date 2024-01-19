<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(isset($_GET['classid']) && $_GET['classid'] != '') {
    $classId = $_GET['classid'];

    // Get the class details to fetch the associated form ID
    $classSql = "SELECT Formid FROM tblclasses WHERE id = :classid";
    $classQuery = $dbh->prepare($classSql);
    $classQuery->bindParam(':classid', $classId, PDO::PARAM_INT);
    $classQuery->execute();
    $classResult = $classQuery->fetch(PDO::FETCH_ASSOC);

    if ($classResult) {
        $formId = $classResult['Formid'];

        // Delete class from tblclasses
        $classDeleteSql = "DELETE FROM tblclasses WHERE id = :classid";
        $classDeleteQuery = $dbh->prepare($classDeleteSql);
        $classDeleteQuery->bindParam(':classid', $classId, PDO::PARAM_INT);
        $classDeleteQuery->execute();

        // Delete associated form from tblforms if it's not linked with any other class
        $formCheckSql = "SELECT COUNT(*) as count FROM tblclasses WHERE Formid = :formid";
        $formCheckQuery = $dbh->prepare($formCheckSql);
        $formCheckQuery->bindParam(':formid', $formId, PDO::PARAM_INT);
        $formCheckQuery->execute();
        $formCheckResult = $formCheckQuery->fetch(PDO::FETCH_ASSOC);

        if ($formCheckResult['count'] == 0) {
            $formDeleteSql = "DELETE FROM tblforms WHERE Id = :formid";
            $formDeleteQuery = $dbh->prepare($formDeleteSql);
            $formDeleteQuery->bindParam(':formid', $formId, PDO::PARAM_INT);
            $formDeleteQuery->execute();
        }

        // Delete associated students from tblstudents
        $studentDeleteSql = "DELETE FROM tblstudents WHERE ClassId = :classid";
        $studentDeleteQuery = $dbh->prepare($studentDeleteSql);
        $studentDeleteQuery->bindParam(':classid', $classId, PDO::PARAM_INT);
        $studentDeleteQuery->execute();

        $msg = "Class and associated students deleted successfully.";
        header("Location: manage-classes.php?msg=".urlencode($msg));
        exit;
    } else {
        $error = "Class not found.";
        header("Location: manage-classes.php?error=".urlencode($error));
        exit;
    }
} else {
    $error = "Invalid class ID.";
    header("Location: manage-classes.php?error=".urlencode($error));
    exit;
}
?>
