<?php
session_start();
require_once('../database/db_conn.php');

// Check if user is logged in
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("Location: login/");
    exit;
}

// Get user data from session
$userName = $_SESSION['name'] ?? 'Bruger';
$userDepartment = $_SESSION['department'] ?? '';
$userEmail = $_SESSION['email'] ?? '';
$userPhone = $_SESSION['phone'] ?? '';
$userId = $_SESSION['id'] ?? '';

// Get actual RKV count from database
$rkvCount = 0;
if ($userId) {
    $stmt = $conn->prepare("SELECT amount FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $rkvCount = $row['amount'];
    }
    $stmt->close();
}

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';

    if ($action === 'change_password') {
        // Handle password change
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Simple validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = 'Alle felter skal udfyldes.';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'De nye koder matcher ikke.';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 6) {
            $message = 'Ny kode skal være mindst 6 tegn.';
            $messageType = 'error';
        } else {
            // Get current user code from database to verify
            $stmt = $conn->prepare("SELECT code FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if ($row['code'] === $currentPassword) {
                    // Update password in database
                    $updateStmt = $conn->prepare("UPDATE users SET code = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $newPassword, $userId);

                    if ($updateStmt->execute()) {
                        $message = 'Adgangskode opdateret succesfuldt!';
                        $messageType = 'success';
                    } else {
                        $message = 'Fejl ved opdatering af adgangskode.';
                        $messageType = 'error';
                    }
                    $updateStmt->close();
                } else {
                    $message = 'Nuværende kode er forkert.';
                    $messageType = 'error';
                }
            }
            $stmt->close();
        }
    } else {
        // Handle profile update
        $newName = trim($_POST['name'] ?? '');
        $newDepartment = trim($_POST['department'] ?? '');
        $newEmail = trim($_POST['email'] ?? '');
        $newPhone = trim($_POST['phone'] ?? '');

        if (!empty($newName) && !empty($newEmail)) {
            // Update user in database
            $stmt = $conn->prepare("UPDATE users SET name = ?, department = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $newName, $newDepartment, $newEmail, $newPhone, $userId);

            if ($stmt->execute()) {
                // Update session data
                $_SESSION['name'] = $newName;
                $_SESSION['department'] = $newDepartment;
                $_SESSION['email'] = $newEmail;
                $_SESSION['phone'] = $newPhone;

                // Update variables for display
                $userName = $newName;
                $userDepartment = $newDepartment;
                $userEmail = $newEmail;
                $userPhone = $newPhone;

                $message = 'Profil opdateret succesfuldt!';
                $messageType = 'success';
            } else {
                $message = 'Fejl ved opdatering af profil.';
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Navn og email er påkrævet.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="da">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Min Profil - RKV</title>
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

        .animate-slideInDown {
            animation: slideInDown 0.6s ease-out;
        }

        .animate-fadeIn {
            animation: fadeIn 0.8s ease-out;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 animate-slideInDown">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Back Button and Title -->
                <div class="flex items-center space-x-4">
                    <a href="../" class="flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span class="hidden sm:inline">Tilbage til Dashboard</span>
                        <span class="sm:hidden">Tilbage</span>
                    </a>
                    <div class="border-l border-gray-300 pl-4">
                        <h1 class="text-xl font-bold text-gray-900">Min Profil</h1>
                    </div>
                </div>

                <!-- User Info -->
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
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 card-hover animate-fadeIn">
                    <div class="text-center">
                        <!-- Profile Picture -->
                        <div class="w-24 h-24 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-white">
                                <?php echo strtoupper(substr($userName, 0, 2)); ?>
                            </span>
                        </div>

                        <h2 class="text-xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($userName); ?></h2>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($userDepartment); ?></p>

                        <!-- Stats -->
                        <div class="grid grid-cols-1 gap-4 mt-6 pt-6 border-t">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-blue-600"><?php echo $rkvCount; ?></p>
                                <p class="text-sm text-gray-600">RKV'er</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-white rounded-xl shadow-sm p-6 mt-6 card-hover animate-fadeIn" style="animation-delay: 0.2s;">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Hurtige links</h3>
                    <div class="space-y-3">
                        <a href="../form/" class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <i class="fas fa-plus text-gray-500 mr-3"></i>
                            Ny RKV
                        </a>
                        <a href="../login/logout.php" class="flex items-center p-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-sign-out-alt text-red-500 mr-3"></i>
                            Log ud
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6 animate-fadeIn" style="animation-delay: 0.1s;">
                    <div class="border-b border-gray-200 pb-4 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Rediger profil</h3>
                        <p class="text-sm text-gray-600 mt-1">Opdater dine personlige oplysninger</p>
                    </div>

                    <form method="POST" class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-2 text-gray-500"></i>Fulde navn
                            </label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building mr-2 text-gray-500"></i>Afdeling
                            </label>
                            <select id="department" name="department"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="Teknologi & Energi" <?php echo $userDepartment === 'Teknologi & Energi' ? 'selected' : ''; ?>>Teknologi & Energi</option>
                                <option value="Business" <?php echo $userDepartment === 'Business' ? 'selected' : ''; ?>>Business</option>
                                <option value="Bygge & Anlæg" <?php echo $userDepartment === 'Bygge & Anlæg' ? 'selected' : ''; ?>>Bygge & Anlæg</option>
                                <option value="Gastronomi & Sundhed" <?php echo $userDepartment === 'Gastronomi & Sundhed' ? 'selected' : ''; ?>>Gastronomi & Sundhed</option>
                                <option value="LOP" <?php echo $userDepartment === 'LOP' ? 'selected' : ''; ?>>LOP</option>
                                <option value="VK" <?php echo $userDepartment === 'VK' ? 'selected' : ''; ?>>VK</option>
                                <option value="VEU - konsulent" <?php echo $userDepartment === 'VEU - konsulent' ? 'selected' : ''; ?>>VEU - konsulent</option>
                            </select>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2 text-gray-500"></i>Email adresse
                            </label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-phone mr-2 text-gray-500"></i>Telefonnummer
                            </label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userPhone); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <!-- User ID (Read-only) -->
                        <div>
                            <label for="userId" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-id-card mr-2 text-gray-500"></i>Bruger ID
                            </label>
                            <input type="text" id="userId" value="<?php echo htmlspecialchars($userId); ?>" readonly
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-6">
                            <button type="submit"
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 flex items-center">
                                <i class="fas fa-save mr-2"></i>
                                Gem ændringer
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Section -->
                <div class="bg-white rounded-xl shadow-sm p-6 mt-6 card-hover animate-fadeIn" style="animation-delay: 0.3s;">
                    <div class="border-b border-gray-200 pb-4 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Skift adgangskode</h3>
                        <p class="text-sm text-gray-600 mt-1">Opdater din login kode for større sikkerhed</p>
                    </div>

                    <form method="POST" class="space-y-6" id="passwordForm">
                        <input type="hidden" name="action" value="change_password">

                        <!-- Current Password -->
                        <div>
                            <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-gray-500"></i>Nuværende kode
                            </label>
                            <div class="relative">
                                <input type="password" id="currentPassword" name="currentPassword" required
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-gray-700"
                                    onclick="togglePasswordVisibility('currentPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-key mr-2 text-gray-500"></i>Ny kode
                            </label>
                            <div class="relative">
                                <input type="password" id="newPassword" name="newPassword" required minlength="6"
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-gray-700"
                                    onclick="togglePasswordVisibility('newPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Minimum 6 tegn</p>
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-check-circle mr-2 text-gray-500"></i>Bekræft ny kode
                            </label>
                            <div class="relative">
                                <input type="password" id="confirmPassword" name="confirmPassword" required
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-gray-700"
                                    onclick="togglePasswordVisibility('confirmPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="text-xs mt-1 hidden">
                                <span id="passwordMatchText"></span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button type="submit"
                                class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 flex items-center">
                                <i class="fas fa-shield-alt mr-2"></i>
                                Opdater kode
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Auto-hide message after 3 seconds
        <?php if (!empty($message)): ?>
            setTimeout(function() {
                const messageAlert = document.getElementById('messageAlert');
                if (messageAlert) {
                    messageAlert.style.opacity = '0';
                    messageAlert.style.transition = 'opacity 0.5s ease-out';
                    setTimeout(function() {
                        messageAlert.style.display = 'none';
                    }, 500);
                }
            }, 3000);
        <?php endif; ?>

        // Toggle password visibility
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password confirmation validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            const matchText = document.getElementById('passwordMatchText');

            if (confirmPassword.length > 0) {
                matchDiv.classList.remove('hidden');
                if (newPassword === confirmPassword) {
                    matchText.textContent = '✓ Koderne matcher';
                    matchText.className = 'text-green-600';
                } else {
                    matchText.textContent = '✗ Koderne matcher ikke';
                    matchText.className = 'text-red-600';
                }
            } else {
                matchDiv.classList.add('hidden');
            }
        });

        // Password form submission
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('De nye koder matcher ikke!');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Opdaterer...';
            submitBtn.disabled = true;

            // Re-enable after a short delay (form will redirect anyway)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '').substring(0, 8);
            this.value = value;
        });

        // Form submission feedback
        document.querySelector('form:not(#passwordForm)').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gemmer...';
            submitBtn.disabled = true;

            // Re-enable after a short delay (form will redirect anyway)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });

        // Add focus effects to inputs
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.01)';
                this.parentElement.style.transition = 'transform 0.2s ease';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>

</html>