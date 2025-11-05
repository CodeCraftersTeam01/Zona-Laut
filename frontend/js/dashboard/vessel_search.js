export class VesselSearchSystem {
    constructor(dependencies = {}) {
        this.searchInput = document.querySelector('.search-input');
        this.allVessels = [];
        this.filteredVessels = [];
        
        // Inject dependencies dengan fallback
        this.vesselMarkers = dependencies.vesselMarkers || [];
        this.toastSystem = dependencies.toastSystem || this.createFallbackToast();
        this.map = dependencies.map;
        this.createVesselIcon = dependencies.createVesselIcon || this.createFallbackVesselIcon;
        
        this.setupSearch();
    }

    // Fallback methods untuk handling missing dependencies
    createFallbackToast() {
        return {
            success: (title, message) => console.log(`✅ ${title}: ${message}`),
            error: (title, message) => console.error(`❌ ${title}: ${message}`),
            info: (title, message) => console.info(`ℹ️ ${title}: ${message}`)
        };
    }

    createFallbackVesselIcon(type = 'trawler') {
        // Fallback vessel icon creation
        const colors = {
            trawler: '#3b82f6',
            longliner: '#10b981',
            'purse-seine': '#8b5cf6',
            default: '#6b7280'
        };
        const color = colors[type] || colors.default;
        
        return L.divIcon({
            className: `vessel-marker vessel-${type}`,
            html: `
                <div class="vessel-simple" style="border-color: ${color}">
                    <i class="fas fa-ship" style="color: ${color}"></i>
                </div>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
    }

    setupSearch() {
        if (!this.searchInput) {
            console.warn('Search input element not found');
            return;
        }

        // Debounce search input untuk performa
        let timeout;
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.handleSearch(e.target.value);
            }, 300);
        });

        // Handle enter key
        this.searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.handleSearch(e.target.value);
            }
        });
    }

    updateVesselData(vessels) {
        this.allVessels = vessels;
    }

    updateVesselMarkers(newVesselMarkers) {
        this.vesselMarkers = newVesselMarkers;
    }

    handleSearch(query) {
        if (!query.trim()) {
            this.clearSearch();
            return;
        }

        const searchTerm = query.toLowerCase().trim();

        // Filter vessels berdasarkan kriteria pencarian
        this.filteredVessels = this.allVessels.filter(vessel => {
            return (
                vessel.nama_kapal?.toLowerCase().includes(searchTerm) ||
                vessel.jenis_kapal?.toLowerCase().includes(searchTerm) ||
                vessel.nama_pemilik?.toLowerCase().includes(searchTerm) ||
                vessel.nama_dpi?.toLowerCase().includes(searchTerm) ||
                vessel.id?.toString().includes(searchTerm)
            );
        });

        this.displaySearchResults();
    }

    displaySearchResults() {
        // Highlight vessels yang sesuai dengan pencarian
        this.highlightMatchingVessels();

        // Jika hanya ada 1 hasil, langsung focus ke vessel tersebut
        if (this.filteredVessels.length === 1) {
            this.focusOnVessel(this.filteredVessels[0]);
        } else if (this.filteredVessels.length > 1) {
            this.showMultipleResults();
        } else if (this.filteredVessels.length === 0 && this.searchInput.value.trim()) {
            this.toastSystem.error('Pencarian Tidak Ditemukan', 'Tidak ada kapal yang sesuai dengan kriteria pencarian');
        }
    }

    highlightMatchingVessels() {
        // Reset semua marker ke warna normal
        this.vesselMarkers.forEach(({ marker, vesselId }) => {
            const vessel = this.allVessels.find(v => v.id === vesselId);
            if (vessel) {
                this.resetVesselMarker(marker, vessel);
            }
        });

        // Highlight vessels yang match
        this.filteredVessels.forEach(vessel => {
            const vesselMarker = this.vesselMarkers.find(v => v.vesselId === vessel.id);
            if (vesselMarker) {
                this.highlightVesselMarker(vesselMarker.marker);
            }
        });

        // Fit map untuk menampilkan semua hasil pencarian
        if (this.filteredVessels.length > 0) {
            this.fitMapToResults();
        }
    }

    resetVesselMarker(marker, vessel) {
        const vesselType = vessel.jenis_kapal?.toLowerCase().includes('long') ? 'longliner' :
            vessel.jenis_kapal?.toLowerCase().includes('purse') ? 'purse-seine' : 'trawler';

        marker.setIcon(this.createVesselIcon(vesselType));
    }

    highlightVesselMarker(marker) {
        // Buat icon khusus untuk highlight
        const highlightIcon = L.divIcon({
            className: 'vessel-marker vessel-highlight',
            html: `
                <div class="vessel-simple" style="border-color: #ff6b00; background: #fff8f0; box-shadow: 0 0 20px rgba(255, 107, 0, 0.5);">
                    <i class="fas fa-ship" style="color: #ff6b00;"></i>
                </div>
            `,
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        marker.setIcon(highlightIcon);

        // Buka popup otomatis
        if (marker.getPopup()) {
            marker.openPopup();
        }
    }

    focusOnVessel(vessel) {
        const vesselMarker = this.vesselMarkers.find(v => v.vesselId === vessel.id);
        if (vesselMarker && this.map) {
            this.map.setView(vesselMarker.marker.getLatLng(), 13);
            if (vesselMarker.marker.getPopup()) {
                vesselMarker.marker.openPopup();
            }

            // Show success message
            this.toastSystem.success('Kapal Ditemukan', `Menampilkan ${vessel.nama_kapal}`);
        }
    }

    showMultipleResults() {
        const vesselNames = this.filteredVessels.map(v => v.nama_kapal).join(', ');
        this.toastSystem.success(
            `${this.filteredVessels.length} Kapal Ditemukan`,
            `Menampilkan: ${vesselNames}`
        );
    }

    fitMapToResults() {
        if (!this.map || !L.LatLngBounds) return;

        const bounds = new L.LatLngBounds();

        this.filteredVessels.forEach(vessel => {
            const vesselMarker = this.vesselMarkers.find(v => v.vesselId === vessel.id);
            if (vesselMarker) {
                bounds.extend(vesselMarker.marker.getLatLng());
            }
        });

        if (bounds.isValid()) {
            this.map.fitBounds(bounds, {
                padding: [50, 50]
            });
        }
    }

    clearSearch() {
        this.filteredVessels = [];

        // Reset semua marker ke normal
        this.vesselMarkers.forEach(({ marker, vesselId }) => {
            const vessel = this.allVessels.find(v => v.id === vesselId);
            if (vessel) {
                this.resetVesselMarker(marker, vessel);
            }
        });

        // Close semua popup
        this.vesselMarkers.forEach(({ marker }) => {
            if (marker.getPopup()) {
                marker.closePopup();
            }
        });

        this.searchInput.value = '';
    }
}