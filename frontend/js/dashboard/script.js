let kapalData = [];
let dpiData = [];
let tangkapanData = [];

const totalKapalElement = document.getElementById("totalKapal");
const totalTangkapanElement = document.getElementById("totalTangkapan");
const totalDPIElement = document.getElementById("totalDPI");

// 🔹 Fetch data kapal
async function fetchKapalData() {
  try {
    const response = await fetch("../backend/system/kapal.php");
    const data = await response.json();

    if (data.success) {
      if (Array.isArray(data.data)) {
        kapalData = data.data.filter(
          (kapal) => kapal.status === 1 && kapal.verified_at !== null
        );
      } else if (typeof data.data === "object" && data.data !== null) {
        kapalData =
          data.data.status === 1 && data.data.verified_at !== null
            ? [data.data]
            : [];
      } else {
        kapalData = [];
      }
      return true;
    } else {
      throw new Error(data.message || "Failed to fetch kapal data");
    }
  } catch (error) {
    console.error("Error fetching kapal data:", error);
    showNotification("Gagal memuat data kapal", "error");
    return false;
  }
}

// 🔹 Fetch data DPI
async function fetchDPIData() {
  try {
    const response = await fetch("../backend/system/dpi.php");
    const data = await response.json();

    if (data.success) {
      dpiData = data.data;
      return true;
    } else {
      throw new Error(data.message || "Failed to fetch DPI data");
    }
  } catch (error) {
    console.error("Error fetching DPI data:", error);
    showNotification("Gagal memuat data DPI", "error");
    return false;
  }
}

// 🔹 Fetch data tangkapan
async function loadTangkapanData() {
  const loadingIndicator = document.getElementById("loadingIndicator");
  const errorState = document.getElementById("errorState");
  const emptyState = document.getElementById("emptyState");

  try {
    const response = await fetch("../backend/system/tangkapan.php");
    const result = await response.json();

    if (result.success && result.data) {
      let processedData = [];

      if (Array.isArray(result.data)) {
        processedData = result.data;
      } else if (typeof result.data === "object" && result.data !== null) {
        processedData = [result.data];
      }

      tangkapanData = processedData;
      // console.log("Tangkapan Data loaded:", tangkapanData);
      return true; // ✅ Kembalikan true bila berhasil
    } else {
      emptyState.classList.remove("hidden");
      return false;
    }
  } catch (error) {
    console.error("Error loading tangkapan data:", error);
    loadingIndicator.classList.add("hidden");
    errorState.classList.remove("hidden");
    document.getElementById("errorMessage").textContent =
      error.message || "Terjadi kesalahan saat memuat data tangkapan.";
    return false;
  }
}

// 🔹 Jalankan semua saat halaman selesai dimuat
document.addEventListener("DOMContentLoaded", async () => {
  const kapalLoaded = await fetchKapalData();
  const dpiLoaded = await fetchDPIData();
  const tangkapanLoaded = await loadTangkapanData();

  if (kapalLoaded) {
    totalKapalElement.textContent = kapalData.length;
  }

  if (dpiLoaded) {
    totalDPIElement.textContent = dpiData.length;
  }

  if (tangkapanLoaded) {
  // Hitung total semua berat ikan dari data tangkapan (dalam kg)
  const totalTangkapanKg = tangkapanData.reduce(
    (sum, item) => sum + (parseFloat(item.berat_ikan) || 0),
    0
  );

  // Konversi ke ton
  const totalTangkapanTon = totalTangkapanKg / 1000;

  // Tampilkan hasil di elemen dashboard, dibulatkan 2 angka desimal
  totalTangkapanElement.textContent = totalTangkapanTon.toFixed(2) + " ton";
}

});
