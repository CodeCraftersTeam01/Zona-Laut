// Variabel utama
const dpiBody = document.getElementById("dpiBody");
const pagination = document.getElementById("pagination");
const rowsPerPage = 5;
let dpiList = [];
let currentPage = 1;
let editIndex = null;

// Pindahkan deklarasi peta ke scope global
let map; // untuk modal tambah
let editMap; // untuk modal edit
let marker;
let editMarker;

function fetchTable() {
    fetch("system/dpi.php")
        .then(response => response.json())
        .then(data => {
            dpiList = data.data;
            renderTable();
        })
        .catch(() => {
            dpiList = [];
            renderTable();
        });
}

fetchTable();

// Render tabel
function renderTable() {
    dpiBody.innerHTML = "";
    dpiList.forEach((dpi, i) => {
        const row = `
          <tr>
            <td>${i + 1}</td>
            <td>${dpi.nama_dpi}</td>
            <td>${dpi.luas}</td>
            <td>
              <button class="btn btn-sm btn-outline-success me-1" onclick="editData(${i})">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteData(${i})">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>`;
        dpiBody.insertAdjacentHTML("beforeend", row);
    });
    setupPagination();
    showPage(currentPage);
}

// Tambah DPI
document.getElementById("addForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const nama = addNama.value.trim();
    const luas = addLuas.value.trim();
    const location = document.getElementById("addLocation").value.trim();

    if (!nama || !luas || !location) {
        alert("Nama dan luas wajib diisi!");
        return;
    }

    try {
        const response = await fetch("system/dpi.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "add",
                nama_dpi: nama,
                luas: luas,
                location: location,
            })
        });

        const result = await response.json();

        if (result.success) {
            e.target.reset();
            bootstrap.Modal.getInstance(document.getElementById("addModal")).hide();
            fetchTable();
            console.log("✅ Data berhasil ditambahkan:", result.message);
        } else {
            alert("Gagal menambahkan data: " + (result.message || "Terjadi kesalahan."));
        }
    } catch (err) {
        console.error("❌ Error:", err);
        alert("Gagal mengirim data ke server.");
    }
});

// Edit DPI
function editData(index) {
    editIndex = index;
    const dpi = dpiList[index];
    editId.value = dpi.id;
    editNama.value = dpi.nama_dpi;
    editLuas.value = dpi.luas;

    // Set lokasi jika tersedia
    if (dpi.location) {
        const [lat, lng] = dpi.location.split(",").map(Number);
        editMap.setView([lat, lng], 10);
        if (editMarker) editMap.removeLayer(editMarker);
        editMarker = L.marker([lat, lng]).addTo(editMap);
        document.getElementById("editLocation").value = dpi.location;
    }

    new bootstrap.Modal(document.getElementById("editModal")).show();
}

document.getElementById("editForm").addEventListener("submit", e => {
    e.preventDefault();

    fetch("system/dpi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            action: "edit",
            id: editId.value,
            nama_dpi: editNama.value.trim(),
            luas: editLuas.value.trim(),
            location: document.getElementById("editLocation").value.trim()
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            fetchTable();
            console.log("✅ Data berhasil diubah:", result.message);
        } else {
            alert("Gagal mengubah data: " + (result.message || "Terjadi kesalahan."));
        }
    })
    .catch(err => {
        console.error("❌ Error:", err);
        alert("Gagal mengirim data ke server.");
    });

    bootstrap.Modal.getInstance(document.getElementById("editModal")).hide();
});

// Hapus DPI
function deleteData(index) {
    if (confirm("Yakin ingin menghapus DPI ini?")) {
        fetch("system/dpi.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "delete",
                id: dpiList[index].id
            })
        }).then(response => response.json())
            .then(result => {
                if (result.success) {
                    fetchTable();
                    console.log("✅ Data berhasil dihapus:", result.message);
                } else {
                    alert("Gagal menghapus data: " + (result.message || "Terjadi kesalahan."));
                }
            })
            .catch(err => {
                console.error("❌ Error:", err);
                alert("Gagal mengirim data ke server.");
            });
        renderTable();
    }
}

// Pencarian
document.getElementById("searchInput").addEventListener("keyup", function () {
    const keyword = this.value.toLowerCase();
    const rows = dpiBody.querySelectorAll("tr");
    rows.forEach(row => {
        const nama = row.children[1].textContent.toLowerCase();
        row.style.display = nama.includes(keyword) ? "" : "none";
    });
});

// Pagination
function setupPagination() {
    pagination.innerHTML = "";
    const totalPages = Math.ceil(dpiList.length / rowsPerPage);
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement("li");
        li.className = "page-item";
        li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        li.addEventListener("click", e => {
            e.preventDefault();
            currentPage = i;
            showPage(i);
        });
        pagination.appendChild(li);
    }
}

function showPage(page) {
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const rows = Array.from(dpiBody.querySelectorAll("tr"));
    rows.forEach((row, i) => {
        row.style.display = (i >= start && i < end) ? "" : "none";
    });
    document.querySelectorAll("#pagination li").forEach(li => li.classList.remove("active"));
    pagination.children[page - 1]?.classList.add("active");
}

// Inisialisasi Peta
document.addEventListener("DOMContentLoaded", () => {
    // ================== PETA TAMBAH ==================
    map = L.map("map").setView([-2.5489, 118.0149], 5);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>'
    }).addTo(map);

    map.on("click", (e) => {
        const { lat, lng } = e.latlng;
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        document.getElementById("addLocation").value = `${lat},${lng}`;
    });

    const addModal = document.getElementById("addModal");
    addModal.addEventListener("shown.bs.modal", () => {
        map.invalidateSize();
    });

    // ================== PETA EDIT ==================
    editMap = L.map("editMap").setView([-2.5489, 118.0149], 5);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>'
    }).addTo(editMap);

    editMap.on("click", (e) => {
        const { lat, lng } = e.latlng;
        if (editMarker) editMap.removeLayer(editMarker);
        editMarker = L.marker([lat, lng]).addTo(editMap);
        document.getElementById("editLocation").value = `${lat},${lng}`;
    });

    const editModal = document.getElementById("editModal");
    editModal.addEventListener("shown.bs.modal", () => {
        editMap.invalidateSize();
    });
});

// Jalankan pertama kali
renderTable();