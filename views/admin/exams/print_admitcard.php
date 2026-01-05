<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Cards - <?php echo htmlspecialchars($exam['exam_name']); ?></title>
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

        .admit-card {
            border: 2px solid #2c3e50;
            padding: 12mm;
            margin: 8px;
            width: calc(<?php echo (100/$cardsPerPage); ?>% - 16px);
            float: left;
            box-sizing: border-box;
            page-break-inside: avoid;
            position: relative;
            background: white;
        }

        .admit-card::before {
            content: '';
            position: absolute;
            top: 4mm;
            left: 4mm;
            right: 4mm;
            bottom: 4mm;
            border: 1px solid #34495e;
            pointer-events: none;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
            border-bottom: 1px solid #2c3e50;
            padding-bottom: 8px;
        }

        .school-logo {
            max-height: 40px;
            margin-bottom: 6px;
        }

        .school-name {
            font-size: 16px;
            font-weight: bold;
            margin: 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #2c3e50;
        }

        .school-address {
            font-size: 9px;
            margin: 2px 0;
            color: #34495e;
            line-height: 1.2;
        }

        .exam-title {
            font-size: 14px;
            font-weight: bold;
            margin: 8px 0;
            text-decoration: underline;
            color: #e74c3c;
            letter-spacing: 0.5px;
        }

        .card-number {
            position: absolute;
            top: 6px;
            right: 6px;
            font-size: 8px;
            font-weight: bold;
            color: #2c3e50;
            background: #f8f9fa;
            padding: 1px 4px;
            border: 1px solid #dee2e6;
        }

        .student-info {
            margin: 10px 0;
            position: relative;
            z-index: 1;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            border: 1px solid #dee2e6;
        }

        .info-table td {
            border: 1px solid #dee2e6;
            padding: 4px 6px;
            vertical-align: top;
        }

        .info-table .label {
            font-weight: bold;
            width: 35%;
            background-color: #f8f9fa;
            color: #2c3e50;
            font-size: 9px;
            text-transform: uppercase;
        }

        .info-table .value {
            width: 65%;
            background: #ffffff;
            color: #2c3e50;
            font-size: 9px;
        }

        .subject-schedule {
            margin: 10px 0;
            background: #f8f9fa;
            padding: 6px;
            border-left: 2px solid #3498db;
            position: relative;
            z-index: 1;
        }

        .subject-title {
            font-weight: bold;
            margin-bottom: 4px;
            color: #2c3e50;
            font-size: 10px;
            text-transform: uppercase;
        }

        .photo {
            float: right;
            width: 60px;
            height: 75px;
            border: 2px solid #2c3e50;
            margin-left: 12px;
            margin-bottom: 8px;
            text-align: center;
            background: #f8f9fa;
            position: relative;
            z-index: 1;
        }

        .photo::before {
            content: 'PHOTO';
            position: absolute;
            bottom: 3px;
            left: 0;
            right: 0;
            font-size: 6px;
            font-weight: bold;
            color: #666;
            text-align: center;
        }

        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .signatures {
            margin-top: 20px;
            clear: both;
            display: table;
            width: 100%;
            border-top: 1px solid #2c3e50;
            padding-top: 10px;
            position: relative;
            z-index: 1;
        }

        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding: 0 3px;
        }

        .signature-line {
            border-top: 1px solid #2c3e50;
            margin-top: 15px;
            padding-top: 3px;
            font-size: 9px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }

        .print-date {
            margin-top: 8px;
            text-align: right;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 3px;
            position: relative;
            z-index: 1;
        }

        @media print {
            body {
                font-size: 9px;
            }

            .admit-card {
                border: 1px solid #000;
                background: white !important;
                margin: 4px;
            }

            .info-table .label {
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
<?php foreach ($students as $index => $student): ?>
    <?php if ($index % $cardsPerPage === 0 && $index > 0): ?>
        <div style="page-break-before: always;"></div>
    <?php endif; ?>

    <div class="admit-card">
        <!-- Card Number -->
        <div class="card-number">Card No: <?php echo ($index + 1); ?></div>

        <!-- Header Section -->
        <div class="header">
            <?php if (!empty($schoolLogo)): ?>
                <img src="/uploads/<?php echo htmlspecialchars($schoolLogo); ?>" alt="School Logo" class="school-logo">
            <?php endif; ?>

            <div class="school-name"><?php echo htmlspecialchars($schoolName); ?></div>

            <?php if (!empty($schoolAddress)): ?>
                <div class="school-address"><?php echo htmlspecialchars($schoolAddress); ?></div>
            <?php endif; ?>

            <div class="exam-title">ADMIT CARD</div>
        </div>

        <!-- Student Photo and Information -->
        <div style="overflow: hidden;">
            <?php if ($includePhotos && !empty($student['photo'])): ?>
                <div class="photo">
                    <img src="/uploads/<?php echo htmlspecialchars($student['photo']); ?>" alt="Student Photo">
                </div>
            <?php endif; ?>

            <!-- Student Information Table -->
            <table class="info-table">
                <tr>
                    <td class="label">Student Name:</td>
                    <td class="value"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                </tr>
                <tr>
                    <td class="label">Scholar Number:</td>
                    <td class="value"><?php echo htmlspecialchars($student['scholar_number']); ?></td>
                </tr>
                <tr>
                    <td class="label">Roll Number:</td>
                    <td class="value"><?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Class:</td>
                    <td class="value"><?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?></td>
                </tr>
                <tr>
                    <td class="label">Exam Name:</td>
                    <td class="value"><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                </tr>
                <tr>
                    <td class="label">Exam Date:</td>
                    <td class="value"><?php echo date('d M Y', strtotime($exam['start_date'])); ?> - <?php echo date('d M Y', strtotime($exam['end_date'])); ?></td>
                </tr>
            </table>

            <!-- Subject Schedule -->
            <div class="subject-schedule">
                <div class="subject-title">Subject Schedule:</div>
                <div>Please check the exam schedule for detailed timings and subject information.</div>
            </div>

            <!-- Important Instructions -->
            <div class="subject-schedule">
                <div class="subject-title">Important Instructions:</div>
                <div style="font-size: 8px; line-height: 1.2;">
                    1. Bring this admit card to the examination hall.<br>
                    2. Arrive at least 30 minutes before the exam starts.<br>
                    3. Carry valid ID proof along with this card.
                </div>
            </div>
        </div>

        <!-- Signatures Section -->
        <?php if ($includeSignatures): ?>
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">Principal</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Exam Controller</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">School Seal</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Print Date -->
        <div class="print-date">
            <small>Generated on: <?php echo date('d M Y \a\t H:i:s'); ?></small>
        </div>
    </div>
<?php endforeach; ?>
</body>
</html>