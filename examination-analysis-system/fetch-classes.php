<?php
include('includes/config.php');

if (isset($_POST['formId'])) {
    $formId = $_POST['formId'];

    // Retrieve classes based on the selected form
    $sql = "SELECT * FROM tblclasses WHERE FormId = :formId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':formId', $formId, PDO::PARAM_INT);
    $query->execute();
    $classes = $query->fetchAll(PDO::FETCH_OBJ);

    $output = '<option value="">Select Class</option>';
    foreach ($classes as $class) {
        $output .= '<option value="' . $class->id . '">' . $class->ClassName . '</option>';
    }

    echo $output;
}
?>
