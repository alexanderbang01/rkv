<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("Location: ../login/");
    exit;
}

// Include database connection
require_once('../database/db_conn.php');

// Get user data from session
$userName = $_SESSION['name'] ?? 'Bruger';
$userDepartment = $_SESSION['department'] ?? '';

// Fetch all education titles for dropdown
$stmt = $conn->prepare("SELECT et.id, et.title, et.link FROM educationtitle et ORDER BY et.title");
$stmt->execute();
$educationTitles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="da">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RKV Formular</title>
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/8224/8224757.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/e0d52d3d3c.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .step-enter {
            animation: slideInRight 0.4s ease-out;
        }

        .step-exit {
            animation: slideInLeft 0.4s ease-out;
        }

        .progress-bar {
            transition: width 0.5s ease;
        }

        .form-card {
            min-height: 600px;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }

        .input-error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="../" class="flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span class="hidden sm:inline">Tilbage til Dashboard</span>
                        <span class="sm:hidden">Tilbage</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($userName); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($userDepartment); ?></p>
                        </div>
                        <div class="sm:hidden">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(explode(' ', $userName)[0]); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Progress Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Realkompetencevurdering</h2>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                <div id="progressBar" class="bg-blue-600 h-2 rounded-full progress-bar" style="width: 10%"></div>
            </div>

            <!-- Step Indicators -->
            <div class="flex justify-between text-xs text-gray-500">
                <span class="step-indicator active" data-step="1">Personligt</span>
                <span class="step-indicator" data-step="2">2 års erfaring</span>
                <span class="step-indicator" data-step="3">Afkortning</span>
                <span class="step-indicator" data-step="4">Uddannelse</span>
                <span class="step-indicator" data-step="5">Vurdering</span>
                <span class="step-indicator" data-step="6">EUV</span>
                <span class="step-indicator" data-step="7">Overblik</span>
                <span class="step-indicator" data-step="8">Længde</span>
                <span class="step-indicator" data-step="9">Fordeling</span>
                <span class="step-indicator" data-step="10">Færdig</span>
            </div>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-lg shadow-sm">
            <form id="rkvForm" class="form-card">

                <!-- STEP 1: Personlige Oplysninger -->
                <div id="step1" class="step active p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Personlige Oplysninger</h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Navn <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Fulde navn">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                CPR-nummer <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="cpr" name="cpr" required maxlength="11"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="DDMMÅÅ-XXXX">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mobilnummer <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="12345678">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="elev@email.dk">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Uddannelsesspeciale <span class="text-red-500">*</span>
                        </label>
                        <select id="education" name="education" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Vælg uddannelse</option>
                            <?php foreach ($educationTitles as $title): ?>
                                <option value="<?php echo htmlspecialchars($title['id']); ?>"
                                    data-title="<?php echo htmlspecialchars($title['title']); ?>"
                                    data-link="<?php echo htmlspecialchars($title['link']); ?>">
                                    <?php echo htmlspecialchars($title['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Loading indicator for education specializations -->
                        <div id="educationLoading" class="hidden mt-2 text-sm text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Henter specialiseringer...
                        </div>
                    </div>

                    <!-- Education Specialization Dropdown (populated via AJAX) -->
                    <div id="specializationContainer" class="mt-4 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Vælg specialisering <span class="text-red-500">*</span>
                        </label>
                        <select id="specialization" name="specialization"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Vælg specialisering</option>
                        </select>
                    </div>

                    <!-- Education Order Link -->
                    <div id="educationOrderContainer" class="mt-4 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Uddannelsesbekendtgørelse</label>
                        <a id="educationOrderLink" href="#" target="_blank" class="text-blue-600 hover:text-blue-800 underline text-sm">
                            Se uddannelsesbekendtgørelse
                        </a>
                    </div>

                    <div class="mt-6 flex items-center space-x-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="euxEducation" name="euxEducation" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">EUX-uddannelse</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="newMentor" name="newMentor" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Ny mesterlære</span>
                        </label>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Har gymnasial baggrund <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="gymBackground" value="yes" class="text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Ja</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="gymBackground" value="no" class="text-blue-600 focus:ring-blue-500" checked>
                                <span class="ml-2 text-sm text-gray-700">Nej</span>
                            </label>
                        </div>
                        <div id="gymDetails" class="mt-4 hidden">
                            <input type="text" id="gymDescription" name="gymDescription"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Beskriv gymnasial baggrund (HHX, HTX, STX, osv.)">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Har tidligere erhvervsuddannelse <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="previousEducation" value="yes" class="text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Ja</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="previousEducation" value="no" class="text-blue-600 focus:ring-blue-500" checked>
                                <span class="ml-2 text-sm text-gray-700">Nej</span>
                            </label>
                        </div>
                        <div id="previousEducationDetails" class="mt-4 hidden">
                            <input type="text" id="previousEducationDescription" name="previousEducationDescription"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Hvilken tidligere uddannelse?">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Opfylder adgangskrav <span class="text-red-500">*</span>
                        </label>
                        <div id="accessRequirements" class="flex flex-wrap gap-2">
                            <label class="flex items-center bg-blue-50 border border-blue-200 rounded-lg px-2 sm:px-3 py-2">
                                <input type="checkbox" name="accessRequirement" value="da" class="text-blue-600 focus:ring-blue-500" checked>
                                <span class="ml-2 text-sm text-gray-700">Da</span>
                            </label>
                            <label class="flex items-center bg-blue-50 border border-blue-200 rounded-lg px-2 sm:px-3 py-2">
                                <input type="checkbox" name="accessRequirement" value="ma" class="text-blue-600 focus:ring-blue-500" checked>
                                <span class="ml-2 text-sm text-gray-700">Ma</span>
                            </label>
                            <button type="button" id="addSubject" class="flex items-center bg-gray-50 border border-gray-300 rounded-lg px-2 sm:px-3 py-2 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-plus text-gray-600 mr-2"></i>
                                <span class="text-sm text-gray-700">Tilføj fag</span>
                            </button>
                        </div>
                        <div id="newSubjectInput" class="mt-3 hidden">
                            <div class="flex gap-2">
                                <input type="text" id="subjectName" placeholder="Fagnavn"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <button type="button" id="confirmAddSubject" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                    Tilføj
                                </button>
                                <button type="button" id="cancelAddSubject" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                                    Annuller
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: 2 års erhvervserfaring -->
                <div id="step2" class="step p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">A) Vurdering af 2 års relevant erhvervserfaring (EUV1)</h3>
                    <p class="text-sm text-gray-600 mb-6">Uddannelsesbekendtgørelsens bilag 1, skema 1 (Hvis der er fastsat forældelse, medregnes kun erhvervserfaring, som stadig er gældende)</p>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Tilføj erhvervserfaring</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Relevant erhvervserfaring</label>
                                <input type="text" id="newExperience"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Beskriv erhvervserfaring">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Skolens vurdering</label>
                                <select id="newExperienceRating"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Vælg vurdering</option>
                                    <option value="Ja">Ja</option>
                                    <option value="Delvist">Delvist</option>
                                    <option value="Nej">Nej</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" id="addExperience" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Tilføj
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div id="experienceEntries">
                            <!-- Dynamic entries will be added here -->
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Afkortning erhvervserfaring -->
                <div id="step3" class="step p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">B) Relevant erhvervserfaring til afkortning ud over standardforløb</h3>
                    <p class="text-sm text-gray-600 mb-6">Uddannelsesbekendtgørelsens bilag 1, skema 2 (Hvis der er fastsat forældelse, medregnes kun erhvervserfaring, som stadig er gældende)</p>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Tilføj afkortning</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Erhvervserfaring</label>
                                <input type="text" id="newShortExperience"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Erhvervserfaring">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Skolens vurdering</label>
                                <select id="newShortRating"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Vælg vurdering</option>
                                    <option value="Ja">Ja</option>
                                    <option value="Delvist">Delvist</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning skole (uger)</label>
                                <input type="number" id="newShortSchool"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning praktik (mdr.)</label>
                                <input type="number" id="newShortPractice"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0">
                            </div>
                        </div>
                        <button type="button" id="addShortening" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Tilføj
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div id="shorteningEntries">
                            <!-- Dynamic entries will be added here -->
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Relevant uddannelse -->
                <div id="step4" class="step p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">C) Relevant uddannelse til afkortning ud over standardforløb</h3>
                    <p class="text-sm text-gray-600 mb-6">Uddannelsesbekendtgørelsens bilag 1, skema 3</p>

                    <div class="space-y-4">
                        <!-- EUD -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">EUD - Betegnelse</label>
                                <input type="text" name="eudDesignation"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning skole (uger)</label>
                                <input type="number" name="eudSchoolShortening"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning praktik (mdr.)</label>
                                <input type="number" name="eudPracticeShortening"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- AMU -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">AMU - Betegnelse</label>
                                <input type="text" name="amuDesignation"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning skole (uger)</label>
                                <input type="number" name="amuSchoolShortening"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning praktik (mdr.)</label>
                                <input type="number" name="amuPracticeShortening"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- UVM-fag -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">UVM-fag - Betegnelse</label>
                                <input type="text" name="uvmDesignation"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning skole (uger)</label>
                                <input type="number" name="uvmSchoolShortening"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning praktik (mdr.)</label>
                                <input type="number" name="uvmPracticeShortening"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Andet -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Andet - Betegnelse</label>
                                <input type="text" name="otherDesignation"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning skole (uger)</label>
                                <input type="number" name="otherSchoolShortening"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning praktik (mdr.)</label>
                                <input type="number" name="otherPracticeShortening"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: Skolens vurdering -->
                <div id="step5" class="step p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">D) Skolens skønsmæssige vurdering</h3>
                    <p class="text-sm text-gray-600 mb-6">Ud fra individuel RKV, herunder afprøvning. Bemærk: Det er det faglige udvalg, der afgør, om du kan få yderligere afkortning af oplæringstiden end det, der står i meritbilaget.</p>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Tilføj vurdering</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grundlag</label>
                                <input type="text" id="newAssessmentBasis"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Grundlag for vurdering">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Teoretiske kompetencer</label>
                                <input type="text" id="newAssessmentCompetencies"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Kompetencer">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Afkortning skole (uger)</label>
                                <input type="number" id="newAssessmentShortening"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0">
                            </div>
                        </div>
                        <button type="button" id="addAssessment" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Tilføj
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div id="assessmentEntries">
                            <!-- Dynamic entries will be added here -->
                        </div>
                    </div>
                </div>

                <!-- STEP 6: EUV betegnelse -->
                <div id="step6" class="step p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">EUV-betegnelse på baggrund af realkompetencevurderingen</h3>

                    <div class="space-y-4">
                        <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="euvDesignation" value="euv1" class="text-blue-600 focus:ring-blue-500">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">EUV1</div>
                                <div class="text-sm text-gray-600">Mindst 2 års relevant erhvervserfaring</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="euvDesignation" value="euv2" class="text-blue-600 focus:ring-blue-500">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">EUV2</div>
                                <div class="text-sm text-gray-600">Mindre end 2 års relevant erhvervserfaring eller har forudgående uddannelse</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="euvDesignation" value="euv3" class="text-blue-600 focus:ring-blue-500" checked>
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">EUV3</div>
                                <div class="text-sm text-gray-600">Uden relevant erhvervserfaring eller forudgående uddannelse</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- STEP 7: Uddannelsesoverblik -->
                <div id="step7" class="step p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Overblik over uddannelse</h3>

                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h4 class="font-medium text-blue-900 mb-2">Valgt uddannelse</h4>
                        <p id="selectedEducationDisplay" class="text-blue-700">Vises automatisk baseret på valg fra step 1</p>
                        <p id="selectedSpecializationDisplay" class="text-blue-600 text-sm mt-1"></p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Uddannelsesbekendtgørelse</h4>
                        <div id="educationOrderOverview">
                            <p class="text-sm text-gray-600">Vælg først en uddannelse for at se bekendtgørelse</p>
                        </div>
                    </div>

                    <!-- Education Overview Table -->
                    <div id="educationOverviewTable" class="hidden">
                        <h4 class="font-medium text-gray-900 mb-3">Uddannelsesspecialiseringer</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 border border-gray-300 text-left text-sm font-medium text-gray-700">Specialisering</th>
                                        <th class="px-4 py-2 border border-gray-300 text-left text-sm font-medium text-gray-700">Normeret længde</th>
                                        <th id="euxColumnHeader" class="px-4 py-2 border border-gray-300 text-left text-sm font-medium text-gray-700 hidden">EUX længde</th>
                                    </tr>
                                </thead>
                                <tbody id="educationOverviewBody">
                                    <!-- Will be populated via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- STEP 8: Uddannelseslængde -->
                <div id="step8" class="step p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Uddannelseslængde på baggrund af realkompetencevurdering</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Afkortning i alt <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="totalShortening" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Antal måneder/uger">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Afkortet uddannelseslængde <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="shortenedDuration" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Ny samlet længde">
                        </div>
                    </div>
                </div>

                <!-- STEP 9: Fordeling af uddannelseslængde -->
                <div id="step9" class="step p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Den afkortede uddannelseslængde er fordelt således</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                GF2 på skole <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="gf2Duration" required
                                    class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0">
                                <span class="absolute right-3 top-2 text-sm text-gray-500">uger</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Hovedforløb på skole <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="mainCourseDuration" required
                                    class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0">
                                <span class="absolute right-3 top-2 text-sm text-gray-500">uger</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Oplæring i virksomhed <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="companyTrainingDuration" required
                                    class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0">
                                <span class="absolute right-3 top-2 text-sm text-gray-500">uger</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 10: Færdiggørelse -->
                <div id="step10" class="step p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Afslutning og samtykke</h3>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Der er samtykke til, at resultatet af RKV kan videregives til oplæringsvirksomhed?
                            </label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="consentSharing" value="yes" class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Ja</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="consentSharing" value="no" class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Nej</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Ønske om specialpædagogisk støtte (SPS)?
                            </label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="spsWish" value="yes" class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Ja</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="spsWish" value="no" class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Nej</span>
                                </label>
                            </div>
                            <div id="spsNote" class="mt-2 hidden">
                                <p class="text-sm text-orange-600 font-medium">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Kontakt skolens SPS-afdeling
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Tilføj kommentar til RKV?
                            </label>
                            <div class="flex items-center space-x-4 mb-3">
                                <label class="flex items-center">
                                    <input type="radio" name="addComment" value="yes" class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Ja</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="addComment" value="no" class="text-blue-600 focus:ring-blue-500" checked>
                                    <span class="ml-2 text-sm text-gray-700">Nej</span>
                                </label>
                            </div>
                            <div id="commentSection" class="hidden">
                                <textarea name="rkvComment" rows="4" maxlength="250"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                    placeholder="Skriv din kommentar her..."></textarea>
                                <div class="text-right text-sm text-gray-500 mt-1">
                                    <span id="commentCount">0</span>/250 tegn
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h4 class="font-medium text-gray-900 mb-4">RKV-ansvarlig</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Navn</label>
                                    <input type="text" name="responsibleName" value="<?php echo htmlspecialchars($userName); ?>" readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Afdeling</label>
                                    <input type="text" name="responsibleDepartment" value="<?php echo htmlspecialchars($userDepartment); ?>" readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" name="responsibleEmail" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                                    <input type="tel" name="responsiblePhone" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>" readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h5 class="font-medium text-gray-900 mb-2">Klagevejledning</h5>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Du kan klage over skolens afgørelse om anerkendelse af realkompetence til Undervisningsministeriet jf. § 142, stk. 2, i bekendtgørelse nr. 2499 af 13.12.2021 om erhvervsuddannelser. Klagen skal være modtaget på skolen senest 4 uger efter, at du har fået skolens afgørelse.
                            </p>
                        </div>
                    </div>
                </div>

            </form>

            <!-- Navigation Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-between p-4 sm:p-6 border-t bg-gray-50 gap-4 sm:gap-0">
                <button type="button" id="prevBtn" class="w-full sm:w-auto flex items-center justify-center px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors order-2 sm:order-1" disabled>
                    <i class="fas fa-arrow-left mr-2"></i>
                    Forrige
                </button>

                <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-3 order-1 sm:order-2 w-full sm:w-auto">
                    <button type="button" id="nextBtn" class="w-full sm:w-auto flex items-center justify-center px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Næste
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>

                    <button type="submit" id="submitBtn" class="hidden w-full sm:w-auto flex items-center justify-center px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Generer PDF
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Save Notification -->
    <div id="saveNotification" class="hidden fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg z-50">
        <i class="fas fa-check mr-2"></i>
        Besked!
    </div>

    <script>
        // Form state management
        let currentStep = 1;
        const totalSteps = 10;
        let formData = {};

        // Dynamic data arrays
        let experienceEntries = [];
        let shorteningEntries = [];
        let assessmentEntries = [];

        // Current education data
        let currentEducationData = null;

        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            initializeForm();
            attachEventListeners();
        });

        function initializeForm() {
            updateProgressBar();
            updateButtons();
            updateStepDisplay();
        }

        function attachEventListeners() {
            // Navigation buttons
            document.getElementById('nextBtn').addEventListener('click', nextStep);
            document.getElementById('prevBtn').addEventListener('click', prevStep);
            document.getElementById('submitBtn').addEventListener('click', submitForm);

            // Dynamic entry buttons
            document.getElementById('addExperience').addEventListener('click', addExperienceEntry);
            document.getElementById('addShortening').addEventListener('click', addShorteningEntry);
            document.getElementById('addAssessment').addEventListener('click', addAssessmentEntry);

            // Education dropdown change handler
            document.getElementById('education').addEventListener('change', handleEducationChange);

            // EUX checkbox handler for updating overview
            document.getElementById('euxEducation').addEventListener('change', updateEducationOverview);

            // Conditional field handlers
            document.querySelectorAll('input[name="gymBackground"]').forEach(radio => {
                radio.addEventListener('change', toggleGymDetails);
            });

            document.querySelectorAll('input[name="previousEducation"]').forEach(radio => {
                radio.addEventListener('change', togglePreviousEducationDetails);
            });

            document.querySelectorAll('input[name="spsWish"]').forEach(radio => {
                radio.addEventListener('change', toggleSpsNote);
            });

            document.querySelectorAll('input[name="addComment"]').forEach(radio => {
                radio.addEventListener('change', toggleCommentSection);
            });

            // Subject management
            document.getElementById('addSubject').addEventListener('click', showSubjectInput);
            document.getElementById('confirmAddSubject').addEventListener('click', addNewSubject);
            document.getElementById('cancelAddSubject').addEventListener('click', hideSubjectInput);

            // Comment counter
            const commentTextarea = document.querySelector('textarea[name="rkvComment"]');
            if (commentTextarea) {
                commentTextarea.addEventListener('input', updateCommentCount);
            }

            // CPR formatting
            document.getElementById('cpr').addEventListener('input', formatCPR);

            // Phone formatting
            document.getElementById('phone').addEventListener('input', formatPhone);
        }

        // Education handling functions
        function handleEducationChange() {
            const educationSelect = document.getElementById('education');
            const selectedOption = educationSelect.options[educationSelect.selectedIndex];

            if (selectedOption.value) {
                const educationId = selectedOption.value;
                const educationTitle = selectedOption.getAttribute('data-title');
                const educationLink = selectedOption.getAttribute('data-link');

                // Show loading indicator
                document.getElementById('educationLoading').classList.remove('hidden');

                // Update education order link
                updateEducationOrderLink(educationLink);

                // Fetch specializations via AJAX
                fetchEducationSpecializations(educationId, educationTitle);

                // Update displays
                updateEducationDisplays(educationTitle);
            } else {
                // Hide specialization dropdown and education order
                hideEducationDetails();
            }
        }

        function updateEducationOrderLink(link) {
            const container = document.getElementById('educationOrderContainer');
            const linkElement = document.getElementById('educationOrderLink');

            if (link) {
                linkElement.href = link;
                linkElement.textContent = link; // Show the actual URL
                container.classList.remove('hidden');

                // Update overview section as well
                const overviewContainer = document.getElementById('educationOrderOverview');
                overviewContainer.innerHTML = `
                    <a href="${link}" target="_blank" class="text-blue-600 hover:text-blue-800 underline text-sm break-all">
                        ${link}
                    </a>
                `;
            } else {
                container.classList.add('hidden');
            }
        }

        function fetchEducationSpecializations(educationId, educationTitle) {
            fetch(`../api/get_edu.php?eduId=${educationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateSpecializationDropdown(data.specializations);
                        currentEducationData = {
                            id: educationId,
                            title: educationTitle,
                            specializations: data.specializations
                        };
                        updateEducationOverview();
                    } else {
                        console.error('Error fetching specializations:', data.message);
                        showNotification('Fejl ved hentning af specialiseringer', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Netværksfejl ved hentning af specialiseringer', 'error');
                })
                .finally(() => {
                    document.getElementById('educationLoading').classList.add('hidden');
                });
        }

        function populateSpecializationDropdown(specializations) {
            const specializationSelect = document.getElementById('specialization');
            const container = document.getElementById('specializationContainer');

            // Clear existing options
            specializationSelect.innerHTML = '<option value="">Vælg specialisering</option>';

            // Add specializations
            specializations.forEach(spec => {
                const option = document.createElement('option');
                option.value = spec.id;
                option.textContent = spec.name;
                option.setAttribute('data-length', spec.length);
                option.setAttribute('data-eux-length', spec.euxLength);
                specializationSelect.appendChild(option);
            });

            // Show container
            container.classList.remove('hidden');

            // Add change handler for specialization
            specializationSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const specName = selectedOption.textContent;
                    document.getElementById('selectedSpecializationDisplay').textContent = `Specialisering: ${specName}`;
                } else {
                    document.getElementById('selectedSpecializationDisplay').textContent = '';
                }
            });
        }

        function updateEducationDisplays(educationTitle) {
            document.getElementById('selectedEducationDisplay').textContent = educationTitle;
        }

        function updateEducationOverview() {
            if (!currentEducationData) return;

            const isEux = document.getElementById('euxEducation').checked;
            const tableContainer = document.getElementById('educationOverviewTable');
            const tableBody = document.getElementById('educationOverviewBody');
            const euxColumn = document.getElementById('euxColumnHeader');

            // Show/hide EUX column
            euxColumn.classList.toggle('hidden', !isEux);

            // Clear existing rows
            tableBody.innerHTML = '';

            // Add specialization rows
            currentEducationData.specializations.forEach(spec => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-4 py-2 border border-gray-300 text-sm">${spec.name}</td>
                    <td class="px-4 py-2 border border-gray-300 text-sm">${spec.length}</td>
                    ${isEux ? `<td class="px-4 py-2 border border-gray-300 text-sm">${spec.euxLength}</td>` : ''}
                `;
                tableBody.appendChild(row);
            });

            // Show table
            tableContainer.classList.remove('hidden');
        }

        function hideEducationDetails() {
            document.getElementById('specializationContainer').classList.add('hidden');
            document.getElementById('educationOrderContainer').classList.add('hidden');
            document.getElementById('educationOverviewTable').classList.add('hidden');
            document.getElementById('selectedEducationDisplay').textContent = 'Vises automatisk baseret på valg fra step 1';
            document.getElementById('selectedSpecializationDisplay').textContent = '';
            document.getElementById('educationOrderOverview').innerHTML = '<p class="text-sm text-gray-600">Vælg først en uddannelse for at se bekendtgørelse</p>';
            currentEducationData = null;
        }

        // Subject management functions
        function showSubjectInput() {
            document.getElementById('newSubjectInput').classList.remove('hidden');
            document.getElementById('subjectName').focus();
        }

        function hideSubjectInput() {
            document.getElementById('newSubjectInput').classList.add('hidden');
            document.getElementById('subjectName').value = '';
        }

        function addNewSubject() {
            const subjectName = document.getElementById('subjectName').value.trim();
            if (subjectName) {
                const container = document.getElementById('accessRequirements');
                const addButton = document.getElementById('addSubject');

                const newSubject = document.createElement('label');
                newSubject.className = 'flex items-center bg-blue-50 border border-blue-200 rounded-lg px-3 py-2';
                newSubject.innerHTML = `
                    <input type="checkbox" name="accessRequirement" value="${subjectName.toLowerCase()}" class="text-blue-600 focus:ring-blue-500" checked>
                    <span class="ml-2 text-sm text-gray-700">${subjectName}</span>
                    <button type="button" class="ml-2 text-red-500 hover:text-red-700" onclick="removeSubject(this)">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                `;

                container.insertBefore(newSubject, addButton);
                hideSubjectInput();
            }
        }

        function removeSubject(button) {
            button.parentElement.remove();
        }

        // Navigation functions
        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                    updateProgressBar();
                    updateButtons();
                    scrollToTop();
                }
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
                updateProgressBar();
                updateButtons();
                scrollToTop();
            }
        }

        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.step').forEach(stepEl => {
                stepEl.classList.remove('active');
            });

            // Show current step
            document.getElementById(`step${step}`).classList.add('active');

            // Update step indicators
            document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                indicator.classList.toggle('active', index + 1 <= step);
            });
        }

        function updateStepDisplay() {
            // This function exists but doesn't do anything now since we removed the counter
        }

        function updateProgressBar() {
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }

        function updateButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');

            prevBtn.disabled = currentStep === 1;
            prevBtn.classList.toggle('btn-disabled', currentStep === 1);

            if (currentStep === totalSteps) {
                nextBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            } else {
                nextBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        function validateCurrentStep() {
            const currentStepEl = document.getElementById(`step${currentStep}`);
            const requiredFields = currentStepEl.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('input-error');
                    isValid = false;
                } else {
                    field.classList.remove('input-error');
                }
            });

            if (!isValid) {
                showNotification('Udfyld venligst alle obligatoriske felter', 'error');
            }

            return isValid;
        }

        // Dynamic entry functions
        function addExperienceEntry() {
            const experience = document.getElementById('newExperience').value;
            const rating = document.getElementById('newExperienceRating').value;

            if (experience && rating) {
                experienceEntries.push({
                    experience,
                    rating
                });
                renderExperienceEntries();

                // Clear inputs
                document.getElementById('newExperience').value = '';
                document.getElementById('newExperienceRating').value = '';
            }
        }

        function renderExperienceEntries() {
            const container = document.getElementById('experienceEntries');
            container.innerHTML = '';

            experienceEntries.forEach((entry, index) => {
                const entryEl = document.createElement('div');
                entryEl.className = 'flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg mb-3';
                entryEl.innerHTML = `
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">${entry.experience}</div>
                        <div class="text-sm text-gray-600">Vurdering: ${entry.rating}</div>
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-800" onclick="removeExperienceEntry(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                container.appendChild(entryEl);
            });
        }

        function removeExperienceEntry(index) {
            experienceEntries.splice(index, 1);
            renderExperienceEntries();
        }

        function addShorteningEntry() {
            const experience = document.getElementById('newShortExperience').value;
            const rating = document.getElementById('newShortRating').value;
            const schoolWeeks = document.getElementById('newShortSchool').value;
            const practiceMonths = document.getElementById('newShortPractice').value;

            if (experience && rating) {
                shorteningEntries.push({
                    experience,
                    rating,
                    schoolWeeks,
                    practiceMonths
                });
                renderShorteningEntries();

                // Clear inputs
                document.getElementById('newShortExperience').value = '';
                document.getElementById('newShortRating').value = '';
                document.getElementById('newShortSchool').value = '';
                document.getElementById('newShortPractice').value = '';
            }
        }

        function renderShorteningEntries() {
            const container = document.getElementById('shorteningEntries');
            container.innerHTML = '';

            shorteningEntries.forEach((entry, index) => {
                const entryEl = document.createElement('div');
                entryEl.className = 'flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg mb-3';
                entryEl.innerHTML = `
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">${entry.experience}</div>
                        <div class="text-sm text-gray-600">
                            Vurdering: ${entry.rating} | Skole: ${entry.schoolWeeks} uger | Praktik: ${entry.practiceMonths} mdr.
                        </div>
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-800" onclick="removeShorteningEntry(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                container.appendChild(entryEl);
            });
        }

        function removeShorteningEntry(index) {
            shorteningEntries.splice(index, 1);
            renderShorteningEntries();
        }

        function addAssessmentEntry() {
            const basis = document.getElementById('newAssessmentBasis').value;
            const competencies = document.getElementById('newAssessmentCompetencies').value;
            const shortening = document.getElementById('newAssessmentShortening').value;

            if (basis && competencies) {
                assessmentEntries.push({
                    basis,
                    competencies,
                    shortening
                });
                renderAssessmentEntries();

                // Clear inputs
                document.getElementById('newAssessmentBasis').value = '';
                document.getElementById('newAssessmentCompetencies').value = '';
                document.getElementById('newAssessmentShortening').value = '';
            }
        }

        function renderAssessmentEntries() {
            const container = document.getElementById('assessmentEntries');
            container.innerHTML = '';

            assessmentEntries.forEach((entry, index) => {
                const entryEl = document.createElement('div');
                entryEl.className = 'flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg mb-3';
                entryEl.innerHTML = `
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">${entry.basis}</div>
                        <div class="text-sm text-gray-600">
                            Kompetencer: ${entry.competencies} | Afkortning: ${entry.shortening} uger
                        </div>
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-800" onclick="removeAssessmentEntry(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                container.appendChild(entryEl);
            });
        }

        function removeAssessmentEntry(index) {
            assessmentEntries.splice(index, 1);
            renderAssessmentEntries();
        }

        // Conditional field toggles
        function toggleGymDetails() {
            const gymDetails = document.getElementById('gymDetails');
            const selected = document.querySelector('input[name="gymBackground"]:checked').value;
            gymDetails.classList.toggle('hidden', selected !== 'yes');
        }

        function togglePreviousEducationDetails() {
            const details = document.getElementById('previousEducationDetails');
            const selected = document.querySelector('input[name="previousEducation"]:checked').value;
            details.classList.toggle('hidden', selected !== 'yes');
        }

        function toggleSpsNote() {
            const note = document.getElementById('spsNote');
            const selected = document.querySelector('input[name="spsWish"]:checked')?.value;
            note.classList.toggle('hidden', selected !== 'yes');
        }

        function toggleCommentSection() {
            const section = document.getElementById('commentSection');
            const selected = document.querySelector('input[name="addComment"]:checked').value;
            section.classList.toggle('hidden', selected !== 'yes');
        }

        function updateCommentCount() {
            const textarea = document.querySelector('textarea[name="rkvComment"]');
            const counter = document.getElementById('commentCount');
            counter.textContent = textarea.value.length;
        }

        // Formatting functions
        function formatCPR() {
            const input = document.getElementById('cpr');
            let value = input.value.replace(/\D/g, '').substring(0, 10);
            if (value.length > 6) {
                value = value.substring(0, 6) + '-' + value.substring(6);
            }
            input.value = value;
        }

        function formatPhone() {
            const input = document.getElementById('phone');
            let value = input.value.replace(/\D/g, '').substring(0, 8);
            input.value = value;
        }

        function submitForm() {
            if (validateCurrentStep()) {
                // Show loading state
                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Genererer PDF...';
                submitBtn.disabled = true;

                // Create a form and submit it directly to download PDF
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../api/test_pdf.php';
                form.style.display = 'none';

                // Add all form data as hidden inputs
                const formData = new FormData(document.getElementById('rkvForm'));
                for (let [key, value] of formData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }

                // Add dynamic entries
                const experienceInput = document.createElement('input');
                experienceInput.type = 'hidden';
                experienceInput.name = 'experienceEntries';
                experienceInput.value = JSON.stringify(experienceEntries);
                form.appendChild(experienceInput);

                const shorteningInput = document.createElement('input');
                shorteningInput.type = 'hidden';
                shorteningInput.name = 'shorteningEntries';
                shorteningInput.value = JSON.stringify(shorteningEntries);
                form.appendChild(shorteningInput);

                const assessmentInput = document.createElement('input');
                assessmentInput.type = 'hidden';
                assessmentInput.name = 'assessmentEntries';
                assessmentInput.value = JSON.stringify(assessmentEntries);
                form.appendChild(assessmentInput);

                // Submit form to trigger download
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);

                // Reset button and show success message
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    showNotification('PDF genereret og downloadet!', 'success');

                    // Redirect after successful generation
                    setTimeout(() => {
                        window.location.href = '../';
                    }, 2000);
                }, 1000);
            }
        }

        // Utility functions
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('saveNotification');

            const iconMap = {
                success: 'fa-check',
                error: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            const colorMap = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                info: 'bg-blue-500 text-white'
            };

            notification.innerHTML = `<i class="fas ${iconMap[type]} mr-2"></i>${message}`;
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md shadow-lg z-50 ${colorMap[type]}`;

            // Show notification
            notification.classList.remove('hidden');

            // Hide after 3 seconds
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 3000);
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        console.log('RKV Form initialized successfully!');
    </script>
</body>

</html>