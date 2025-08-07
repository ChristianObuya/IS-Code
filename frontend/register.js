document.getElementById('registerForm').addEventListener('submit', function (e) {
    const userID = document.getElementById('userID').value.trim();
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const role = document.getElementById('role').value;

    const messageEl = document.getElementById('message');
    messageEl.textContent = '';
    messageEl.style.color = 'red';

    if (!userID || !name || !email || !password || !role) {
        e.preventDefault();
        messageEl.textContent = 'All fields are required.';
        return;
    }

    if (!email.includes('@') || !email.includes('.')) {
        e.preventDefault();
        messageEl.textContent = 'Invalid email format.';
        return;
    }

    if (password.length < 6) {
        e.preventDefault();
        messageEl.textContent = 'Password must be at least 6 characters.';
        return;
    }

    if (role !== 'student' && role !== 'staff') {
        e.preventDefault();
        messageEl.textContent = 'Please select a valid role.';
        return;
    }
});
