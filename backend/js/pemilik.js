const pemilikBody = document.getElementById("pemilikBody");
const searchInput = document.getElementById("searchInput");

// Ambil data dari server
let pemilikData = [];

function fetchTable() {
    fetch("system/pemilik.php")
        .then(response => response.json())
        .then(data => {
            pemilikData = data.data;
            renderTable();
        })
        .catch((error) => {
            console.error("Error fetching data:", error);
            pemilikData = [];
            renderTable();
        });
}

fetchTable();

// Tampilkan data ke tabel
function renderTable() {
    pemilikBody.innerHTML = "";
    const keyword = searchInput.value.toLowerCase();

    pemilikData.forEach((data, index) => {
        if (data.nama_pemilik.toLowerCase().includes(keyword) || 
            data.email.toLowerCase().includes(keyword)) {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${data.nama_pemilik}</td>
                <td>${data.email}</td>
                <td>${data.alamat}</td>
                <td>${data.nomor_telepon}</td>
                <td>${data.nik}</td>
                <td>
                    <button class="btn btn-sm btn-outline-success edit-btn" data-index="${index}">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-outline-warning reset-btn" data-index="${index}">
                        <i class="bi bi-key"></i> Reset
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-btn" data-index="${index}">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </td>
            `;
            pemilikBody.appendChild(tr);
        }
    });
    setupButtons();
}

// Tambah data
document.getElementById("addForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const nama = document.getElementById("addNama").value.trim();
    const email = document.getElementById("addEmail").value.trim();
    const alamat = document.getElementById("addAlamat").value.trim();
    const telp = document.getElementById("addTelp").value.trim();
    const nik = document.getElementById("addNIK").value.trim();
    const password = document.getElementById("addPassword").value.trim();

    // Validasi input
    if (!nama || !email || !alamat || !telp || !nik || !password) {
        alert("Semua field harus diisi!");
        return;
    }

    // Validasi email
    if (!isValidEmail(email)) {
        alert("Format email tidak valid!");
        return;
    }

    try {
        const response = await fetch("system/pemilik.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "add",
                nama_pemilik: nama,
                email: email,
                alamat: alamat,
                nomor_telepon: telp,
                nik: nik,
                password: password
            })
        });

        const result = await response.json();

        if (result.success) {
            e.target.reset();
            bootstrap.Modal.getInstance(document.getElementById("addModal")).hide();
            fetchTable();
            showAlert('Data pemilik berhasil ditambahkan!', 'success');
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
    // Edit Button
    document.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const index = btn.dataset.index;
            const data = pemilikData[index];
            
            document.getElementById("editID").value = data.id;
            document.getElementById("editNama").value = data.nama_pemilik;
            document.getElementById("editEmail").value = data.email;
            document.getElementById("editAlamat").value = data.alamat;
            document.getElementById("editTelp").value = data.nomor_telepon;
            document.getElementById("editNIK").value = data.nik;
            document.getElementById("editForm").dataset.index = index;
            
            new bootstrap.Modal(document.getElementById("editModal")).show();
        });
    });

    // Reset Password Button
    document.querySelectorAll(".reset-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const index = btn.dataset.index;
            const data = pemilikData[index];
            
            document.getElementById("resetID").value = data.id;
            document.getElementById("resetNama").textContent = data.nama_pemilik;
            document.getElementById("resetConfirm").value = '';
            
            new bootstrap.Modal(document.getElementById("resetPasswordModal")).show();
        });
    });

    // Delete Button
    document.querySelectorAll(".delete-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const index = btn.dataset.index;
            const data = pemilikData[index];
            
            if (confirm(`Yakin ingin menghapus data ${data.nama_pemilik}?`)) {
                fetch("system/pemilik.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        action: "delete",
                        id: data.id
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
    const nama = document.getElementById("editNama").value.trim();
    const email = document.getElementById("editEmail").value.trim();
    const alamat = document.getElementById("editAlamat").value.trim();
    const telp = document.getElementById("editTelp").value.trim();
    const nik = document.getElementById("editNIK").value.trim();

    // Validasi input
    if (!nama || !email || !alamat || !telp || !nik) {
        alert("Semua field harus diisi!");
        return;
    }

    // Validasi email
    if (!isValidEmail(email)) {
        alert("Format email tidak valid!");
        return;
    }

    try {
        const response = await fetch("system/pemilik.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "edit",
                id: id,
                nama_pemilik: nama,
                email: email,
                alamat: alamat,
                nomor_telepon: telp,
                nik: nik
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

// Reset Password
document.getElementById("resetPasswordForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const id = document.getElementById("resetID").value;
    const confirmText = document.getElementById("resetConfirm").value.trim();
    
    if (confirmText !== 'RESET') {
        alert("Silakan ketik 'RESET' untuk mengonfirmasi reset password!");
        return;
    }

    try {
        const response = await fetch("system/pemilik.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "reset_password",
                id: id
            })
        });

        const result = await response.json();

        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById("resetPasswordModal")).hide();
            showAlert('Password berhasil direset ke default!', 'success');
        } else {
            showAlert('Gagal reset password: ' + (result.message || "Terjadi kesalahan."), 'error');
        }
    } catch (err) {
        console.error("❌ Error:", err);
        showAlert('Gagal mengirim data ke server.', 'error');
    }
});

// Fungsi bantuan
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showAlert(message, type) {
    // Buat alert Bootstrap sederhana
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove setelah 5 detik
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}

// Cari data
searchInput.addEventListener("keyup", renderTable);

// Reset form ketika modal ditutup
document.getElementById('addModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('addForm').reset();
});

document.getElementById('editModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('editForm').reset();
});

document.getElementById('resetPasswordModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('resetPasswordForm').reset();
});