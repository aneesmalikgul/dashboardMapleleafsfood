<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';
// code for uploading report
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnUploadReport'])) {
    try {
        $conn->begin_transaction(); // Start transaction
        // Check if all form fields are set
        if (isset($_POST['patient_name'], $_POST['cnic']) && !empty($_POST['patient_name']) && !empty($_POST['cnic']) && isset($_FILES['pdf_report'])) {
            // Get form data
            $patient_name = mysqli_real_escape_string($conn, $_POST['patient_name']);
            $cnic = mysqli_real_escape_string($conn, $_POST['cnic']);
            $uploader_id = $_SESSION['user_id']; // Assuming you have a user ID for the uploader

            // Check file size and type
            $pdf_report = $_FILES['pdf_report'];
            $maxSize = 5 * 1024 * 1024; // 5 MB
            if ($pdf_report['size'] > $maxSize || $pdf_report['type'] !== 'application/pdf') {
                throw new Exception("Invalid file. Please select a PDF file with a maximum size of 5MB.");
            }

            // Define upload directory
            $uploadDir = 'assets/upload/report/';

            // Ensure upload directory exists
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Failed to create upload directory.");
                }
            }

            // Generate filename with CNIC of the patient
            $fileName = $cnic . '_' . basename($pdf_report['name']);
            $filePath = $uploadDir . $fileName;

            // Move the uploaded file to the upload directory
            if (!move_uploaded_file($pdf_report['tmp_name'], $filePath)) {
                throw new Exception("Failed to move uploaded file.");
            }

            // Prepare SQL statement to insert the report into the database
            $sql = "INSERT INTO reports (filename, uploader_id, cnic_no, patient_name, file_path) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement.");
            }

            // Bind parameters and execute the statement
            $stmt->bind_param("sisss", $fileName, $uploader_id, $cnic, $patient_name, $filePath);
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement.");
            }

            $conn->commit(); // Commit the transaction

            // Set success message
            $_SESSION['message'][] = array("type" => "success", "content" => "Report uploaded successfully!");

            // Redirect to success page
            header("Location: upload-report.php");
            exit();
        } else {
            throw new Exception("All form fields are required.");
        }
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback(); // Roll back the transaction on error

        // Set error message
        $error_message = $e->getMessage();
        $_SESSION['message'][] = array("type" => "error", "content" => $error_message);
    } finally {
        // Close the statement and connection
        if (isset($stmt)) {
            $stmt->close();
        }
        // if (isset($conn)) {
        //     $conn->close();
        // }
    }
}
?>

<head>
    <title>Upload Report | The National Way Medical Lab</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">
        <?php include 'layouts/menu.php'; ?>
        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->
        <div class="content-page">
            <div class="content">
                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">The National Medical Lab</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Reports</a></li>
                                        <li class="breadcrumb-item active">Upload Report</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Upload Report </h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <form class="needs-validation" novalidate style=" margin-left: 0; margin-right: 0;" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">

                                        <div class="mb-3 col-lg-6">
                                            <label class="form-label" for="validationCustom01">Full Name of Patient</label>
                                            <input type="text" class="form-control" name="patient_name" id="validationCustom01" placeholder="Enter Full Name of Patient" required>
                                            <div class="valid-feedback">Looks good!</div>
                                            <div class="invalid-feedback">Please enter the full name of the patient.</div>
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                            <label class="form-label" for="validationCustom02">CNIC # </label>
                                            <input type="number" class="form-control" name="cnic" id="validationCustom02" placeholder="12345-1234567-1" required>
                                            <div class="valid-feedback">Looks good!</div>
                                            <div class="invalid-feedback">Please enter the CNIC # of the patient.</div>
                                        </div>
                                        <div class="mb-3 col-lg-6">
                                            <label for="example-fileinput" class="form-label">Select PDF Report (MAX Size &lt;= 5MB)</label>
                                            <input type="file" name="pdf_report" id="pdf_report" class="form-control" accept=".pdf" required>
                                            <div class="valid-feedback">Looks good!</div>
                                            <div class="invalid-feedback">Please select a PDF file with a maximum size of 5MB.</div>
                                        </div>
                                        <input class="btn btn-primary" type="submit" name="btnUploadReport" id="btnUploadReport">
                                    </form>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                    </div> <!-- end row -->

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Uploaded Reports</h4>
                                    <p class="text-muted fs-14">

                                    </p>

                                    <table id="scroll-horizontal-datatable" class="table table-striped w-100 nowrap">
                                        <thead>
                                            <tr>
                                                <th>Report ID</th>
                                                <th>Patient Name</th>
                                                <th>Patient's CNIC #</th>
                                                <th>Uploaded Date</th>
                                                <th>File Name</th>
                                                <th>Uploaded By</th>
                                                <th>Actions</th> <!-- New column for actions -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php

                                            try {
                                                // Fetch uploaded reports data
                                                $sql = "SELECT id, patient_name, cnic_no, upload_date, filename, uploader_id FROM reports";
                                                $result = $conn->query($sql);
                                                if (!$result) {
                                                    throw new Exception("Error fetching uploaded reports: " . $conn->error);
                                                }
                                                $reports = array();
                                                while ($row = $result->fetch_assoc()) {
                                                    $reports[] = $row;
                                                }
                                            } catch (Exception $e) {
                                                $error_message = $e->getMessage();
                                                $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $error_message);
                                            } finally {
                                                $conn->close();
                                            }
                                            ?>
                                            <?php if (!empty($reports)) : ?>
                                                <?php foreach ($reports as $report) : ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($report['id']); ?></td>
                                                        <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($report['cnic_no']); ?></td>
                                                        <td><?php echo htmlspecialchars($report['upload_date']); ?></td>
                                                        <td><?php echo htmlspecialchars($report['filename']); ?></td>
                                                        <td><?php echo htmlspecialchars($report['uploader_id']); ?></td>
                                                        <td>
                                                            <a href="edit-report.php?report_id=<?php echo htmlspecialchars($report['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                            <a href="delete-report.php?report_id=<?php echo htmlspecialchars($report['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this report?');">Delete</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No reports found.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>


                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div> <!-- end row-->

                </div> <!-- container -->
            </div> <!-- content -->
            <?php include 'layouts/footer.php'; ?>
        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
    </div>
    <!-- END wrapper -->
    <?php include 'layouts/right-sidebar.php'; ?>
    <?php include 'layouts/footer-scripts.php'; ?>

    <!-- App js -->
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