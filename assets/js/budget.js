document.addEventListener('DOMContentLoaded', function () {
    const budgetInputs = document.querySelectorAll('.budget-input');
    const totalPercentageEl = document.getElementById('total-percentage');
    const totalWarningEl = document.getElementById('total-warning');

    function calculateTotal() {
        let total = 0;
        budgetInputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });

        totalPercentageEl.textContent = total + '%';
        totalPercentageEl.style.color = total > 100 ? '#dc3545' : '#212529';
        totalWarningEl.classList.toggle('d-none', total <= 100);
    }

    budgetInputs.forEach(input => {
        input.addEventListener('input', calculateTotal);
    });

    calculateTotal(); // Initial calculation on page load
});