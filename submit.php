<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$required = ['visit_date', 'visit_no', 'time_from', 'time_to', 'project_name_code',
             'unit_construction_stage', 'contractor_company', 'committee_head',
             'reason_for_visit', 'project_activities'];

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die("Error: Field '$field' is required.");
    }
}

$db = getDB();

$stmt = $db->prepare("
    INSERT INTO project_visits (
        visit_date, visit_no, time_from, time_to, project_name_code,
        tehsil, town_district_division, unit_construction_stage,
        contractor_company, committee_head, previous_visit_date,
        reason_for_visit, project_activities, activity_status,
        obstructions_violations, manpower_details,
        witness_name, witness_responsibility, witness_signature,
        supervisor_signature
    ) VALUES (
        :visit_date, :visit_no, :time_from, :time_to, :project_name_code,
        :tehsil, :town_district_division, :unit_construction_stage,
        :contractor_company, :committee_head, :previous_visit_date,
        :reason_for_visit, :project_activities, :activity_status,
        :obstructions_violations, :manpower_details,
        :witness_name, :witness_responsibility, :witness_signature,
        :supervisor_signature
    )
");

$stmt->execute([
    ':visit_date'              => $_POST['visit_date'],
    ':visit_no'                => $_POST['visit_no'],
    ':time_from'               => $_POST['time_from'],
    ':time_to'                 => $_POST['time_to'],
    ':project_name_code'       => $_POST['project_name_code'],
    ':tehsil'                  => $_POST['tehsil'] ?? null,
    ':town_district_division'  => $_POST['town_district_division'] ?? null,
    ':unit_construction_stage' => $_POST['unit_construction_stage'],
    ':contractor_company'      => $_POST['contractor_company'],
    ':committee_head'          => $_POST['committee_head'],
    ':previous_visit_date'     => $_POST['previous_visit_date'] ?: null,
    ':reason_for_visit'        => $_POST['reason_for_visit'],
    ':project_activities'      => $_POST['project_activities'],
    ':activity_status'         => $_POST['activity_status'] ?? null,
    ':obstructions_violations' => $_POST['obstructions_violations'] ?? null,
    ':manpower_details'        => $_POST['manpower_details'] ?? null,
    ':witness_name'            => $_POST['witness_name'] ?? null,
    ':witness_responsibility'  => $_POST['witness_responsibility'] ?? null,
    ':witness_signature'       => $_POST['witness_signature'] ?? null,
    ':supervisor_signature'    => $_POST['supervisor_signature'] ?? null,
]);

$id = $db->lastInsertId();
header("Location: generate_pdf.php?id=" . $id);
exit;
