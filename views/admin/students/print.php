<?php
$page_title = 'Print Student List';
ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List - <?php echo $school_name; ?></title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 14px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .school-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #007bff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .school-address {
            font-size: 14px;
            margin-bottom: 5px;
            color: #6c757d;
        }
        .report-title {
            font-size: 18px;
            font-weight: 600;
            margin: 10px 0 0 0;
            color: #495057;
            border-top: 1px solid #dee2e3;
            padding-top: 10px;
        }
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 6px;
            overflow: hidden;
        }
        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        .table th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .table tbody tr:hover {
            background-color: #e3f2fd;
        }
        .photo-cell {
            width: 60px;
            text-align: center;
        }
        .photo-cell img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        .no-print {
            display: none;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .header {
                margin-bottom: 20px;
            }
            .table {
                font-size: 12px;
            }
            .table th, .table td {
                padding: 6px;
            }
            @page {
                margin: 1in;
                @bottom-center {
                    content: "Page " counter(page) " of " counter(pages);
                }
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 15px; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h5 style="margin-bottom: 15px; color: white; font-weight: 600;">Print Options</h5>
        <button onclick="window.print()" class="btn btn-light btn-lg me-3" style="border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
            <i class="fas fa-print me-2"></i>Print Student List
        </button>
        <button onclick="window.close()" class="btn btn-outline-light btn-lg" style="border: 2px solid white;">
            <i class="fas fa-times me-2"></i>Close Window
        </button>
    </div>

    <div class="header">
        <?php if ($school_logo): ?>
            <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                <img src="/uploads/<?php echo htmlspecialchars($school_logo); ?>" alt="School Logo" style="max-height: 60px; max-width: 150px; margin-right: 15px; border-radius: 4px;">
                <div>
                    <div class="school-name"><?php echo htmlspecialchars($school_name); ?></div>
                    <?php if ($school_address): ?>
                        <div class="school-address"><?php echo htmlspecialchars($school_address); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="school-name"><?php echo htmlspecialchars($school_name); ?></div>
            <?php if ($school_address): ?>
                <div class="school-address"><?php echo htmlspecialchars($school_address); ?></div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="report-title">STUDENT LIST REPORT</div>
    </div>

    <div class="report-info" style="display: flex; justify-content: space-between; background: #f8f9fa; padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="font-weight: 600; color: #495057;"><i class="fas fa-users me-2" style="color: #007bff;"></i>Total Students: <span style="color: #007bff;"><?php echo count($students); ?></span></div>
        <div style="font-weight: 600; color: #495057;"><i class="fas fa-calendar me-2" style="color: #007bff;"></i>Generated on: <span style="color: #007bff;"><?php echo date('F d, Y \a\t h:i A'); ?></span></div>
    </div>

    <?php if (!empty($students)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">S.No</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Scholar No.</th>
                    <th>Admission No.</th>
                    <th>Class</th>
                    <th>Contact</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $serial = 1; foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $serial++; ?></td>
                        <td class="photo-cell">
                            <?php if ($student['photo']): ?>
                                <img src="/uploads/<?php echo $student['photo']; ?>" alt="Photo">
                            <?php else: ?>
                                <div style="width: 40px; height: 40px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #6c757d;">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['scholar_number'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['admission_number'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['class_name'] ? $student['class_name'] . ' ' . $student['section'] : 'No Class'); ?></td>
                        <td>
                            <div><?php echo htmlspecialchars($student['mobile'] ?? ''); ?></div>
                            <?php if (!empty($student['email'])): ?>
                                <div style="font-size: 11px;"><?php echo htmlspecialchars($student['email']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $student['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <h4>No Students Found</h4>
        </div>
    <?php endif; ?>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
// Don't include layout, as this is a standalone print page
echo $content;
?>