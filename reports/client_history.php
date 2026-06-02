<?php
require_once '../includes/database.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4><i class="fas fa-history"></i> Client Transaction History</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="searchClient" class="form-control" 
                           placeholder="Search by client name, ID, or contact...">
                </div>
                <div id="results"></div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('searchClient').addEventListener('input', function() {
            let search = this.value;
            if(search.length > 2) {
                fetch(`client_history_ajax.php?search=${search}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '<div class="accordion" id="clientAccordion">';
                        data.forEach((client, index) => {
                            html += `
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button ${index > 0 ? 'collapsed' : ''}" 
                                                type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse${index}">
                                            <strong>${client.client_name}</strong> (${client.client_id}) - 
                                            ${client.total_transactions} transactions | 
                                            ₱${client.total_spent}
                                        </button>
                                    </h2>
                                    <div id="collapse${index}" class="accordion-collapse collapse ${index == 0 ? 'show' : ''}" 
                                         data-bs-parent="#clientAccordion">
                                        <div class="accordion-body">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr><th>Transaction ID</th><th>Date</th>
                                                    <th>Items</th><th>Amount</th><th>Status</th></tr>
                                                </thead>
                                                <tbody>
                            `;
                            client.transactions.forEach(t => {
                                html += `<tr>
                                    <td>${t.transaction_id}</td>
                                    <td>${t.transaction_date}</td>
                                    <td>${t.items_count}</td>
                                    <td>₱${t.amount}</td>
                                    <td>${t.status}</td>
                                </tr>`;
                            });
                            html += `</tbody></table></div></div></div>`;
                        });
                        html += '</div>';
                        document.getElementById('results').innerHTML = html;
                    });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>