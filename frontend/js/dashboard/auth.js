function checkAuth() {
    const token = localStorage.getItem('authToken');
    const user = localStorage.getItem('currentUser');

    if (!token || !user) {
        window.location.href = '../auth/login';
        return;
    }

    try {
        const userData = JSON.parse(user);
        sessionStorage.setItem('currentUser', user);
        document.getElementById('userName').textContent = userData.username;
        document.getElementById('userAvatar').textContent = userData.username.charAt(0).toUpperCase();
    } catch (error) {
        console.error('Error parsing user data:', error);
        // window.location.href = '../auth/login';
    }
}