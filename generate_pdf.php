<?php
require_once 'config.php';
require_once 'fpdf/fpdf.php';

$student_ids = [];

if (isset($_GET['id'])) {
    $student_ids[] = intval($_GET['id']);
} elseif (isset($_GET['action']) && $_GET['action'] == 'all') {
    $result = $conn->query("SELECT id FROM students WHERE is_printed = 0 ORDER BY name ASC");
    while ($row = $result->fetch_assoc()) {
        $student_ids[] = $row['id'];
    }
}

if (empty($student_ids)) {
    die("No pending students selected for printing. (Note: 'Print All' only prints cards that are not marked as Printed yet).");
}

$id_list = implode(',', $student_ids);
$sql = "SELECT * FROM students WHERE id IN ($id_list) ORDER BY name ASC";
$students = mysqli_query($conn, $sql);

$college_name = getSetting($conn, 'college_name');
$college_logo = getSetting($conn, 'college_logo');
$logo_path = '';
if ($college_logo && file_exists(UPLOAD_DIR . $college_logo)) {
    $logo_path = UPLOAD_DIR . $college_logo;
}

$principal_signature = getSetting($conn, 'principal_signature');
$sig_path = '';
if ($principal_signature && file_exists(UPLOAD_DIR . $principal_signature)) {
    $sig_path = UPLOAD_DIR . $principal_signature;
}

$card_w = 54;
$card_h = 86;

$pdf = new FPDF('P', 'mm', array($card_w, $card_h));
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);

while ($student = mysqli_fetch_assoc($students)) {
    $pdf->AddPage();

    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.4);
    $pdf->Rect(0, 0, $card_w, $card_h);

    $pdf->SetFillColor(0, 51, 102);
    $pdf->Rect(0, 0, $card_w, 12, 'F');

    if ($logo_path) {
        try {
            $pdf->Image($logo_path, 2, 1.5, 9, 9);
        } catch (Exception $e) {}
    }

    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetTextColor(255, 255, 255);
    $logo_offset = $logo_path ? 11 : 2;
    $pdf->SetXY($logo_offset, 1);
    $pdf->Cell($card_w - $logo_offset - 2, 5, strtoupper($college_name), 0, 0, 'C');

    $pdf->SetFont('Helvetica', '', 5);
    $pdf->SetXY($logo_offset, 6);
    $pdf->Cell($card_w - $logo_offset - 2, 4, 'STUDENT IDENTITY CARD', 0, 0, 'C');

    $photo_w = 18;
    $photo_h = 22;
    $photo_x = ($card_w - $photo_w) / 2;
    $photo_y = 14;

    $photo_path = '';
    if ($student['photo'] && file_exists(UPLOAD_DIR . $student['photo'])) {
        $photo_path = UPLOAD_DIR . $student['photo'];
    }

    if ($photo_path) {
        try {
            $pdf->Image($photo_path, $photo_x, $photo_y, $photo_w, $photo_h);
        } catch (Exception $e) {
            $pdf->SetDrawColor(150, 150, 150);
            $pdf->Rect($photo_x, $photo_y, $photo_w, $photo_h);
            $pdf->SetFont('Helvetica', '', 6);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->SetXY($photo_x, $photo_y + 10);
            $pdf->Cell($photo_w, 4, 'No Photo', 0, 0, 'C');
        }
    } else {
        $pdf->SetDrawColor(150, 150, 150);
        $pdf->Rect($photo_x, $photo_y, $photo_w, $photo_h);
        $pdf->SetFont('Helvetica', '', 6);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetXY($photo_x, $photo_y + 10);
        $pdf->Cell($photo_w, 4, 'No Photo', 0, 0, 'C');
    }

    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.2);
    $pdf->Rect($photo_x, $photo_y, $photo_w, $photo_h);

    $detail_x = 3;
    $detail_y = 38;
    $detail_w = $card_w - 6;
    $line_h = 3.6;

    $pdf->SetTextColor(0, 0, 0);

    $fields = [
        'Name' => $student['name'],
        'Enroll' => $student['enrollment_no'] ?: 'N/A',
        'Course' => $student['course'],
        'Branch' => $student['branch'],
        'DOB' => date('d-m-Y', strtotime($student['dob'])),
        'Mobile' => $student['mobile'],
        'Blood' => $student['blood_group'] ?: '-',
        'Address' => $student['address']
    ];

    foreach ($fields as $label => $val) {
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY($detail_x, $detail_y);
        $pdf->Cell(12, $line_h, $label . ':', 0, 0);
        
        $pdf->SetFont('Helvetica', '', 6);
        
        if ($label === 'Address') {
            $pdf->SetXY($detail_x + 12, $detail_y + 0.8);
            $pdf->MultiCell($detail_w - 12, 2.8, $val, 0, 'L');
            $detail_y = $pdf->GetY() + 0.5;
        } else {
            if (strlen($val) > 28) {
                $val = substr($val, 0, 26) . '..';
            }
            $pdf->Cell($detail_w - 12, $line_h, $val, 0, 0);
            $detail_y += $line_h;
        }
    }

    $sig_w = 12;
    $sig_h = 4;
    $sig_x = $card_w - $sig_w - 2;
    $sig_y = $card_h - $sig_h - 4;

    if ($sig_path) {
        try {
            $pdf->Image($sig_path, $sig_x, $sig_y, $sig_w, $sig_h);
        } catch (Exception $e) { }
    }
    
    $pdf->SetFont('Helvetica', '', 4);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($sig_x, $sig_y + $sig_h);
    $pdf->Cell($sig_w, 2, 'Principal', 0, 0, 'C');
}

$pdf->Output('I', 'Student_ID_Cards.pdf');
?>
