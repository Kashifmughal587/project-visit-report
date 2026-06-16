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
        $this->SetFont('dejavusans', 'B', 16);
        $this->Cell(0, 8, 'PROJECT VISIT REPORT', 0, 1, 'C');
        $this->Ln(2);
        $this->SetFont('dejavusans', '', 9);
        $this->Cell(0, 5, 'Government of the Punjab - Planning & Development Department', 0, 1, 'C');
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(4);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }

    public function sectionLabel($label) {
        $this->SetFont('dejavusans', 'B', 10);
        $this->SetFillColor(0, 51, 102);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 7, '  ' . $label, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
    }

    private function infoRow($label, $value, $w1 = 55, $w2 = 0) {
        $this->SetFont('dejavusans', 'B', 10);
        $this->Cell($w1, 6, $label . ':', 0, 0);
        $this->SetFont('dejavusans', '', 10);
        $value = $value ?: 'N/A';
        if ($w2 > 0) {
            $this->Cell($w2, 6, $value, 0, 1);
        } else {
            $this->Cell(0, 6, $value, 0, 1);
        }
    }

    private function infoRowMulti($label, $value, $w1 = 55) {
        $this->SetFont('dejavusans', 'B', 10);
        $this->Cell($w1, 6, $label . ':', 0, 0);
        $this->SetFont('dejavusans', '', 10);
        $value = $value ?: 'N/A';
        $this->writeHTMLCell(0, 6, '', '', nl2br(htmlspecialchars($value)), 0, 1);
    }
}

$pdf = new ReportPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('Project Visit Report System');
$pdf->SetAuthor('Planning & Development Department');
$pdf->SetTitle('Project Visit Report - ' . $r['visit_no']);
$pdf->SetMargins(15, 30, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

$pdf->SetFont('dejavusans', '', 10);

$html = '
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <td width="25%"><b>Date of Visit</b></td>
        <td width="25%">' . htmlspecialchars(date('d-m-Y', strtotime($r['visit_date']))) . '</td>
        <td width="20%"><b>Visit No.</b></td>
        <td width="30%">' . htmlspecialchars($r['visit_no']) . '</td>
    </tr>
    <tr>
        <td><b>Time at Site</b></td>
        <td colspan="3">' . htmlspecialchars(date('h:i A', strtotime($r['time_from']))) . ' - ' . htmlspecialchars(date('h:i A', strtotime($r['time_to']))) . '</td>
    </tr>
    <tr>
        <td><b>Project Name with Code</b></td>
        <td colspan="3">' . htmlspecialchars($r['project_name_code']) . '</td>
    </tr>
    <tr>
        <td><b>Tehsil</b></td>
        <td>' . htmlspecialchars($r['tehsil'] ?: 'N/A') . '</td>
        <td><b>Town / District / Division</b></td>
        <td>' . htmlspecialchars($r['town_district_division'] ?: 'N/A') . '</td>
    </tr>
    <tr>
        <td><b>Unit In-Construction Stage</b></td>
        <td colspan="3">' . htmlspecialchars($r['unit_construction_stage']) . '</td>
    </tr>
    <tr>
        <td><b>Name of Contractor / Company</b></td>
        <td colspan="3">' . htmlspecialchars($r['contractor_company']) . '</td>
    </tr>
    <tr>
        <td><b>Project Committee Head</b></td>
        <td>' . htmlspecialchars($r['committee_head']) . '</td>
        <td><b>Previous Visit Date</b></td>
        <td>' . ($r['previous_visit_date'] ? htmlspecialchars(date('d-m-Y', strtotime($r['previous_visit_date']))) : 'N/A') . '</td>
    </tr>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(4);

$pdf->sectionLabel('REASON FOR VISIT');
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell(0, 6, htmlspecialchars($r['reason_for_visit'] ?: 'N/A'), 0, 'L');
$pdf->Ln(3);

$pdf->sectionLabel('TODAY\'S PROJECT ACTIVITIES');
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell(0, 6, htmlspecialchars($r['project_activities'] ?: 'N/A'), 0, 'L');
$pdf->Ln(3);

$pdf->sectionLabel('ACTIVITY STATUS & INSPECTION SCHEDULE');
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell(0, 6, htmlspecialchars($r['activity_status'] ?: 'N/A'), 0, 'L');
$pdf->Ln(3);

$pdf->sectionLabel('OBSTRUCTIONS / SITE VIOLATIONS & ACTIONS');
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell(0, 6, htmlspecialchars($r['obstructions_violations'] ?: 'None reported'), 0, 'L');
$pdf->Ln(3);

$pdf->sectionLabel('MANPOWER DETAIL & INSTRUCTIONS');
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell(0, 6, htmlspecialchars($r['manpower_details'] ?: 'N/A'), 0, 'L');
$pdf->Ln(5);

$pdf->sectionLabel('WITNESS & SIGNATURES');
$sig_html = '
<table border="1" cellpadding="5" cellspacing="0">
    <tr style="background-color:#f0f0f0;">
        <th width="33%"><b>Name</b></th>
        <th width="33%"><b>Responsibility</b></th>
        <th width="34%"><b>Signature</b></th>
    </tr>
    <tr>
        <td>' . htmlspecialchars($r['witness_name'] ?: 'N/A') . '</td>
        <td>' . htmlspecialchars($r['witness_responsibility'] ?: 'N/A') . '</td>
        <td>' . htmlspecialchars($r['witness_signature'] ?: 'N/A') . '</td>
    </tr>
    <tr>
        <td colspan="2"><b>Civil / Site Supervisor</b></td>
        <td>' . htmlspecialchars($r['supervisor_signature'] ?: 'N/A') . '</td>
    </tr>
</table>
';
$pdf->writeHTML($sig_html, true, false, true, false, '');
$pdf->Ln(4);

$pdf->SetFont('dejavusans', 'I', 8);
$pdf->Cell(0, 5, 'Generated on: ' . date('d-m-Y h:i A'), 0, 1, 'R');

$pdf->Output('Project_Visit_Report_' . $r['visit_no'] . '.pdf', 'I');
