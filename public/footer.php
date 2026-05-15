</div> <!-- container -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', () => {

    const expenseForm = document.getElementById('expenseForm');

    if (expenseForm) {

        expenseForm.addEventListener('submit', async function(e) {

            e.preventDefault();

            const formData = new FormData(expenseForm);

            try {

                const response = await fetch('add_expense.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {

                    document.getElementById('expenseAlert').innerHTML = `
                        <div class="alert alert-success">
                            Expense added successfully!
                        </div>
                    `;

                    expenseForm.reset();

                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);

                } else if (result.status === 'warning') {

                    document.getElementById('expenseAlert').innerHTML = `
                        <div class="alert alert-warning">
                            ${result.message}
                        </div>
                    `;

                } else {

                    document.getElementById('expenseAlert').innerHTML = `
                        <div class="alert alert-danger">
                            Something went wrong.
                        </div>
                    `;
                }

            } catch (error) {

                console.error(error);

                document.getElementById('expenseAlert').innerHTML = `
                    <div class="alert alert-danger">
                        Server error occurred.
                    </div>
                `;
            }

        });

    }

});

</script>
</body>
</html>
