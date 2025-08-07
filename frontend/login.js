document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');

    form.addEventListener('submit', function (e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        const expectedRole = document.getElementById('expectedRole').value;

        if (email === '' || password === '' || expectedRole === '') {
            e.preventDefault();
            alert('All fields are required');
        }
    });
});