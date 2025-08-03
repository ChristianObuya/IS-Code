document.getElementById('loginForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const messageEl = document.getElementById('message');

    messageEl.textContent = '';
    messageEl.style.color = 'red';

    try {
        const response = await fetch('../backend/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        });

        const result = await response.json();

        if (result.success) {
            messageEl.style.color = 'green';
            messageEl.textContent = `Welcome, ${result.name}!`;
            setTimeout(() => {
                if (result.role === 'student') {
                    window.location.href = 'student_home.html';
                } else {
                    window.location.href = 'staff_dashboard.html';
                }
            }, 1000);
        } else {
            messageEl.textContent = result.message;
        }
    } catch (error) {
        messageEl.textContent = 'An error occurred. Please try again.';
        console.error('Login error:', error);
    }
});