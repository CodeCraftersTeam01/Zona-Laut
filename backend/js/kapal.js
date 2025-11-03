// --- Variabel Global ---
        const kapalBody = document.getElementById("kapalBody");
        const pagination = document.getElementById("pagination");
        const rowsPerPage = 5;
        let kapalList = [];
        let pemilikList = [];
        let dpiList = [];
        let currentPage = 1;

        // --- Fetch Data dari Server ---
        function fetchTable() {
            fetch("system/kapal.php")
                .then(response => response.json())
                .then(data => {
                    kapalList = data.data;
                    renderTable();
                })
                .catch((error) => {
                    console.error("Error fetching data:", error);
                    kapalList = [];
                    renderTable();
                });
        }

        // --- Fetch Data Pemilik untuk Dropdown ---
        function fetchPemilik() {
            fetch("system/pemilik.php")
                .then(response => response.json())
                .then(data => {
                    pemilikList = data.data;
                    populatePemilikDropdown();
                })
                .catch((error) => {
                    console.error("Error fetching pemilik:", error);
                    pemilikList = [];
                });
        }

        // --- Fetch Data DPI untuk Dropdown ---
        function fetchDPI() {
            fetch("system/dpi.php")
                .then(response => response.json())
                .then(data => {
                    dpiList = data.data;
                    populateDPIDropdown();
                })
                .catch((error) => {
                    console.error("Error fetching DPI:", error);
                    dpiList = [];
                });
        }

        // --- Isi Dropdown Pemilik ---
        function populatePemilikDropdown() {
            const addDropdown = document.getElementById("addPemilik");
            const editDropdown = document.getElementById("editPemilik");
            
            // Clear existing options except the first one
            addDropdown.innerHTML = '<option value="">Pilih Pemilik</option>';
            editDropdown.innerHTML = '<option value="">Pilih Pemilik</option>';
            
            pemilikList.forEach(pemilik => {
                addDropdown.innerHTML += `<option value="${pemilik.id}">${pemilik.nama_pemilik}</option>`;
                editDropdown.innerHTML += `<option value="${pemilik.id}">${pemilik.nama_pemilik}</option>`;
            });
        }

        // --- Isi Dropdown DPI ---
        function populateDPIDropdown() {
            const addDropdown = document.getElementById("addDPI");
            const editDropdown = document.getElementById("editDPI");
            
            // Clear existing options except the first one
            addDropdown.innerHTML = '<option value="">Pilih DPI</option>';
            editDropdown.innerHTML = '<option value="">Pilih DPI</option>';
            
            dpiList.forEach(dpi => {
                addDropdown.innerHTML += `<option value="${dpi.id}">${dpi.nama_dpi}</option>`;
                editDropdown.innerHTML += `<option value="${dpi.id}">${dpi.nama_dpi}</option>`;
            });
        }

        // --- Render Tabel ---
        function renderTable() {
            kapalBody.innerHTML = "";
            
            if (kapalList.length === 0) {
                kapalBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada data kapal</td>
                    </tr>
                `;
                return;
            }

            kapalList.forEach((kapal, i) => {
                // Cari nama pemilik berdasarkan ID
                const pemilik = pemilikList.find(p => p.id == kapal.id_pemilik) || {};
                // Cari nama DPI berdasarkan ID
                const dpi = dpiList.find(d => d.id == kapal.id_dpi) || {};
                
                const row = `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${kapal.nama_kapal}</td>
                        <td>${kapal.jenis_kapal}</td>
                        <td>${pemilik.nama_pemilik || 'Tidak diketahui'}</td>
                        <td>${dpi.nama_dpi || 'Tidak ada DPI'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-success me-1" onclick="editData(${kapal.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteData(${kapal.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                kapalBody.insertAdjacentHTML("beforeend", row);
            });
            setupPagination();
            showPage(currentPage);
        }

        // --- Tambah Data ---
        document.getElementById("addForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            
            const nama = document.getElementById("addNama").value.trim();
            const jenis = document.getElementById("addJenis").value;
            const id_pemilik = document.getElementById("addPemilik").value;
            const id_dpi = document.getElementById("addDPI").value;
            const verification = document.getElementById("addVerification").value;
            const active = document.getElementById("addActive").checked ? 1 : 0;

            if (!nama || !jenis || !id_pemilik || !id_dpi || !verification || !active) {
                alert("Semua field wajib diisi!");
                return;
            }

            try {
                const response = await fetch("system/kapal.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        action: "add",
                        nama_kapal: nama,
                        jenis_kapal: jenis,
                        id_pemilik: id_pemilik,
                        id_dpi: id_dpi,
                        verification: verification,
                        status: active
                    })
                });

                const result = await response.json();

                if (result.success) {
                    e.target.reset();
                    bootstrap.Modal.getInstance(document.getElementById("addModal")).hide();
                    fetchTable();
                    console.log("✅ Data kapal berhasil ditambahkan:", result.message);
                } else {
                    alert("Gagal menambahkan data: " + (result.message || "Terjadi kesalahan."));
                }
            } catch (err) {
                console.error("❌ Error:", err);
                alert("Gagal mengirim data ke server.");
            }
        });

        // --- Edit Data ---
        function editData(id) {
            const kapal = kapalList.find(k => k.id == id);
            if (!kapal) return;

            document.getElementById("editID").value = kapal.id;
            document.getElementById("editNama").value = kapal.nama_kapal;
            document.getElementById("editJenis").value = kapal.jenis_kapal;
            document.getElementById("editPemilik").value = kapal.id_pemilik;
            document.getElementById("editDPI").value = kapal.id_dpi;
            document.getElementById("editStatus").value = kapal.status;
            if(kapal.verified_at != null){
                document.getElementById("editVerification").value = "1";
            } else {
                document.getElementById("editVerification").value = "0";
            }
            
            new bootstrap.Modal(document.getElementById("editModal")).show();
        }

        document.getElementById("editForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            
            const id = document.getElementById("editID").value;
            const nama = document.getElementById("editNama").value.trim();
            const jenis = document.getElementById("editJenis").value;
            const id_pemilik = document.getElementById("editPemilik").value;
            const id_dpi = document.getElementById("editDPI").value;
            const verification = document.getElementById("editVerification").value;
            const active = document.getElementById("editStatus").value;
            // alert(active);

            if (!nama || !jenis || !id_pemilik || !id_dpi || !verification || !active) {
                alert("Semua field wajib diisi!");
                return;
            }

            try {
                const response = await fetch("system/kapal.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        action: "edit",
                        id: id,
                        nama_kapal: nama,
                        jenis_kapal: jenis,
                        id_pemilik: id_pemilik,
                        id_dpi: id_dpi,
                        verification: verification,
                        status: active
                    })
                });

                const result = await response.json();

                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById("editModal")).hide();
                    fetchTable();
                    console.log("✅ Data kapal berhasil diubah:", result.message);
                } else {
                    alert("Gagal mengubah data: " + (result.message || "Terjadi kesalahan."));
                }
            } catch (err) {
                console.error("❌ Error:", err);
                alert("Gagal mengirim data ke server.");
            }
        });

        // --- Hapus Data ---
        function deleteData(id) {
            if (confirm("Yakin ingin menghapus data kapal ini?")) {
                fetch("system/kapal.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        action: "delete",
                        id: id
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        fetchTable();
                        console.log("✅ Data kapal berhasil dihapus:", result.message);
                    } else {
                        alert("Gagal menghapus data: " + (result.message || "Terjadi kesalahan."));
                    }
                })
                .catch(err => {
                    console.error("❌ Error:", err);
                    alert("Gagal mengirim data ke server.");
                });
            }
        }

        // --- Search ---
        document.getElementById("searchInput").addEventListener("keyup", function () {
            const keyword = this.value.toLowerCase();
            const rows = kapalBody.querySelectorAll("tr");
            rows.forEach(row => {
                const nama = row.children[1].textContent.toLowerCase();
                row.style.display = nama.includes(keyword) ? "" : "none";
            });
        });

        // --- Pagination ---
        function setupPagination() {
            pagination.innerHTML = "";
            const totalPages = Math.ceil(kapalList.length / rowsPerPage);
            
            if (totalPages <= 1) return;
            
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
            const rows = Array.from(kapalBody.querySelectorAll("tr"));
            rows.forEach((row, i) => {
                row.style.display = (i >= start && i < end) ? "" : "none";
            });

            document.querySelectorAll("#pagination li").forEach(li => li.classList.remove("active"));
            if (pagination.children[page - 1]) {
                pagination.children[page - 1].classList.add("active");
            }
        }

        // --- Inisialisasi ---
        document.addEventListener("DOMContentLoaded", function() {
            fetchPemilik();
            fetchDPI();
            fetchTable();
        });