<!DOCTYPE html>
<html lang="da">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realkompetencevurdering</title>
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/8224/8224757.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/e0d52d3d3c.js" crossorigin="anonymous"></script>
    <style>
        /* Enhanced animations with staggered timing */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
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

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            }

            50% {
                box-shadow: 0 0 30px rgba(59, 130, 246, 0.5);
            }
        }

        /* Animation classes */
        .animate-fadeInUp {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .animate-slideInLeft {
            animation: slideInLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .animate-scaleIn {
            animation: scaleIn 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-glow {
            animation: glow 2s ease-in-out infinite;
        }

        /* Enhanced styling */
        .login-container {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .logo-container {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            animation: float 6s ease-in-out infinite;
        }

        .input-focus:focus {
            transform: translateY(-3px);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
        }

        .btn-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.4);
        }

        .eye-icon {
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .eye-icon:hover {
            transform: scale(1.2);
            color: #3b82f6;
        }

        .error-message {
            animation: fadeInUp 0.4s ease-out;
        }

        /* Particle animation */
        @keyframes float-up {
            to {
                transform: translateY(-100vh) rotate(720deg);
                opacity: 0;
            }
        }

        /* Loading state */
        .loading-state {
            position: relative;
            overflow: hidden;
        }

        .loading-state::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }

        /* Delayed animations */
        .delay-100 {
            animation-delay: 0.1s;
            animation-fill-mode: both;
        }

        .delay-200 {
            animation-delay: 0.2s;
            animation-fill-mode: both;
        }

        .delay-300 {
            animation-delay: 0.3s;
            animation-fill-mode: both;
        }

        .delay-400 {
            animation-delay: 0.4s;
            animation-fill-mode: both;
        }

        .delay-500 {
            animation-delay: 0.5s;
            animation-fill-mode: both;
        }

        .delay-600 {
            animation-delay: 0.6s;
            animation-fill-mode: both;
        }

        .delay-700 {
            animation-delay: 0.7s;
            animation-fill-mode: both;
        }

        .delay-800 {
            animation-delay: 0.8s;
            animation-fill-mode: both;
        }
    </style>
</head>

<body class="min-h-screen login-container flex items-center justify-center p-4">
    <!-- Enhanced background decorative elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse-slow"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse-slow delay-500"></div>
        <div class="absolute top-1/2 left-1/2 w-60 h-60 bg-indigo-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse-slow delay-300"></div>
    </div>

    <!-- Login Card -->
    <div class="w-full max-w-md relative z-10">
        <!-- Enhanced Logo Section -->
        <div class="text-center mb-8 animate-scaleIn delay-100">
            <div class="inline-flex items-center justify-center w-24 h-24 logo-container rounded-full mb-6 shadow-2xl animate-glow">
                <i class="fas fa-graduation-cap text-3xl text-white"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-3 animate-fadeInUp delay-200">RKV</h1>
            <p class="text-slate-300 text-base animate-slideInLeft delay-300">Realkompetencevurdering</p>
        </div>

        <!-- Enhanced Login Form Card -->
        <div class="glass-effect rounded-3xl p-10 shadow-2xl animate-fadeInUp delay-400">
            <div class="text-center mb-8 animate-slideInLeft delay-500">
                <h2 class="text-2xl font-semibold text-white mb-3">Velkommen</h2>
                <p class="text-slate-300 text-sm">Log ind for at fortsætte</p>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="hidden mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-xl error-message">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-300 mr-3"></i>
                    <p class="text-red-300 text-sm"></p>
                </div>
            </div>

            <form id="loginForm" class="space-y-8">
                <!-- Enhanced Code Field -->
                <div class="animate-slideInLeft delay-600">
                    <label for="code" class="block text-sm font-medium text-slate-200 mb-3">
                        <i class="fas fa-key mr-2 text-blue-400"></i>Kode
                    </label>
                    <div class="relative group">
                        <input type="password" id="code" name="code"
                            class="w-full px-5 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus transition-all duration-300 pr-14 text-lg"
                            placeholder="••••••••••" required>
                        <button type="button"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 eye-icon cursor-pointer"
                            onclick="togglePassword()">
                            <i id="eyeIcon" class="fas fa-eye text-slate-400 text-lg hover:text-blue-400"></i>
                        </button>
                    </div>
                </div>

                <!-- Enhanced Login Button -->
                <button type="submit" id="loginBtn"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold py-4 px-6 rounded-xl hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-800 btn-hover transition-all duration-300 animate-fadeInUp delay-700 text-lg">
                    <span id="loginBtnContent">
                        <i class="fas fa-sign-in-alt mr-3"></i>
                        <span id="loginBtnText">Log ind</span>
                    </span>
                </button>
            </form>
        </div>

        <!-- Enhanced Contact Info -->
        <div class="text-center mt-8 animate-fadeInUp delay-800">
            <div class="glass-effect rounded-2xl p-4">
                <p class="text-slate-300 text-sm mb-2">
                    <i class="fas fa-headset mr-2 text-blue-400"></i>
                    Brug for hjælp?
                </p>
                <a href="mailto:support@mercantec.dk"
                    class="text-blue-400 hover:text-blue-300 transition-all duration-300 hover:underline font-medium">
                    <i class="fas fa-envelope mr-2"></i>
                    support@mercantec.dk
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('code');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
                eyeIcon.style.color = '#3b82f6';
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
                eyeIcon.style.color = '';
            }
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            const errorText = errorDiv.querySelector('p');
            errorText.textContent = message;
            errorDiv.classList.remove('hidden');
        }

        function hideError() {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.classList.add('hidden');
        }

        function setLoading(loading) {
            const btn = document.getElementById('loginBtn');
            const btnContent = document.getElementById('loginBtnContent');

            if (loading) {
                btn.disabled = true;
                btn.classList.add('loading-state', 'opacity-90');
                btnContent.innerHTML = `
                    <span class="flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-3"></i>
                        <span>Logger ind...</span>
                    </span>
                `;
            } else {
                btn.disabled = false;
                btn.classList.remove('loading-state', 'opacity-90');
                btnContent.innerHTML = `
                    <i class="fas fa-sign-in-alt mr-3"></i>
                    <span>Log ind</span>
                `;
            }
        }

        // Enhanced form submission with better visual feedback
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideError();
            setLoading(true);

            // Add a slight delay for better UX
            await new Promise(resolve => setTimeout(resolve, 500));

            const formData = new FormData(this);

            try {
                // Simulate API call for demo - replace with actual endpoint
                const response = await fetch('authenticate.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Success state
                    const btn = document.getElementById('loginBtn');
                    const btnContent = document.getElementById('loginBtnContent');

                    btn.classList.add('bg-green-500');
                    btnContent.innerHTML = `
                        <span class="flex items-center justify-center">
                            <i class="fas fa-check mr-3 text-xl"></i>
                            <span>Succes!</span>
                        </span>
                    `;

                    // Redirect after showing success
                    setTimeout(() => {
                        window.location.href = '../';
                    }, 1000);
                } else {
                    showError(result.message || 'Ugyldig kode. Prøv igen.');
                }
            } catch (error) {
                showError('Netværksfejl. Prøv igen senere.');
                console.error('Login error:', error);
            } finally {
                if (!document.getElementById('loginBtn').classList.contains('bg-green-500')) {
                    setLoading(false);
                }
            }
        });

        // Enhanced input effects
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[type="password"], input[type="text"]');

            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                    this.parentElement.style.transition = 'transform 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
                });

                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });

                // Add typing effect
                input.addEventListener('input', function() {
                    if (this.value) {
                        this.parentElement.classList.add('animate-pulse');
                        setTimeout(() => {
                            this.parentElement.classList.remove('animate-pulse');
                        }, 200);
                    }
                });
            });
        });

        // Enhanced particle system
        function createFloatingParticle() {
            const particle = document.createElement('div');
            const size = Math.random() * 3 + 1;
            const duration = Math.random() * 4 + 3;

            particle.className = 'fixed bg-blue-400 rounded-full opacity-40 pointer-events-none';
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = Math.random() * 100 + 'vw';
            particle.style.top = '100vh';
            particle.style.animation = `float-up ${duration}s linear forwards`;
            particle.style.filter = 'blur(0.5px)';

            document.body.appendChild(particle);

            setTimeout(() => {
                particle.remove();
            }, duration * 1000);
        }

        // Create particles more frequently
        setInterval(createFloatingParticle, 2000);

        // Add initial particle burst
        setTimeout(() => {
            for (let i = 0; i < 5; i++) {
                setTimeout(createFloatingParticle, i * 300);
            }
        }, 1000);
    </script>
</body>

</html>