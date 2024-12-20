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
    <h2>Manage Categories</h2>
    <div class="row">
        <div class="col-md-6">
            <h4>Add/Edit Category</h4>
            <form id="categoryForm">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" id="categoryId" name="id">
                <div class="form-group">
                    <label for="categoryName">Category Name</label>
                    <input type="text" class="form-control" id="categoryName" name="name" required>
                </div>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </form>
        </div>
        <div class="col-md-6">
            <h4>Categories List</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesList"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    var _token = $("input[name=_token]").val();
    $(document).ready(function () {

        fetchCategories();

        // Add/Edit Category
        $('#categoryForm').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const id = $('#categoryId').val();
            const url = id ? `/categories/${id}` : '/categories';
            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                headers: { 'X-CSRF-TOKEN': _token },
                data: formData,
                success: function (response) {
                    alert('Category saved successfully!');
                    fetchCategories();
                    $('#categoryForm')[0].reset();
                },
                error: function () {
                    alert('Error saving category.');
                }
            });
        });

        // Fetch Categories
        function fetchCategories() {
            $.ajax({
                url: '/categories',
                method: 'GET',
                success: function (categories) {
                    const rows = categories.map(category => `
                        <tr>
                            <td>${category.id}</td>
                            <td>${category.name}</td>
                            <td>
                                <button class="btn btn-sm btn-warning editCategory" data-id="${category.id}" data-name="${category.name}">Edit</button>
                                <button class="btn btn-sm btn-danger deleteCategory" data-id="${category.id}">Delete</button>
                            </td>
                        </tr>
                    `);
                    $('#categoriesList').html(rows);

                    // Attach edit event
                    $('.editCategory').on('click', function () {
                        const id = $(this).data('id');
                        const name = $(this).data('name');
                        $('#categoryId').val(id);
                        $('#categoryName').val(name);
                    });

                    // Attach delete event
                    $('.deleteCategory').on('click', function () {
                        const id = $(this).data('id');
                        if (confirm('Are you sure you want to delete this category and associated expenses?')) {
                            deleteCategory(id);
                        }
                    });
                },
                error: function () {
                    alert('Error fetching categories.');
                }
            });
        }

        // Delete Category
        function deleteCategory(id) {
            $.ajax({
                url: `/categories/${id}`,
                headers: { 'X-CSRF-TOKEN': _token },
                method: 'DELETE',
                success: function () {
                      
                    fetchCategories();
                },
                error: function () {
                    alert('Error deleting category.');
                }
            });
        }
    });
</script>
</body>
</html>