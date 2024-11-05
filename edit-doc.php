<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';

$reportId = $_GET['doc_id'] ?? null; // Get report ID from query parameter

$report = null;
if ($reportId) {
    try {
        $conn->begin_transaction(); // Start transaction
        $sql = "SELECT * FROM reports WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        $report = $result->fetch_assoc();
        $stmt->close();

        if (!$report) {
            $conn->rollback();
            $_SESSION['message'][] = array("type" => "error", "content" => "Document not found.");
            header("Location: upload-doc.php");
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'][] = array("type" => "error", "content" => $e->getMessage());
        header("Location: upload-doc.php");
        exit();
    }
} else {
    $_SESSION['message'][] = array("type" => "error", "content" => "No Document ID specified.");
    header("Location: upload-doc.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnUploadReport'])) {
    try {
        if (isset($_POST['patient_name'], $_POST['cnic']) && !empty($_POST['patient_name']) && !empty($_POST['cnic'])) {
            // Get form data
            $patient_name = mysqli_real_escape_string($conn, $_POST['patient_name']);
            $cnic = mysqli_real_escape_string($conn, $_POST['cnic']);
            $uploader_id = $_SESSION['user_id']; // Assuming you have a user ID for the uploader

            $pdf_report_1 = $_FILES['pdf_report_1'];
            $pdf_report_2 = $_FILES['pdf_report_2'];
            $maxSize = 5 * 1024 * 1024; // 5 MB

            // Initialize file paths
            $filePath1 = null;
            $filePath2 = null;

            // Define upload directory
            $uploadDir = 'assets/upload/report/';
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Failed to create upload directory.");
                }
            }

            // Check and upload file 1
            if ($pdf_report_1['size'] > 0) {
                if ($pdf_report_1['size'] > $maxSize || $pdf_report_1['type'] !== 'application/pdf') {
                    throw new Exception("Invalid file for Report 1. Please select a PDF file with a maximum size of 5MB.");
                }

                // Generate filename with CNIC of the patient
                $fileName1 = $cnic . '_report1_' . basename($pdf_report_1['name']);
                $filePath1 = $uploadDir . $fileName1;

                // Move the uploaded file to the upload directory
                if (!move_uploaded_file($pdf_report_1['tmp_name'], $filePath1)) {
                    throw new Exception("Failed to move uploaded file for Report 1.");
                }
            }

            // Check and upload file 2
            if ($pdf_report_2['size'] > 0) {
                if ($pdf_report_2['size'] > $maxSize || $pdf_report_2['type'] !== 'application/pdf') {
                    throw new Exception("Invalid file for Report 2. Please select a PDF file with a maximum size of 5MB.");
                }

                // Generate filename with CNIC of the patient
                $fileName2 = $cnic . '_report2_' . basename($pdf_report_2['name']);
                $filePath2 = $uploadDir . $fileName2;

                // Move the uploaded file to the upload directory
                if (!move_uploaded_file($pdf_report_2['tmp_name'], $filePath2)) {
                    throw new Exception("Failed to move uploaded file for Report 2.");
                }
            }

            // Prepare SQL statement to update the report in the database with file paths
            $sql = "UPDATE reports SET uploader_id = ?, cnic_no = ?, patient_name = ?";
            $params = [$uploader_id, $cnic, $patient_name];
            $types = "iss";

            if ($filePath1) {
                $sql .= ", file_path_1 = ?";
                $params[] = $filePath1;
                $types .= "s";
            }
            if ($filePath2) {
                $sql .= ", file_path_2 = ?";
                $params[] = $filePath2;
                $types .= "s";
            }
            $sql .= " WHERE id = ?";
            $params[] = $reportId;
            $types .= "i";

            // Prepare the statement and bind parameters
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement.");
            }

            $conn->commit(); // Commit the transaction

            $_SESSION['message'][] = array("type" => "success", "content" => "Document updated successfully!");
            header("Location: upload-doc.php");
            exit();
        } else {
            throw new Exception("All form fields are required.");
        }
    } catch (Exception $e) {
        $conn->rollback(); // Roll back the transaction on error
        $_SESSION['message'][] = array("type" => "error", "content" => $e->getMessage());
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Document | Maple Leafs Food</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'layouts/menu.php'; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Maple Leafs Food</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Documents</a></li>
                                        <li class="breadcrumb-item active">Edit Uploaded Document</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Edit Uploaded Document</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <form class="needs-validation" novalidate action="<?php echo $_SERVER['PHP_SELF'] . '?doc_id=' . $reportId; ?>" method="post" enctype="multipart/form-data">
                                        <div class="mb-3 col-lg-6">
                                            <label class="form-label" for="validationCustom01">Full Name of Person</label>
                                            <input type="text" class="form-control" name="patient_name" id="validationCustom01" placeholder="Enter Full Name of Patient" value="<?php echo htmlspecialchars($report['patient_name'] ?? '', ENT_QUOTES); ?>" required>
                                            <div class="valid-feedback">Looks good!</div>
                                            <div class="invalid-feedback">Please enter the full name of the person.</div>
                                        </div>

                                        <div class="mb-3 col-lg-6">
                                            <label class="form-label" for="validationCustom02">CNIC # OR Passport #</label>
                                            <input type="text" class="form-control" name="cnic" id="validationCustom02" placeholder="1234512345671" value="<?php echo htmlspecialchars($report['cnic_no'] ?? '', ENT_QUOTES); ?>" required>
                                            <div class="valid-feedback">Looks good!</div>
                                            <div class="invalid-feedback">Please enter the CNIC # OR Passport #.</div>
                                        </div>

                                        <div class="mb-3 col-lg-6">
                                            <label for="example-fileinput1" class="form-label">Select First PDF Report (MAX Size <= 5MB, Leave empty to keep current file)</label>
                                                    <input type="file" name="pdf_report_1" id="pdf_report_1" class="form-control" accept=".pdf">
                                                    <div class="valid-feedback">Looks good!</div>
                                                    <div class="invalid-feedback">Please select a PDF file with a maximum size of 5MB.</div>
                                        </div>

                                        <div class="mb-3 col-lg-6">
                                            <label for="example-fileinput2" class="form-label">Select Second PDF Report (MAX Size <= 5MB, Leave empty to keep current file)</label>
                                                    <input type="file" name="pdf_report_2" id="pdf_report_2" class="form-control" accept=".pdf">
                                                    <div class="valid-feedback">Looks good!</div>
                                                    <div class="invalid-feedback">Please select a PDF file with a maximum size of 5MB.</div>
                                        </div>

                                        <input class="btn btn-primary" type="submit" name="btnUploadReport" id="btnUploadReport" value="Update Report">
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>
    <?php include 'layouts/right-sidebar.php'; ?>
    <?php include 'layouts/footer-scripts.php'; ?>
    <script src="assets/js/app.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('pdf_report').addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const maxSize = 5 * 1024 * 1024; // 5 MB
                    if (file.size > maxSize || file.type !== 'application/pdf') {
                        this.setCustomValidity('Invalid file. Please select a PDF file with a maximum size of 5MB.');
                        this.classList.add('is-invalid');
                    } else {
                        this.setCustomValidity('');
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                }
            });
        });
    </script>
</body>

</html>