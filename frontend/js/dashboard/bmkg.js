// ===============================
// SISTEM CUACA BMKG - LEVEL KABUPATEN/KOTA
// ===============================

export class WeatherSystem {
    constructor() {
        this.baseUrl = 'https://api.bmkg.go.id/publik/prakiraan-cuaca';
        this.cache = new Map();
        // Database kode wilayah kabupaten/kota utama
        this.kabupatenAreaCodes = {
            // Aceh
            'banda-aceh': '11.71.01.1001',
            'sabang': '11.72.01.1001',
            'lholseumawe': '11.73.01.1001',
            'langsa': '11.74.01.1001',
            
            // Sumatra Utara
            'medan': '12.71.01.1001',
            'binjai': '12.72.01.1001',
            'tebing-tinggi': '12.73.01.1001',
            'pematang-siantar': '12.74.01.1001',
            
            // Sumatra Barat
            'padang': '13.71.01.1001',
            'solok': '13.72.01.1001',
            'sawahlunto': '13.73.01.1001',
            'padang-panjang': '13.74.01.1001',
            'bukittinggi': '13.75.01.1001',
            'payakumbuh': '13.76.01.1001',
            'pariman': '13.77.01.1001',
            
            // Riau
            'pekanbaru': '14.71.01.1001',
            'dumai': '14.72.01.1001',
            
            // Jambi
            'jambi': '15.71.01.1001',
            'sungaipenuh': '15.72.01.1001',
            
            // Sumatra Selatan
            'palembang': '16.71.01.1001',
            'prabumulih': '16.74.01.1001',
            'pagarlam': '16.75.01.1001',
            'lubuklinggau': '16.73.01.1001',
            
            // Bengkulu
            'bengkulu': '17.71.01.1001',
            
            // Lampung
            'bandar-lampung': '18.71.01.1001',
            'metro': '18.72.01.1001',
            
            // Kepulauan Bangka Belitung
            'pangkal-pinang': '19.71.01.1001',
            
            // Kepulauan Riau
            'tanjung-pinang': '21.71.01.1001',
            'batam': '21.71.02.1001',
            
            // DKI Jakarta
            'jakarta-pusat': '31.71.01.1001',
            'jakarta-utara': '31.71.02.1001',
            'jakarta-barat': '31.71.03.1001',
            'jakarta-selatan': '31.71.04.1001',
            'jakarta-timur': '31.71.05.1001',
            
            // Jawa Barat
            'bandung': '32.73.01.1001',
            'bekasi': '32.75.01.1001',
            'bogor': '32.71.01.1001',
            'cimahi': '32.77.01.1001',
            'cirebon': '32.74.01.1001',
            'depok': '32.76.01.1001',
            'sukabumi': '32.72.01.1001',
            'tasikmalaya': '32.78.01.1001',
            
            // Jawa Tengah
            'semarang': '33.74.01.1001',
            'surakarta': '33.72.01.1001',
            'pekalongan': '33.75.01.1001',
            'tegal': '33.76.01.1001',
            'magelang': '33.71.01.1001',
            
            // DI Yogyakarta
            'yogyakarta': '34.71.01.1001',
            
            // Jawa Timur
            'surabaya': '35.78.01.1001',
            'malang': '35.73.01.1001',
            'kediri': '35.71.01.1001',
            'blitar': '35.72.01.1001',
            'madiun': '35.77.01.1001',
            'pasuruan': '35.75.01.1001',
            'mojokerto': '35.76.01.1001',
            
            // Banten
            'serang': '36.73.01.1001',
            'cilegon': '36.72.01.1001',
            'tangerang': '36.71.01.1001',
            'tangerang-selatan': '36.74.01.1001',
            
            // Bali
            'denpasar': '51.71.01.1001',
            
            // Nusa Tenggara Barat
            'mataram': '52.71.01.1001',
            'bima': '52.72.01.1001',
            
            // Nusa Tenggara Timur
            'kupang': '53.71.01.1001',
            
            // Kalimantan Barat
            'pontianak': '61.71.01.1001',
            'singkawang': '61.72.01.1001',
            
            // Kalimantan Tengah
            'palangkaraya': '62.71.01.1001',
            
            // Kalimantan Selatan
            'banjarmasin': '63.71.01.1001',
            'banjarbaru': '63.72.01.1001',
            
            // Kalimantan Timur
            'samarinda': '64.71.01.1001',
            'balikpapan': '64.72.01.1001',
            'bontang': '64.74.01.1001',
            
            // Kalimantan Utara
            'tanjung-selor': '65.71.01.1001',
            'tarakan': '65.72.01.1001',
            
            // Sulawesi Utara
            'manado': '71.71.01.1001',
            'bitung': '71.72.01.1001',
            'tomohon': '71.73.01.1001',
            'kotamobagu': '71.74.01.1001',
            
            // Sulawesi Tengah
            'palu': '72.71.01.1001',
            
            // Sulawesi Selatan
            'makassar': '73.71.01.1001',
            'parepare': '73.72.01.1001',
            'palopo': '73.73.01.1001',
            
            // Sulawesi Tenggara
            'kendari': '74.71.01.1001',
            'baubau': '74.72.01.1001',
            
            // Gorontalo
            'gorontalo': '75.71.01.1001',
            
            // Sulawesi Barat
            'mamuju': '76.71.01.1001',
            
            // Maluku
            'ambon': '81.71.01.1001',
            'tual': '81.72.01.1001',
            
            // Maluku Utara
            'ternate': '82.71.01.1001',
            'tidore': '82.72.01.1001',
            
            // Papua
            'jayapura': '91.71.01.1001',
            
            // Papua Barat
            'manokwari': '92.71.01.1001',
            'sorong': '92.71.02.1001'
        };
    }

    async getWeather(lat, lng) {
        const cacheKey = `${lat.toFixed(4)},${lng.toFixed(4)}`;
        
        // Cache hanya 10 menit
        if (this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < 10 * 60 * 1000) {
                return cached.data;
            }
        }

        try {
            console.log(`🔍 Mencari data BMKG untuk: ${lat}, ${lng}`);
            
            // Step 1: Deteksi kabupaten terdekat berdasarkan koordinat
            const kabupaten = this.findNearestKabupaten(lat, lng);
            
            if (!kabupaten) {
                throw new Error('Tidak dapat menemukan kabupaten terdekat');
            }

            console.log(`📍 Kabupaten terdekat: ${kabupaten.name}`);

            // Step 2: Ambil data cuaca dari API BMKG
            const weatherData = await this.fetchBMKGWeatherData(kabupaten.code);
            
            if (!weatherData || !weatherData.cuaca || weatherData.cuaca.length === 0) {
                throw new Error('Data cuaca tidak tersedia');
            }

            const result = this.formatWeatherData(weatherData, kabupaten.name);
            this.cache.set(cacheKey, { data: result, timestamp: Date.now() });
            
            console.log('✅ Data BMKG berhasil diambil:', result);
            return result;

        } catch (error) {
            console.error('❌ Error mengambil data BMKG:', error);
            return null;
        }
    }

    findNearestKabupaten(lat, lng) {
        // Koordinat pusat kabupaten/kota utama
        const kabupatenCoordinates = {
            // Aceh
            'banda-aceh': { lat: 5.5482, lng: 95.3237, name: 'Banda Aceh', code: '11.71.01.1001' },
            'sabang': { lat: 5.8943, lng: 95.3234, name: 'Sabang', code: '11.72.01.1001' },
            
            // Sumatra Utara
            'medan': { lat: 3.5952, lng: 98.6722, name: 'Medan', code: '12.71.01.1001' },
            'binjai': { lat: 3.6001, lng: 98.4854, name: 'Binjai', code: '12.72.01.1001' },
            
            // Sumatra Barat
            'padang': { lat: -0.9471, lng: 100.4172, name: 'Padang', code: '13.71.01.1001' },
            'bukittinggi': { lat: -0.3056, lng: 100.3692, name: 'Bukittinggi', code: '13.75.01.1001' },
            
            // Riau
            'pekanbaru': { lat: 0.5071, lng: 101.4478, name: 'Pekanbaru', code: '14.71.01.1001' },
            
            // Jambi
            'jambi': { lat: -1.6101, lng: 103.6071, name: 'Jambi', code: '15.71.01.1001' },
            
            // Sumatra Selatan
            'palembang': { lat: -2.9761, lng: 104.7754, name: 'Palembang', code: '16.71.01.1001' },
            
            // Bengkulu
            'bengkulu': { lat: -3.7956, lng: 102.2592, name: 'Bengkulu', code: '17.71.01.1001' },
            
            // Lampung
            'bandar-lampung': { lat: -5.4294, lng: 105.2620, name: 'Bandar Lampung', code: '18.71.01.1001' },
            
            // Kepulauan Bangka Belitung
            'pangkal-pinang': { lat: -2.1333, lng: 106.1333, name: 'Pangkal Pinang', code: '19.71.01.1001' },
            
            // Kepulauan Riau
            'batam': { lat: 1.0456, lng: 104.0305, name: 'Batam', code: '21.71.02.1001' },
            'tanjung-pinang': { lat: 0.9186, lng: 104.4554, name: 'Tanjung Pinang', code: '21.71.01.1001' },
            
            // DKI Jakarta
            'jakarta-pusat': { lat: -6.1862, lng: 106.8341, name: 'Jakarta Pusat', code: '31.71.01.1001' },
            'jakarta-utara': { lat: -6.1333, lng: 106.8333, name: 'Jakarta Utara', code: '31.71.02.1001' },
            
            // Jawa Barat
            'bandung': { lat: -6.9175, lng: 107.6191, name: 'Bandung', code: '32.73.01.1001' },
            'bogor': { lat: -6.5971, lng: 106.8060, name: 'Bogor', code: '32.71.01.1001' },
            'bekasi': { lat: -6.2383, lng: 106.9756, name: 'Bekasi', code: '32.75.01.1001' },
            'depok': { lat: -6.4025, lng: 106.7942, name: 'Depok', code: '32.76.01.1001' },
            
            // Jawa Tengah
            'semarang': { lat: -6.9667, lng: 110.4167, name: 'Semarang', code: '33.74.01.1001' },
            'surakarta': { lat: -7.5667, lng: 110.8167, name: 'Surakarta', code: '33.72.01.1001' },
            
            // DI Yogyakarta
            'yogyakarta': { lat: -7.7972, lng: 110.3688, name: 'Yogyakarta', code: '34.71.01.1001' },
            
            // Jawa Timur
            'surabaya': { lat: -7.2504, lng: 112.7688, name: 'Surabaya', code: '35.78.01.1001' },
            'malang': { lat: -7.9666, lng: 112.6326, name: 'Malang', code: '35.73.01.1001' },
            
            // Banten
            'tangerang': { lat: -6.1783, lng: 106.6319, name: 'Tangerang', code: '36.71.01.1001' },
            'serang': { lat: -6.1200, lng: 106.1503, name: 'Serang', code: '36.73.01.1001' },
            
            // Bali
            'denpasar': { lat: -8.6500, lng: 115.2167, name: 'Denpasar', code: '51.71.01.1001' },
            
            // Nusa Tenggara Barat
            'mataram': { lat: -8.5833, lng: 116.1167, name: 'Mataram', code: '52.71.01.1001' },
            
            // Nusa Tenggara Timur
            'kupang': { lat: -10.1833, lng: 123.5833, name: 'Kupang', code: '53.71.01.1001' },
            
            // Kalimantan Barat
            'pontianak': { lat: -0.0263, lng: 109.3425, name: 'Pontianak', code: '61.71.01.1001' },
            
            // Kalimantan Tengah
            'palangkaraya': { lat: -2.2100, lng: 113.9200, name: 'Palangkaraya', code: '62.71.01.1001' },
            
            // Kalimantan Selatan
            'banjarmasin': { lat: -3.3199, lng: 114.5908, name: 'Banjarmasin', code: '63.71.01.1001' },
            
            // Kalimantan Timur
            'samarinda': { lat: -0.5022, lng: 117.1536, name: 'Samarinda', code: '64.71.01.1001' },
            'balikpapan': { lat: -1.2635, lng: 116.8275, name: 'Balikpapan', code: '64.72.01.1001' },
            
            // Kalimantan Utara
            'tarakan': { lat: 3.3000, lng: 117.6333, name: 'Tarakan', code: '65.72.01.1001' },
            
            // Sulawesi Utara
            'manado': { lat: 1.5016, lng: 124.8440, name: 'Manado', code: '71.71.01.1001' },
            
            // Sulawesi Tengah
            'palu': { lat: -0.8917, lng: 119.8707, name: 'Palu', code: '72.71.01.1001' },
            
            // Sulawesi Selatan
            'makassar': { lat: -5.1477, lng: 119.4327, name: 'Makassar', code: '73.71.01.1001' },
            
            // Sulawesi Tenggara
            'kendari': { lat: -3.9674, lng: 122.5948, name: 'Kendari', code: '74.71.01.1001' },
            
            // Gorontalo
            'gorontalo': { lat: 0.5333, lng: 123.0667, name: 'Gorontalo', code: '75.71.01.1001' },
            
            // Sulawesi Barat
            'mamuju': { lat: -2.6749, lng: 118.8935, name: 'Mamuju', code: '76.71.01.1001' },
            
            // Maluku
            'ambon': { lat: -3.6954, lng: 128.1814, name: 'Ambon', code: '81.71.01.1001' },
            
            // Maluku Utara
            'ternate': { lat: 0.7833, lng: 127.3667, name: 'Ternate', code: '82.71.01.1001' },
            
            // Papua
            'jayapura': { lat: -2.5489, lng: 140.7183, name: 'Jayapura', code: '91.71.01.1001' },
            
            // Papua Barat
            'manokwari': { lat: -0.8615, lng: 134.0620, name: 'Manokwari', code: '92.71.01.1001' },
            'sorong': { lat: -0.8667, lng: 131.2500, name: 'Sorong', code: '92.71.02.1001' }
        };

        let nearestKabupaten = null;
        let minDistance = Infinity;

        for (const [key, kab] of Object.entries(kabupatenCoordinates)) {
            const distance = this.calculateDistance(lat, lng, kab.lat, kab.lng);
            
            if (distance < minDistance) {
                minDistance = distance;
                nearestKabupaten = kab;
            }
        }

        return nearestKabupaten;
    }

    async fetchBMKGWeatherData(areaCode) {
        try {
            console.log(`🌤️ Mengambil data cuaca untuk kode: ${areaCode}`);
            const response = await fetch(`${this.baseUrl}?adm4=${areaCode}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data || !data.data || data.data.length === 0) {
                throw new Error('Data cuaca kosong dari API');
            }

            return data.data[0];
        } catch (error) {
            console.error(`❌ Gagal mengambil data cuaca untuk ${areaCode}:`, error);
            return null;
        }
    }

    formatWeatherData(weatherData, kabupatenName) {
        try {
            // Ambil data cuaca terbaru
            const latestWeather = weatherData.cuaca[0][0];
            
            if (!latestWeather) {
                throw new Error('Data cuaca tidak valid');
            }

            return {
                location: kabupatenName,
                temperature: latestWeather.t,
                humidity: latestWeather.hu,
                windSpeed: latestWeather.ws,
                weather: latestWeather.weather_desc,
                windDirection: latestWeather.wd,
                visibility: latestWeather.vs_text,
                cloudCover: latestWeather.tcc,
                lastUpdate: this.formatDateTime(latestWeather.local_datetime),
                source: 'BMKG',
                rawData: latestWeather
            };
        } catch (error) {
            console.error('Error formatting weather data:', error);
            return null;
        }
    }

    formatDateTime(dateTimeString) {
        try {
            const [date, time] = dateTimeString.split(' ');
            const [year, month, day] = date.split('-');
            return `${day}/${month}/${year} ${time.substring(0, 5)}`;
        } catch (error) {
            return new Date().toLocaleTimeString('id-ID');
        }
    }

    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    getWeatherIcon(condition) {
        if (!condition) return 'fa-cloud';
        
        const conditionLower = condition.toLowerCase();
        if (conditionLower.includes('cerah') && !conditionLower.includes('berawan')) return 'fa-sun';
        if (conditionLower.includes('cerah berawan')) return 'fa-cloud-sun';
        if (conditionLower.includes('berawan')) return 'fa-cloud';
        if (conditionLower.includes('hujan')) return 'fa-cloud-rain';
        if (conditionLower.includes('petir')) return 'fa-bolt';
        if (conditionLower.includes('kabut')) return 'fa-smog';
        return 'fa-cloud';
    }

    getWaveHeightFromWind(windSpeed) {
        if (!windSpeed) return null;
        if (windSpeed < 5) return '0.3-0.6';
        if (windSpeed < 10) return '0.6-1.2';
        if (windSpeed < 15) return '1.2-2.0';
        if (windSpeed < 20) return '2.0-3.0';
        return '3.0-4.0';
    }
}

// Initialize weather system
const weatherSystem = new WeatherSystem();