<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Saccos Management System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <livewire:styles />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flowbite.min.css') }}">

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script nonce="{{ csp_nonce() }}">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
    50:  '#f5f6fa',    // lightest - very soft background
    100: '#5761A1',    // very light
    200: '#c5c9e3',    // lighter tint
    300: '#5761A1',    // light tint
    400: '#5761A1',    // mid tint
    500: '#606db0',    // slightly lighter than base
    600: '#5761A1',    // 🎯 your original base
    700: '#454e84',    // darker shade
    800: '#343c67',    // deep shade
    900: '#23294a',    // darkest - good for hover or emphasis
  },
  brand: {
    50: '#f0f4ff',
    100: '#e0e7ff',
    200: '#c7d2fe',
    300: '#a5b4fc',
    400: '#818cf8',
    500: '#6366f1',
    600: '#5761A1', // ← updated to make it more prominent
    700: '#4338ca',
    800: '#3730a3',
    900: '#312e81',
}
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0'
                            },
                            '100%': {
                                opacity: '1'
                            },
                        },
                        slideUp: {
                            '0%': {
                                transform: 'translateY(20px)',
                                opacity: '0'
                            },
                            '100%': {
                                transform: 'translateY(0)',
                                opacity: '1'
                            },
                        },
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0px)'
                            },
                            '50%': {
                                transform: 'translateY(-20px)'
                            },
                        }
                    },
                    backdropBlur: {
                        xs: '2px',
                    }
                }
            }
        }
    </script>

    <style nonce="{{ csp_nonce() }}">
        [x-cloak] {
            display: none !important;
        }

        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .glass-dark {
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #5761A1 0%, #764ba2 100%);
        }

        .gradient-bg-alt {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #5761A1 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-input {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .form-input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #5761A1 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 40%;
            left: 50%;
            animation-delay: 1s;
        }

        @keyframes shimmer {
            0% {
                background-position: -200px 0;
            }

            100% {
                background-position: calc(200px + 100%) 0;
            }
        }

        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            background-size: 200px 100%;
            animation: shimmer 2s infinite;
        }
    </style>
</head>

<body class="h-full font-sans antialiased">
    <div class="min-h-screen gradient-bg relative overflow-hidden">
        <!-- Floating Background Shapes -->
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>

        <!-- Main Content -->
        <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8">
            <!-- Header -->
  
            <!-- Content Area -->
            <div class="w-full max-w-6xl px-4 sm:px-6 lg:px-8 flex items-center justify-center min-h-screen">
                <div class="w-full animate-fade-in flex flex-col items-center justify-center">
                    <div class="w-1/2">
                     
                        <!-- Right Side - Authentication Form -->
                        <div class="animate-slide-up" style="animation-delay: 0.2s;">
                            <div class="glass rounded-3xl p-8 lg:p-12">
                            <div class="flex items-center justify-center mb-8">
                                        <div class="relative shadow-md rounded-full overflow-hidden bg-white flex items-center justify-center p-6" style="width: 200px; height: 200px;">
                                            <img src="{{ asset('/images/nbc.png') }}" alt="NBC Logo" style="object-fit: contain;">
                                            <div class="absolute inset-0 bg-white rounded-full opacity-20 blur-xl"></div>
                                        </div>
                                    </div>

                                <div class="max-w-md mx-auto">
                                    {{ $slot }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="absolute bottom-0 left-0 right-0 py-6 text-center">
                <div class="container mx-auto px-4">
                    <div class="glass rounded-2xl p-4 max-w-2xl mx-auto">
                        <p class="text-white text-sm mb-2">&copy; {{ date('Y') }} NBC Saccos Management System. All rights reserved.</p>
                        <div class="flex justify-center space-x-6 text-sm">
                            <a href="#" class="text-gray-500 hover:text-white transition-colors duration-200">Privacy Policy</a>
                            <a href="#" class="text-gray-500 hover:text-white transition-colors duration-200">Terms of Service</a>
                            <a href="#" class="text-gray-500 hover:text-white transition-colors duration-200">Contact Support</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <livewire:scripts />
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
</body>

</html>