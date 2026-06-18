<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/vendor/tcpdf/tcpdf.php';

$id = $_GET['id'] ?? 0;
$db = getDB();
$stmt = $db->prepare("SELECT * FROM project_visits WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    die("Record not found.");
}

class ReportPDF extends TCPDF {
    public function Header() {
        $this->SetFont('freeserif', 'B', 24);
        $this->Cell(0, 8, 'PROJECT VISIT REPORT', 0, 1, 'C');
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(4);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('freeserif', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new ReportPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('Project Visit Report System');
$pdf->SetAuthor('Planning & Development Department');
$pdf->SetTitle('Project Visit Report - ' . ($r['visit_no'] ?: ''));
$pdf->SetMargins(15, 30, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

$visit_date = $r['visit_date'] ? date('d-m-Y', strtotime($r['visit_date'])) : '';
$visit_no = $r['visit_no'] ?: '';
$time_from = $r['time_from'] ? date('h:i A', strtotime($r['time_from'])) : '';
$time_to = $r['time_to'] ? date('h:i A', strtotime($r['time_to'])) : '';
$project_name_code = $r['project_name_code'] ?: '';
$unit_construction_stage = $r['unit_construction_stage'] ?: '';
$contractor_company = $r['contractor_company'] ?: '';
$committee_head = $r['committee_head'] ?: '';
$prev_visit_date = $r['previous_visit_date'] ? htmlspecialchars(date('d-m-Y', strtotime($r['previous_visit_date']))) : '';

$witnesses = [];
if ($r['witnesses']) {
    $decoded = json_decode($r['witnesses'], true);
    if (is_array($decoded)) {
        $witnesses = $decoded;
    }
}
if (empty($witnesses)) {
    if ($r['witness_name'] || $r['witness_responsibility'] || $r['witness_signature']) {
        $witnesses[] = [
            'name' => $r['witness_name'],
            'responsibility' => $r['witness_responsibility'],
            'signature' => $r['witness_signature'],
        ];
    } else {
        $witnesses[] = ['name' => '', 'responsibility' => '', 'signature' => ''];
    }
}

$sig_rows = '';
foreach ($witnesses as $w) {
    $sig_rows .= '
    <tr>
        <td width="33%">' . htmlspecialchars($w['name'] ?? '') . '</td>
        <td width="33%">' . htmlspecialchars($w['responsibility'] ?? '') . '</td>
        <td width="34%">' . htmlspecialchars($w['signature'] ?? '') . '</td>
    </tr>';
}

$pdf->SetFont('freeserif', '', 10);

$authority = htmlspecialchars($r['visit_authority'] ?? '');

$full_html = '
<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
    <tr>
        <td width="25%"><b>Date of Visit</b></td>
        <td width="25%">' . htmlspecialchars($visit_date) . '</td>
        <td width="20%"><b>Visit No.</b></td>
        <td width="30%">' . htmlspecialchars($visit_no) . '</td>
    </tr>
    <tr>
        <td><b>Time at Site</b></td>
        <td colspan="3">' . htmlspecialchars($time_from) . ' - ' . htmlspecialchars($time_to) . '</td>
    </tr>
    <tr>
        <td><b>Project Name with Code</b></td>
        <td colspan="3">' . htmlspecialchars($project_name_code) . '</td>
    </tr>
    <tr>
        <td><b>Tehsil / Town</b></td>
        <td>' . htmlspecialchars($r['tehsil'] ?: '') . '</td>
        <td><b>District / Division</b></td>
        <td>' . htmlspecialchars($r['town_district_division'] ?: '') . '</td>
    </tr>
    <tr>
        <td><b>Unit In-Construction Stage</b></td>
        <td colspan="3">' . htmlspecialchars($unit_construction_stage) . '</td>
    </tr>
    <tr>
        <td><b>Name of Contractor / Company</b></td>
        <td colspan="3">' . htmlspecialchars($contractor_company) . '</td>
    </tr>
    <tr>
        <td><b>Project Committee Head</b></td>
        <td>' . htmlspecialchars($committee_head) . '</td>
        <td><b>Previous Visit Date</b></td>
        <td>' . $prev_visit_date . '</td>
    </tr>
</table>
<table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
    <tr>
        <td style="background-color:#003366; color:#ffffff;"><b>REASON FOR VISIT</b></td>
    </tr>
    <tr>
        <td style="padding:8px;">' . nl2br(htmlspecialchars($r['reason_for_visit'] ?: '')) . '<br><br><br></td>
    </tr>
</table>
<br>
<table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
    <tr>
        <td style="background-color:#003366; color:#ffffff;"><b>DESCRIBE TODAY\'S PROJECT ACTIVITIES (IF ANY)<br>Activity Name, Current Status, Next Inspection Stage @ Date</b></td>
    </tr>
    <tr>
        <td style="padding:8px;">' . nl2br(htmlspecialchars($r['activity_status'] ?: '')) . '<br><br><br><br><br></td>
    </tr>
</table>
<br>
<table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
    <tr>
        <td style="background-color:#003366; color:#ffffff;"><b>DESCRIBE ANY OBSTRUCTIONS / SITE VIOLATIONS AND ACTIONS (IF ANY)</b></td>
    </tr>
    <tr>
        <td style="padding:8px;">' . nl2br(htmlspecialchars($r['obstructions_violations'] ?: '')) . '<br><br><br></td>
    </tr>
</table>
<br>
<table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
    <tr>
        <td style="background-color:#003366; color:#ffffff;"><b>MANPOWER DETAIL AND INSTRUCTIONS (IF ANY)</b></td>
    </tr>
    <tr>
        <td style="padding:8px;">' . nl2br(htmlspecialchars($r['manpower_details'] ?: '')) . '<br><br><br></td>
    </tr>
</table>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
    <tr>
        <td colspan="3" style="background-color:#003366; color:#ffffff;"><b>WITNESS: NAME OF PROJECT COMMITTEE / PROJECT RESPONSIBLE PRESENT DURING VISIT:</b></td>
    </tr>
    <tr style="background-color:#f0f0f0;">
        <th width="33%"><b>Name</b></th>
        <th width="33%"><b>Responsibility</b></th>
        <th width="34%"><b>Signature</b></th>
    </tr>
    ' . $sig_rows . '
    <tr>
        <td colspan="2"><b>Civil / Site Supervisor</b></td>
        <td>' . htmlspecialchars($r['supervisor_signature'] ?: '') . '</td>
    </tr>
</table>
';
$pdf->writeHTML($full_html, true, false, true, false, '');
$pdf->Ln(4);

$pdf->SetFont('freeserif', 'I', 8);
$pdf->Cell(0, 5, 'Generated on: ' . date('d-m-Y h:i A'), 0, 1, 'R');

$pdf->Output('Project_Visit_Report_' . ($r['visit_no'] ?: '') . '.pdf', 'I');
