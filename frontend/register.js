document.getElementById('registerForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const userID = document.getElementById('userID').value;
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;
    const messageEl = document.getElementById('message');

    messageEl.textContent = '';
    messageEl.style.color = 'red';

    try {
        const response = await fetch('../backend/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `userID=${encodeURIComponent(userID)}&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&role=${encodeURIComponent(role)}`
        });

        const result = await response.json();

        if (result.success) {
            messageEl.style.color = 'green';
            messageEl.textContent = result.message;
            document.getElementById('registerForm').reset();
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
        } else {
            messageEl.textContent = result.message;
        }
    } catch (error) {
        messageEl.textContent = 'An error occurred. Please try again.';
        console.error('Registration error:', error);
    }
});