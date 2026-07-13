<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_check.php';

$id = $_GET['id'] ?? 0;
$db = getDB();
$stmt = $db->prepare("SELECT * FROM project_visits WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    die("Record not found.");
}

function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$visit_date = $r['visit_date'] ? date('d-m-Y', strtotime($r['visit_date'])) : '';
$visit_no   = e($r['visit_no']);
$time_from  = $r['time_from'] ? date('h:i A', strtotime($r['time_from'])) : '';
$time_to    = $r['time_to'] ? date('h:i A', strtotime($r['time_to'])) : '';
$project    = e($r['project_name_code']);
$tehsil     = e($r['tehsil']);
$district   = e($r['town_district_division']);
$stage      = e($r['unit_construction_stage']);
$contractor = e($r['contractor_company']);
$committee  = e($r['committee_head']);
$reason     = e($r['reason_for_visit']);
$activity   = e($r['activity_status']);
$obstruct   = e($r['obstructions_violations']);
$manpower   = e($r['manpower_details']);
$supervisor = e(trim($r['supervisor_signature'] ?? ''));
$prev_visit = $r['previous_visit_date'] ? date('d-m-Y', strtotime($r['previous_visit_date'])) : '';

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
            'name' => e($r['witness_name']),
            'responsibility' => e($r['witness_responsibility']),
            'signature' => e($r['witness_signature']),
        ];
    } else {
        $witnesses[] = ['name' => '', 'responsibility' => '', 'signature' => ''];
    }
}

$sig_rows = '';
foreach ($witnesses as $w) {
    $name = e($w['name'] ?? '');
    $resp = e($w['responsibility'] ?? '');
    $sig  = e($w['signature'] ?? '');
    if ($name === '') { $name = '&nbsp;'; }
    if ($resp === '') { $resp = '&nbsp;'; }
    if ($sig  === '') { $sig  = '&nbsp;'; }
    $sig_rows .= '<tr>
        <td width="33%">' . $name . '</td>
        <td width="33%">' . $resp . '</td>
        <td width="34%">' . $sig . '</td>
    </tr>';
}

// Aamir Shehzad signature
$sigImgHtml = '';
$sigImgPath = __DIR__ . '/assets/signatures/aamir_shehzad_signature_flat.png';
if (!file_exists($sigImgPath)) { $sigImgPath = __DIR__ . '/assets/signatures/aamir_shehzad_signature.png'; }
if (file_exists($sigImgPath)) {
    $raw = file_get_contents($sigImgPath);
    if ($raw !== false && strlen($raw) > 100) {
        $sigImgHtml = '<img src="data:image/png;base64,' . base64_encode($raw) . '" style="height:60px;">';
    }
}
if ($supervisor === '__AAMIR_SIG__') {
    $supervisor = $sigImgHtml ?: 'Aamir Shehzad';
}

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Project Visit Report</title>
<style>
    * { margin: 0; padding: 0; }
    body {
        font-size: 10pt;
        color: #000;
        line-height: 1.3;
    }
    .title {
        font-family: dejavuserif;
        font-size: 20pt;
        font-weight: bold;
        text-align: center;
        margin-bottom: 2mm;
    }
    .hline {
        border: none;
        border-top: 0.5pt solid #000;
        margin-bottom: 4mm;
    }
    table.info {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 0;
    }
    table.info td {
        border: 0.5pt solid #000;
        padding: 5px;
        vertical-align: top;
        font-family: xbriyaz, dejavuserif;
    }
    table.info td b {
        font-family: dejavuserif;
    }
    table.section {
        border-collapse: collapse;
        width: 100%;
        margin-top: 2mm;
    }
    table.section td {
        border: 0.5pt solid #000;
        padding: 5px;
        vertical-align: top;
    }
    .hdr {
        background-color: #003366;
        color: #ffffff;
        padding: 8px;
        font-weight: bold;
        font-size: 10pt;
        font-family: dejavuserif;
    }
    table.witness {
        border-collapse: collapse;
        width: 100%;
        margin-top: 2mm;
    }
    table.witness td, table.witness th {
        border: 0.5pt solid #000;
        padding: 5px;
        vertical-align: top;
    }
    table.witness td {
        font-family: xbriyaz, dejavuserif;
    }
    .witness-hdr {
        background-color: #003366;
        color: #ffffff;
        padding: 8px;
        font-weight: bold;
        font-size: 10pt;
        font-family: dejavuserif;
    }
    .witness-th {
        background-color: #f0f0f0;
        padding: 5px;
        font-weight: bold;
        text-align: left;
        font-family: dejavuserif;
    }
    .content-cell {
        padding: 8px;
        font-family: xbriyaz, dejavuserif;
    }
</style>
</head>
<body>

<div class="title">PROJECT VISIT REPORT</div>
<hr class="hline">

<table class="info">
    <tr>
        <td width="25%"><b>Date of Visit</b></td>
        <td width="25%"><?= $visit_date ?></td>
        <td width="20%"><b>Visit No.</b></td>
        <td width="30%"><?= $visit_no ?></td>
    </tr>
    <tr>
        <td><b>Time at Site</b></td>
        <td colspan="3"><?= htmlspecialchars($time_from) ?> - <?= htmlspecialchars($time_to) ?></td>
    </tr>
    <tr>
        <td><b>Project Name with Code</b></td>
        <td colspan="3"><?= $project ?></td>
    </tr>
    <tr>
        <td><b>Tehsil / Town / District / Division</b></td>
        <td colspan="3"><?= $tehsil ?></td>
    </tr>
    <tr>
        <td><b>Unit In-Construction Stage</b></td>
        <td colspan="3"><?= $stage ?></td>
    </tr>
    <tr>
        <td><b>Project Committee Head</b></td>
        <td><?= $committee ?></td>
        <td><b>Name of Contractor / Company</b></td>
        <td><?= $contractor ?></td>
    </tr>
    <tr>
        <td><b>Previous Visit Date</b></td>
        <td><?= $prev_visit ?></td>
        <td><b>Reason for Visit</b></td>
        <td><?= nl2br($reason) ?></td>
    </tr>
</table>

<table class="section">
    <tr>
        <td class="hdr">DESCRIBE TODAY'S PROJECT ACTIVITIES (IF ANY)<br>Activity Name, Current Status, Next Inspection Stage @ Date</td>
    </tr>
    <tr>
        <td class="content-cell"><?= nl2br($activity) ?><?= $r['activity_status'] ? '<br><br>' : '<br><br><br><br>' ?></td>
    </tr>
</table>

<table class="section">
    <tr>
        <td class="hdr">DESCRIBE ANY OBSTRUCTIONS / SITE VIOLATIONS AND ACTIONS (IF ANY)</td>
    </tr>
    <tr>
        <td class="content-cell"><?= nl2br($obstruct) ?><?= $r['obstructions_violations'] ? '<br><br>' : '<br><br><br><br>' ?></td>
    </tr>
</table>

<table class="section">
    <tr>
        <td class="hdr">MANPOWER DETAIL AND INSTRUCTIONS (IF ANY)</td>
    </tr>
    <tr>
        <td class="content-cell"><?= nl2br($manpower) ?><?= $r['manpower_details'] ? '<br><br>' : '<br><br><br><br>' ?></td>
    </tr>
</table>

<table class="witness">
    <tr>
        <td colspan="3" class="witness-hdr">WITNESS: NAME OF PROJECT COMMITTEE / PROJECT RESPONSIBLE PRESENT DURING VISIT:</td>
    </tr>
    <tr>
        <th width="33%" class="witness-th"><b>Name</b></th>
        <th width="33%" class="witness-th"><b>Responsibility</b></th>
        <th width="34%" class="witness-th"><b>Signature</b></th>
    </tr>
    <?= $sig_rows ?>
    <tr>
        <td colspan="2"><b>Civil / Site Supervisor</b></td>
        <td align="center"><?= $supervisor ?></td>
    </tr>
</table>



</body>
</html>
<?php
$html = ob_get_clean();

require_once __DIR__ . '/vendor/autoload.php';

try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 5,
        'margin_bottom' => 20,
    ]);

    $mpdf->WriteHTML($html);
    $mpdf->Output('Project_Visit_Report.pdf', 'I');
} catch (\Mpdf\MpdfException $e) {
    header('Content-Type: text/html; charset=utf-8');
    die('<pre>PDF generation failed: ' . htmlspecialchars($e->getMessage()) . '</pre>');
}
