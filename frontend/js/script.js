
document.addEventListener("DOMContentLoaded", () => {
    const domain = window.location.origin; // otomatis pakai domain saat ini
    const links = document.querySelectorAll("a[href]");

    links.forEach(link => {
        const href = link.getAttribute("href");

        // Skip kondisi tertentu
        if (
            !href || // kosong
            href.startsWith("http") || // sudah URL penuh
            href.startsWith("mailto:") || // email link
            href.startsWith("tel:") || // nomor telepon
            href.startsWith("#") // anchor link (pagar)
        ) return;

        // Hapus / berlebih di depan href lalu tambahkan domain
        link.href = `${domain}/${href.replace(/^\/+/, "")}`;
    });
});