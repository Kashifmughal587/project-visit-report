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
$supervisor = e($r['supervisor_signature']);
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

$fontFile = __DIR__ . '/assets/fonts/' . URDU_FONT;
$fontUrl = 'file:///' . str_replace(['\\', ' '], ['/', '%20'], $fontFile);

// Aamir Shehzad signature image (base64-embedded so Chrome doesn't need file access)
$sigImgHtml = '';
$sigImgPath = __DIR__ . '/assets/signatures/aamir_shehzad-signature.png';
if (file_exists($sigImgPath)) {
    $sigImgData = base64_encode(file_get_contents($sigImgPath));
    $sigImgHtml = '<img src="data:image/png;base64,' . $sigImgData . '" style="height:60px;width:auto;display:block;margin:0 auto;">';
}
if ($supervisor === '__AAMIR_SIG__') {
    $supervisor = $sigImgHtml ?: 'Aamir Shehzad';
}

$wkhtmltopdf = defined('WKHTMLTOPDF_PATH') ? WKHTMLTOPDF_PATH : '';
if (!$wkhtmltopdf) {
    $wkPaths = [
        'C:\Program Files (x86)\wkhtmltopdf\bin\wkhtmltopdf.exe',
        'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe',
        getenv('LOCALAPPDATA') . '\Programs\wkhtmltopdf\bin\wkhtmltopdf.exe',
        '/usr/local/bin/wkhtmltopdf',
        '/usr/bin/wkhtmltopdf',
    ];
    foreach ($wkPaths as $p) {
        if (file_exists($p)) { $wkhtmltopdf = $p; break; }
    }
}
if (!$wkhtmltopdf) {
    die('wkhtmltopdf not found. Set WKHTMLTOPDF_PATH in config.php.');
}

if (!file_exists($fontFile)) {
    die('Urdu font not found: ' . $fontFile . '. Place the TTF in assets/fonts/ and set URDU_FONT in config.php.');
}

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Project Visit Report</title>
<style>
    @font-face {
        font-family: 'Nastaleeq';
        src: url('<?= $fontUrl ?>') format('truetype');
        unicode-range: U+0600-06FF, U+0750-077F, U+08A0-08FF, U+FB50-FDFF, U+FE70-FEFF;
    }
    @page { margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Nastaleeq', 'Times New Roman', Times, serif;
        font-size: 10pt;
        color: #000;
        line-height: 1.3;
        padding: 15pt 42pt 57pt;
    }
    .title {
        font-family: 'Times New Roman', Times, serif;
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
        font-family: 'Times New Roman', Times, serif;
        font-weight: bold;
        font-size: 10pt;
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
    .witness-hdr {
        background-color: #003366;
        color: #ffffff;
        padding: 8px;
        font-family: 'Times New Roman', Times, serif;
        font-weight: bold;
        font-size: 10pt;
    }
    .witness-th {
        background-color: #f0f0f0;
        padding: 5px;
        font-family: 'Times New Roman', Times, serif;
        font-weight: bold;
        text-align: left;
    }
    .content-cell {
        padding: 8px;
        min-height: 40px;
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
        <td><?= $supervisor ?></td>
    </tr>
</table>



</body>
</html>
<?php
$html = ob_get_clean();

$tmpHtml = tempnam(sys_get_temp_dir(), 'pvr_') . '.html';
file_put_contents($tmpHtml, $html);

$tmpPdf = tempnam(sys_get_temp_dir(), 'pvr_') . '.pdf';

$wkFlags = [
    '--enable-local-file-access',
    '--encoding', 'utf-8',
    '--page-size', 'A4',
    '--margin-top', '0',
    '--margin-right', '0',
    '--margin-bottom', '0',
    '--margin-left', '0',
    '--disable-smart-shrinking',
    '--quiet',
];

$cmd = escapeshellarg($wkhtmltopdf) . ' '
    . join(' ', array_map('escapeshellarg', $wkFlags)) . ' '
    . escapeshellarg($tmpHtml) . ' '
    . escapeshellarg($tmpPdf) . ' 2>&1';
exec($cmd, $output, $exitCode);

if ($exitCode !== 0 || !file_exists($tmpPdf) || filesize($tmpPdf) < 1000) {
    $err = 'PDF generation failed. Exit code: ' . $exitCode;
    if ($output) $err .= "\nOutput:\n" . implode("\n", $output);
    @unlink($tmpHtml);
    @unlink($tmpPdf);
    header('Content-Type: text/html; charset=utf-8');
    die('<pre>' . htmlspecialchars($err) . '</pre>');
}

$pdfContent = @file_get_contents($tmpPdf);
if ($pdfContent === false || $pdfContent === '') {
    @unlink($tmpHtml); @unlink($tmpPdf);
    header('Content-Type: text/html; charset=utf-8');
    die('<pre>Failed to read generated PDF file.</pre>');
}

@unlink($tmpHtml);
@unlink($tmpPdf);

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Project_Visit_Report.pdf"');
header('Content-Length: ' . strlen($pdfContent));
echo $pdfContent;
