<?php
// Get certificate data
$certificate = $certificate ?? null;
if (!$certificate) {
    die('Certificate data not found');
}

// Get student data
$student = $student ?? null;
if (!$student) {
    die('Student data not found');
}

// Get school settings
$schoolName = $school_name ?? 'School Management System';
$schoolAddress = $school_address ?? '';
$schoolPhone = $school_phone ?? '';
$schoolEmail = $school_email ?? '';
$schoolLogo = $school_logo ?? '';

// Get academic record
$academicRecord = $academic_record ?? 'No academic records found';

// Certificate details
$certificateNumber = $certificate['certificate_number'] ?? '';
$issueDate = $certificate['issue_date'] ? date('d/m/Y', strtotime($certificate['issue_date'])) : '';
$admissionDate = $student['admission_date'] ? date('d/m/Y', strtotime($student['admission_date'])) : 'N/A';
$reasonText = $certificate['transfer_reason'] ?? '';
$conduct = ucfirst($certificate['conduct'] ?? 'good');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Certificate - <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            background: white;
            color: #000;
        }

        .certificate-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .certificate-border {
            border: 2px solid #2c3e50;
            padding: 12mm;
            position: relative;
            background: white;
        }

        .certificate-border::before {
            content: '';
            position: absolute;
            top: 4mm;
            left: 4mm;
            right: 4mm;
            bottom: 4mm;
            border: 1px solid #34495e;
            pointer-events: none;
        }

        .header-section {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
            border-bottom: 1px solid #2c3e50;
            padding-bottom: 8px;
        }

        .school-logo {
            max-height: 50px;
            margin-bottom: 8px;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            margin: 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #2c3e50;
        }

        .school-address {
            font-size: 10px;
            margin: 3px 0;
            color: #34495e;
            line-height: 1.2;
        }

        .certificate-title {
            font-size: 16px;
            font-weight: bold;
            margin: 12px 0 8px 0;
            text-decoration: underline;
            color: #e74c3c;
            letter-spacing: 0.5px;
        }

        .certificate-number {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
            background: #f8f9fa;
            padding: 2px 6px;
            border: 1px solid #dee2e6;
        }

        .content-section {
            margin: 15px 0;
            position: relative;
            z-index: 1;
        }

        .certification-text {
            margin-bottom: 15px;
            text-align: justify;
            font-size: 11px;
            line-height: 1.4;
            color: #2c3e50;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            border: 1px solid #dee2e6;
        }

        .student-table td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            vertical-align: top;
        }

        .student-table .label {
            font-weight: bold;
            width: 35%;
            background-color: #f8f9fa;
            color: #2c3e50;
            font-size: 10px;
            text-transform: uppercase;
        }

        .student-table .value {
            width: 65%;
            background: #ffffff;
            color: #2c3e50;
            font-size: 10px;
        }

        .academic-record {
            margin: 15px 0;
            background: #f8f9fa;
            padding: 8px;
            border-left: 3px solid #3498db;
        }

        .academic-title {
            font-weight: bold;
            margin-bottom: 6px;
            color: #2c3e50;
            font-size: 11px;
            text-transform: uppercase;
        }

        .conduct-section,
        .reason-section,
        .remarks-section {
            margin: 10px 0;
            padding: 6px 8px;
            background: #f8f9fa;
            border-left: 2px solid #3498db;
        }

        .conduct-section strong,
        .reason-section strong,
        .remarks-section strong {
            color: #2c3e50;
            font-weight: bold;
            font-size: 10px;
        }

        .signatures-section {
            margin-top: 30px;
            display: table;
            width: 100%;
            border-top: 1px solid #2c3e50;
            padding-top: 15px;
        }

        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding: 0 5px;
        }

        .signature-line {
            border-top: 1px solid #2c3e50;
            margin-top: 25px;
            padding-top: 5px;
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }

        .print-date {
            margin-top: 15px;
            text-align: right;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        @media print {

            body {
                font-size: 10px;
            }

            .certificate-border {
                border: 1px solid #000;
                background: white !important;
            }

            .student-table .label {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }

        @media screen {
            .no-screen {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-border">
            <!-- Certificate Number -->
            <div class="certificate-number">Certificate No: <?php echo htmlspecialchars($certificateNumber); ?></div>

            <!-- Header Section -->
            <div class="header-section">
                <?php if (!empty($schoolLogo)): ?>
                    <img src="/uploads/<?php echo htmlspecialchars($schoolLogo); ?>" alt="School Logo" class="school-logo">
                <?php endif; ?>

                <div class="school-name"><?php echo htmlspecialchars($schoolName); ?></div>

                <?php if (!empty($schoolAddress)): ?>
                    <div class="school-address"><?php echo htmlspecialchars($schoolAddress); ?></div>
                <?php endif; ?>

                <?php if (!empty($schoolPhone) || !empty($schoolEmail)): ?>
                    <div class="school-address">
                        <?php if (!empty($schoolPhone)): ?>Phone: <?php echo htmlspecialchars($schoolPhone); ?><?php endif; ?>
                        <?php if (!empty($schoolPhone) && !empty($schoolEmail)): ?> | <?php endif; ?>
                        <?php if (!empty($schoolEmail)): ?>Email: <?php echo htmlspecialchars($schoolEmail); ?><?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="certificate-title">TRANSFER CERTIFICATE</div>
            </div>

            <!-- Content Section -->
            <div class="content-section">
                <div class="certification-text">
                    This is to certify that the following student was admitted to this school and that he/she has left the school on the date mentioned below:
                </div>

                <!-- Student Information Table -->
                <table class="student-table">
                    <tr>
                        <td class="label">Name of the Student:</td>
                        <td class="value"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Father's Name:</td>
                        <td class="value"><?php echo htmlspecialchars($student['father_name'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Mother's Name:</td>
                        <td class="value"><?php echo htmlspecialchars($student['mother_name'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Date of Birth:</td>
                        <td class="value"><?php echo htmlspecialchars($student['date_of_birth'] ? date('d/m/Y', strtotime($student['date_of_birth'])) : 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Scholar Number:</td>
                        <td class="value"><?php echo htmlspecialchars($student['scholar_number']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Admission Date:</td>
                        <td class="value"><?php echo htmlspecialchars($admissionDate); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Class:</td>
                        <td class="value"><?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Nationality:</td>
                        <td class="value"><?php echo htmlspecialchars($student['nationality'] ?? 'Indian'); ?></td>
                    </tr>
                </table>

                <!-- Academic Record -->
                <div class="academic-record">
                    <div class="academic-title">ACADEMIC RECORD:</div>
                    <div><?php echo nl2br(htmlspecialchars($academicRecord)); ?></div>
                </div>

                <!-- Conduct -->
                <div class="conduct-section">
                    <strong>Conduct:</strong> <?php echo htmlspecialchars($conduct); ?>
                </div>

                <!-- Reason for Leaving -->
                <div class="reason-section">
                    <strong>Reason for Leaving:</strong> <?php echo htmlspecialchars($reasonText); ?>
                </div>

                <!-- Remarks -->
                <?php if (!empty($certificate['remarks'])): ?>
                    <div class="remarks-section">
                        <strong>Remarks:</strong> <?php echo htmlspecialchars($certificate['remarks']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Signatures Section -->
            <div class="signatures-section">
                <div class="signature-box">
                    <div class="signature-line">Class Teacher</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Principal</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">School Seal</div>
                </div>
            </div>

            <!-- Print Date -->
            <div class="print-date">
                <small>Printed on: <?php echo date('d M Y \a\t H:i:s'); ?></small>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when loaded (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>