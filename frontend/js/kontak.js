// Quick message functions untuk tombol langsung
        function quickMessage(purpose) {
            let message = '';

            switch (purpose) {
                case 'consultation':
                    message = "Halo Zona Laut, saya ingin konsultasi tentang layanan monitoring perikanan. Bisa diinfokan detail layanannya?";
                    break;
                case 'demo':
                    message = "Halo Zona Laut, saya ingin request demo gratis platform monitoring perikanan. Kapan bisa dijadwalkan?";
                    break;
                case 'pricing':
                    message = "Halo Zona Laut, saya ingin mengetahui informasi harga dan paket layanan monitoring perikanan.";
                    break;
                default:
                    message = "Halo Zona Laut, saya ingin bertanya tentang layanan monitoring perikanan.";
            }

            const encodedMessage = encodeURIComponent(message);
            window.open(`https://wa.me/6285648077829?text=${encodedMessage}`, '_blank');
        }

        // AJAX form submission untuk pengiriman via PHP
        document.getElementById('whatsappForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('success').classList.add('hidden');
            document.getElementById('error').classList.add('hidden');

            try {
                const formData = new FormData(this);

                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                // Hide loading
                document.getElementById('loading').style.display = 'none';

                if (result.success) {
                    // Show success message
                    document.getElementById('success').classList.remove('hidden');

                    // Reset form
                    document.getElementById('whatsappForm').reset();

                    // Hide success message after 5 seconds
                    setTimeout(() => {
                        document.getElementById('success').classList.add('hidden');
                    }, 5000);
                } else {
                    throw new Error(result.message || 'Failed to send message');
                }

            } catch (error) {
                // Hide loading
                document.getElementById('loading').style.display = 'none';

                // Show error message
                document.getElementById('errorMessage').textContent = error.message;
                document.getElementById('error').classList.remove('hidden');

                // Fallback: redirect to regular WhatsApp
                console.log('API failed, falling back to regular WhatsApp...');
                const formData = new FormData(document.getElementById('whatsappForm'));
                const message = formatWhatsAppMessage({
                    nama: formData.get('nama'),
                    email: formData.get('email'),
                    perusahaan: formData.get('perusahaan'),
                    layanan: formData.get('layanan'),
                    pesan: formData.get('pesan')
                });
                const encodedMessage = encodeURIComponent(message);
                window.open(`https://wa.me/6285648077829?text=${encodedMessage}`, '_blank');
            }
        });

        // Format pesan untuk WhatsApp (fallback)
        function formatWhatsAppMessage(formData) {
            return `*PESAN BARU DARI WEBSITE ZONA LAUT*

*Data Pengirim:*
📛 Nama: ${formData.nama}
📧 Email: ${formData.email}
📞 WhatsApp: ${formData.phone || 'Tidak disebutkan'}
🏢 Perusahaan: ${formData.perusahaan || 'Tidak disebutkan'}

*Layanan yang Diminati:*
${formData.layanan}

*Pesan:*
${formData.pesan}

_*Pesan ini dikirim melalui website Zona Laut*_`;
        }