<?php
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Application - <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background: white;
            color: #000;
        }

        .application-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .application-border {
            border: 3px solid #1a252f;
            padding: 18mm;
            position: relative;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .application-border::before {
            content: '';
            position: absolute;
            top: 6mm;
            left: 6mm;
            right: 6mm;
            bottom: 6mm;
            border: 2px solid #34495e;
            pointer-events: none;
        }

        .application-border::after {
            content: '';
            position: absolute;
            top: 10mm;
            left: 10mm;
            right: 10mm;
            bottom: 10mm;
            border: 1px solid #7f8c8d;
            pointer-events: none;
        }

        .header-section {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            border-bottom: 3px solid #1a252f;
            padding-bottom: 12px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            padding: 15px;
            margin: -15px -15px 25px -15px;
        }

        .school-logo {
            max-height: 65px;
            margin-bottom: 12px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .school-name {
            font-size: 26px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #1a252f;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .school-address {
            font-size: 11px;
            margin: 5px 0;
            color: #34495e;
            line-height: 1.4;
            font-weight: 500;
        }

        .application-title {
            font-size: 20px;
            font-weight: bold;
            margin: 18px 0 8px 0;
            color: #c0392b;
            letter-spacing: 1px;
            border: 2px solid #c0392b;
            padding: 8px 25px;
            display: inline-block;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            text-transform: uppercase;
        }

        .form-number {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 12px;
            font-weight: bold;
            color: #1a252f;
            background: #ecf0f1;
            padding: 6px 12px;
            border-radius: 4px;
            border: 2px solid #34495e;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #1a252f;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background: #3498db;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 4px;
            overflow: hidden;
        }

        .info-table td {
            border: 1px solid #bdc3c7;
            padding: 8px 10px;
            vertical-align: top;
        }

        .info-table .label {
            font-weight: bold;
            width: 32%;
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-right: 2px solid #bdc3c7;
        }

        .info-table .value {
            width: 68%;
            background: #ffffff;
            color: #2c3e50;
            font-size: 11px;
            font-weight: 500;
        }

        .photo-section {
            float: right;
            width: 85px;
            height: 105px;
            border: 3px solid #2c3e50;
            margin-left: 18px;
            margin-bottom: 12px;
            text-align: center;
            background: #f8f9fa;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            position: relative;
        }

        .photo-section::before {
            content: 'PHOTO';
            position: absolute;
            bottom: 5px;
            left: 0;
            right: 0;
            font-size: 8px;
            font-weight: bold;
            color: #666;
            text-align: center;
        }

        .photo-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .declaration {
            margin: 25px 0;
            padding: 15px;
            background: linear-gradient(135deg, #ecf0f1 0%, #f8f9fa 100%);
            border-left: 5px solid #3498db;
            border-radius: 0 4px 4px 0;
            font-style: italic;
            font-size: 11px;
            line-height: 1.5;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .declaration strong {
            color: #2c3e50;
            font-weight: bold;
        }

        .signatures-section {
            margin-top: 35px;
            display: table;
            width: 100%;
            border-top: 2px solid #2c3e50;
            padding-top: 20px;
            position: relative;
        }

        .signatures-section::before {
            content: 'AUTHORIZATION';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 15px;
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-line {
            border-top: 2px solid #2c3e50;
            margin-top: 35px;
            padding-top: 8px;
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .signature-title {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .print-date {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            font-style: italic;
        }

        .application-number {
            background: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
            margin-top: 5px;
        }

        @media print {
            body {
                font-size: 10px;
            }

            .application-border {
                border: 2px solid #000 !important;
                box-shadow: none !important;
            }

            .info-table .label {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .header-section {
                background: white !important;
            }
        }
    </style>
</head>
<body>
    <div class="application-container">
        <div class="application-border">
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

                <div class="application-title">ADMISSION APPLICATION FORM</div>
            </div>

            <!-- Student Photo -->
            <div style="overflow: hidden; margin-bottom: 15px;">
                <div class="photo-section">
                    <?php if ($student['photo']): ?>
                        <img src="/uploads/<?php echo htmlspecialchars($student['photo']); ?>" alt="Student Photo">
                    <?php else: ?>
                        <div style="padding-top: 30px; color: #666; font-size: 10px;">Photo</div>
                    <?php endif; ?>
                </div>

                <!-- Basic Information -->
                <div class="section-title">Basic Information</div>
                <table class="info-table">
                    <tr>
                        <td class="label">Application No:</td>
                        <td class="value"><?php echo htmlspecialchars($student['admission_number'] ?: 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Scholar Number:</td>
                        <td class="value"><?php echo htmlspecialchars($student['scholar_number']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Admission Date:</td>
                        <td class="value"><?php echo $student['admission_date'] ? date('d M Y', strtotime($student['admission_date'])) : 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <td class="label">Applied For Class:</td>
                        <td class="value"><?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?></td>
                    </tr>
                </table>
            </div>

            <!-- Personal Information -->
            <div class="section-title">Personal Information</div>
            <table class="info-table">
                <tr>
                    <td class="label">Full Name:</td>
                    <td class="value"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']); ?></td>
                </tr>
                <tr>
                    <td class="label">Date of Birth:</td>
                    <td class="value"><?php echo $student['date_of_birth'] ? date('d M Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></td>
                </tr>
                <tr>
                    <td class="label">Gender:</td>
                    <td class="value"><?php echo ucfirst($student['gender'] ?: ''); ?></td>
                </tr>
                <tr>
                    <td class="label">Nationality:</td>
                    <td class="value"><?php echo htmlspecialchars($student['nationality']); ?></td>
                </tr>
                <tr>
                    <td class="label">Religion:</td>
                    <td class="value"><?php echo htmlspecialchars($student['religion'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Caste/Category:</td>
                    <td class="value"><?php echo htmlspecialchars($student['caste_category'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Blood Group:</td>
                    <td class="value"><?php echo htmlspecialchars($student['blood_group'] ?: 'N/A'); ?></td>
                </tr>
            </table>

            <!-- Contact Information -->
            <div class="section-title">Contact Information</div>
            <table class="info-table">
                <tr>
                    <td class="label">Mobile Number:</td>
                    <td class="value"><?php echo htmlspecialchars($student['mobile']); ?></td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td class="value"><?php echo htmlspecialchars($student['email'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Village:</td>
                    <td class="value"><?php echo htmlspecialchars($student['village'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Address:</td>
                    <td class="value"><?php echo nl2br(htmlspecialchars($student['address'] ?: 'N/A')); ?></td>
                </tr>
                <tr>
                    <td class="label">Permanent Address:</td>
                    <td class="value"><?php echo nl2br(htmlspecialchars($student['permanent_address'] ?: 'N/A')); ?></td>
                </tr>
            </table>

            <!-- Family Information -->
            <div class="section-title">Family Information</div>
            <table class="info-table">
                <tr>
                    <td class="label">Father's Name:</td>
                    <td class="value"><?php echo htmlspecialchars($student['father_name'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Mother's Name:</td>
                    <td class="value"><?php echo htmlspecialchars($student['mother_name'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Guardian's Name:</td>
                    <td class="value"><?php echo htmlspecialchars($student['guardian_name'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Guardian Contact:</td>
                    <td class="value"><?php echo htmlspecialchars($student['guardian_contact'] ?: 'N/A'); ?></td>
                </tr>
            </table>

            <!-- Academic Information -->
            <div class="section-title">Academic Information</div>
            <table class="info-table">
                <tr>
                    <td class="label">Previous School:</td>
                    <td class="value"><?php echo htmlspecialchars($student['previous_school'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Aadhar Number:</td>
                    <td class="value"><?php echo htmlspecialchars($student['aadhar_number'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Samagra Number:</td>
                    <td class="value"><?php echo htmlspecialchars($student['samagra_number'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Aapaar ID:</td>
                    <td class="value"><?php echo htmlspecialchars($student['apaar_id'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">PAN Number:</td>
                    <td class="value"><?php echo htmlspecialchars($student['pan_number'] ?: 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Medical Conditions:</td>
                    <td class="value"><?php echo nl2br(htmlspecialchars($student['medical_conditions'] ?: 'None')); ?></td>
                </tr>
            </table>

            <!-- Declaration -->
            <div class="declaration">
                <strong>Declaration:</strong> I hereby declare that the information provided above is true and correct to the best of my knowledge. I understand that providing false information may result in cancellation of admission.
            </div>

            <!-- Signatures Section -->
            <div class="signatures-section">
                <div class="signature-box">
                    <div class="signature-line">Parent/Guardian Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Applicant Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Principal/Admission Officer</div>
                </div>
            </div>

            <!-- Print Date -->
            <div class="print-date">
                <small>Application printed on: <?php echo date('d M Y \a\t H:i:s'); ?></small>
            </div>
        </div>
    </div>
</body>
</html>