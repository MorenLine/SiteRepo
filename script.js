document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('buyModal');
    const closeButton = document.querySelector('.close-button');
    const buyButtons = document.querySelectorAll('.buy-button');
    const buyForm = document.getElementById('buyForm');
    const phoneInput = document.getElementById('phone');
    const consentCheckbox = document.getElementById('consent');

    const phoneMask = IMask(phoneInput, {
        mask: '+{7}(000)000-00-00'
    });

    const sliders = document.querySelectorAll('.slider');
    
    sliders.forEach(slider => {
        const dots = slider.parentElement.querySelectorAll('.dot');
        const slides = slider.querySelectorAll('.slide');
        
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                const slideIndex = dot.getAttribute('data-slide');
                
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(d => d.classList.remove('active'));

                slides[slideIndex].classList.add('active');
                dot.classList.add('active');
            });
        });
    });

    buyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card');
            const productData = JSON.parse(productCard.dataset.product);
            
            const activeSlide = productCard.querySelector('.slide.active');
            
            document.querySelector('.modal-product-image').src = activeSlide.src;
            document.querySelector('.modal-product-title').textContent = productData.name;
            document.querySelector('.modal-product-price').innerHTML = productCard.querySelector('.product-price').innerHTML;
            document.querySelector('.modal-product-class').textContent = productData.class;
            
            modal.style.display = 'block';
        });
    });

    closeButton.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    function showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        errorElement.textContent = message;
        errorElement.classList.add('show');
        const inputElement = document.getElementById(elementId.replace('Error', ''));
        inputElement.classList.add('error');
    }
    function hideError(elementId) {
        const errorElement = document.getElementById(elementId);
        errorElement.classList.remove('show');
        const inputElement = document.getElementById(elementId.replace('Error', ''));
        inputElement.classList.remove('error');
    }

    buyForm.addEventListener('submit', function(e) {
        e.preventDefault();

        hideError('nameError');
        hideError('phoneError');
        hideError('consentError');

        const name = document.getElementById('name').value;
        const phone = phoneInput.value;
        const consent = consentCheckbox.checked;

        const productName = document.querySelector('.modal-product-title').textContent;
        const productPriceElement = document.querySelector('.modal-product-price');

        const productPrice = productPriceElement.textContent.split('₽')[0].trim().replace(/[^\d]/g, '');
        const productClass = document.querySelector('.modal-product-class').textContent;

        fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_name: productName,
                product_price: productPrice,
                customer_name: name,
                customer_phone: phone,
                consent: consent
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                buyForm.reset();
                modal.style.display = 'none';
                alert('Спасибо за заказ! Мы свяжемся с вами в ближайшее время.');
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorMessage = data.errors[field];
                        switch(field) {
                            case 'customer_name':
                                showError('nameError', errorMessage);
                                break;
                            case 'customer_phone':
                                showError('phoneError', errorMessage);
                                break;
                            case 'consent':
                                showError('consentError', errorMessage);
                                break;
                        }
                    });
                } else {
                    alert('Произошла ошибка при оформлении заказа. Пожалуйста, попробуйте позже.');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при оформлении заказа. Пожалуйста, попробуйте позже.');
        });
    });

    document.getElementById('name').addEventListener('input', () => hideError('nameError'));
    phoneInput.addEventListener('input', () => hideError('phoneError'));
    consentCheckbox.addEventListener('change', () => hideError('consentError'));
}); 