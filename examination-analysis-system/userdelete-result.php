<?php
session_start();
include('includes/config.php');

// Check if student ID is provided
if (isset($_GET['stid'])) {
    $studentId = $_GET['stid'];

    // Delete the result record for the given student ID
    $deleteQuery = $dbh->prepare("DELETE FROM tblresults WHERE StudentId = :studentId");
    $deleteQuery->bindParam(':studentId', $studentId, PDO::PARAM_INT);
    $deleteQuery->execute();

    // Check if the deletion was successful
    if ($deleteQuery) {
        $_SESSION['success_msg'] = "Result record deleted successfully.";
    } else {
        $_SESSION['error_msg'] = "Error occurred while deleting result record.";
    }
} else {
    $_SESSION['error_msg'] = "Invalid student ID.";
}

// Redirect back to the manage-results.php page
header("Location: usermanage-results.php");
exit();
?>
