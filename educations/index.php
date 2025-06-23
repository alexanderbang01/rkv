<?php
session_start();
require_once('../database/db_conn.php');

// Check if user is logged in
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("Location: ../login/");
    exit;
}

// Get user data from session
$userName = $_SESSION['name'] ?? 'Bruger';
$userDepartment = $_SESSION['department'] ?? '';

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_title':
            $title = trim($_POST['title'] ?? '');
            $link = trim($_POST['link'] ?? '');
            if (!empty($title)) {
                $stmt = $conn->prepare("INSERT INTO educationtitle (title, link) VALUES (?, ?)");
                $stmt->bind_param("ss", $title, $link);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Uddannelsestitel '$title' tilføjet succesfuldt!";
                    $_SESSION['messageType'] = 'success';
                } else {
                    $_SESSION['message'] = "Fejl ved tilføjelse af uddannelsestitel.";
                    $_SESSION['messageType'] = 'error';
                }
                $stmt->close();
            }
            header("Location: ./");
            exit;

        case 'update_title_link':
            $titleId = $_POST['title_id'] ?? '';
            $link = trim($_POST['link'] ?? '');
            if (!empty($titleId)) {
                $stmt = $conn->prepare("UPDATE educationtitle SET link = ? WHERE id = ?");
                $stmt->bind_param("si", $link, $titleId);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Bekendtgørelse opdateret succesfuldt!";
                    $_SESSION['messageType'] = 'success';
                } else {
                    $_SESSION['message'] = "Fejl ved opdatering af bekendtgørelse.";
                    $_SESSION['messageType'] = 'error';
                }
                $stmt->close();
            }
            header("Location: ./");
            exit;

        case 'add_education':
            $eduId = $_POST['eduId'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $length = trim($_POST['length'] ?? '');
            $euxLength = trim($_POST['euxLength'] ?? '');

            if (!empty($eduId) && !empty($name) && !empty($length)) {
                $stmt = $conn->prepare("INSERT INTO education (eduId, name, length, euxLength) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $eduId, $name, $length, $euxLength);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Uddannelse '$name' tilføjet succesfuldt!";
                    $_SESSION['messageType'] = 'success';
                } else {
                    $_SESSION['message'] = "Fejl ved tilføjelse af uddannelse.";
                    $_SESSION['messageType'] = 'error';
                }
                $stmt->close();
            }
            header("Location: ./");
            exit;

        case 'update_education':
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $length = trim($_POST['length'] ?? '');
            $euxLength = trim($_POST['euxLength'] ?? '');

            if (!empty($id) && !empty($name) && !empty($length)) {
                $stmt = $conn->prepare("UPDATE education SET name = ?, length = ?, euxLength = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $length, $euxLength, $id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Uddannelse opdateret succesfuldt!";
                    $_SESSION['messageType'] = 'success';
                } else {
                    $_SESSION['message'] = "Fejl ved opdatering af uddannelse.";
                    $_SESSION['messageType'] = 'error';
                }
                $stmt->close();
            }
            header("Location: ./");
            exit;

        case 'delete_education':
            $id = $_POST['id'] ?? '';
            if (!empty($id)) {
                $stmt = $conn->prepare("DELETE FROM education WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Uddannelse slettet succesfuldt!";
                    $_SESSION['messageType'] = 'success';
                } else {
                    $_SESSION['message'] = "Fejl ved sletning af uddannelse.";
                    $_SESSION['messageType'] = 'error';
                }
                $stmt->close();
            }
            header("Location: ./");
            exit;

        case 'delete_title':
            $id = $_POST['id'] ?? '';
            if (!empty($id)) {
                // Check if title has associated educations
                $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM education WHERE eduId = ?");
                $checkStmt->bind_param("i", $id);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                $row = $result->fetch_assoc();
                $checkStmt->close();

                if ($row['count'] > 0) {
                    $_SESSION['message'] = "Kan ikke slette uddannelsestitel - der er stadig tilknyttede uddannelser.";
                    $_SESSION['messageType'] = 'error';
                } else {
                    $stmt = $conn->prepare("DELETE FROM educationtitle WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "Uddannelsestitel slettet succesfuldt!";
                        $_SESSION['messageType'] = 'success';
                    } else {
                        $_SESSION['message'] = "Fejl ved sletning af uddannelsestitel.";
                        $_SESSION['messageType'] = 'error';
                    }
                    $stmt->close();
                }
            }
            header("Location: ./");
            exit;
    }
}

// Check for session messages and clear them
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

// Get education titles for dropdown
$titles = [];
$titleStmt = $conn->prepare("SELECT id, title FROM educationtitle ORDER BY title");
$titleStmt->execute();
$titleResult = $titleStmt->get_result();
while ($row = $titleResult->fetch_assoc()) {
    $titles[] = $row;
}
$titleStmt->close();
?>
<!DOCTYPE html>
<html lang="da">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uddannelser - RKV</title>
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/8224/8224757.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/e0d52d3d3c.js" crossorigin="anonymous"></script>
    <style>
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
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

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .animate-slideInDown {
            animation: slideInDown 0.6s ease-out;
        }

        .animate-fadeIn {
            animation: fadeIn 0.8s ease-out;
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 animate-slideInDown">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="../" class="flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span class="hidden sm:inline">Tilbage til Dashboard</span>
                        <span class="sm:hidden">Tilbage</span>
                    </a>
                    <div class="border-l border-gray-300 pl-4">
                        <h1 class="text-xl font-bold text-gray-900">Uddannelser</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($userName); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($userDepartment); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success/Error Message -->
        <?php if (!empty($message)): ?>
            <div class="mb-6 animate-fadeIn" id="messageAlert">
                <div class="<?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'; ?> px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Header Section with Search and Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between">
                <!-- Search Section -->
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            id="searchInput"
                            placeholder="Søg i uddannelser og titler..."
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <div id="searchLoader" class="hidden absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-spinner animate-spin text-blue-500"></i>
                        </div>
                    </div>
                    <div id="searchResults" class="text-sm text-gray-500 mt-2"></div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button id="addTitleBtn" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i>
                        Ny Uddannelsestitel
                    </button>
                    <button id="addEducationBtn" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Ny Uddannelse
                    </button>
                </div>
            </div>
        </div>

        <!-- Education Data Container -->
        <div id="educationContainer" class="space-y-6">
            <!-- Loading state will be inserted here by JavaScript -->
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="hidden text-center py-16">
            <div class="bg-white rounded-xl shadow-sm p-12">
                <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Ingen resultater fundet</h3>
                <p class="text-gray-500">Prøv at ændre dine søgekriterier eller tilføj en ny uddannelse</p>
            </div>
        </div>
    </main>

    <!-- Add Title Modal -->
    <div id="addTitleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-300" id="addTitleModalContent">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Tilføj Uddannelsestitel</h3>
            </div>
            <form method="POST" class="p-6">
                <input type="hidden" name="action" value="add_title">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titel</label>
                        <input type="text" name="title" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="F.eks. IT, Byggefag, osv.">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bekendtgørelse Link</label>
                        <input type="url" name="link"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="https://www.retsinformation.dk/...">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('addTitleModal')"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                        Annuller
                    </button>
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Tilføj
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add/Edit Education Modal -->
    <div id="educationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full transform scale-95 opacity-0 transition-all duration-300" id="educationModalContent">
            <div class="p-6 border-b">
                <h3 id="educationModalTitle" class="text-lg font-semibold text-gray-900">Tilføj Uddannelse</h3>
            </div>
            <form method="POST" class="p-6" id="educationForm">
                <input type="hidden" name="action" value="add_education" id="educationAction">
                <input type="hidden" name="id" id="educationId">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Uddannelsestitel</label>
                        <select name="eduId" id="educationTitleSelect" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Vælg uddannelsestitel</option>
                            <?php foreach ($titles as $title): ?>
                                <option value="<?php echo $title['id']; ?>"><?php echo htmlspecialchars($title['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Uddannelsesnavn</label>
                        <input type="text" name="name" id="educationName" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="F.eks. Datatekniker med speciale i programmering">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Normeret længde</label>
                        <input type="text" name="length" id="educationLength" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="F.eks. 4 år">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">EUX længde</label>
                        <input type="text" name="euxLength" id="educationEuxLength"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="F.eks. 4½ år (valgfrit)">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('educationModal')"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                        Annuller
                    </button>
                    <button type="submit" id="educationSubmitBtn"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                        Tilføj
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Link Modal -->
    <div id="updateLinkModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full transform scale-95 opacity-0 transition-all duration-300" id="updateLinkModalContent">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Opdater Bekendtgørelse</h3>
                <p class="text-sm text-gray-500 mt-1">Opdater link for <span id="updateLinkTitle" class="font-medium"></span></p>
            </div>
            <form method="POST" class="p-6" id="updateLinkForm">
                <input type="hidden" name="action" value="update_title_link">
                <input type="hidden" name="title_id" id="updateLinkTitleId">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bekendtgørelse Link</label>
                    <input type="url" name="link" id="updateLinkInput"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="https://www.retsinformation.dk/...">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('updateLinkModal')"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                        Annuller
                    </button>
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Opdater Link
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-300" id="confirmModalContent">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Bekræft sletning</h3>
                </div>
                <p id="confirmMessage" class="text-gray-600 mb-6"></p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('confirmModal')"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                        Annuller
                    </button>
                    <form method="POST" style="display: inline;" id="confirmForm">
                        <input type="hidden" name="action" id="confirmAction">
                        <input type="hidden" name="id" id="confirmId">
                        <button type="submit"
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                            Slet
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let searchTimeout;
        let currentSearchTerm = '';

        // Auto-hide message after 5 seconds
        <?php if (!empty($message)): ?>
            setTimeout(function() {
                const messageAlert = document.getElementById('messageAlert');
                if (messageAlert) {
                    messageAlert.style.opacity = '0';
                    messageAlert.style.transition = 'opacity 0.5s ease-out';
                    setTimeout(() => messageAlert.style.display = 'none', 500);
                }
            }, 5000);
        <?php endif; ?>

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.trim();

            clearTimeout(searchTimeout);

            if (searchTerm === currentSearchTerm) return;

            currentSearchTerm = searchTerm;

            if (searchTerm.length === 0) {
                loadAllEducations();
                return;
            }

            document.getElementById('searchLoader').classList.remove('hidden');

            searchTimeout = setTimeout(() => {
                performSearch(searchTerm);
            }, 300);
        });

        function performSearch(searchTerm) {
            fetch(`search.php?search=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('searchLoader').classList.add('hidden');

                    if (data.success) {
                        displayEducations(data.data);
                        updateSearchResults(data.total_results, data.search_term);
                    } else {
                        console.error('Search error:', data.error);
                    }
                })
                .catch(error => {
                    document.getElementById('searchLoader').classList.add('hidden');
                    console.error('Search error:', error);
                });
        }

        function loadAllEducations() {
            showLoadingState();

            fetch('search.php?search=')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayEducations(data.data);
                        document.getElementById('searchResults').textContent = '';
                    }
                })
                .catch(error => {
                    console.error('Load error:', error);
                });
        }

        function showLoadingState() {
            const container = document.getElementById('educationContainer');
            container.innerHTML = `
                <div class="space-y-6">
                    ${Array(3).fill().map(() => `
                        <div class="bg-white rounded-xl shadow-sm p-6 animate-pulse">
                            <div class="loading-skeleton h-6 w-1/3 rounded mb-4"></div>
                            <div class="space-y-3">
                                <div class="loading-skeleton h-4 w-full rounded"></div>
                                <div class="loading-skeleton h-4 w-2/3 rounded"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        function updateSearchResults(count, searchTerm) {
            const resultsDiv = document.getElementById('searchResults');
            if (searchTerm) {
                resultsDiv.textContent = `${count} resultater fundet for "${searchTerm}"`;
            } else {
                resultsDiv.textContent = '';
            }
        }

        function displayEducations(educationData) {
            const container = document.getElementById('educationContainer');
            const noResults = document.getElementById('noResults');

            if (educationData.length === 0) {
                container.innerHTML = '';
                noResults.classList.remove('hidden');
                return;
            }

            noResults.classList.add('hidden');

            container.innerHTML = educationData.map(data => `
                <div class="bg-white rounded-xl shadow-sm card-hover animate-fadeIn">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold text-gray-900 mb-1">${escapeHtml(data.title)}</h2>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span class="flex items-center">
                                        <i class="fas fa-graduation-cap mr-1"></i>
                                        ${data.educations.length} uddannelse(r)
                                    </span>
                                    ${data.last_updated ? `
                                        <span class="flex items-center">
                                            <i class="fas fa-clock mr-1"></i>
                                            Opdateret ${formatDate(data.last_updated)}
                                        </span>
                                    ` : ''}
                                    ${data.link ? `
                                        <a href="${escapeHtml(data.link)}" target="_blank" 
                                           class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                                            <i class="fas fa-external-link-alt mr-1"></i>
                                            Bekendtgørelse
                                        </a>
                                    ` : ''}
                                </div>
                            </div>
                            <div class="relative">
                                <button onclick="toggleDropdown('title-${data.title_id}')"
                                    class="text-gray-500 hover:text-gray-700 transition-colors p-2"
                                    title="Flere muligheder">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="title-${data.title_id}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 border">
                                    <button onclick="updateTitleLink(${data.title_id}, '${escapeHtml(data.title)}', '${escapeHtml(data.link || '')}'); hideDropdown('title-${data.title_id}')"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-link mr-2"></i>Opdater bekendtgørelse
                                    </button>
                                    <button onclick="deleteTitleConfirm(${data.title_id}, '${escapeHtml(data.title)}'); hideDropdown('title-${data.title_id}')"
                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-trash mr-2"></i>Slet uddannelsestitel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        ${data.educations.length === 0 ? `
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-graduation-cap text-4xl mb-4"></i>
                                <p>Ingen uddannelser under denne titel endnu</p>
                            </div>
                        ` : `
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                ${data.educations.map(education => `
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h3 class="font-medium text-gray-900">${escapeHtml(education.name)}</h3>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    <strong>Længde:</strong> ${escapeHtml(education.length)}
                                                </p>
                                                ${education.euxLength ? `
                                                    <p class="text-sm text-gray-600">
                                                        <strong>EUX:</strong> ${escapeHtml(education.euxLength)}
                                                    </p>
                                                ` : ''}
                                                ${education.updated_at ? `
                                                    <p class="text-xs text-gray-500 mt-2">
                                                        <i class="fas fa-edit mr-1"></i>
                                                        Sidst opdateret ${formatDate(education.updated_at)}
                                                    </p>
                                                ` : ''}
                                            </div>
                                            <div class="relative ml-4">
                                                <button onclick="toggleDropdown('education-${education.id}')"
                                                    class="text-gray-500 hover:text-gray-700 transition-colors p-1"
                                                    title="Flere muligheder">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div id="education-${education.id}" class="hidden absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg py-1 z-10 border">
                                                    <button onclick="editEducation(${JSON.stringify(education).replace(/"/g, '&quot;')}); hideDropdown('education-${education.id}')"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <i class="fas fa-edit mr-2"></i>Rediger
                                                    </button>
                                                    <button onclick="deleteEducationConfirm(${education.id}, '${escapeHtml(education.name)}'); hideDropdown('education-${education.id}')"
                                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                        <i class="fas fa-trash mr-2"></i>Slet
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        `}
                    </div>
                </div>
            `).join('');
        }

        // Utility functions
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('da-DK', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(modalId + 'Content');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(modalId + 'Content');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        // Add title modal
        document.getElementById('addTitleBtn').addEventListener('click', () => openModal('addTitleModal'));

        // Add education modal
        document.getElementById('addEducationBtn').addEventListener('click', function() {
            document.getElementById('educationModalTitle').textContent = 'Tilføj Uddannelse';
            document.getElementById('educationAction').value = 'add_education';
            document.getElementById('educationSubmitBtn').textContent = 'Tilføj';
            document.getElementById('educationSubmitBtn').className = 'bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors';
            document.getElementById('educationForm').reset();
            document.getElementById('educationTitleSelect').style.display = 'block';
            document.getElementById('educationTitleSelect').required = true;
            openModal('educationModal');
        });

        // Edit education function
        function editEducation(education) {
            document.getElementById('educationModalTitle').textContent = 'Rediger Uddannelse';
            document.getElementById('educationAction').value = 'update_education';
            document.getElementById('educationId').value = education.id;
            document.getElementById('educationName').value = education.name;
            document.getElementById('educationLength').value = education.length;
            document.getElementById('educationEuxLength').value = education.euxLength || '';
            document.getElementById('educationSubmitBtn').textContent = 'Opdater';
            document.getElementById('educationSubmitBtn').className = 'bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors';

            // Hide the select dropdown for editing since we can't change the title
            document.getElementById('educationTitleSelect').style.display = 'none';
            document.getElementById('educationTitleSelect').required = false;

            openModal('educationModal');
        }

        // Update title link function
        function updateTitleLink(titleId, titleName, currentLink) {
            document.getElementById('updateLinkTitle').textContent = titleName;
            document.getElementById('updateLinkTitleId').value = titleId;
            document.getElementById('updateLinkInput').value = currentLink || '';
            openModal('updateLinkModal');
        }

        // Delete confirmations
        function deleteEducationConfirm(id, name) {
            document.getElementById('confirmMessage').textContent = `Er du sikker på, at du vil slette uddannelsen "${name}"?`;
            document.getElementById('confirmAction').value = 'delete_education';
            document.getElementById('confirmId').value = id;
            openModal('confirmModal');
        }

        function deleteTitleConfirm(id, title) {
            document.getElementById('confirmMessage').textContent = `Er du sikker på, at du vil slette uddannelsestitel "${title}"? Dette er kun muligt, hvis der ikke er tilknyttede uddannelser.`;
            document.getElementById('confirmAction').value = 'delete_title';
            document.getElementById('confirmId').value = id;
            openModal('confirmModal');
        }

        // Dropdown functions
        function toggleDropdown(dropdownId) {
            // Close all other dropdowns first
            document.querySelectorAll('[id^="title-"], [id^="education-"]').forEach(dropdown => {
                if (dropdown.id !== dropdownId) {
                    dropdown.classList.add('hidden');
                }
            });

            // Toggle the clicked dropdown
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('hidden');
        }

        function hideDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.add('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.relative')) {
                document.querySelectorAll('[id^="title-"], [id^="education-"]').forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });

        // Close modals with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('addTitleModal');
                closeModal('educationModal');
                closeModal('updateLinkModal');
                closeModal('confirmModal');
            }
        });

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('fixed')) {
                const modalId = e.target.id;
                if (['addTitleModal', 'educationModal', 'updateLinkModal', 'confirmModal'].includes(modalId)) {
                    closeModal(modalId);
                }
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadAllEducations();
        });
    </script>
</body>

</html>