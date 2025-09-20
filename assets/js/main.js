document.addEventListener('DOMContentLoaded', function () {
    console.log('Expense Manager loaded');

    // Make expense table rows clickable
    const rows = document.querySelectorAll('table tbody tr[data-href]');

    rows.forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', (e) => {
            // Don't navigate if a button or link inside the row was clicked
            if (e.target.closest('a, button')) {
                return;
            }
            window.location.href = row.dataset.href;
        });
    });

});
