const type = document.querySelector('[data-discount-type]');
const value = document.querySelector('[data-discount-value]');
const help = document.querySelector('[data-discount-help]');

function updateDiscountHint() {
    if (!type || !value || !help) return;
    const percentage = type.value === 'percentage';
    value.max = percentage ? '100' : '1000000000';
    help.textContent = percentage
        ? 'Masukkan persentase bulat antara 1 dan 100.'
        : 'Masukkan rupiah utuh antara Rp1 dan Rp1.000.000.000.';
}

type?.addEventListener('change', updateDiscountHint);
updateDiscountHint();
document.querySelectorAll('form[method="post"]').forEach((form) => {
    form.addEventListener('submit', () => {
        form.querySelectorAll('button[type="submit"]:not(:disabled)').forEach((button) => {
            button.dataset.submitting = 'true';
            button.disabled = true;
        });
    });
});
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        document.querySelectorAll('button[data-submitting]').forEach((button) => {
            button.disabled = false;
            delete button.dataset.submitting;
        });
    }
});
