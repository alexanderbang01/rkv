<?php
session_start();
require_once('database/db_conn.php');

// Check if user is logged in
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("Location: login/");
    exit;
}

// Get user data from session
$userName = $_SESSION['name'] ?? 'Bruger';
$userDepartment = $_SESSION['department'] ?? '';
$userEmail = $_SESSION['email'] ?? '';
$userId = $_SESSION['id'] ?? 0;

// Get dashboard statistics from database
$stats = [
    'total_rkv' => 0,
    'this_month' => 0,
    'user_rkv_count' => 0
];

// Get total RKV count from rkv_activities table
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM rkv_activities");
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['total_rkv'] = $row['total'];
}
$stmt->close();

// Get this month's RKV count
$stmt = $conn->prepare("SELECT COUNT(*) as this_month FROM rkv_activities WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['this_month'] = $row['this_month'];
}
$stmt->close();

// Get user's RKV count from users table
$stmt = $conn->prepare("SELECT amount FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['user_rkv_count'] = $row['amount'];
}
$stmt->close();

// Get recent activities (for all users instead of just current user)
$recent_activities = [];
$stmt = $conn->prepare("
    SELECT ra.*, u.name as user_name, et.title as education_title
    FROM rkv_activities ra 
    JOIN users u ON ra.user_id = u.id
    JOIN educationtitle et ON ra.education_title_id = et.id
    ORDER BY ra.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_activities[] = $row;
}
$stmt->close();

// Get department distribution
$dept_stats = [];
$stmt = $conn->prepare("
    SELECT u.department, COUNT(ra.id) as count 
    FROM users u 
    LEFT JOIN rkv_activities ra ON u.id = ra.user_id 
    WHERE u.department IS NOT NULL 
    GROUP BY u.department 
    ORDER BY count DESC
");
$stmt->execute();
$result = $stmt->get_result();
$total_dept_rkv = 0;
while ($row = $result->fetch_assoc()) {
    $dept_stats[] = $row;
    $total_dept_rkv += $row['count'];
}
$stmt->close();

// Calculate percentages for departments
foreach ($dept_stats as &$dept) {
    $dept['percentage'] = $total_dept_rkv > 0 ? round(($dept['count'] / $total_dept_rkv) * 100) : 0;
}

// Get education distribution based on education_title_id
$education_stats = [];
$stmt = $conn->prepare("
    SELECT et.title, COUNT(ra.id) as count 
    FROM rkv_activities ra
    JOIN educationtitle et ON ra.education_title_id = et.id
    GROUP BY et.id, et.title
    ORDER BY count DESC
");
$stmt->execute();
$result = $stmt->get_result();
$total_education_rkv = 0;
while ($row = $result->fetch_assoc()) {
    $education_stats[] = $row;
    $total_education_rkv += $row['count'];
}
$stmt->close();

// Calculate percentages for education categories
foreach ($education_stats as &$education) {
    $education['percentage'] = $total_education_rkv > 0 ? round(($education['count'] / $total_education_rkv) * 100) : 0;
}

// Prepare data for doughnut chart
$chart_labels = [];
$chart_data = [];
$chart_colors = [
    '#3B82F6',
    '#EF4444',
    '#10B981',
    '#F59E0B',
    '#8B5CF6',
    '#EC4899',
    '#06B6D4',
    '#84CC16',
    '#F97316',
    '#6366F1',
    '#8B5A2B',
    '#FF6B6B',
    '#4ECDC4',
    '#45B7D1',
    '#96CEB4'
];

foreach ($education_stats as $index => $education) {
    $chart_labels[] = $education['title'];
    $chart_data[] = $education['count'];
}

// Function to get time ago string
function timeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'lige nu';
    if ($time < 3600) return floor($time / 60) . ' min siden';
    if ($time < 86400) return floor($time / 3600) . ' timer siden';
    if ($time < 2592000) return floor($time / 86400) . ' dage siden';

    return date('d.m.Y', strtotime($datetime));
}

// Function to get education color based on title
function getEducationColor($education_title)
{
    $colors = [
        'Elektronikfagtekniker' => '#3B82F6',
        'Byggefag' => '#EF4444',
        'Automatik' => '#10B981',
        'IT' => '#F59E0B',
        'Elektronikfag' => '#8B5CF6',
        'Industri- og maskinteknik' => '#EC4899',
        'Økonomi' => '#06B6D4',
        'VVS- og energispecialist' => '#84CC16',
        'Gastronom' => '#F97316',
        'Bager og konditor' => '#6366F1'
    ];

    return $colors[$education_title] ?? '#64748B';
}

// Function to get Danish action text
function getDanishAction($action_type)
{
    switch ($action_type) {
        case 'created':
            return 'RKV oprettet';
        case 'sent_for_review':
            return 'RKV sendt til gennemgang';
        case 'approved':
            return 'RKV godkendt efter gennemgang';
        case 'rejected':
            return 'RKV kræver rettelser';
        case 'needs_revision':
            return 'RKV returneret til revision';
        default:
            return 'RKV aktivitet';
    }
}
?>
<!DOCTYPE html>
<html lang="da">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realkompetencevurdering</title>
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/8224/8224757.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/e0d52d3d3c.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
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

        .animate-slideInDown {
            animation: slideInDown 0.6s ease-out;
        }

        .animate-slideInUp {
            animation: slideInUp 0.6s ease-out;
        }

        .animate-slideInLeft {
            animation: slideInLeft 0.6s ease-out;
        }

        .animate-fadeIn {
            animation: fadeIn 0.8s ease-out;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .progress-bar {
            transition: width 1s ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 animate-slideInDown">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and Title -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <h1 class="text-xl font-bold text-gray-900">RKV Forside</h1>
                            <p class="text-sm text-gray-500">Realkompetencevurdering</p>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <!-- User Profile -->
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($userName); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($userDepartment); ?></p>
                        </div>
                        <div class="relative">
                            <button id="userMenuBtn" class="flex items-center text-gray-500 hover:text-gray-700 focus:outline-none">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <!-- Dropdown menu -->
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="profile/" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profil
                                </a>
                                <hr class="my-1">
                                <a href="login/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Log ud
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8 animate-slideInLeft">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Velkommen tilbage, <?php echo explode(' ', $userName)[0]; ?>! 👋</h2>
            <p class="text-gray-600">Her er et overblik over dine RKV aktiviteter</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Total RKV -->
            <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition-all duration-300 animate-slideInUp" style="animation-delay: 0.1s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total RKV</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_rkv']; ?></p>
                        <p class="text-sm text-blue-600 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>På tværs af alle afdelinger
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- This Month -->
            <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition-all duration-300 animate-slideInUp" style="animation-delay: 0.2s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Denne måned</p>
                        <p class="text-3xl font-bold text-purple-600"><?php echo $stats['this_month']; ?></p>
                        <p class="text-sm text-purple-600 mt-1">
                            <i class="fas fa-calendar mr-1"></i>Juni 2025
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- My RKV Count -->
            <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition-all duration-300 animate-slideInUp" style="animation-delay: 0.3s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Mine RKV'er</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo $stats['user_rkv_count']; ?></p>
                        <p class="text-sm text-green-600 mt-1">
                            <i class="fas fa-user mr-1"></i>Udført af dig
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Quick Actions -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 animate-slideInLeft" style="animation-delay: 0.4s;">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Hurtige handlinger</h3>
                    <div class="space-y-3">
                        <a href="./form/" class="w-full flex items-center justify-between p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200">
                            <div class="flex items-center">
                                <i class="fas fa-plus text-blue-600 mr-3"></i>
                                <span class="text-blue-600 font-medium">Ny RKV</span>
                            </div>
                            <i class="fas fa-arrow-right text-blue-600"></i>
                        </a>

                        <a href="./educations/" class="w-full flex items-center justify-between p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors duration-200">
                            <div class="flex items-center">
                                <i class="fas fa-graduation-cap text-orange-600 mr-3"></i>
                                <span class="text-orange-600 font-medium">Uddannelser</span>
                            </div>
                            <i class="fas fa-arrow-right text-orange-600"></i>
                        </a>

                        <a href="./overview/" class="w-full flex items-center justify-between p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200">
                            <div class="flex items-center">
                                <i class="fas fa-chart-bar text-green-600 mr-3"></i>
                                <span class="text-green-600 font-medium">Oversigt</span>
                            </div>
                            <i class="fas fa-arrow-right text-green-600"></i>
                        </a>

                        <a href="./statistics/" class="w-full flex items-center justify-between p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200">
                            <div class="flex items-center">
                                <i class="fas fa-chart-pie text-purple-600 mr-3"></i>
                                <span class="text-purple-600 font-medium">Statistikker</span>
                            </div>
                            <i class="fas fa-arrow-right text-purple-600"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6 animate-slideInUp" style="animation-delay: 0.5s;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Seneste aktivitet</h3>
                        <button id="showAllActivities" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Se alle</button>
                    </div>
                    <div class="space-y-4">
                        <?php if (empty($recent_activities)): ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-4"></i>
                                <p>Ingen aktiviteter endnu</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <?php $educationColor = getEducationColor($activity['education_title']); ?>
                                <div class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: <?php echo $educationColor; ?>20;">
                                        <i class="fas fa-plus" style="color: <?php echo $educationColor; ?>;"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900"><?php echo getDanishAction($activity['action_type']); ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($activity['student_name']); ?> -
                                            <span style="color: <?php echo $educationColor; ?>; font-weight: 500;"><?php echo htmlspecialchars($activity['education_title']); ?></span> -
                                            <?php echo htmlspecialchars($activity['user_name']); ?> • <?php echo timeAgo($activity['created_at']); ?>
                                        </p>
                                    </div>
                                    <span class="text-xs text-gray-400"><?php echo date('H:i', strtotime($activity['created_at'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Education Distribution -->
        <div class="grid grid-cols-1 gap-8">
            <!-- Education Trends Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6 animate-fadeIn" style="animation-delay: 0.6s;">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">RKV Fordeling efter Uddannelse</h3>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                    <!-- Doughnut Chart -->
                    <div class="flex justify-center">
                        <div class="relative w-72 h-72">
                            <canvas id="educationChart"></canvas>
                            <!-- Center text -->
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-gray-900"><?php echo $total_education_rkv; ?></div>
                                    <div class="text-sm text-gray-500">Total RKV'er</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legend and Details -->
                    <div class="space-y-2 max-h-72 overflow-y-auto">
                        <?php if (!empty($education_stats)): ?>
                            <?php foreach (array_slice($education_stats, 0, 8) as $index => $education): ?>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-3" style="background-color: <?php echo $chart_colors[$index % count($chart_colors)]; ?>"></div>
                                        <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($education['title']); ?></span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-gray-900"><?php echo $education['count']; ?></div>
                                        <div class="text-xs text-gray-500"><?php echo $education['percentage']; ?>%</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($education_stats) > 8): ?>
                                <div class="text-center pt-2">
                                    <button id="showAllEducations" class="text-blue-600 hover:text-blue-700 text-xs font-medium transition-colors">
                                        +<?php echo count($education_stats) - 8; ?> flere uddannelser
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-chart-pie text-4xl mb-4"></i>
                                <p>Ingen uddannelsesdata tilgængelig</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities Modal -->
        <div id="activitiesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div id="activitiesModalContent" class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[85vh] overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Alle Aktiviteter</h3>
                    <button id="closeActivitiesModal" class="text-gray-400 hover:text-gray-600 text-2xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Filters -->
                <div class="p-4 border-b bg-gray-50 flex flex-wrap gap-4 items-center">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Sortér efter:</label>
                        <select id="sortActivities" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                            <option value="date_desc">Dato (nyeste først)</option>
                            <option value="date_asc">Dato (ældste først)</option>
                            <option value="education">Uddannelse</option>
                            <option value="user">Bruger</option>
                            <option value="student">Studerende</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Filtrer uddannelse:</label>
                        <select id="filterEducation" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                            <option value="">Alle uddannelser</option>
                            <?php foreach ($education_stats as $education): ?>
                                <option value="<?php echo htmlspecialchars($education['title']); ?>"><?php echo htmlspecialchars($education['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Filtrer bruger:</label>
                        <select id="filterUser" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                            <option value="">Alle brugere</option>
                            <?php
                            $userStmt = $conn->prepare("SELECT DISTINCT u.name FROM users u JOIN rkv_activities ra ON u.id = ra.user_id ORDER BY u.name");
                            $userStmt->execute();
                            $userResult = $userStmt->get_result();
                            while ($userRow = $userResult->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($userRow['name']) . '">' . htmlspecialchars($userRow['name']) . '</option>';
                            }
                            $userStmt->close();
                            ?>
                        </select>
                    </div>
                </div>

                <div class="p-6 max-h-96 overflow-y-auto" id="allActivitiesContainer">
                    <!-- Activities will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Education Modal -->
        <div id="educationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div id="modalContent" class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Alle Uddannelser</h3>
                    <button id="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6 max-h-96 overflow-y-auto">
                    <div class="space-y-3">
                        <?php foreach ($education_stats as $index => $education): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full mr-3" style="background-color: <?php echo $chart_colors[$index % count($chart_colors)]; ?>"></div>
                                    <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($education['title']); ?></span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-900"><?php echo $education['count']; ?> RKV'er</div>
                                    <div class="text-xs text-gray-500"><?php echo $education['percentage']; ?>% af total</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // User menu toggle
        document.getElementById('userMenuBtn').addEventListener('click', function() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('userMenu');
            const button = document.getElementById('userMenuBtn');

            if (!menu.contains(event.target) && !button.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

        // Initialize Chart
        document.addEventListener('DOMContentLoaded', function() {
            // Chart data from PHP
            const chartData = {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: [
                        '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
                        '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1',
                        '#8B5A2B', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            };

            const ctx = document.getElementById('educationChart').getContext('2d');
            const educationChart = new Chart(ctx, {
                type: 'doughnut',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false // We'll use custom legend
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#ffffff',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.parsed / total) * 100);
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    elements: {
                        arc: {
                            borderJoinStyle: 'round'
                        }
                    }
                }
            });

            // Education modal functionality
            const showAllBtn = document.getElementById('showAllEducations');
            const modal = document.getElementById('educationModal');
            const modalContent = document.getElementById('modalContent');
            const closeBtn = document.getElementById('closeModal');

            if (showAllBtn) {
                showAllBtn.addEventListener('click', function() {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modalContent.classList.remove('scale-95', 'opacity-0');
                        modalContent.classList.add('scale-100', 'opacity-100');
                    }, 10);
                });
            }

            function closeModal() {
                modalContent.classList.remove('scale-100', 'opacity-100');
                modalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Activities modal functionality
            const showAllActivitiesBtn = document.getElementById('showAllActivities');
            const activitiesModal = document.getElementById('activitiesModal');
            const activitiesModalContent = document.getElementById('activitiesModalContent');
            const closeActivitiesBtn = document.getElementById('closeActivitiesModal');

            // All activities data
            let allActivities = <?php
                                $allActivitiesStmt = $conn->prepare("
                    SELECT ra.*, u.name as user_name, et.title as education_title
                    FROM rkv_activities ra 
                    JOIN users u ON ra.user_id = u.id
                    JOIN educationtitle et ON ra.education_title_id = et.id
                    ORDER BY ra.created_at DESC
                ");
                                $allActivitiesStmt->execute();
                                $allActivitiesResult = $allActivitiesStmt->get_result();
                                $allActivitiesData = [];
                                while ($row = $allActivitiesResult->fetch_assoc()) {
                                    $allActivitiesData[] = $row;
                                }
                                $allActivitiesStmt->close();
                                echo json_encode($allActivitiesData);
                                ?>;

            if (showAllActivitiesBtn) {
                showAllActivitiesBtn.addEventListener('click', function() {
                    loadAllActivities();
                    activitiesModal.classList.remove('hidden');
                    setTimeout(() => {
                        activitiesModalContent.classList.remove('scale-95', 'opacity-0');
                        activitiesModalContent.classList.add('scale-100', 'opacity-100');
                    }, 10);
                });
            }

            function closeActivitiesModal() {
                activitiesModalContent.classList.remove('scale-100', 'opacity-100');
                activitiesModalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    activitiesModal.classList.add('hidden');
                }, 300);
            }

            if (closeActivitiesBtn) {
                closeActivitiesBtn.addEventListener('click', closeActivitiesModal);
            }

            // Close modal when clicking outside
            activitiesModal.addEventListener('click', function(e) {
                if (e.target === activitiesModal) {
                    closeActivitiesModal();
                }
            });

            // Filter and sort functionality
            document.getElementById('sortActivities').addEventListener('change', loadAllActivities);
            document.getElementById('filterEducation').addEventListener('change', loadAllActivities);
            document.getElementById('filterUser').addEventListener('change', loadAllActivities);

            function loadAllActivities() {
                const sortBy = document.getElementById('sortActivities').value;
                const filterEducation = document.getElementById('filterEducation').value;
                const filterUser = document.getElementById('filterUser').value;

                let filteredActivities = [...allActivities];

                // Apply filters
                if (filterEducation) {
                    filteredActivities = filteredActivities.filter(activity =>
                        activity.education_title === filterEducation
                    );
                }

                if (filterUser) {
                    filteredActivities = filteredActivities.filter(activity =>
                        activity.user_name === filterUser
                    );
                }

                // Apply sorting
                filteredActivities.sort((a, b) => {
                    switch (sortBy) {
                        case 'date_desc':
                            return new Date(b.created_at) - new Date(a.created_at);
                        case 'date_asc':
                            return new Date(a.created_at) - new Date(b.created_at);
                        case 'education':
                            return a.education_title.localeCompare(b.education_title);
                        case 'user':
                            return a.user_name.localeCompare(b.user_name);
                        case 'student':
                            return a.student_name.localeCompare(b.student_name);
                        default:
                            return 0;
                    }
                });

                renderActivities(filteredActivities);
            }

            function renderActivities(activities) {
                const container = document.getElementById('allActivitiesContainer');

                if (activities.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-search text-4xl mb-4"></i>
                            <p>Ingen aktiviteter fundet med de valgte filtre</p>
                        </div>
                    `;
                    return;
                }

                const educationColors = {
                    'Elektronikfagtekniker': '#3B82F6',
                    'Byggefag': '#EF4444',
                    'Automatik': '#10B981',
                    'IT': '#F59E0B',
                    'Elektronikfag': '#8B5CF6',
                    'Industri- og maskinteknik': '#EC4899',
                    'Økonomi': '#06B6D4',
                    'VVS- og energispecialist': '#84CC16',
                    'Gastronom': '#F97316',
                    'Bager og konditor': '#6366F1'
                };

                function timeAgo(datetime) {
                    const time = Math.floor((new Date() - new Date(datetime)) / 1000);
                    if (time < 60) return 'lige nu';
                    if (time < 3600) return Math.floor(time / 60) + ' min siden';
                    if (time < 86400) return Math.floor(time / 3600) + ' timer siden';
                    if (time < 2592000) return Math.floor(time / 86400) + ' dage siden';
                    return new Date(datetime).toLocaleDateString('da-DK');
                }

                container.innerHTML = `
                    <div class="space-y-3">
                        ${activities.map(activity => {
                            const educationColor = educationColors[activity.education_title] || '#64748B';
                            const date = new Date(activity.created_at);
                            
                            return `
                                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: ${educationColor}20;">
                                        <i class="fas fa-plus" style="color: ${educationColor};"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900">RKV oprettet</p>
                                            <span class="text-xs text-gray-400">${date.toLocaleTimeString('da-DK', { hour: '2-digit', minute: '2-digit' })}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <span class="font-medium">${activity.student_name}</span> - 
                                            <span style="color: ${educationColor}; font-weight: 500;">${activity.education_title}</span>
                                        </p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-xs text-gray-500">Oprettet af: ${activity.user_name}</span>
                                            <span class="text-xs text-gray-500">${timeAgo(activity.created_at)}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                    <div class="text-center pt-4 border-t mt-4">
                        <p class="text-sm text-gray-500">Viser ${activities.length} aktivitet${activities.length !== 1 ? 'er' : ''}</p>
                    </div>
                `;
            }

            // Animate progress bars on load
            setTimeout(() => {
                const progressBars = document.querySelectorAll('.progress-bar');
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                });
            }, 1000);

            // Add click effects to action buttons
            const actionButtons = document.querySelectorAll('button');
            actionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
            });

            // Close modal with ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (!modal.classList.contains('hidden')) {
                        closeModal();
                    }
                    if (!activitiesModal.classList.contains('hidden')) {
                        closeActivitiesModal();
                    }
                }
            });
        });
    </script>
</body>

</html>