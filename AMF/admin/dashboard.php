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

// Order trends (last 30 days)
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

// Additional data visualizations
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

// Customer Insights
$customer_acquisition_query = "
    SELECT DATE(created_at) AS date, COUNT(*) AS count
    FROM users
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) DESC
    LIMIT 30
";
$customer_acquisition_result = $conn->query($customer_acquisition_query);
$customer_acquisition = [];
while ($row = $customer_acquisition_result->fetch_assoc()) {
    $customer_acquisition[] = $row;
}

// Sales by Product
$sales_by_product_query = "
    SELECT p.name, SUM(oi.quantity * p.price_per_unit) AS total_sales
    FROM order_items oi
    JOIN product p ON oi.product_id = p.product_id
    GROUP BY p.name
    ORDER BY total_sales DESC
    LIMIT 5
";
$sales_by_product_result = $conn->query($sales_by_product_query);
$sales_by_product = [];
while ($row = $sales_by_product_result->fetch_assoc()) {
    $sales_by_product[] = $row;
}

// Average Order Processing Time (from order_date to current_date)
$avg_processing_time_query = "
    SELECT AVG(TIMESTAMPDIFF(DAY, order_date, NOW())) AS avg_processing_time
    FROM orders
";
$avg_processing_time_result = $conn->query($avg_processing_time_query);
$avg_processing_time = $avg_processing_time_result->fetch_assoc()['avg_processing_time'];

// Order Fulfillment Rates
$fulfillment_rate_query = "
    SELECT 
        SUM(CASE WHEN order_date IS NOT NULL THEN 1 ELSE 0 END) / COUNT(*) * 100 AS fulfillment_rate
    FROM orders
";
$fulfillment_rate_result = $conn->query($fulfillment_rate_query);
$fulfillment_rate = $fulfillment_rate_result->fetch_assoc()['fulfillment_rate'];

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Au Maraicher Des Flandres-admin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../images/logo2.png" type="image/icon type">

    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,400i,700,700i&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Amatic+SC:400,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="../css/animate.css">

    <link rel="stylesheet" href="../css/owl.carousel.min.css">
    <link rel="stylesheet" href="../css/owl.theme.default.min.css">
    <link rel="stylesheet" href="../css/magnific-popup.css">

    <link rel="stylesheet" href="../css/aos.css">

    <link rel="stylesheet" href="../css/ionicons.min.css">

    <link rel="stylesheet" href="../css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="../css/jquery.timepicker.css">

    <link rel="stylesheet" href="../css/flaticon.css">
    <link rel="stylesheet" href="../css/icomoon.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        <div>
            <h4>Commandes Aujourd'hui</h4>
            <p><?php echo $orders_today_count; ?></p>
        </div>
    </div>
    <div class="metrics">
        <div>
            <h4>Top 5 Produits les Plus Vendus</h4>
            <ul>
                <?php foreach ($top_products as $product): ?>
                    <li><?php echo htmlspecialchars($product['name']); ?>: <?php echo $product['total_quantity']; ?> unités</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4>Ventes Totales par Produit</h4>
            <ul>
                <?php foreach ($sales_by_product as $product): ?>
                    <li><?php echo htmlspecialchars($product['name']); ?>: €<?php echo number_format($product['total_sales'], 2); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="chart-container additional-visualizations">
        <h3>Répartition des Produits par Catégorie</h3>
        <canvas id="categoryChart"></canvas>
    </div>
    <div class="chart-container additional-visualizations">
        <h3>Trends des Commandes (30 Derniers Jours)</h3>
        <canvas id="orderTrendsChart"></canvas>
    </div>
    <div class="chart-container additional-visualizations">
        <h3>Ventes par Jour (30 Derniers Jours)</h3>
        <canvas id="salesPerDayChart"></canvas>
    </div>
    <div class="chart-container additional-visualizations">
        <h3>Acquisition de Clients (30 Derniers Jours)</h3>
        <canvas id="customerAcquisitionChart"></canvas>
    </div>
</div>
<!-- Welcome Modal -->
<?php include '../modals/admin_welcome.php'; ?>


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
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
<script src="../js/google-map.js"></script>
<script src="../js/main.js"></script>
<script src="../js/modal-switch.js"></script>

</body>
</html>

<script>
    // Pie chart for order status
    const statusChartCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusChartCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($order_status_counts, 'status')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($order_status_counts, 'count')); ?>,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (context.parsed !== null) {
                                label += ': ' + context.parsed.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Bar chart for product categories
    const categoryChartCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryChartCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($category_counts, 'category_name')); ?>,
            datasets: [{
                label: 'Produits par Catégorie',
                data: <?php echo json_encode(array_column($category_counts, 'count')); ?>,
                backgroundColor: '#FF6384',
                borderColor: '#FF6384',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (context.parsed !== null) {
                                label += ': ' + context.parsed.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });

    // Line chart for order trends
    const orderTrendsChartCtx = document.getElementById('orderTrendsChart').getContext('2d');
    const orderTrendsChart = new Chart(orderTrendsChartCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($order_trends, 'date')); ?>,
            datasets: [{
                label: 'Commandes',
                data: <?php echo json_encode(array_column($order_trends, 'count')); ?>,
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (context.parsed !== null) {
                                label += ': ' + context.parsed.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Line chart for sales per day
    const salesPerDayChartCtx = document.getElementById('salesPerDayChart').getContext('2d');
    const salesPerDayChart = new Chart(salesPerDayChartCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($sales_per_day, 'date')); ?>,
            datasets: [{
                label: 'Ventes Totales',
                data: <?php echo json_encode(array_column($sales_per_day, 'total_sales')); ?>,
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (context.parsed !== null) {
                                label += ': €' + context.parsed.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Line chart for customer acquisition
    const customerAcquisitionChartCtx = document.getElementById('customerAcquisitionChart').getContext('2d');
    const customerAcquisitionChart = new Chart(customerAcquisitionChartCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($customer_acquisition, 'date')); ?>,
            datasets: [{
                label: 'Acquisition de Clients',
                data: <?php echo json_encode(array_column($customer_acquisition, 'count')); ?>,
                borderColor: '#FFCE56',
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (context.parsed !== null) {
                                label += ': ' + context.parsed.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>
</body>
</html>