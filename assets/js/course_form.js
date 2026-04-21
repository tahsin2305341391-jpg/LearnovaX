// ============================================
// LearnovaX – Course Form JS
// assets/js/course_form.js
// ============================================

document.addEventListener('DOMContentLoaded', function () {

    // ── Image Preview ────────────────────────
    const thumbInput   = document.getElementById('thumbnail');
    const previewBox   = document.getElementById('imagePreview');
    const previewImg   = document.getElementById('previewImg');

    if (thumbInput) {
        thumbInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            // Validate type
            const allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowed.includes(file.type)) {
                alert('Please select a JPG, PNG or WEBP image.');
                this.value = '';
                if (previewBox) previewBox.style.display = 'none';
                return;
            }

            // Validate size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Image must be under 2MB.');
                this.value = '';
                if (previewBox) previewBox.style.display = 'none';
                return;
            }

            // Show preview
            if (previewBox && previewImg) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewBox.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ── Form Validation ──────────────────────
    const courseForm = document.getElementById('courseForm');
    if (courseForm) {
        courseForm.addEventListener('submit', function (e) {
            let valid = true;
            clearErrors();

            const title       = document.getElementById('title');
            const description = document.getElementById('description');
            const price       = document.getElementById('price');
            const level       = document.getElementById('level');
            const category    = document.getElementById('category_id');

            // Title
            if (title && !title.value.trim()) {
                showError('titleErr', 'Course title is required.');
                valid = false;
            } else if (title && title.value.trim().length < 5) {
                showError('titleErr', 'Title must be at least 5 characters.');
                valid = false;
            }

            // Description
            if (description && !description.value.trim()) {
                showError('descErr', 'Description is required.');
                valid = false;
            } else if (description && description.value.trim().length < 20) {
                showError('descErr', 'Description must be at least 20 characters.');
                valid = false;
            }

            // Price
            if (price && (price.value === '' || isNaN(price.value) || parseFloat(price.value) < 0)) {
                showError('priceErr', 'Please enter a valid price (0 for free).');
                valid = false;
            }

            // Level
            if (level && !level.value) {
                showError('levelErr', 'Please select a level.');
                valid = false;
            }

            // Category
            if (category && !category.value) {
                showError('categoryErr', 'Please select a category.');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    // ── Helpers ──────────────────────────────
    function showError(id, message) {
        const el = document.getElementById(id);
        if (el) el.textContent = message;
    }

    function clearErrors() {
        document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
    }

    // ── Clear field error on input ────────────
    document.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input', function () {
            const errId = this.id + 'Err';
            const errEl = document.getElementById(errId);
            if (errEl) errEl.textContent = '';
        });
    });
});
