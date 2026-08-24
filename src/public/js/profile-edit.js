// public/js/profile-edit.js
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image-input');
    const profilePreview = document.getElementById('profile-preview');

    if (imageInput && profilePreview) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // 画像のパスをセットして、hiddenクラスを外す
                    profilePreview.src = e.target.result;
                    profilePreview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
