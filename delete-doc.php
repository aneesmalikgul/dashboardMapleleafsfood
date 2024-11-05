<?php
include 'layouts/session.php';
include 'layouts/config.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['doc_id'])) {
    $report_id = $_GET['report_id'];

    try {
        // Start transaction
        $conn->begin_transaction();

        // Fetch file paths from the database
        $sqlFetch = "SELECT file_path_1, file_path_2 FROM reports WHERE id = ?";
        $stmtFetch = $conn->prepare($sqlFetch);
        if (!$stmtFetch) {
            throw new Exception("Failed to prepare fetch statement: " . $conn->error);
        }
        $stmtFetch->bind_param("i", $report_id);
        $stmtFetch->execute();
        $stmtFetch->bind_result($file_path_1, $file_path_2);
        $stmtFetch->fetch();
        $stmtFetch->close();

        // Prepare the SQL statement to delete the record
        $sqlDelete = "DELETE FROM reports WHERE id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        if (!$stmtDelete) {
            throw new Exception("Failed to prepare delete statement: " . $conn->error);
        }
        $stmtDelete->bind_param("i", $report_id);

        // Execute the delete statement
        if (!$stmtDelete->execute()) {
            throw new Exception("Failed to execute delete statement: " . $stmtDelete->error);
        }

        // Commit the transaction
        $conn->commit();

        // Delete the files if they exist
        if ($file_path_1 && file_exists($file_path_1)) {
            unlink($file_path_1);
        }
        if ($file_path_2 && file_exists($file_path_2)) {
            unlink($file_path_2);
        }

        // Set success message
        $_SESSION['message'][] = array("type" => "success", "content" => "Document and associated files deleted successfully!");
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();

        // Set error message
        $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
    } finally {
        // Close the delete statement and connection
        if (isset($stmtDelete)) {
            $stmtDelete->close();
        }
        if (isset($conn)) {
            $conn->close();
        }

        // Redirect back to the reports page
        header("Location: upload-doc.php");
        exit();
    }
} else {
    // Redirect back to the docs page if report_id is not set
    $_SESSION['message'][] = array("type" => "error", "content" => "Invalid request.");
    header("Location: upload-doc.php");
    exit();
}
