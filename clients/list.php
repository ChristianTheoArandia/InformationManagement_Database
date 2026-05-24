<?php
require_once '../includes/database.php';

$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM Client";
if ($search) {
    $sql .= " WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR contact LIKE '%$search%'";
}
$sql .= " ORDER BY client_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-users"></i> Client List</h4>
                <a href="add.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Client
                </a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search clients...">
                </div>
                
                <table id="clientTable" class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Client ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['client_id'] ?></td>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['contact']) ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-btn" data-id="<?= $row['client_id'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $row['client_id'] ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="edit_client_id">
                        <div class="mb-3">
                            <label>First Name</label>
                            <input type="text" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Last Name</label>
                            <input type="text" id="edit_last_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Contact</label>
                            <input type="text" id="edit_contact" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Location</label>
                            <input type="text" id="edit_location" class="form-control">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateClient()">Update</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#clientTable').DataTable();
            
            $('#searchInput').on('keyup', function() {
                $('#clientTable').DataTable().search(this.value).draw();
            });
        });
        
        $('.edit-btn').click(function() {
            let clientId = $(this).data('id');
            $.get('get_client.php?id=' + clientId, function(data) {
                $('#edit_client_id').val(data.client_id);
                $('#edit_first_name').val(data.first_name);
                $('#edit_last_name').val(data.last_name);
                $('#edit_contact').val(data.contact);
                $('#edit_location').val(data.location);
                $('#editModal').modal('show');
            });
        });
        
        function updateClient() {
            $.post('edit.php', {
                client_id: $('#edit_client_id').val(),
                first_name: $('#edit_first_name').val(),
                last_name: $('#edit_last_name').val(),
                contact: $('#edit_contact').val(),
                location: $('#edit_location').val()
            }, function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Error updating client');
                }
            });
        }
        
        $('.delete-btn').click(function() {
            if(confirm('Are you sure you want to delete this client?')) {
                $.post('delete.php', {client_id: $(this).data('id')}, function(response) {
                    if(response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting client');
                    }
                });
            }
        });
    </script>
</body>
</html>