<?php
// Include database configuration
include('../db_connect.php');

session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Redirect to the home page or show an error message
    header('Location: ../index.php');
    exit();
}

// Fetch data for visualizations
$orders_today_query = "SELECT COUNT(*) AS count FROM orders WHERE DATE(order_date) = CURDATE()";
$orders_today_result = $conn->query($orders_today_query);
$orders_today_count = $orders_today_result->fetch_assoc()['count'];

$orders_total_query = "SELECT COUNT(*) AS count FROM orders";
$orders_total_result = $conn->query($orders_total_query);
$orders_total_count = $orders_total_result->fetch_assoc()['count'];

$sales_total_query = "SELECT SUM(total_amount) AS total FROM orders";
$sales_total_result = $conn->query($sales_total_query);
$sales_total_amount = $sales_total_result->fetch_assoc()['total'];

$products_total_query = "SELECT COUNT(*) AS count FROM product";
$products_total_result = $conn->query($products_total_query);
$products_total_count = $products_total_result->fetch_assoc()['count'];

$category_counts_query = "
    SELECT c.category_name, COUNT(p.product_id) AS count 
    FROM categories c
    LEFT JOIN product p ON c.category_id = p.category_id
    GROUP BY c.category_name
";
$category_counts_result = $conn->query($category_counts_query);
$category_counts = [];
while ($row = $category_counts_result->fetch_assoc()) {
    $category_counts[] = $row;
}

$order_status_counts_query = "
    SELECT status, COUNT(*) AS count 
    FROM orders 
    GROUP BY status
";
$order_status_counts_result = $conn->query($order_status_counts_query);
$order_status_counts = [];
while ($row = $order_status_counts_result->fetch_assoc()) {
    $order_status_counts[] = $row;
}

// Example for order trends, you may need to adjust the query
$order_trends_query = "
    SELECT DATE(order_date) AS date, COUNT(*) AS count 
    FROM orders 
    GROUP BY DATE(order_date)
    ORDER BY DATE(order_date) DESC
    LIMIT 30
";
$order_trends_result = $conn->query($order_trends_query);
$order_trends = [];
while ($row = $order_trends_result->fetch_assoc()) {
    $order_trends[] = $row;
}

// Additional data visualizations (for example, top products and sales per day)
$top_products_query = "
    SELECT p.name, SUM(oi.quantity) AS total_quantity
    FROM order_items oi
    JOIN product p ON oi.product_id = p.product_id
    GROUP BY p.name
    ORDER BY total_quantity DESC
    LIMIT 5
";
$top_products_result = $conn->query($top_products_query);
$top_products = [];
while ($row = $top_products_result->fetch_assoc()) {
    $top_products[] = $row;
}

$sales_per_day_query = "
    SELECT DATE(order_date) AS date, SUM(total_amount) AS total_sales
    FROM orders
    GROUP BY DATE(order_date)
    ORDER BY DATE(order_date) DESC
    LIMIT 30
";
$sales_per_day_result = $conn->query($sales_per_day_query);
$sales_per_day = [];
while ($row = $sales_per_day_result->fetch_assoc()) {
    $sales_per_day[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Tableau de Bord Administrateur</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo2.png" type="image/icon type">

    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .dashboard-section {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }
        .chart-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .chart-container h3 {
            margin-bottom: 15px;
            font-size: 1.5em;
            color: #333;
        }
        .chart-container canvas {
            width: 100% !important;
            height: auto !important;
        }
        .metrics, .repartition, .additional {
            flex: 1 1 24%;
            min-width: 250px;
        }
        .metrics div, .repartition div, .additional div {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        .metrics div h4, .repartition div h4, .additional div h4 {
            font-size: 1.2em;
            color: #555;
        }
        .metrics div p, .repartition div p, .additional div p {
            font-size: 1.5em;
            color: #333;
        }
    </style>
</head>
<body>
<?php include 'admin_navbar.php'; ?>

<div class="container mt-4 dashboard-section">
    <div class="chart-container">
        <h3>Répartition des Commandes par Statut</h3>
        <canvas id="statusChart"></canvas>
    </div>
    <div class="metrics">
        <div>
            <h4>Total des Commandes</h4>
            <p><?php echo $orders_total_count; ?></p>
        </div>
        <div>
            <h4>Ventes Totales en Euros</h4>
            <p>€<?php echo number_format($sales_total_amount, 2); ?></p>
        </div>
        <div>
            <h4>Total des Produits</h4>
            <p><?php echo $products_total_count; ?></p>
        </div>
        <!-- Add two more metrics here -->
        <div>
            <h4>Commandes Aujourd'hui</h4>
            <p><?php echo $orders_today_count; ?></p>
        </div>
        
    </div>
    <div class="metrics">
        <div>
            <h4>Produits les Plus Vendus</h4>
            <ul>
                <?php foreach ($top_products as $product): ?>
                    <li><?php echo htmlspecialchars($product['name']) . ': ' . $product['total_quantity'] . ' unités'; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="chart-container repartition">
        <h3>Répartition des Produits par Catégorie</h3>
        <canvas id="categoryChart"></canvas>
    </div>
    <div class="chart-container additional">
        <h3>Évolution des Commandes</h3>
        <canvas id="trendsChart"></canvas>
    </div>
    <!-- Additional visualizations -->
    <div class="chart-container additional">
        <h3>Ventes par Jour</h3>
        <canvas id="salesPerDayChart"></canvas>
    </div>

</div>

<?php include '../components/footer.php'; ?>

<!-- loader -->
<div id="ftco-loader" class="show fullscreen">
    <svg class="circular" width="48px" height="48px">
        <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
        <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/>
    </svg>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="../js/jquery.min.js"></script>
<script src="../js/jquery-migrate-3.0.1.min.js"></script>
<script src="../js/popper.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.easing.1.3.js"></script>
<script src="../js/jquery.waypoints.min.js"></script>
<script src="../js/jquery.stellar.min.js"></script>
<script src="../js/owl.carousel.min.js"></script>
<script src="../js/jquery.magnific-popup.min.js"></script>
<script src="../js/aos.js"></script>
<script src="../js/jquery.animateNumber.min.js"></script>
<script src="../js/bootstrap-datepicker.js"></script>
<script src="../js/scrollax.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiUL
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
<script src="../js/google-map.js"></script>
<script src="../js/main.js"></script>
<script src="../js/modal-switch.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($order_status_counts, 'status')); ?>,
                datasets: [{
                    label: 'Commandes par Statut',
                    data: <?php echo json_encode(array_column($order_status_counts, 'count')); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 10
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' commandes';
                            }
                        }
                    }
                }
            }
        });

        var ctxCategory = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCategory, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($category_counts, 'category_name')); ?>,
                datasets: [{
                    label: 'Nombre de Produits par Catégorie',
                    data: <?php echo json_encode(array_column($category_counts, 'count')); ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nombre de Produits'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Catégorie'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' produits';
                            }
                        }
                    }
                }
            }
        });

        var ctxTrends = document.getElementById('trendsChart').getContext('2d');
        new Chart(ctxTrends, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($order_trends, 'date')); ?>,
                datasets: [{
                    label: 'Commandes au Fil du Temps',
                    data: <?php echo json_encode(array_column($order_trends, 'count')); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nombre de Commandes'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' commandes';
                            }
                        }
                    }
                }
            }
        });

        var ctxSalesPerDay = document.getElementById('salesPerDayChart').getContext('2d');
        new Chart(ctxSalesPerDay, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($sales_per_day, 'date')); ?>,
                datasets: [{
                    label: 'Ventes par Jour',
                    data: <?php echo json_encode(array_column($sales_per_day, 'total_sales')); ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Ventes en Euros'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': €' + tooltipItem.raw.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    });
</script>

</body>
</html>
