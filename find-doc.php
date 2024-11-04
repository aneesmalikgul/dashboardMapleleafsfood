<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="The City Care Medical Lab Admin Dashboard Developed by The Millionaire Soft." name="description" />
    <meta content="The Millionaire Soft." name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon-16x16.png">
    <link rel="manifest" href="assets/images/site.webmanifest">
    <title>Search Report | The National Way Medical Lab</title>

    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f3f3f3;
            font-family: 'Roboto', sans-serif;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            width: 100%;
            max-width: 500px;
            border: 1px solid #ccc;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            background-color: #fff;
        }

        .btn-search {
            width: 100%;
            background-color: #6f42c1;
            border-color: #6f42c1;
        }

        .btn-search:hover {
            background-color: #5a33a7;
            border-color: #5a33a7;
        }

        .card-header {
            background-color: #6f42c1;
            color: #fff;
            text-align: center;
            border-bottom: none;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 20px 0;
        }

        .card-body {
            padding: 30px;
        }

        .instructions {
            margin-bottom: 20px;
            font-size: 15px;
            color: #666;
            text-align: center;
        }

        .lab-name {
            font-size: 22px;
            font-weight: bold;
            color: #6f42c1;
            margin-bottom: 30px;
            text-align: center;
        }

        .alert {
            margin-bottom: 20px;
        }

        .table {
            background-color: #fff;
        }

        .table th,
        .table td {
            text-align: center;
        }

        /* Apply same color as form header to table header */
        .table thead {
            background-color: #6f42c1;
            color: white;
        }

        @media (max-width: 768px) {
            .card {
                width: 90%;
            }
        }

        @media (max-width: 576px) {
            .lab-name {
                font-size: 18px;
            }

            .instructions {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Medical Lab Name -->
        <div class="lab-name">
            <h2>The National Medical Lab</h2>
        </div>
        <!-- Instructions -->
        <div class="instructions">Please enter your CNIC number without dashes to search for the report.</div>
        <!-- Search Card -->
        <div class="card">
            <!-- Card header -->
            <div class="card-header">
                <h5 class="card-title">Search Report</h5>
            </div>
            <!-- Card body with search form -->
            <div class="card-body">
                <form id="searchForm">
                    <div class="form-group">
                        <div class="mb-3">
                            <label class="form-label">CNIC #</label>
                            <input type="text" class="form-control" id="cnic" placeholder="Enter CNIC Number" required>
                            <span class="fs-13 text-muted">e.g "xxxxxxxxxxxxx"</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-search">Search</button>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div id="reportTable" class="w-100" style="display: none;">
            <h5 class="mt-4 text-center">Report Details</h5>
            <div class="table-responsive">
                <table class="table table-bordered mt-2">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Patient Name</th>
                            <th>Download</th>
                        </tr>
                    </thead>
                    <tbody id="reportBody">
                        <!-- Report data will be populated here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Error Message -->
        <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
    </div>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // AJAX request on form submission
        $(document).ready(function() {
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                var cnic = $('#cnic').val();
                $.ajax({
                    type: 'POST',
                    url: 'search_report.php',
                    data: {
                        cnic: cnic
                    },
                    success: function(response) {
                        // Parse JSON response
                        var reportData = JSON.parse(response);
                        // Check if report data contains error message
                        if ('error' in reportData) {
                            // Show error message if present
                            $('#errorMessage').text(reportData.error);
                            $('#errorMessage').show();
                            // Hide the report table
                            $('#reportTable').hide();
                        } else {
                            // Build the HTML for report table
                            var tableHtml = '<tr>';
                            tableHtml += '<td>' + reportData.id + '</td>';
                            tableHtml += '<td>' + reportData.patient_name + '</td>';
                            tableHtml += '<td><a href="' + reportData.file_path + '" class="btn btn-primary" download>Download</a></td>';
                            tableHtml += '</tr>';
                            // Append the HTML to report table body
                            $('#reportBody').html(tableHtml);
                            // Show the report table
                            $('#reportTable').show();
                            // Hide the error message if it was previously shown
                            $('#errorMessage').hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
</body>

</html>