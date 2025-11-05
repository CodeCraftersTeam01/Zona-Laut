const tangkapanBody = document.getElementById("tangkapanBody");
const searchInput = document.getElementById("searchInput");
const filterTanggal = document.getElementById("filterTanggal");
const filterKapal = document.getElementById("filterKapal");
const filterPemilik = document.getElementById("filterPemilik");

// Ambil data dari server
let tangkapanData = [];
let kapalData = [];
let dpiData = [];
let pemilikData = [];

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(angka);
}

function calculateTotal(berat, harga) {
    return berat * harga;
}

function fetchMasterData() {
    // Fetch data pemilik
    fetch("system/pemilik.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                pemilikData = data.data;
                populatePemilikDropdown();
            }
        })
        .catch(error => console.error("Error fetching pemilik:", error));

    // Fetch data kapal
    fetch("system/kapal.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                kapalData = data.data;
                populateFilterDropdowns();
            }
        })
        .catch(error => console.error("Error fetching kapal:", error));

    // Fetch data DPI
    fetch("system/dpi.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                dpiData = data.data;
                populateDpiDropdown();
            }
        })
        .catch(error => console.error("Error fetching DPI:", error));
}

function populatePemilikDropdown() {
    const addSelect = document.getElementById("addIdPemilik");
    const filterSelect = document.getElementById("filterPemilik");
    
    // Clear existing options
    addSelect.innerHTML = '<option value="">Pilih Pemilik</option>';
    filterSelect.innerHTML = '<option value="">Semua Pemilik</option>';
    
    pemilikData.forEach(pemilik => {
        const option = `<option value="${pemilik.id}">${pemilik.nama_pemilik}</option>`;
        addSelect.innerHTML += option;
        filterSelect.innerHTML += option;
    });
}

function populateDpiDropdown() {
    const addSelect = document.getElementById("addIdDpi");
    const editSelect = document.getElementById("editIdDpi");
    
    // Clear existing options
    addSelect.innerHTML = '<option value="">Pilih DPI</option>';
    editSelect.innerHTML = '<option value="">Pilih DPI</option>';
    
    dpiData.forEach(dpi => {
        const option = `<option value="${dpi.id}">${dpi.nama_dpi}</option>`;
        addSelect.innerHTML += option;
        editSelect.innerHTML += option;
    });
}

function populateFilterDropdowns() {
    const filterKapalSelect = document.getElementById("filterKapal");
    
    // Clear existing options
    filterKapalSelect.innerHTML = '<option value="">Semua Kapal</option>';
    
    kapalData.forEach(kapal => {
        const option = `<option value="${kapal.id}">${kapal.nama_kapal}</option>`;
        filterKapalSelect.innerHTML += option;
    });
}

function populateKapalByPemilik(pemilikId, targetSelect) {
    const filteredKapal = kapalData.filter(kapal => kapal.id_pemilik == pemilikId);
    
    targetSelect.innerHTML = '<option value="">Pilih Kapal</option>';
    filteredKapal.forEach(kapal => {
        const option = `<option value="${kapal.id}">${kapal.nama_kapal}</option>`;
        targetSelect.innerHTML += option;
    });
    
    targetSelect.disabled = filteredKapal.length === 0;
}

function fetchTable() {
    fetch("system/tangkapan.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tangkapanData = data.data;
                renderTable();
            }
        })
        .catch((error) => {
            console.error("Error fetching data:", error);
            tangkapanData = [];
            renderTable();
        });
}

// Tampilkan data ke tabel
function renderTable() {
    tangkapanBody.innerHTML = "";
    const keyword = searchInput.value.toLowerCase();
    const selectedTanggal = filterTanggal.value;
    const selectedKapal = filterKapal.value;
    const selectedPemilik = filterPemilik.value;

    tangkapanData.forEach((data, index) => {
        const matchesSearch = data.nama_ikan.toLowerCase().includes(keyword) || 
                             data.nama_kapal.toLowerCase().includes(keyword);
        const matchesTanggal = !selectedTanggal || data.tanggal_tangkapan === selectedTanggal;
        const matchesKapal = !selectedKapal || data.id_kapal == selectedKapal;
        const matchesPemilik = !selectedPemilik || data.id_pemilik == selectedPemilik;

        if (matchesSearch && matchesTanggal && matchesKapal && matchesPemilik) {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${data.nama_ikan}</td>
                <td>${data.nama_kapal || 'Tidak Diketahui'}</td>
                <td>${data.nama_pemilik || 'Tidak Diketahui'}</td>
                <td>${data.berat_ikan} kg</td>
                <td>${formatRupiah(data.harga_perkilo)}</td>
                <td>${formatRupiah(data.total)}</td>
                <td>${data.nama_dpi || 'Tidak Diketahui'}</td>
                <td>${formatTanggal(data.tanggal_tangkapan)} ${data.waktu_tangkapan || ''}</td>
                <td>
                    <button class="btn btn-sm btn-outline-success edit-btn" data-index="${index}">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-btn" data-index="${index}">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </td>
            `;
            tangkapanBody.appendChild(tr);
        }
    });
    setupButtons();
}

function formatTanggal(tanggal) {
    return new Date(tanggal).toLocaleDateString('id-ID');
}

// Event listener untuk pemilik dropdown
document.getElementById("addIdPemilik").addEventListener("change", function() {
    const pemilikId = this.value;
    const kapalSelect = document.getElementById("addIdKapal");
    
    if (pemilikId) {
        populateKapalByPemilik(pemilikId, kapalSelect);
    } else {
        kapalSelect.innerHTML = '<option value="">Pilih Pemilik terlebih dahulu</option>';
        kapalSelect.disabled = true;
    }
});

// Tambah data
document.getElementById("addForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const id_pemilik = document.getElementById("addIdPemilik").value;
    const id_kapal = document.getElementById("addIdKapal").value;
    const nama_ikan = document.getElementById("addNamaIkan").value.trim();
    const berat_ikan = parseFloat(document.getElementById("addBeratIkan").value);
    const harga_perkilo = parseInt(document.getElementById("addHargaPerKilo").value);
    const id_dpi = document.getElementById("addIdDpi").value;
    const tanggal_tangkapan = document.getElementById("addTanggalTangkapan").value;
    const waktu_tangkapan = document.getElementById("addWaktuTangkapan").value;

    // Validasi input
    if (!id_pemilik || !id_kapal || !nama_ikan || !berat_ikan || !harga_perkilo || !id_dpi || !tanggal_tangkapan || !waktu_tangkapan) {
        alert("Semua field wajib diisi!");
        return;
    }

    if (berat_ikan <= 0 || harga_perkilo <= 0) {
        alert("Berat dan harga harus lebih dari 0!");
        return;
    }

    try {
        const response = await fetch("system/tangkapan.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "add",
                id_kapal: id_kapal,
                id_pemilik: id_pemilik,
                nama_ikan: nama_ikan,
                berat_ikan: berat_ikan,
                harga_perkilo: harga_perkilo,
                id_dpi: id_dpi,
                tanggal_tangkapan: tanggal_tangkapan,
                waktu_tangkapan: waktu_tangkapan
            })
        });

        const result = await response.json();

        if (result.success) {
            e.target.reset();
            document.getElementById("addTotal").value = '';
            document.getElementById("addIdKapal").innerHTML = '<option value="">Pilih Pemilik terlebih dahulu</option>';
            document.getElementById("addIdKapal").disabled = true;
            bootstrap.Modal.getInstance(document.getElementById("addModal")).hide();
            fetchTable();
            showAlert('Data tangkapan berhasil ditambahkan!', 'success');
        } else {
            showAlert('Gagal menambahkan data: ' + (result.message || "Terjadi kesalahan."), 'error');
        }
    } catch (err) {
        console.error("❌ Error:", err);
        showAlert('Gagal mengirim data ke server.', 'error');
    }
});

// Edit data
function setupButtons() {
    document.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const index = btn.dataset.index;
            const data = tangkapanData[index];
            
            // Populate form dengan data yang ada
            document.getElementById("editID").value = data.id;
            document.getElementById("editIdPemilik").value = data.id_pemilik;
            document.getElementById("editNamaPemilik").value = data.nama_pemilik || 'Tidak Diketahui';
            document.getElementById("editNamaIkan").value = data.nama_ikan;
            document.getElementById("editBeratIkan").value = data.berat_ikan;
            document.getElementById("editHargaPerKilo").value = data.harga_perkilo;
            document.getElementById("editTotal").value = formatRupiah(data.total);
            document.getElementById("editIdDpi").value = data.id_dpi;
            document.getElementById("editTanggalTangkapan").value = data.tanggal_tangkapan;
            document.getElementById("editWaktuTangkapan").value = data.waktu_tangkapan;
            
            // Populate kapal dropdown berdasarkan pemilik
            const editKapalSelect = document.getElementById("editIdKapal");
            populateKapalByPemilik(data.id_pemilik, editKapalSelect);
            editKapalSelect.value = data.id_kapal;
            
            document.getElementById("editForm").dataset.index = index;
            
            new bootstrap.Modal(document.getElementById("editModal")).show();
        });
    });

    document.querySelectorAll(".delete-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const index = btn.dataset.index;
            const data = tangkapanData[index];
            
            if (confirm(`Yakin ingin menghapus data tangkapan ${data.nama_ikan}?`)) {
                fetch("system/tangkapan.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        action: "delete",
                        id: data.id,
                        id_pemilik: data.id_pemilik
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        fetchTable();
                        showAlert('Data berhasil dihapus!', 'success');
                    } else {
                        showAlert('Gagal menghapus data: ' + (result.message || "Terjadi kesalahan."), 'error');
                    }
                })
                .catch(err => {
                    console.error("❌ Error:", err);
                    showAlert('Gagal mengirim data ke server.', 'error');
                });
            }
        });
    });
}

// Simpan perubahan edit
document.getElementById("editForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const id = document.getElementById("editID").value;
    const id_pemilik = document.getElementById("editIdPemilik").value;
    const id_kapal = document.getElementById("editIdKapal").value;
    const nama_ikan = document.getElementById("editNamaIkan").value.trim();
    const berat_ikan = parseFloat(document.getElementById("editBeratIkan").value);
    const harga_perkilo = parseInt(document.getElementById("editHargaPerKilo").value);
    const id_dpi = document.getElementById("editIdDpi").value;
    const tanggal_tangkapan = document.getElementById("editTanggalTangkapan").value;
    const waktu_tangkapan = document.getElementById("editWaktuTangkapan").value;

    // Validasi input
    if (!id_kapal || !nama_ikan || !berat_ikan || !harga_perkilo || !id_dpi || !tanggal_tangkapan || !waktu_tangkapan) {
        alert("Semua field wajib diisi!");
        return;
    }

    if (berat_ikan <= 0 || harga_perkilo <= 0) {
        alert("Berat dan harga harus lebih dari 0!");
        return;
    }

    try {
        const response = await fetch("system/tangkapan.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "edit",
                id: id,
                id_kapal: id_kapal,
                id_pemilik: id_pemilik,
                nama_ikan: nama_ikan,
                berat_ikan: berat_ikan,
                harga_perkilo: harga_perkilo,
                id_dpi: id_dpi,
                tanggal_tangkapan: tanggal_tangkapan,
                waktu_tangkapan: waktu_tangkapan
            })
        });

        const result = await response.json();

        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById("editModal")).hide();
            fetchTable();
            showAlert('Data berhasil diubah!', 'success');
        } else {
            showAlert('Gagal mengubah data: ' + (result.message || "Terjadi kesalahan."), 'error');
        }
    } catch (err) {
        console.error("❌ Error:", err);
        showAlert('Gagal mengirim data ke server.', 'error');
    }
});

// Auto calculate total
document.getElementById("addBeratIkan").addEventListener("input", calculateAddTotal);
document.getElementById("addHargaPerKilo").addEventListener("input", calculateAddTotal);
document.getElementById("editBeratIkan").addEventListener("input", calculateEditTotal);
document.getElementById("editHargaPerKilo").addEventListener("input", calculateEditTotal);

function calculateAddTotal() {
    const berat = parseFloat(document.getElementById("addBeratIkan").value) || 0;
    const harga = parseInt(document.getElementById("addHargaPerKilo").value) || 0;
    const total = calculateTotal(berat, harga);
    document.getElementById("addTotal").value = formatRupiah(total);
}

function calculateEditTotal() {
    const berat = parseFloat(document.getElementById("editBeratIkan").value) || 0;
    const harga = parseInt(document.getElementById("editHargaPerKilo").value) || 0;
    const total = calculateTotal(berat, harga);
    document.getElementById("editTotal").value = formatRupiah(total);
}

// Fungsi bantuan
function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}

// Event listeners untuk filter
searchInput.addEventListener("keyup", renderTable);
filterTanggal.addEventListener("change", renderTable);
filterKapal.addEventListener("change", renderTable);
filterPemilik.addEventListener("change", renderTable);

// Reset form ketika modal ditutup
document.getElementById('addModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('addForm').reset();
    document.getElementById('addTotal').value = '';
    document.getElementById('addIdKapal').innerHTML = '<option value="">Pilih Pemilik terlebih dahulu</option>';
    document.getElementById('addIdKapal').disabled = true;
});

document.getElementById('editModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('editForm').reset();
    document.getElementById('editTotal').value = '';
});

// Set tanggal default ke hari ini
document.getElementById('addTanggalTangkapan').valueAsDate = new Date();
document.getElementById('editTanggalTangkapan').valueAsDate = new Date();

// Set waktu default ke waktu sekarang
const now = new Date();
const currentTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
document.getElementById('addWaktuTangkapan').value = currentTime;
document.getElementById('editWaktuTangkapan').value = currentTime;

// Initialize
fetchMasterData();
fetchTable();