document.addEventListener("DOMContentLoaded", function () {
    // Load LINK (CSS)
    fetch("components/link.html")
        .then((res) => res.text())
        .then((data) => {
            document.head.insertAdjacentHTML("afterbegin", data);
        })
        .catch((err) => console.error("Gagal memuat link.html:", err));

    // Load SCRIPT (Bootstrap, jQuery, dsb)
    fetch("components/script.html")
        .then((res) => res.text())
        .then((data) => {
            // Buat DOM parser untuk membaca script.html
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, "text/html");

            // Ambil semua <script> di dalam script.html
            const scripts = doc.querySelectorAll("script");

            scripts.forEach((oldScript) => {
                const newScript = document.createElement("script");

                if (oldScript.src) {
                    // Kalau pakai src, muat dari URL
                    newScript.src = oldScript.src;
                } else {
                    // Kalau ada isi inline
                    newScript.textContent = oldScript.textContent;
                }

                // Masukkan setelah script.js
                const target = document.querySelector('script[src="js/script.js"]');
                if (target) {
                    target.insertAdjacentElement("afterend", newScript);
                } else {
                    document.body.appendChild(newScript);
                }
            });
        })
        .catch((err) => console.error("Gagal memuat script.html:", err));

    // Ambil nama file dari URL (misalnya "dpi.html")
    const currentPage = window.location.pathname.split("/").pop();

    // Ambil semua link sidebar
    const links = document.querySelectorAll(".list-group-item");

    links.forEach(link => {
        const href = link.getAttribute("href");

        // Cek apakah nama file di href ada dalam URL aktif
        if (currentPage === href || window.location.href.includes(href)) {
            link.classList.add("active");
        } else {
            link.classList.remove("active");
        }
    });

    if (currentPage != "index.html") {
        // Di dashboard.html, cek session
        fetch('./system/checkAuthenticate.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    window.location.href = 'index.html';
                }
            });
    }

});
