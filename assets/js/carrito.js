window.handleProductImageError = (img) => {
    img.style.display = 'none';
    if (img.nextElementSibling) {
        img.nextElementSibling.style.display = 'flex';
        img.nextElementSibling.classList.remove('d-none');
    }
};
