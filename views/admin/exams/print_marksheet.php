<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Marksheets - <?php echo htmlspecialchars($exam['exam_name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .marksheet { border: 2px solid #000; padding: 20px; margin: 10px; width: <?php echo (100/$marksheetsPerPage - 2); ?>%; float: left; box-sizing: border-box; page-break-inside: avoid; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .school-name { font-size: 18px; font-weight: bold; }
        .exam-title { font-size: 16px; font-weight: bold; margin: 10px 0; }
        .student-info { margin: 10px 0; }
        .marks-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .marks-table th, .marks-table td { border: 1px solid #000; padding: 8px; text-align: center; }
        .marks-table th { background-color: #f0f0f0; font-weight: bold; }
        .signatures { margin-top: 30px; clear: both; }
        .signature-box { width: 30%; float: left; text-align: center; border-top: 1px solid #000; padding-top: 20px; min-height: 60px; }
        .signature-box:last-child { margin-right: 0; }
        .photo { float: right; width: 60px; height: 80px; border: 1px solid #000; margin-left: 20px; }
        .grade-badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 0.8rem; font-weight: bold; }
        .grade-A { background-color: #d4edda; color: #155724; }
        .grade-B { background-color: #fff3cd; color: #856404; }
        .grade-C { background-color: #ffeaa7; color: #d68910; }
        .grade-F { background-color: #f8d7da; color: #721c24; }
        @media print { body { margin: 10px; } .marksheet { margin: 5px; } }
    </style>
</head>
<body>
<?php foreach ($students as $index => $student): ?>
    <?php if ($index % $marksheetsPerPage === 0 && $index > 0): ?>
        <div style="page-break-before: always;"></div>
    <?php endif; ?>

    <div class="marksheet">
        <div class="header">
            <div class="school-name"><?php echo htmlspecialchars($schoolName); ?></div>
            <div><?php echo htmlspecialchars($schoolAddress); ?></div>
            <div class="exam-title">MARKSHEET - <?php echo htmlspecialchars($exam['exam_name']); ?></div>
        </div>

        <div style="overflow: hidden;">
        <?php if ($includePhotos && !empty($student['photo'])): ?>
            <img src="<?php echo BASE_PATH; ?>uploads/<?php echo $student['photo']; ?>" class="photo" alt="Photo">
        <?php endif; ?>

        <div class="student-info">
            <strong>Student Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?><br>
            <strong>Scholar Number:</strong> <?php echo htmlspecialchars($student['scholar_number']); ?><br>
            <strong>Class:</strong> <?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?><br>
            <strong>Roll Number:</strong> <?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?><br>
            <strong>Exam Date:</strong> <?php echo date('M d, Y', strtotime($exam['start_date'])); ?> - <?php echo date('M d, Y', strtotime($exam['end_date'])); ?><br>
        </div>

        <table class="marks-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Max Marks</th>
                    <th>Marks Obtained</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $totalMarks = 0;
            $maxTotalMarks = 0;
            foreach ($student['results'] as $result):
                $marks = $result['marks_obtained'];
                $maxMarks = $result['max_marks'];
                $totalMarks += $marks;
                $maxTotalMarks += $maxMarks;
                $grade = $this->calculateGrade($marks, $maxMarks);
                $gradeClass = 'grade-' . $grade;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                    <td><?php echo $maxMarks; ?></td>
                    <td><?php echo $marks; ?></td>
                    <td><span class="grade-badge <?php echo $gradeClass; ?>"><?php echo $grade; ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">Total Marks</th>
                    <th><?php echo $totalMarks; ?>/<?php echo $maxTotalMarks; ?></th>
                    <th><span class="grade-badge grade-<?php echo $this->calculateGrade($totalMarks, $maxTotalMarks); ?>"><?php echo $this->calculateGrade($totalMarks, $maxTotalMarks); ?></span></th>
                </tr>
                <tr>
                    <th colspan="2">Percentage</th>
                    <th colspan="2"><?php echo $maxTotalMarks > 0 ? round(($totalMarks / $maxTotalMarks) * 100, 2) : 0; ?>%</th>
                </tr>
            <?php if ($includeRankings && isset($student['rank'])): ?>
                <tr>
                    <th colspan="2">Class Rank</th>
                    <th colspan="2"><?php echo $student['rank']; ?></th>
                </tr>
            <?php endif; ?>
            </tfoot>
        </table>
        </div>

        <div class="signatures">
            <div class="signature-box">
                <small>Class Teacher</small>
            </div>
            <div class="signature-box">
                <small>Exam Controller</small>
            </div>
            <div class="signature-box">
                <small>Principal</small>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</body>
</html>