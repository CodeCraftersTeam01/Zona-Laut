document.addEventListener("DOMContentLoaded", function () {

    const DPI = document.getElementById("dpi-counter");
    const PEMILIK = document.getElementById("pemilik-counter");
    const KAPAL = document.getElementById("kapal-counters");
    const USERS = document.getElementById("users-counter");


    fetch('system/counter.php')
        .then(response => response.json())
        .then(data => {
            DPI.textContent = data.jumlah_dpi ? data.jumlah_dpi : 0;
            PEMILIK.textContent = data.jumlah_pemilik ? data.jumlah_pemilik : 0;
            KAPAL.textContent = data.jumlah_kapal ? data.jumlah_kapal : 0;
            USERS.textContent = data.jumlah_user ? data.jumlah_user : 0;
        }).catch(error => {
            console.error('Error fetching counter data:', error);
        });
});