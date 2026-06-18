<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_check.php';
$db = getDB();

$visit_no  = $_GET['visit_no'] ?? '';
$project   = $_GET['project'] ?? '';
$contractor = $_GET['contractor'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to'] ?? '';
$saved     = $_GET['saved'] ?? '';
$pdf_id    = $_GET['pdf_id'] ?? '';

$sql = "SELECT * FROM project_visits WHERE 1=1";
$params = [];

if ($visit_no !== '') {
    $sql .= " AND visit_no LIKE :visit_no";
    $params[':visit_no'] = "%$visit_no%";
}
if ($project !== '') {
    $sql .= " AND project_name_code LIKE :project";
    $params[':project'] = "%$project%";
}
if ($contractor !== '') {
    $sql .= " AND contractor_company LIKE :contractor";
    $params[':contractor'] = "%$contractor%";
}
if ($date_from !== '') {
    $sql .= " AND visit_date >= :date_from";
    $params[':date_from'] = $date_from;
}
if ($date_to !== '') {
    $sql .= " AND visit_date <= :date_to";
    $params[':date_to'] = $date_to;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Visit Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="assets/style.css?v=2" rel="stylesheet">
</head>
<body>
    <?php if ($saved && $pdf_id): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-0 text-center mb-0" role="alert">
        <strong>Report saved!</strong> <a href="generate_pdf.php?id=<?= $pdf_id ?>" target="_blank" class="alert-link">Open PDF</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <script>
        window.open('generate_pdf.php?id=<?= $pdf_id ?>', '_blank');
    </script>
    <?php endif; ?>
    <div class="header-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h5>دعوتِ اسلامی</h5>
                <small>Dawat-e-Islami — Project Visit Reports</small>
            </div>
            <div>
                <a href="change_password.php" class="text-white text-decoration-none me-3" title="Change Password"><i class="bi bi-key"></i></a>
                <a href="logout.php" class="text-white text-decoration-none" title="Logout"><i class="bi bi-box-arrow-right fs-5"></i></a>
            </div>
        </div>
    </div>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Visit Reports</h4>
            <a href="form.php" class="btn btn-primary">+ New Report</a>
        </div>

        <div class="card shadow mb-3">
            <div class="card-body">
                <form method="GET" action="index.php">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Visit No.</label>
                            <input type="text" name="visit_no" class="form-control" value="<?= htmlspecialchars($visit_no) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Project Name</label>
                            <input type="text" name="project" class="form-control" value="<?= htmlspecialchars($project) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contractor</label>
                            <input type="text" name="contractor" class="form-control" value="<?= htmlspecialchars($contractor) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
                        </div>
                        <div class="col-md-9 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-outline-primary">Filter</button>
                            <a href="index.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Submitted Reports (<?= count($records) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($records)): ?>
                    <p class="text-muted text-center mb-0">No reports found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Visit No.</th>
                                    <th>Date</th>
                                    <th>Project Name</th>
                                    <th>Contractor</th>
                                    <th>Tehsil / Town</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $i => $r): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($r['visit_no'] ?: '') ?></td>
                                    <td><?= $r['visit_date'] ? htmlspecialchars(date('d-m-Y', strtotime($r['visit_date']))) : '' ?></td>
                                    <td><?= htmlspecialchars($r['project_name_code'] ?: '') ?></td>
                                    <td><?= htmlspecialchars($r['contractor_company'] ?: '') ?></td>
                                    <td><?= htmlspecialchars($r['tehsil'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars(date('d-m-Y h:i A', strtotime($r['created_at']))) ?></td>
                                    <td class="text-nowrap">
                                        <a href="generate_pdf.php?id=<?= $r['id'] ?>" class="text-success me-2" title="View PDF" target="_blank"><i class="bi bi-file-pdf fs-5"></i></a>
                                        <a href="form.php?id=<?= $r['id'] ?>" class="text-primary me-2" title="Edit"><i class="bi bi-pencil fs-5"></i></a>
                                        <a href="delete.php?id=<?= $r['id'] ?>" class="text-danger" title="Delete" onclick="return confirm('Delete this report?')"><i class="bi bi-trash fs-5"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
