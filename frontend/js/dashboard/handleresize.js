function getDeviceCategory(width) {
        if (width < 640) return "hp";
        if (width < 1024) return "tablet";
        if (width < 1440) return "laptop";
        return "tv";
    }

    // 🔹 Simpan kategori awal
    let currentCategory = getDeviceCategory(window.innerWidth);

    window.addEventListener("resize", () => {
        const newCategory = getDeviceCategory(window.innerWidth);

        // 🔹 Jika kategori berubah, reload halaman
        if (newCategory !== currentCategory) {
            console.log(`Kategori berubah: ${currentCategory} → ${newCategory}`);
            currentCategory = newCategory;
            location.reload();
        }
    });