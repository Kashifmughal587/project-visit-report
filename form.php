<?php
require_once __DIR__ . '/db.php';
$db = getDB();

$visit_no = date('Ymd') . '-' . str_pad(($db->query("SELECT COUNT(*) FROM project_visits")->fetchColumn() + 1), 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Visit Report - Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3 class="mb-0">Project Visit Report Form</h3>
            </div>
            <div class="card-body">
                <form action="submit.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="visit_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Visit No.</label>
                            <input type="text" name="visit_no" class="form-control" value="<?= htmlspecialchars($visit_no) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Time at Site (From)</label>
                            <input type="time" name="time_from" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Time at Site (To)</label>
                            <input type="time" name="time_to" class="form-control" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Project Name with Code</label>
                            <input type="text" name="project_name_code" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit In-Construction Stage</label>
                            <input type="text" name="unit_construction_stage" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tehsil</label>
                            <input type="text" name="tehsil" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Town / District / Division</label>
                            <input type="text" name="town_district_division" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Name of Contractor / Company</label>
                            <input type="text" name="contractor_company" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Name of Project Committee Head</label>
                            <input type="text" name="committee_head" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Previous Visit Date</label>
                            <input type="date" name="previous_visit_date" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Reason for Visit</label>
                            <textarea name="reason_for_visit" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Describe Today's Project Activities</label>
                            <textarea name="project_activities" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Activity Name, Current Status, Next Inspection Stage & Date</label>
                            <textarea name="activity_status" class="form-control" rows="3" placeholder="Activity Name | Current Status | Next Inspection Stage @ Date"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Describe Any Obstructions / Site Violations and Actions (if any)</label>
                            <textarea name="obstructions_violations" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Manpower Detail and Instructions (if any)</label>
                            <textarea name="manpower_details" class="form-control" rows="3"></textarea>
                        </div>

                        <h5 class="mt-3 border-bottom pb-2">Witness Details</h5>

                        <div class="col-md-4">
                            <label class="form-label">Witness Name</label>
                            <input type="text" name="witness_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Responsibility</label>
                            <input type="text" name="witness_responsibility" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Signature</label>
                            <input type="text" name="witness_signature" class="form-control" placeholder="Full Name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Civil / Site Supervisor's Signature</label>
                            <input type="text" name="supervisor_signature" class="form-control" placeholder="Full Name">
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">Generate Report</button>
                        <a href="index.php" class="btn btn-outline-secondary">Back to History</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
