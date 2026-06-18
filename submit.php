<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$db = getDB();
$id = $_POST['id'] ?? null;

$witnesses = isset($_POST['witnesses']) ? array_values($_POST['witnesses']) : [['name' => '', 'responsibility' => '', 'signature' => '']];

$params = [
    ':visit_date'              => $_POST['visit_date'] ?: null,
    ':visit_no'                => $_POST['visit_no'] ?: null,
    ':time_from'               => $_POST['time_from'] ?: null,
    ':time_to'                 => $_POST['time_to'] ?: null,
    ':project_name_code'       => $_POST['project_name_code'] ?: null,
    ':tehsil'                  => $_POST['tehsil'] ?: null,
    ':town_district_division'  => $_POST['town_district_division'] ?: null,
    ':unit_construction_stage' => $_POST['unit_construction_stage'] ?: null,
    ':contractor_company'      => $_POST['contractor_company'] ?: null,
    ':committee_head'          => $_POST['committee_head'] ?: null,
    ':previous_visit_date'     => $_POST['previous_visit_date'] ?: null,
    ':reason_for_visit'        => $_POST['reason_for_visit'] ?: null,
    ':activity_status'         => $_POST['activity_status'] ?: null,
    ':obstructions_violations' => $_POST['obstructions_violations'] ?: null,
    ':manpower_details'        => $_POST['manpower_details'] ?: null,
    ':supervisor_signature'    => $_POST['supervisor_signature'] ?: null,
    ':witnesses'               => !empty($witnesses) ? json_encode($witnesses, JSON_UNESCAPED_UNICODE) : null,
];

if ($id) {
    $params[':id'] = $id;
    $stmt = $db->prepare("
        UPDATE project_visits SET
            visit_date = :visit_date, visit_no = :visit_no,
            time_from = :time_from, time_to = :time_to,
            project_name_code = :project_name_code,
            tehsil = :tehsil, town_district_division = :town_district_division,
            unit_construction_stage = :unit_construction_stage,
            contractor_company = :contractor_company,
            committee_head = :committee_head,
            previous_visit_date = :previous_visit_date,
            reason_for_visit = :reason_for_visit,
            activity_status = :activity_status,
            obstructions_violations = :obstructions_violations,
            manpower_details = :manpower_details,
            witnesses = :witnesses,
            supervisor_signature = :supervisor_signature
        WHERE id = :id
    ");
} else {
    $stmt = $db->prepare("
        INSERT INTO project_visits (
            visit_date, visit_no, time_from, time_to,
            project_name_code, tehsil, town_district_division,
            unit_construction_stage, contractor_company,
            committee_head, previous_visit_date,
            reason_for_visit, activity_status,
            obstructions_violations, manpower_details,
            witnesses, supervisor_signature
        ) VALUES (
            :visit_date, :visit_no, :time_from, :time_to,
            :project_name_code, :tehsil, :town_district_division,
            :unit_construction_stage, :contractor_company,
            :committee_head, :previous_visit_date,
            :reason_for_visit, :activity_status,
            :obstructions_violations, :manpower_details,
            :witnesses, :supervisor_signature
        )
    ");
}

$stmt->execute($params);

$redirect_id = $id ? $id : $db->lastInsertId();
header("Location: index.php?saved=1&pdf_id=" . $redirect_id);
exit;
