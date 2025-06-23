<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Include database connection
require_once('../database/db_conn.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method allowed'
    ]);
    exit;
}

try {
    // Get form data
    $name = $_POST['name'] ?? '';
    $cpr = $_POST['cpr'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $education = $_POST['education'] ?? '';
    $specialization = $_POST['specialization'] ?? '';

    // Get dynamic entries
    $experienceEntries = json_decode($_POST['experienceEntries'] ?? '[]', true);
    $shorteningEntries = json_decode($_POST['shorteningEntries'] ?? '[]', true);
    $assessmentEntries = json_decode($_POST['assessmentEntries'] ?? '[]', true);

    // Get education data from database
    $educationData = null;
    $specializationData = null;
    $educationLink = '';

    if ($education) {
        // Get education title and link
        $stmt = $conn->prepare("SELECT title, link FROM educationtitle WHERE id = ?");
        $stmt->bind_param('i', $education);
        $stmt->execute();
        $result = $stmt->get_result();
        $educationData = $result->fetch_assoc();
        $stmt->close();

        if ($specialization) {
            // Get specialization data
            $stmt = $conn->prepare("SELECT name, length, euxLength FROM education WHERE id = ?");
            $stmt->bind_param('i', $specialization);
            $stmt->execute();
            $result = $stmt->get_result();
            $specializationData = $result->fetch_assoc();
            $stmt->close();
        }
    }

    // Update user's RKV count
    $userId = $_SESSION['id'];
    $stmt = $conn->prepare("UPDATE users SET amount = amount + 1 WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();

    // Log RKV activity
    if ($educationData) {
        $stmt = $conn->prepare("INSERT INTO rkv_activities (user_id, student_name, education_title_id, action_type, description) VALUES (?, ?, ?, 'created', ?)");
        $description = "RKV oprettet for " . $educationData['title'];
        $stmt->bind_param('isis', $userId, $name, $education, $description);
        $stmt->execute();
        $stmt->close();
    }

    // Generate filename
    $filename = "rkv-" . str_replace([' ', 'æ', 'ø', 'å'], ['-', 'ae', 'oe', 'aa'], strtolower($name)) . "-" . date('Y-m-d') . ".pdf";

    // Generate HTML for PDF
    $html = generatePDFHTML([
        'name' => $name,
        'cpr' => $cpr,
        'phone' => $phone,
        'email' => $email,
        'education' => $educationData,
        'specialization' => $specializationData,
        'experienceEntries' => $experienceEntries,
        'shorteningEntries' => $shorteningEntries,
        'assessmentEntries' => $assessmentEntries,
        'formData' => $_POST,
        'responsible' => [
            'name' => $_SESSION['name'],
            'department' => $_SESSION['department'],
            'email' => $_SESSION['email'],
            'phone' => $_SESSION['phone']
        ],
        'date' => date('d.m.Y')
    ]);

    // Return HTML and filename for client-side PDF generation
    echo json_encode([
        'success' => true,
        'html' => $html,
        'filename' => $filename,
        'message' => 'PDF data generated successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error generating PDF: ' . $e->getMessage()
    ]);
}

function generatePDFHTML($data)
{
    ob_start();
?>
    <!DOCTYPE html>
    <html lang="da">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>RKV Rapport</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                margin: 0;
                padding: 20px;
                color: #333;
            }

            .header {
                text-align: center;
                border-bottom: 2px solid #1f2937;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }

            .header h1 {
                margin: 0;
                font-size: 24px;
                color: #1f2937;
            }

            .header h2 {
                margin: 5px 0 0 0;
                font-size: 16px;
                color: #6b7280;
            }

            .info-section {
                border: 1px solid #d1d5db;
                margin-bottom: 15px;
            }

            .info-row {
                display: flex;
                border-bottom: 1px solid #d1d5db;
            }

            .info-row:last-child {
                border-bottom: none;
            }

            .info-cell {
                padding: 8px;
                flex: 1;
                border-right: 1px solid #d1d5db;
            }

            .info-cell:last-child {
                border-right: none;
            }

            .info-label {
                font-weight: bold;
                color: #374151;
            }

            .section {
                margin-bottom: 20px;
            }

            .section h3 {
                font-size: 14px;
                margin: 0 0 10px 0;
                color: #1f2937;
                border-bottom: 1px solid #d1d5db;
                padding-bottom: 5px;
            }

            .section h4 {
                font-size: 11px;
                margin: 0 0 8px 0;
                color: #6b7280;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 10px;
                font-size: 11px;
            }

            table,
            th,
            td {
                border: 1px solid #d1d5db;
            }

            th {
                background-color: #f9fafb;
                padding: 6px;
                font-weight: bold;
                text-align: left;
            }

            td {
                padding: 6px;
            }

            .checkbox {
                width: 12px;
                height: 12px;
                border: 1px solid #333;
                display: inline-block;
                text-align: center;
                line-height: 10px;
                margin-right: 5px;
            }

            .checkbox.checked::after {
                content: '✓';
                font-size: 10px;
            }

            .page-break {
                page-break-before: always;
            }

            .signature-section {
                margin-top: 30px;
                border-top: 1px solid #d1d5db;
                padding-top: 15px;
            }

            .two-column {
                display: flex;
                gap: 20px;
            }

            .column {
                flex: 1;
            }

            .highlight-box {
                background-color: #eff6ff;
                border: 1px solid #bfdbfe;
                padding: 10px;
                margin-bottom: 15px;
                border-radius: 4px;
            }

            .legal-text {
                font-size: 10px;
                color: #6b7280;
                line-height: 1.3;
                margin-top: 20px;
            }
        </style>
    </head>

    <body>
        <div class="header">
            <h1>Realkompetencevurdering</h1>
            <h2>RKV – Erhvervsuddannelse for voksne</h2>
            <p>Mercantec</p>
        </div>

        <!-- Personal Information -->
        <div class="info-section">
            <div class="info-row">
                <div class="info-cell">
                    <span class="info-label">Navn:</span> <?= htmlspecialchars($data['name']) ?>
                </div>
                <div class="info-cell">
                    <span class="info-label">CPR-nr:</span> <?= htmlspecialchars($data['cpr']) ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <span class="info-label">Mobil:</span> <?= htmlspecialchars($data['phone']) ?>
                </div>
                <div class="info-cell">
                    <span class="info-label">Email:</span> <?= htmlspecialchars($data['email']) ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <span class="info-label">Uddannelse:</span>
                    <?= $data['education'] ? htmlspecialchars($data['education']['title']) : 'Ikke valgt' ?>
                </div>
                <div class="info-cell">
                    <span class="info-label">Specialisering:</span>
                    <?= $data['specialization'] ? htmlspecialchars($data['specialization']['name']) : 'Ikke valgt' ?>
                </div>
            </div>
            <?php if ($data['education'] && $data['education']['link']): ?>
                <div class="info-row">
                    <div class="info-cell" style="flex: 2;">
                        <span class="info-label">Uddannelsesbekendtgørelse:</span>
                        <?= htmlspecialchars($data['education']['link']) ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="info-row">
                <div class="info-cell">
                    <span class="checkbox <?= isset($data['formData']['euxEducation']) ? 'checked' : '' ?>"></span>
                    <span class="info-label">EUX-uddannelse</span>
                </div>
                <div class="info-cell">
                    <span class="checkbox <?= isset($data['formData']['newMentor']) ? 'checked' : '' ?>"></span>
                    <span class="info-label">Ny mesterlære</span>
                </div>
            </div>
        </div>

        <!-- Section A: Experience Assessment -->
        <div class="section">
            <h3>A) Vurdering af 2 års relevant erhvervserfaring (EUV1)</h3>
            <h4>Uddannelsesbekendtgørelsens bilag 1, skema 1</h4>

            <table>
                <thead>
                    <tr>
                        <th style="width: 60%;">Relevant erhvervserfaring</th>
                        <th style="width: 40%;">Skolens vurdering</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['experienceEntries'])): ?>
                        <?php foreach ($data['experienceEntries'] as $entry): ?>
                            <tr>
                                <td><?= htmlspecialchars($entry['experience']) ?></td>
                                <td><?= htmlspecialchars($entry['rating']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">Ingen erhvervserfaring tilføjet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Section B: Shortening Experience -->
        <div class="section">
            <h3>B) Relevant erhvervserfaring til afkortning ud over standardforløb</h3>
            <h4>Uddannelsesbekendtgørelsens bilag 1, skema 2</h4>

            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Erhvervserfaring</th>
                        <th style="width: 25%;">Skolens vurdering</th>
                        <th style="width: 17.5%;">Afkortning skole (uger)</th>
                        <th style="width: 17.5%;">Afkortning praktik (mdr.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['shorteningEntries'])): ?>
                        <?php foreach ($data['shorteningEntries'] as $entry): ?>
                            <tr>
                                <td><?= htmlspecialchars($entry['experience']) ?></td>
                                <td><?= htmlspecialchars($entry['rating']) ?></td>
                                <td><?= htmlspecialchars($entry['schoolWeeks'] ?? '0') ?></td>
                                <td><?= htmlspecialchars($entry['practiceMonths'] ?? '0') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">Ingen afkortning tilføjet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Section C: Education Shortening -->
        <div class="section">
            <h3>C) Relevant uddannelse til afkortning ud over standardforløb</h3>
            <h4>Uddannelsesbekendtgørelsens bilag 1, skema 3</h4>

            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;">Type</th>
                        <th style="width: 35%;">Betegnelse</th>
                        <th style="width: 20%;">Afkortning skole (uger)</th>
                        <th style="width: 20%;">Afkortning praktik (mdr.)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>EUD</td>
                        <td><?= htmlspecialchars($data['formData']['eudDesignation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['formData']['eudSchoolShortening'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['formData']['eudPracticeShortening'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td>AMU</td>
                        <td><?= htmlspecialchars($data['formData']['amuDesignation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['formData']['amuSchoolShortening'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['formData']['amuPracticeShortening'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td>UVM-fag</td>
                        <td><?= htmlspecialchars($data['formData']['uvmDesignation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['formData']['uvmSchoolShortening'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['formData']['uvmPracticeShortening'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td>Andet</td>
                        <td><?= htmlspecialchars($data['formData']['otherDesignation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['formData']['otherSchoolShortening'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['formData']['otherPracticeShortening'] ?? '') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section D: Assessment -->
        <div class="section">
            <h3>D) Skolens skønsmæssige vurdering</h3>
            <h4>Ud fra individuel RKV, herunder afprøvning</h4>

            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Grundlag</th>
                        <th style="width: 40%;">Teoretiske kompetencer</th>
                        <th style="width: 20%;">Afkortning skole (uger)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['assessmentEntries'])): ?>
                        <?php foreach ($data['assessmentEntries'] as $entry): ?>
                            <tr>
                                <td><?= htmlspecialchars($entry['basis']) ?></td>
                                <td><?= htmlspecialchars($entry['competencies']) ?></td>
                                <td><?= htmlspecialchars($entry['shortening'] ?? '0') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">Ingen vurdering tilføjet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="page-break"></div>

        <!-- Results Section -->
        <div class="section">
            <h3>Resultat af realkompetencevurdering</h3>

            <div class="highlight-box">
                <h4 style="margin-top: 0;">EUV-betegnelse</h4>
                <div>
                    <span class="checkbox <?= ($data['formData']['euvDesignation'] ?? '') === 'euv1' ? 'checked' : '' ?>"></span>
                    <strong>EUV1</strong> - Mindst 2 års relevant erhvervserfaring<br>

                    <span class="checkbox <?= ($data['formData']['euvDesignation'] ?? '') === 'euv2' ? 'checked' : '' ?>"></span>
                    <strong>EUV2</strong> - Mindre end 2 års relevant erhvervserfaring eller har forudgående uddannelse<br>

                    <span class="checkbox <?= ($data['formData']['euvDesignation'] ?? '') === 'euv3' ? 'checked' : '' ?>"></span>
                    <strong>EUV3</strong> - Uden relevant erhvervserfaring eller forudgående uddannelse
                </div>
            </div>

            <div class="two-column">
                <div class="column">
                    <h4>Uddannelseslængde</h4>
                    <p><strong>Afkortning i alt:</strong> <?= htmlspecialchars($data['formData']['totalShortening'] ?? '') ?></p>
                    <p><strong>Afkortet uddannelseslængde:</strong> <?= htmlspecialchars($data['formData']['shortenedDuration'] ?? '') ?></p>
                </div>
                <div class="column">
                    <h4>Fordeling</h4>
                    <p><strong>GF2 på skole:</strong> <?= htmlspecialchars($data['formData']['gf2Duration'] ?? '') ?> uger</p>
                    <p><strong>Hovedforløb på skole:</strong> <?= htmlspecialchars($data['formData']['mainCourseDuration'] ?? '') ?> uger</p>
                    <p><strong>Oplæring i virksomhed:</strong> <?= htmlspecialchars($data['formData']['companyTrainingDuration'] ?? '') ?> uger</p>
                </div>
            </div>

            <h4>Samtykke og ønsker</h4>
            <p>
                <span class="checkbox <?= ($data['formData']['consentSharing'] ?? '') === 'yes' ? 'checked' : '' ?>"></span>
                <strong>Ja</strong>
                <span class="checkbox <?= ($data['formData']['consentSharing'] ?? '') === 'no' ? 'checked' : '' ?>"></span>
                <strong>Nej</strong>
                - Samtykke til videregvelse til oplæringsvirksomhed
            </p>
            <p>
                <span class="checkbox <?= ($data['formData']['spsWish'] ?? '') === 'yes' ? 'checked' : '' ?>"></span>
                <strong>Ja</strong>
                <span class="checkbox <?= ($data['formData']['spsWish'] ?? '') === 'no' ? 'checked' : '' ?>"></span>
                <strong>Nej</strong>
                - Ønske om specialpædagogisk støtte (SPS)
            </p>

            <?php if (($data['formData']['addComment'] ?? '') === 'yes' && !empty($data['formData']['rkvComment'])): ?>
                <div style="margin-top: 15px;">
                    <h4>Kommentar til RKV</h4>
                    <div style="border: 1px solid #d1d5db; padding: 10px; background-color: #f9fafb;">
                        <?= htmlspecialchars($data['formData']['rkvComment']) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <h4>RKV-ansvarlig</h4>
            <div class="two-column">
                <div class="column">
                    <p><strong>Navn:</strong> <?= htmlspecialchars($data['responsible']['name']) ?></p>
                    <p><strong>Afdeling:</strong> <?= htmlspecialchars($data['responsible']['department']) ?></p>
                </div>
                <div class="column">
                    <p><strong>Email:</strong> <?= htmlspecialchars($data['responsible']['email']) ?></p>
                    <p><strong>Telefon:</strong> <?= htmlspecialchars($data['responsible']['phone']) ?></p>
                </div>
            </div>

            <div style="margin-top: 20px; text-align: right;">
                <p><strong>Dato:</strong> <?= $data['date'] ?></p>
                <div style="border-top: 1px solid #333; width: 200px; margin-left: auto; margin-top: 30px; padding-top: 5px; text-align: center;">
                    <small>Digital signatur: <?= htmlspecialchars($data['responsible']['name']) ?></small>
                </div>
            </div>
        </div>

        <!-- Legal Text -->
        <div class="legal-text">
            <h5 style="margin-top: 30px;">Klagevejledning</h5>
            <p>Du kan klage over skolens afgørelse om anerkendelse af realkompetence til Undervisningsministeriet jf. § 142, stk. 2, i bekendtgørelse nr. 2499 af 13.12.2021 om erhvervsuddannelser. Klagen skal være modtaget på skolen senest 4 uger efter, at du har fået skolens afgørelse. Skolen skal beslutte, om skolen vil ændre sin afgørelse på grund af din klage. Hvis skolen ikke ændrer afgørelsen, skal skolen orientere dig om det og give dig 1 uge til at komme med dine eventuelle bemærkninger til sagen. Herefter sender skolen klagesagen til ministeriet.</p>
        </div>
    </body>

    </html>
<?php
    return ob_get_clean();
}
?></head>