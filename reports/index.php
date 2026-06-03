<?php
require_once '../includes/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Table & Chair Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/sidebar.css">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; margin: 0; }
        .main-content { margin-left: 280px; padding: 25px 30px; min-height: 100vh; }
        .report-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header-section h3 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        .header-section h3 i { color: #667eea; margin-right: 10px; }
        .btn-back {
            background: #6b7280;
            color: white;
            padding: 10px 24px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }
        .btn-back:hover { background: #4b5563; color: white; }
        .report-link {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 16px;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid #667eea;
        }
        .report-link:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: white;
        }
        .report-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
        }
        .report-icon i { font-size: 28px; color: white; }
        .report-title { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 5px; }
        .report-desc { font-size: 14px; color: #6b7280; margin: 0; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="report-card">
            <div class="header-section">
                <h3><i class="fas fa-chart-line"></i> Reports Dashboard</h3>
            </div>
            
            <div class="grid-2">
                <a href="monthly_revenue.php" class="report-link">
                    <div class="report-icon"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <h4 class="report-title">Monthly Revenue Report</h4>
                        <p class="report-desc">View rental and repair revenue by month</p>
                    </div>
                </a>
                
                <a href="top_items.php" class="report-link">
                    <div class="report-icon"><i class="fas fa-trophy"></i></div>
                    <div>
                        <h4 class="report-title">Top Rented Items</h4>
                        <p class="report-desc">Most popular items by rental frequency</p>
                    </div>
                </a>
            </div>
            
            <div><a href="../index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></div>
        </div>
    </div>
</body>
</html>