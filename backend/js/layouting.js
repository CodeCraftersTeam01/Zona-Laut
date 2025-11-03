document.addEventListener("DOMContentLoaded", function(){

    const sidebar = document.getElementById("sidebar");
    fetch("components/side.html").then(response => {
        return response.text();
    }).then(data => {
        sidebar.innerHTML = data;
    });
});