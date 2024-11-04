<?php
include 'layouts/session.php';

include 'layouts/config.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['report_id'])) {
    $report_id = $_GET['report_id'];

    try {
        // Start transaction
        $conn->begin_transaction();

        // Prepare the SQL statement
        $sql = "DELETE FROM reports WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("i", $report_id);

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }

        // Commit the transaction
        $conn->commit();

        // Set success message
        $_SESSION['message'][] = array("type" => "success", "content" => "Report deleted successfully!");
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();

        // Set error message
        $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
    } finally {
        // Close the statement and connection
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }

        // Redirect back to the reports page
        header("Location: upload-report.php");
        exit();
    }
} else {
    // Redirect back to the reports page if report_id is not set
    $_SESSION['message'][] = array("type" => "error", "content" => "Invalid request.");
    header("Location: upload-report.php");
    exit();
}
