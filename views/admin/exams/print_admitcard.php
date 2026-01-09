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
            font-family: 'Arial', 'Helvetica Neue', Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.4;
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
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
            border-bottom: 1px solid #2c3e50;
            padding-bottom: 8px;
        }

        .school-logo {
            max-height: 70px;
            margin-right: 15px;
        }

        .school-info {
            flex: 1;
            text-align: center;
        }

        .school-name {
            font-size: 22px;
            font-weight: bolder;
            margin: 4px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2c3e50;
        }

        .school-address {
            font-size: 10px;
            margin: 2px 0;
            color: #34495e;
            line-height: 1.2;
            font-weight: bold;
        }

        .exam-title {
            font-size: 16px;
            font-weight: bolder;
            margin: 8px 0 4px 0;
            text-decoration: none;
            color: #e74c3c;
            letter-spacing: 1px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .exam-name-badge {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin: 8px auto 12px auto;
            color: #ffffff;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 16px;
            border-radius: 50px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            width: fit-content;
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
            color: #e74c3c;
            font-size: 14px;
            text-transform: uppercase;
            text-align: center;
        }

        .admit-card-content {
            display: flex;
            align-items: flex-start;
            margin-top: 10px;
        }

        .photo {
            flex: 0 0 110px;
            width: 110px;
            height: 130px;
            border: 3px solid #2c3e50;
            border-radius: 8px;
            margin-left: 15px;
            text-align: center;
            background: #f8f9fa;
            position: relative;
            z-index: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-placeholder {
            font-size: 8px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
        }

        .details-section {
            flex: 1;
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

        .timetable-container {
            margin: 8px 0;
        }

        .timetable-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .timetable-section {
            display: table-cell;
            width: 25%;
            vertical-align: top;
            padding: 2px;
        }

        .timetable-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #3498db;
            border-radius: 8px;
            overflow: hidden;
            font-size: 7px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .timetable-table th,
        .timetable-table td {
            border: 1px solid #dee2e6;
            padding: 4px;
            text-align: center;
        }

        .timetable-table th {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .timetable-table tbody tr:nth-child(even) {
            background-color: #ecf0f1;
        }

        .timetable-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .timetable-table tbody tr:hover {
            background-color: #d5dbdb;
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

            <div class="school-info">
                <div class="school-name"><?php echo htmlspecialchars($schoolName); ?></div>

                <?php if (!empty($schoolAddress)): ?>
                    <div class="school-address"><?php echo htmlspecialchars($schoolAddress); ?></div>
                <?php endif; ?>

                <div class="exam-title">ADMIT CARD</div>
            </div>
        </div>

        <!-- Exam Name Badge -->
        <div class="exam-name-badge"><?php echo htmlspecialchars($exam['exam_name']); ?></div>

        <!-- Student Photo and Information -->
        <div class="admit-card-content">
            <div class="details-section">
                <!-- Student Information Table -->
                <table class="info-table">
                    <tr>
                        <td class="label">Scholar No.:</td>
                        <td class="value"><?php echo htmlspecialchars($student['scholar_number']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Student Name:</td>
                        <td class="value"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Father Name:</td>
                        <td class="value"><?php echo htmlspecialchars($student['father_name'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Mother Name:</td>
                        <td class="value"><?php echo htmlspecialchars($student['mother_name'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Class & Sec:</td>
                        <td class="value"><?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Aadhar No.:</td>
                        <td class="value"><?php echo htmlspecialchars($student['aadhar_number'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Gender:</td>
                        <td class="value"><?php echo htmlspecialchars(ucfirst($student['gender'] ?? 'N/A')); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Roll No.:</td>
                        <td class="value"><?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Date of Birth:</td>
                        <td class="value"><?php echo $student['date_of_birth'] ? date('d/m/Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></td>
                    </tr>
                </table>
            </div>

            <div class="photo">
                <?php if ($includePhotos && !empty($student['photo'])): ?>
                    <img src="/uploads/<?php echo htmlspecialchars($student['photo']); ?>" alt="Student Photo">
                <?php else: ?>
                    <div class="photo-placeholder">Photo</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Academic Year Badge -->
        <?php
        $academicYearQuery = $this->db->selectOne("SELECT year_name FROM academic_years WHERE id = ?", [$exam['academic_year_id'] ?? null]);
        $academicYear = $academicYearQuery['year_name'] ?? '';
        if (!empty($academicYear)) {
        ?>
        <div class="exam-name-badge">Academic Session: <?php echo htmlspecialchars($academicYear); ?></div>
        <?php } ?>

        <!-- Subject Schedule / Timetable and Instructions -->
        <div class="subject-schedule">
            <div class="subject-title">Examination Timetable:</div>
            <?php if (!empty($examSubjects)): ?>
                <div class="timetable-container">
                    <table class="timetable-table">
                        <thead>
                            <tr>
                                <th style="width: 35%; font-size: 8px; padding: 4px;">Subject</th>
                                <th style="width: 20%; font-size: 8px; padding: 4px;">Day</th>
                                <th style="width: 25%; font-size: 8px; padding: 4px;">Date</th>
                                <th style="width: 20%; font-size: 8px; padding: 4px;">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($examSubjects as $subject): ?>
                                <tr>
                                    <td style="font-size: 8px; padding: 4px; text-align: center;"><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                    <td style="font-size: 8px; padding: 4px; text-align: center;"><?php echo $subject['exam_date'] ? date('l', strtotime($subject['exam_date'])) : 'N/A'; ?></td>
                                    <td style="font-size: 8px; padding: 4px; text-align: center;"><?php echo $subject['exam_date'] ? date('d/m/Y', strtotime($subject['exam_date'])) : 'N/A'; ?></td>
                                    <td style="font-size: 8px; padding: 4px; text-align: center;"><?php echo ($subject['start_time'] && $subject['end_time']) ? date('H:i', strtotime($subject['start_time'])) . ' - ' . date('H:i', strtotime($subject['end_time'])) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="font-size: 9px; text-align: center; padding: 10px;">No subjects scheduled for this exam.</div>
            <?php endif; ?>

            <div class="subject-title" style="margin-top: 12px;">Important Instructions:</div>
            <div style="font-size: 8px; line-height: 1.4;">
                <?php if (!empty($instructions)): ?>
                    <?php foreach ($instructions as $index => $instruction): ?>
                        <?php echo ($index + 1) . '. ' . htmlspecialchars($instruction['instruction_text']); ?><br>
                    <?php endforeach; ?>
                <?php else: ?>
                    1. Bring this admit card to the examination hall.<br>
                    2. Arrive at least 30 minutes before the exam starts.<br>
                    3. Carry valid ID proof along with this card.
                <?php endif; ?>
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