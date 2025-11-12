<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - Zona Laut</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes bubble {
            0% {
                transform: translateY(0) scale(1);
                opacity: 0.7;
            }

            100% {
                transform: translateY(-100px) scale(1.2);
                opacity: 0;
            }
        }

        .animate-bubble {
            animation: bubble 6s infinite ease-in;
        }

        .whatsapp-float {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 1000;
        }

        .loading {
            display: none;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-white via-blue-50 to-blue-100 min-h-screen font-sans">
    <!-- Wave Background Elements -->
    <div class="fixed top-0 left-0 w-full h-64 bg-gradient-to-b from-blue-500/10 to-transparent -z-10"></div>
    <div class="fixed bottom-0 left-0 w-full h-64 bg-gradient-to-t from-blue-400/10 to-transparent -z-10"></div>

    <!-- Floating Bubbles -->
    <div class="fixed top-1/4 left-10 w-6 h-6 bg-blue-300/30 rounded-full animate-bubble"></div>
    <div class="fixed top-1/3 right-20 w-4 h-4 bg-blue-400/40 rounded-full animate-bubble" style="animation-delay: 1s;"></div>
    <div class="fixed bottom-1/4 left-1/4 w-8 h-8 bg-blue-200/20 rounded-full animate-bubble" style="animation-delay: 2s;"></div>

    <!-- WhatsApp Floating Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/6285648077829?text=Halo%20Zona%20Laut,%20saya%20ingin%20bertanya%20tentang%20layanan%20monitoring%20perikanan"
            target="_blank"
            class="bg-green-500 hover:bg-green-600 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110">
            <i class="fab fa-whatsapp text-2xl"></i>
        </a>
    </div>

    <?php include '../components/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="pt-32 pb-16 px-6">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium mb-6">
                <i class="fas fa-envelope mr-2"></i>
                Hubungi Kami
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-blue-900 mb-6">
                Mari <span class="text-blue-600">Berkolaborasi</span>
            </h1>
            <p class="text-xl text-blue-700 mb-8">
                Kami siap membantu Anda dengan solusi monitoring perikanan terbaik.
                Hubungi tim kami untuk informasi lebih lanjut.
            </p>

            <!-- WhatsApp Quick Action -->
            <div class="bg-green-50 border border-green-200 rounded-2xl p-6 max-w-md mx-auto mb-8">
                <div class="flex items-center justify-center mb-4">
                    <i class="fab fa-whatsapp text-green-600 text-3xl mr-3"></i>
                    <h3 class="text-xl font-bold text-green-800">Chat Langsung via WhatsApp</h3>
                </div>
                <p class="text-green-700 mb-4 text-center">Dapatkan respons cepat dari tim kami</p>
                <a href="https://wa.me/6285648077829?text=Halo%20Zona%20Laut,%20saya%20ingin%20bertanya%20tentang%20layanan%20monitoring%20perikanan"
                    target="_blank"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-semibold transition duration-300 inline-flex items-center">
                    <i class="fab fa-whatsapp mr-2"></i>Chat Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Contact Content -->
   

    <!-- CTA Section -->
    <section class="py-16 px-6 bg-gradient-to-b from-blue-50 to-white">
        <div class="max-w-4xl mx-auto text-center">
            <div class="bg-white rounded-3xl p-12 border border-blue-200 shadow-xl">
                <h2 class="text-3xl font-bold text-blue-900 mb-4">Siap Memulai?</h2>
                <p class="text-xl text-blue-700 mb-8">
                    Jadilah bagian dari revolusi digital industri perikanan Indonesia.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="./frontend/auth/login" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition duration-300 shadow-lg hover:shadow-xl">
                        Masuk Ke Dashboard
                    </a>
                    <a href="tel:085648077829" class="bg-white text-blue-700 border-2 border-blue-200 hover:border-blue-300 px-8 py-4 rounded-xl font-semibold text-lg transition duration-300 shadow-md hover:shadow-lg">
                        <i class="fas fa-phone mr-2"></i>Telepon Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-6 md:mb-0">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center mr-3">
                        <i class="fas fa-anchor text-blue-600 text-lg"></i>
                    </div>
                    <span class="text-white font-bold text-xl">Zona Laut</span>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-blue-200 hover:text-white transition duration-300">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-blue-200 hover:text-white transition duration-300">
                        <i class="fab fa-facebook text-xl"></i>
                    </a>
                    <a href="#" class="text-blue-200 hover:text-white transition duration-300">
                        <i class="fab fa-linkedin text-xl"></i>
                    </a>
                    <a href="https://wa.me/6285648077829" target="_blank" class="text-blue-200 hover:text-white transition duration-300">
                        <i class="fab fa-whatsapp text-xl"></i>
                    </a>
                </div>
            </div>
            <div class="border-t border-blue-700 mt-8 pt-8 text-center text-blue-200">
                <p>&copy; 2025 Zona Laut. All rights reserved. | Membangun Masa Depan Perikanan Berkelanjutan</p>
            </div>
        </div>
    </footer>

    <script src="../js/kontak.js"></script>
    <script src="../js/script.js"></script>
</body>

</html>