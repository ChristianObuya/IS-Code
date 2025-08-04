const loginForm = document.getElementById('loginForm');
const message = document.getElementById('message');

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    message.textContent = '';
    message.style.color = 'red';

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        const response = await fetch('../backend/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        });

        const result = await response.json();

        if (result.success) {
            if (result.role === 'student') {
                window.location.href = 'student_home.html';
            } else if (result.role === 'staff') {
                window.location.href = 'staff_dashboard.html';
            }
        } else {
            message.textContent = result.message; // Show real error
        }
    } catch (error) {
        message.textContent = 'Network error. Check console.';
        console.error('Login error:', error);
    }
});