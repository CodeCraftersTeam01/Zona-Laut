// Variabel utama
    const usersBody = document.getElementById("usersBody");
    const pagination = document.getElementById("pagination");
    const rowsPerPage = 5;
    let usersList = [];
    let currentPage = 1;

    // Fetch data dari server
    function fetchTable() {
      fetch("system/users.php")
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            usersList = data.data;
            renderTable();
          } else {
            console.error("Error fetching data:", data.message);
            usersList = [];
            renderTable();
          }
        })
        .catch((error) => {
          console.error("Error fetching data:", error);
          usersList = [];
          renderTable();
        });
    }

    // Render tabel
    function renderTable() {
      usersBody.innerHTML = "";
      
      if (usersList.length === 0) {
        usersBody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center text-muted">Tidak ada data user</td>
          </tr>
        `;
        return;
      }

      usersList.forEach((user, i) => {
        const createdDate = new Date(user.created_at).toLocaleDateString('id-ID', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        });
        
        const row = `
          <tr>
            <td>${i + 1}</td>
            <td>${user.nama}</td>
            <td>${user.email}</td>
            <td>${createdDate}</td>
            <td>
              <button class="btn btn-sm btn-outline-success me-1" onclick="editData(${user.id})">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteData(${user.id})" ${user.id === 1 ? 'disabled' : ''}>
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>`;
        usersBody.insertAdjacentHTML("beforeend", row);
      });
      setupPagination();
      showPage(currentPage);
    }

    // Toggle password visibility
    function setupPasswordToggles() {
      // Add modal toggles
      document.getElementById('toggleAddPassword').addEventListener('click', function() {
        togglePasswordVisibility('addPassword', this);
      });
      
      document.getElementById('toggleAddConfirmPassword').addEventListener('click', function() {
        togglePasswordVisibility('addConfirmPassword', this);
      });
      
      // Edit modal toggles
      document.getElementById('toggleEditPassword').addEventListener('click', function() {
        togglePasswordVisibility('editPassword', this);
      });
      
      document.getElementById('toggleEditConfirmPassword').addEventListener('click', function() {
        togglePasswordVisibility('editConfirmPassword', this);
      });
    }

    function togglePasswordVisibility(inputId, button) {
      const input = document.getElementById(inputId);
      const icon = button.querySelector('i');
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    }

    // Tambah User
    document.getElementById("addForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      
      const nama = document.getElementById("addNama").value.trim();
      const email = document.getElementById("addEmail").value.trim();
      const password = document.getElementById("addPassword").value;
      const confirmPassword = document.getElementById("addConfirmPassword").value;

      // Validasi
      if (!nama || !email || !password || !confirmPassword) {
        alert("Semua field wajib diisi!");
        return;
      }

      if (password.length < 6) {
        alert("Password minimal 6 karakter!");
        return;
      }

      if (password !== confirmPassword) {
        alert("Password dan konfirmasi password tidak cocok!");
        return;
      }

      if (!validateEmail(email)) {
        alert("Format email tidak valid!");
        return;
      }

      try {
        const response = await fetch("system/users.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: "add",
            nama: nama,
            email: email,
            password: password
          })
        });

        const result = await response.json();

        if (result.success) {
          e.target.reset();
          bootstrap.Modal.getInstance(document.getElementById("addModal")).hide();
          fetchTable();
          console.log("✅ User berhasil ditambahkan:", result.message);
        } else {
          alert("Gagal menambahkan user: " + (result.message || "Terjadi kesalahan."));
        }
      } catch (err) {
        console.error("❌ Error:", err);
        alert("Gagal mengirim data ke server.");
      }
    });

    // Edit User
    function editData(id) {
      const user = usersList.find(u => u.id == id);
      if (!user) return;

      document.getElementById("editId").value = user.id;
      document.getElementById("editNama").value = user.nama;
      document.getElementById("editEmail").value = user.email;
      document.getElementById("editPassword").value = '';
      document.getElementById("editConfirmPassword").value = '';
      
      new bootstrap.Modal(document.getElementById("editModal")).show();
    }

    document.getElementById("editForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      
      const id = document.getElementById("editId").value;
      const nama = document.getElementById("editNama").value.trim();
      const email = document.getElementById("editEmail").value.trim();
      const password = document.getElementById("editPassword").value;
      const confirmPassword = document.getElementById("editConfirmPassword").value;

      // Validasi
      if (!nama || !email) {
        alert("Nama dan email wajib diisi!");
        return;
      }

      if (password && password.length < 6) {
        alert("Password minimal 6 karakter!");
        return;
      }

      if (password !== confirmPassword) {
        alert("Password dan konfirmasi password tidak cocok!");
        return;
      }

      if (!validateEmail(email)) {
        alert("Format email tidak valid!");
        return;
      }

      try {
        const response = await fetch("system/users.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: "edit",
            id: id,
            nama: nama,
            email: email,
            password: password || null
          })
        });

        const result = await response.json();

        if (result.success) {
          bootstrap.Modal.getInstance(document.getElementById("editModal")).hide();
          fetchTable();
          console.log("✅ User berhasil diubah:", result.message);
        } else {
          alert("Gagal mengubah user: " + (result.message || "Terjadi kesalahan."));
        }
      } catch (err) {
        console.error("❌ Error:", err);
        alert("Gagal mengirim data ke server.");
      }
    });

    // Hapus User
    function deleteData(id) {
      if (id === 1) {
        alert("User utama tidak dapat dihapus!");
        return;
      }

      if (confirm("Yakin ingin menghapus user ini?")) {
        fetch("system/users.php", {
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
            console.log("✅ User berhasil dihapus:", result.message);
          } else {
            alert("Gagal menghapus user: " + (result.message || "Terjadi kesalahan."));
          }
        })
        .catch(err => {
          console.error("❌ Error:", err);
          alert("Gagal mengirim data ke server.");
        });
      }
    }

    // Validasi email
    function validateEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    }

    // Pencarian
    document.getElementById("searchInput").addEventListener("keyup", function () {
      const keyword = this.value.toLowerCase();
      const rows = usersBody.querySelectorAll("tr");
      rows.forEach(row => {
        const nama = row.children[1].textContent.toLowerCase();
        const email = row.children[2].textContent.toLowerCase();
        const display = nama.includes(keyword) || email.includes(keyword) ? "" : "none";
        row.style.display = display;
      });
    });

    // Pagination
    function setupPagination() {
      pagination.innerHTML = "";
      const totalPages = Math.ceil(usersList.length / rowsPerPage);
      
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
      const rows = Array.from(usersBody.querySelectorAll("tr"));
      rows.forEach((row, i) => {
        row.style.display = (i >= start && i < end) ? "" : "none";
      });

      document.querySelectorAll("#pagination li").forEach(li => li.classList.remove("active"));
      if (pagination.children[page - 1]) {
        pagination.children[page - 1].classList.add("active");
      }
    }

    // Inisialisasi
    document.addEventListener("DOMContentLoaded", function() {
      setupPasswordToggles();
      fetchTable();
    });