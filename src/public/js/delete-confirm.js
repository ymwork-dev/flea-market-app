document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('delete-modal');
    const openBtn = document.querySelector('.js-delete-open');
    const cancelBtn = document.querySelector('.js-delete-cancel');

    if (openBtn) {
        openBtn.addEventListener('click', function () {
            modal.classList.add('is-show');
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            modal.classList.remove('is-show');
        });
    }
});
