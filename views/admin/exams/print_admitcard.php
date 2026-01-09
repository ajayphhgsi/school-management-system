<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admit Cards - <?php echo htmlspecialchars($exam['exam_name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .admit-card { border: 2px solid #000; padding: 20px; margin: 10px; width: <?php echo (100/$cardsPerPage - 2); ?>%; float: left; box-sizing: border-box; page-break-inside: avoid; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .school-name { font-size: 18px; font-weight: bold; }
        .exam-title { font-size: 16px; margin: 10px 0; }
        .student-info { margin: 10px 0; }
        .subject-schedule { margin: 15px 0; }
        .signatures { margin-top: 30px; clear: both; }
        .signature-box { border-top: 1px solid #000; width: 30%; float: left; text-align: center; padding-top: 30px; margin-right: 3%; min-height: 60px; }
        .signature-box:last-child { margin-right: 0; }
        .photo { float: right; width: 80px; height: 100px; border: 1px solid #000; margin-left: 20px; }
        @media print { body { margin: 0; } .admit-card { margin: 5px; } }
    </style>
</head>
<body>
    <?php foreach ($students as $index => $student): ?>
        <?php if ($index % $cardsPerPage === 0 && $index > 0): ?>
            <div style="page-break-before: always;"></div>
        <?php endif; ?>

        <div class="admit-card">
            <div class="header">
                <div class="school-name"><?php echo htmlspecialchars($schoolName); ?></div>
                <div><?php echo htmlspecialchars($schoolAddress); ?></div>
                <div class="exam-title">Admit Card - <?php echo htmlspecialchars($exam['exam_name']); ?></div>
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

                <div class="subject-schedule">
                    <strong>Subject Schedule:</strong><br>
                    <?php if (!empty($examSubjects)): ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #000; padding: 5px; text-align: left;">Subject</th>
                                    <th style="border: 1px solid #000; padding: 5px; text-align: left;">Date</th>
                                    <th style="border: 1px solid #000; padding: 5px; text-align: left;">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Filter subjects for this student's class
                                $studentSubjects = array_filter($examSubjects, function($subject) use ($student) {
                                    return $subject['class_id'] == $student['class_id'];
                                });
                                foreach ($studentSubjects as $subject):
                                ?>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 5px;"><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                        <td style="border: 1px solid #000; padding: 5px;"><?php echo date('M d, Y', strtotime($subject['exam_date'])); ?></td>
                                        <td style="border: 1px solid #000; padding: 5px;"><?php echo date('H:i', strtotime($subject['start_time'])) . ' - ' . date('H:i', strtotime($subject['end_time'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <small>Please check the exam schedule for detailed timings</small>
                    <?php endif; ?>
                </div>

                <?php if ($includeSignatures): ?>
                    <div class="signatures">
                        <div class="signature-box">
                            <small>Principal</small>
                        </div>
                        <div class="signature-box">
                            <small>Exam Controller</small>
                        </div>
                        <div class="signature-box">
                            <small>School Seal</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>