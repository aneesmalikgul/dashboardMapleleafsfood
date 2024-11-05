<?php
// Include database configuration
include 'layouts/config.php';

// Check if CNIC is provided
if (isset($_POST['cnic'])) {
    // Initialize variables
    $cnic = $_POST['cnic'];
    $reportData = array();

    try {
        // Start transaction
        $conn->begin_transaction();

        // Prepare statement to search for the report based on CNIC number
        $sql = "SELECT * FROM reports WHERE cnic_no = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $cnic);

        // Execute the statement
        $stmt->execute();

        // Get result
        $result = $stmt->get_result();

        // Check if the query executed successfully
        if (!$result) {
            throw new Exception("Error searching report: " . $conn->error);
        }

        // Check if report found
        if ($result->num_rows > 0) {
            // Fetch the report data
            $reportData = $result->fetch_assoc();
        } else {
            // Report not found
            throw new Exception("Document not found or CNIC/Passport# does not match.");
        }

        // Commit transaction
        $conn->commit();

        // Return report data as JSON
        echo json_encode($reportData);
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();

        // Error occurred
        echo json_encode(array('error' => $e->getMessage()));
    } finally {
        // Close prepared statement and free result set
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($result)) {
            $result->close();
        }
        // Close database connection
        // $conn->close();
    }
} else {
    // CNIC not provided
    echo json_encode(array('error' => 'CNIC not provided'));
}
