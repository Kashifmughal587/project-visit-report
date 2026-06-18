<?php
require_once __DIR__ . '/db.php';
$db = getDB();

$edit_id = $_GET['id'] ?? null;
$r = null;

if ($edit_id) {
    $stmt = $db->prepare("SELECT * FROM project_visits WHERE id = ?");
    $stmt->execute([$edit_id]);
    $r = $stmt->fetch();
    if (!$r) {
        die("Record not found.");
    }
}

$visit_no = $r['visit_no'] ?? date('Ymd') . '-' . str_pad(($db->query("SELECT COUNT(*) FROM project_visits")->fetchColumn() + 1), 3, '0', STR_PAD_LEFT);

$witnesses = [];
if ($r && $r['witnesses']) {
    $decoded = json_decode($r['witnesses'], true);
    $witnesses = is_array($decoded) ? $decoded : [];
}
if (empty($witnesses)) {
    $witnesses[] = ['name' => '', 'responsibility' => '', 'signature' => ''];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Visit Report - <?= $edit_id ? 'Edit' : 'New' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3 class="mb-0"><?= $edit_id ? 'Edit' : 'New' ?> Project Visit Report</h3>
            </div>
            <div class="card-body">
                <form action="submit.php" method="POST">
                    <?php if ($edit_id): ?>
                        <input type="hidden" name="id" value="<?= $edit_id ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="visit_date" class="form-control" value="<?= htmlspecialchars($r['visit_date'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Visit No.</label>
                            <input type="text" name="visit_no" class="form-control" value="<?= htmlspecialchars($visit_no) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Time at Site (From)</label>
                            <input type="time" name="time_from" class="form-control" value="<?= htmlspecialchars($r['time_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Time at Site (To)</label>
                            <input type="time" name="time_to" class="form-control" value="<?= htmlspecialchars($r['time_to'] ?? '') ?>">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Project Name with Code</label>
                            <input type="text" name="project_name_code" class="form-control" value="<?= htmlspecialchars($r['project_name_code'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit In-Construction Stage</label>
                            <input type="text" name="unit_construction_stage" class="form-control" value="<?= htmlspecialchars($r['unit_construction_stage'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tehsil / Town</label>
                            <input type="text" name="tehsil" class="form-control" value="<?= htmlspecialchars($r['tehsil'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">District / Division</label>
                            <input type="text" name="town_district_division" class="form-control" value="<?= htmlspecialchars($r['town_district_division'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Name of Contractor / Company</label>
                            <input type="text" name="contractor_company" class="form-control" value="<?= htmlspecialchars($r['contractor_company'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Name of Project Committee Head</label>
                            <input type="text" name="committee_head" class="form-control" value="<?= htmlspecialchars($r['committee_head'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Previous Visit Date</label>
                            <input type="date" name="previous_visit_date" class="form-control" value="<?= htmlspecialchars($r['previous_visit_date'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Reason for Visit</label>
                            <textarea name="reason_for_visit" class="form-control" rows="3"><?= htmlspecialchars($r['reason_for_visit'] ?? '') ?></textarea>
                        </div>

                        <h5 class="mt-3 border-bottom pb-2">Describe Today's Project Activities (If Any)</h5>

                        <div class="col-12">
                            <label class="form-label">Activity Name, Current Status, Next Inspection Stage @ Date</label>
                            <textarea name="activity_status" class="form-control" rows="3" placeholder="Activity Name | Current Status | Next Inspection Stage @ Date"><?= htmlspecialchars($r['activity_status'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Describe Any Obstructions / Site Violations and Actions (If Any)</label>
                            <textarea name="obstructions_violations" class="form-control" rows="3"><?= htmlspecialchars($r['obstructions_violations'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Manpower Detail and Instructions (If Any)</label>
                            <textarea name="manpower_details" class="form-control" rows="3"><?= htmlspecialchars($r['manpower_details'] ?? '') ?></textarea>
                        </div>

                        <h5 class="mt-3 border-bottom pb-2">Witness: Name of Project Committee / Project Responsible Present during Visit:</h5>

                        <div id="witnesses-container">
                            <?php foreach ($witnesses as $wi => $w): ?>
                            <div class="witness-row row g-2 mb-2 border rounded p-2 pb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Witness Name</label>
                                    <input type="text" name="witnesses[<?= $wi ?>][name]" class="form-control" value="<?= htmlspecialchars($w['name'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Responsibility</label>
                                    <input type="text" name="witnesses[<?= $wi ?>][responsibility]" class="form-control" value="<?= htmlspecialchars($w['responsibility'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Signature</label>
                                    <input type="text" name="witnesses[<?= $wi ?>][signature]" class="form-control" value="<?= htmlspecialchars($w['signature'] ?? '') ?>" placeholder="Full Name">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-witness" <?= count($witnesses) <= 1 ? 'disabled' : '' ?>>-</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success mb-3" id="add-witness">+ Add Witness</button>

                        <div class="col-md-6">
                            <label class="form-label">Civil / Site Supervisor's Signature</label>
                            <input type="text" name="supervisor_signature" class="form-control" value="<?= htmlspecialchars($r['supervisor_signature'] ?? '') ?>" placeholder="Full Name">
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4"><?= $edit_id ? 'Update Report' : 'Generate Report' ?></button>
                        <a href="index.php" class="btn btn-outline-secondary">Back to History</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        let witnessIndex = <?= count($witnesses) ?>;
        const container = document.getElementById('witnesses-container');

        document.getElementById('add-witness').addEventListener('click', function() {
            const row = document.querySelector('.witness-row').cloneNode(true);
            row.querySelectorAll('input').forEach(input => {
                input.name = input.name.replace(/\[\d+\]/, '[' + witnessIndex + ']');
                input.value = '';
            });
            row.querySelector('.remove-witness').disabled = false;
            container.appendChild(row);
            witnessIndex++;
            updateRemoveButtons();
        });

        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-witness')) {
                if (container.querySelectorAll('.witness-row').length > 1) {
                    e.target.closest('.witness-row').remove();
                    updateRemoveButtons();
                }
            }
        });

        function updateRemoveButtons() {
            const rows = container.querySelectorAll('.witness-row');
            rows.forEach((row, i) => {
                row.querySelector('.remove-witness').disabled = rows.length <= 1;
            });
        }
    </script>
</body>
</html>
