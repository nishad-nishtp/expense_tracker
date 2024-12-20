<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Expense Tracker</h2>
        <div class="row">
            <div class="col-md-6">
                <h4>Filter Expenses</h4>
                <form id="filterForm">
                    <div class="form-group">
                        <label for="filterCategory">Category</label>
                        <select class="form-control" id="filterCategory" name="category_id">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filterStartDate">Start Date</label>
                        <input type="date" class="form-control" id="filterStartDate" name="start_date">
                    </div>
                    <div class="form-group">
                        <label for="filterEndDate">End Date</label>
                        <input type="date" class="form-control" id="filterEndDate" name="end_date">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h4>Add/Edit Expense</h4>
                <form id="expenseForm">
                    <input type="hidden" id="expenseId" name="id">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select class="form-control" id="category" name="category_id" required></select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" class="form-control" id="description" name="description">
                    </div>
                    <div class="form-group">
                        <label for="expense_date">Date</label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </form>
            </div>
            <div class="col-md-6">
                <h4>Expenses</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="expensesList"></tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4>Expenses Summary</h4>
                <canvas id="expensesChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            fetchCategories();
            fetchExpenses();
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                fetchExpenses();
            });

            $('#expenseForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                const id = $('#expenseId').val();
                const url = id ? `/expenses/${id}` : '/expenses';
                const method = id ? 'PUT' : 'POST';
                const _token = $("input[name=_token]").val();

                $.ajax({
                    url: url,
                    method: method,
                    headers: { 'X-CSRF-TOKEN': _token },
                    data: formData,
                    success: function(response) {
                        alert('Expense saved successfully!');
                        fetchExpenses();
                        $('#expenseForm')[0].reset();
                        $('#expenseId').val('');
                    },
                    error: function(error) {
                        alert('Error saving expense.');
                    }
                });
            });

            function fetchCategories() {
                $.ajax({
                    url: '/categories',
                    method: 'GET',
                    success: function(categories) {
                        const options = categories.map(category => `<option value="${category.id}">${category.name}</option>`);
                        $('#category').html(options);
                        $('#filterCategory').append(options);
                    },
                    error: function() {
                        alert('Error fetching categories.');
                    }
                });
            }

            function fetchExpenses() {

                const categoryId = $('#filterCategory').val();
                const startDate = $('#filterStartDate').val();
                const endDate = $('#filterEndDate').val();

                const params = {
                    category_id: categoryId,
                    start_date: startDate,
                    end_date: endDate
                };

                $.ajax({
                    url: '/expenses',
                    method: 'GET',
                    data: params,
                    success: function(expenses) {
                        const rows = expenses.map(expense => `
                            <tr>
                                <td>${expense.category.name}</td>
                                <td>${expense.amount}</td>
                                <td>${expense.description || ''}</td>
                                <td>${expense.expense_date}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning editExpense" data-id="${expense.id}" data-category-id="${expense.category_id}" data-amount="${expense.amount}" data-description="${expense.description}" data-expense-date="${expense.expense_date}">Edit</button>
                                    <button class="btn btn-sm btn-danger deleteExpense" data-id="${expense.id}">Delete</button>
                                </td>
                            </tr>
                        `);
                        $('#expensesList').html(rows);
                        updateChart(expenses);

                        $('.editExpense').on('click', function() {
                            const id = $(this).data('id');
                            const categoryId = $(this).data('category-id');
                            const amount = $(this).data('amount');
                            const description = $(this).data('description');
                            const expenseDate = $(this).data('expense-date');

                            $('#expenseId').val(id);
                            $('#category').val(categoryId);
                            $('#amount').val(amount);
                            $('#description').val(description);
                            $('#expense_date').val(expenseDate);
                        });

                        $('.deleteExpense').on('click', function() {
                            const id = $(this).data('id');
                            if (confirm('Are you sure you want to delete this expense?')) {
                                deleteExpense(id);
                            }
                        });
                    },
                    error: function() {
                        alert('Error fetching expenses.');
                    }
                });
            }

            function deleteExpense(id) {
                const _token = $("input[name=_token]").val();
                $.ajax({
                    url: `/expenses/${id}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': _token },
                    success: function() {
                        alert('Expense deleted successfully!');
                        fetchExpenses();
                    },
                    error: function() {
                        alert('Error deleting expense.');
                    }
                });
            }

            // function updateChart(expenses) {
            //     const summary = expenses.reduce((acc, expense) => {
            //         acc[expense.category.name] = (acc[expense.category.name] || 0) + parseFloat(expense.amount);
            //         return acc;
            //     }, {});

            //     const labels = Object.keys(summary);
            //     const data = Object.values(summary);

            //     new Chart(document.getElementById('expensesChart'), {
            //         type: 'pie',
            //         data: {
            //             labels: labels,
            //             datasets: [{
            //                 data: data,
            //                 backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
            //             }]
            //         }
            //     });
            // }

            let expensesChart; // Declare the chart variable outside the function

            function updateChart(expenses) {
                const summary = expenses.reduce((acc, expense) => {
                    acc[expense.category.name] = (acc[expense.category.name] || 0) + parseFloat(expense.amount);
                    return acc;
                }, {});

                const labels = Object.keys(summary);
                const data = Object.values(summary);

                if (expensesChart) {
                    expensesChart.destroy(); // Destroy the existing chart instance
                }

                expensesChart = new Chart(document.getElementById('expensesChart'), {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                        }]
                    }
                });
            }

        });
    </script>
</body>
</html>