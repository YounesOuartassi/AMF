<?php
// Include database configuration
include('../db_connect.php'); // Ensure this file contains your database connection settings

session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Redirect to the home page or show an error message
    header('Location: ../index.php'); // Adjust the path as necessary
    exit();
}

// Fetch data for visualizations
$orders_today_query = "SELECT COUNT(*) AS count FROM orders WHERE DATE(order_date) = CURDATE()";
$orders_today_result = $conn->query($orders_today_query);
$orders_today_count = $orders_today_result->fetch_assoc()['count'];

$orders_total_query = "SELECT COUNT(*) AS count FROM orders";
$orders_total_result = $conn->query($orders_total_query);
$orders_total_count = $orders_total_result->fetch_assoc()['count'];

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

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Tableau de Bord Administrateur</title>
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
    <style>
        .dashboard-section {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
            padding: 20px;
        }
        .dashboard-section > div {
            flex: 1 1 calc(33.333% - 20px);
            box-sizing: border-box;
            padding: 10px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            height: 250px;
        }
        .dashboard-section h3 {
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        .dashboard-section canvas {
            width: 100% !important;
            height: auto !important;
        }
    </style>
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<div class="container mt-4 dashboard-section">
    <div>
        <h3>Répartition des Commandes par Statut</h3>
        <canvas id="statusChart"></canvas>
    </div>
    <div>
        <h3>Commandes Aujourd'hui</h3>
        <p>Nombre de commandes aujourd'hui: <?php echo $orders_today_count; ?></p>
    </div>
    <div>
        <h3>Total des Commandes</h3>
        <p>Nombre total de commandes: <?php echo $orders_total_count; ?></p>
    </div>
    <div>
        <h3>Répartition des Produits par Catégorie</h3>
        <canvas id="categoryChart"></canvas>
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
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
<script src="../js/google-map.js"></script>
<script src="../js/main.js"></script>
<script src="../js/modal-switch.js"></script>

</body>
</html>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ctxStatus = document.getElementById('statusChart').getContext('2d');
        var statusChart = new Chart(ctxStatus, {
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
        var categoryChart = new Chart(ctxCategory, {
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
    });
</script>