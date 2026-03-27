document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.querySelector('#register-form');
    const productForm = document.querySelector('#product-form');

    if (registerForm) {
        registerForm.addEventListener('submit', (event) => {
            const email = document.querySelector('#email').value.trim();
            const passwordInput = document.querySelector('#password');
            const password = passwordInput ? passwordInput.value.trim() : '';
            if (!email.includes('@')) {
                alert('Ingresa un correo válido.');
                event.preventDefault();
                return;
            }
            const mustValidatePassword =
                passwordInput &&
                (passwordInput.required || password.length > 0);

            if (mustValidatePassword && password.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres.');
                event.preventDefault();
            }
        });
    }

    if (productForm) {
        productForm.addEventListener('submit', (event) => {
            const price = parseFloat(document.querySelector('#precio').value || '0');
            const stock = parseInt(document.querySelector('#stock').value || '0', 10);
            if (price < 0 || stock < 0) {
                alert('Precio y stock deben ser valores positivos.');
                event.preventDefault();
            }
        });
    }
});
