<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("Location: ../login/");
    exit;
}

// Include database connection
require_once('../database/db_conn.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Only POST allowed');
}

// Get form data
$name = $_POST['name'] ?? 'Ikke angivet';
$cpr = $_POST['cpr'] ?? 'Ikke angivet';
$phone = $_POST['phone'] ?? 'Ikke angivet';
$email = $_POST['email'] ?? 'Ikke angivet';
$education = $_POST['education'] ?? '';
$specialization = $_POST['specialization'] ?? '';

// Get dynamic entries
$experienceEntries = json_decode($_POST['experienceEntries'] ?? '[]', true);
$shorteningEntries = json_decode($_POST['shorteningEntries'] ?? '[]', true);
$assessmentEntries = json_decode($_POST['assessmentEntries'] ?? '[]', true);

// Get education data from database
$educationData = null;
$specializationData = null;

if ($education) {
    $stmt = $conn->prepare("SELECT title, link FROM educationtitle WHERE id = ?");
    $stmt->bind_param('i', $education);
    $stmt->execute();
    $result = $stmt->get_result();
    $educationData = $result->fetch_assoc();
    $stmt->close();

    if ($specialization) {
        $stmt = $conn->prepare("SELECT name, length, euxLength FROM education WHERE id = ?");
        $stmt->bind_param('i', $specialization);
        $stmt->execute();
        $result = $stmt->get_result();
        $specializationData = $result->fetch_assoc();
        $stmt->close();
    }
}

// Get session data
$responsible_name = $_SESSION['name'] ?? 'Ikke angivet';
$responsible_dept = $_SESSION['department'] ?? 'Ikke angivet';
$responsible_email = $_SESSION['email'] ?? 'Ikke angivet';
$responsible_phone = $_SESSION['phone'] ?? 'Ikke angivet';

// Update user's RKV count
$userId = $_SESSION['id'];
$stmt = $conn->prepare("UPDATE users SET amount = amount + 1 WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->close();

// Create filename
$clean_name = strtolower(str_replace(' ', '-', trim($name)));
$clean_name = preg_replace('/[^a-z0-9-]/', '', $clean_name);
$filename = "rkv-" . $clean_name . "-" . date('d-m-Y') . ".pdf";

// Set headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');

// Helper function to escape text for PDF
function pdfEscape($text)
{
    return str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $text);
}

// Build content strings
$educationTitle = $educationData ? pdfEscape($educationData['title']) : 'Ikke valgt';
$educationLink = $educationData ? pdfEscape($educationData['link']) : '';
$specializationName = $specializationData ? pdfEscape($specializationData['name']) : 'Ikke valgt';

// Build experience entries text
$experienceText = '';
$y_pos = 440;
if (!empty($experienceEntries)) {
    foreach ($experienceEntries as $entry) {
        $experienceText .= "0 -15 Td\n";
        $experienceText .= "(" . pdfEscape($entry['experience']) . " - " . pdfEscape($entry['rating']) . ") Tj\n";
        $y_pos -= 15;
    }
} else {
    $experienceText = "0 -15 Td\n(Ingen erhvervserfaring tilfojet) Tj\n";
    $y_pos -= 15;
}

// Build shortening entries text
$shorteningText = '';
if (!empty($shorteningEntries)) {
    foreach ($shorteningEntries as $entry) {
        $shorteningText .= "0 -15 Td\n";
        $shorteningText .= "(" . pdfEscape($entry['experience']) . " - " . pdfEscape($entry['rating']) . ") Tj\n";
        $shorteningText .= "0 -12 Td\n";
        $shorteningText .= "(Skole: " . pdfEscape($entry['schoolWeeks'] ?? '0') . " uger, Praktik: " . pdfEscape($entry['practiceMonths'] ?? '0') . " mdr.) Tj\n";
        $y_pos -= 27;
    }
} else {
    $shorteningText = "0 -15 Td\n(Ingen afkortning tilfojet) Tj\n";
    $y_pos -= 15;
}

// Build assessment entries text
$assessmentText = '';
if (!empty($assessmentEntries)) {
    foreach ($assessmentEntries as $entry) {
        $assessmentText .= "0 -15 Td\n";
        $assessmentText .= "(" . pdfEscape($entry['basis']) . " - " . pdfEscape($entry['competencies']) . ") Tj\n";
        $assessmentText .= "0 -12 Td\n";
        $assessmentText .= "(Afkortning: " . pdfEscape($entry['shortening'] ?? '0') . " uger) Tj\n";
        $y_pos -= 27;
    }
} else {
    $assessmentText = "0 -15 Td\n(Ingen vurdering tilfojet) Tj\n";
    $y_pos -= 15;
}

// Get EUV designation
$euvDesignation = $_POST['euvDesignation'] ?? 'euv3';
$euvText = '';
switch ($euvDesignation) {
    case 'euv1':
        $euvText = 'EUV1 - Mindst 2 ars relevant erhvervserfaring';
        break;
    case 'euv2':
        $euvText = 'EUV2 - Mindre end 2 ars relevant erhvervserfaring';
        break;
    default:
        $euvText = 'EUV3 - Uden relevant erhvervserfaring';
        break;
}

// Get final results
$totalShortening = $_POST['totalShortening'] ?? 'Ikke angivet';
$shortenedDuration = $_POST['shortenedDuration'] ?? 'Ikke angivet';
$gf2Duration = $_POST['gf2Duration'] ?? 'Ikke angivet';
$mainCourseDuration = $_POST['mainCourseDuration'] ?? 'Ikke angivet';
$companyTrainingDuration = $_POST['companyTrainingDuration'] ?? 'Ikke angivet';

// Consent and SPS
$consentSharing = $_POST['consentSharing'] ?? 'no';
$spsWish = $_POST['spsWish'] ?? 'no';
$rkvComment = $_POST['rkvComment'] ?? '';

// Generate PDF content
$pdf_content = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj

2 0 obj
<<
/Type /Pages
/Kids [3 0 R 4 0 R]
/Count 2
>>
endobj

3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 5 0 R
/Resources <<
/Font <<
/F1 6 0 R
/F2 7 0 R
>>
>>
>>
endobj

4 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 8 0 R
/Resources <<
/Font <<
/F1 6 0 R
/F2 7 0 R
>>
>>
>>
endobj

5 0 obj
<<
/Length 2000
>>
stream
BT
/F2 20 Tf
50 750 Td
(REALKOMPETENCEVURDERING) Tj
0 -25 Td
/F1 14 Tf
(RKV - Erhvervsuddannelse for voksne) Tj
0 -20 Td
(Mercantec) Tj

0 -40 Td
/F2 14 Tf
(PERSONLIGE OPLYSNINGER) Tj
0 -20 Td
/F1 11 Tf
(Navn: ' . pdfEscape($name) . ') Tj
0 -15 Td
(CPR-nr: ' . pdfEscape($cpr) . ') Tj
0 -15 Td
(Telefon: ' . pdfEscape($phone) . ') Tj
0 -15 Td
(Email: ' . pdfEscape($email) . ') Tj
0 -15 Td
(Uddannelse: ' . $educationTitle . ') Tj
0 -15 Td
(Specialisering: ' . $specializationName . ') Tj';

if ($educationLink) {
    $pdf_content .= '
0 -15 Td
(Bekendtgorelse: ' . $educationLink . ') Tj';
}

$pdf_content .= '

0 -30 Td
/F2 12 Tf
(A) VURDERING AF 2 ARS RELEVANT ERHVERVSERFARING) Tj
0 -15 Td
/F1 10 Tf
(Uddannelsesbekendtgorelsens bilag 1, skema 1) Tj
/F1 11 Tf
' . $experienceText . '

0 -25 Td
/F2 12 Tf
(B) ERHVERVSERFARING TIL AFKORTNING) Tj
0 -15 Td
/F1 10 Tf
(Uddannelsesbekendtgorelsens bilag 1, skema 2) Tj
/F1 11 Tf
' . $shorteningText . '

0 -25 Td
/F2 12 Tf
(C) RELEVANT UDDANNELSE TIL AFKORTNING) Tj
0 -15 Td
/F1 10 Tf
(Uddannelsesbekendtgorelsens bilag 1, skema 3) Tj
0 -15 Td
/F1 11 Tf
(EUD: ' . pdfEscape($_POST['eudDesignation'] ?? '') . ') Tj
0 -12 Td
(AMU: ' . pdfEscape($_POST['amuDesignation'] ?? '') . ') Tj
0 -12 Td
(UVM-fag: ' . pdfEscape($_POST['uvmDesignation'] ?? '') . ') Tj
0 -12 Td
(Andet: ' . pdfEscape($_POST['otherDesignation'] ?? '') . ') Tj

0 -25 Td
/F2 12 Tf
(D) SKOLENS SKONSMASSIGE VURDERING) Tj
0 -15 Td
/F1 10 Tf
(Ud fra individuel RKV, herunder afprovning) Tj
/F1 11 Tf
' . $assessmentText . '
ET
endstream
endobj

6 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj

7 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica-Bold
>>
endobj

8 0 obj
<<
/Length 1500
>>
stream
BT
/F2 16 Tf
50 750 Td
(RESULTAT AF REALKOMPETENCEVURDERING) Tj

0 -30 Td
/F2 12 Tf
(EUV-BETEGNELSE) Tj
0 -15 Td
/F1 11 Tf
(' . pdfEscape($euvText) . ') Tj

0 -25 Td
/F2 12 Tf
(UDDANNELSESLANGDE) Tj
0 -15 Td
/F1 11 Tf
(Afkortning i alt: ' . pdfEscape($totalShortening) . ') Tj
0 -15 Td
(Afkortet uddannelseslangde: ' . pdfEscape($shortenedDuration) . ') Tj

0 -25 Td
/F2 12 Tf
(FORDELING AF UDDANNELSESLANGDE) Tj
0 -15 Td
/F1 11 Tf
(GF2 pa skole: ' . pdfEscape($gf2Duration) . ' uger) Tj
0 -15 Td
(Hovedforlob pa skole: ' . pdfEscape($mainCourseDuration) . ' uger) Tj
0 -15 Td
(Oplaring i virksomhed: ' . pdfEscape($companyTrainingDuration) . ' uger) Tj

0 -30 Td
/F2 12 Tf
(SAMTYKKE OG ONSKER) Tj
0 -15 Td
/F1 11 Tf
(Samtykke til videregtvelse: ' . ($consentSharing === 'yes' ? 'Ja' : 'Nej') . ') Tj
0 -15 Td
(Onske om SPS: ' . ($spsWish === 'yes' ? 'Ja' : 'Nej') . ') Tj';

if (!empty($rkvComment)) {
    $pdf_content .= '
0 -20 Td
/F2 11 Tf
(KOMMENTAR TIL RKV:) Tj
0 -15 Td
/F1 10 Tf
(' . pdfEscape($rkvComment) . ') Tj';
}

$pdf_content .= '

0 -40 Td
/F2 12 Tf
(RKV-ANSVARLIG) Tj
0 -15 Td
/F1 11 Tf
(Navn: ' . pdfEscape($responsible_name) . ') Tj
0 -15 Td
(Afdeling: ' . pdfEscape($responsible_dept) . ') Tj
0 -15 Td
(Email: ' . pdfEscape($responsible_email) . ') Tj
0 -15 Td
(Telefon: ' . pdfEscape($responsible_phone) . ') Tj
0 -15 Td
(Dato: ' . date('d-m-Y') . ') Tj

0 -30 Td
/F2 11 Tf
(Digital signatur: ' . pdfEscape($responsible_name) . ') Tj

0 -40 Td
/F2 10 Tf
(KLAGEVEJLEDNING) Tj
0 -12 Td
/F1 8 Tf
(Du kan klage over skolens afgorelse om anerkendelse af realkompetence) Tj
0 -10 Td
(til Undervisningsministeriet jf. paragraf 142, stk. 2, i bekendtgorelse) Tj
0 -10 Td
(nr. 2499 af 13.12.2021 om erhvervsuddannelser. Klagen skal vare) Tj
0 -10 Td
(modtaget pa skolen senest 4 uger efter, at du har faet skolens afgorelse.) Tj
ET
endstream
endobj

xref
0 9
0000000000 65535 f 
0000000010 00000 n 
0000000062 00000 n 
0000000125 00000 n 
0000000269 00000 n 
0000000413 00000 n 
0000002467 00000 n 
0000002534 00000 n 
0000002606 00000 n 
trailer
<<
/Size 9
/Root 1 0 R
>>
startxref
4160
%%EOF';

echo $pdf_content;
exit;
