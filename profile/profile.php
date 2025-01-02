<?php
session_start();

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'lunch');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function checkIfSeller($conn, $userId) {
    $stmt = $conn->prepare("SELECT seller_id FROM seller WHERE seller_userid = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search = trim($_GET['search']);
    header("Location: /lunch/home/home.php?search=" . urlencode($search));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="profilestyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            transition: opacity 0.3s ease-out;
        }
        .modal-content {
            padding: 30px;
            background: white;
            border-radius: 8px;
            min-width: 300px;
            position: relative;
            animation: modalPopup 0.3s ease-out;
        }
        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 35px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        @keyframes modalPopup {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
        .earnings-container, .followers-container, .pending-orders-container {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            width: 80%;
            margin-bottom: 20px;
        }
        .earnings-container:hover, .followers-container:hover, .pending-orders-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-sm sticky-top" style="background-color:white;">
            <div class="container-xxl">
            <a href="/lunch/home/home.php" class="navbar-brand me-auto">
                <span class="fw-bold fs-3" style="color:rgb(37, 40, 43)">
                Lunchkorbo
                </span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#main-nav" aria-controls="main-nav" aria-expanded="false"
            aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end align -center"
            id="main-nav">
                <ul class="navbar-nav">
                <li class="nav-item mx-2">
                    <div class="search-container nav-link position-relative">
                    <i class="fa-solid fa-magnifying-glass search-icon fa-lg" onclick="toggleSearch()" style="color: rgb(37, 40, 43); position: relative; z-index: 2;"></i>     
                    <form class="search-form d-flex position-absolute" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" role="search" style="right: 100%; top: 50%; transform: translateY(-50%);">
                        <input 
                        class="form-control rounded-pill ps-2 pe-2" 
                        type="search" 
                        name="search"
                        aria-label="Search">
                        <button 
                        class="btn position-absolute top-50 translate-middle-y" 
                        type="submit" 
                        style="right: 10px; background: none; border: none;"
                        name="submit">
                        <i class="fa-solid fa-arrow-right fa-lg" style="color: rgb(37, 40, 43);"></i>
                        </button>
                    </form>
                    </div>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="/lunch/profile/profile.php">
                    <i class="fa-solid fa-user fa-lg" style="color:rgb(37, 40, 43);"></i>
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="/lunch/menu/menu.php">
                    <i class="fas fa-utensils fa-lg" style="color: rgb(37, 40, 43);"></i>
                    </a>
                </li>
                <li class="nav-item mx-2 position-relative">
                    <a class="nav-link" href="/lunch/cart/cart.php">
                    <i class="fas fa-shopping-cart fa-lg" style="color: rgb(37, 40, 43);"></i>
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                        $count_query = "SELECT COUNT(*) as item_count FROM cart_item ci 
                                        JOIN cart c ON ci.cart_id = c.cart_id 
                                        WHERE c.user_id = ?";
                        $count_stmt = $conn->prepare($count_query);
                        $count_stmt->bind_param("i", $user_id);
                        $count_stmt->execute();
                        $count_result = $count_stmt->get_result();
                        $count = $count_result->fetch_assoc()['item_count'];
                        if ($count > 0) {
                            echo '<span class="position-absolute translate-middle badge rounded-pill bg-danger" 
                                  style="top: 5px; right: -10px;">' 
                                 . $count . '</span>';
                        }
                    }
                    ?>
                    </a>
                </li>
                </ul>

            </div>
            </div>
        </nav>

    <div class="main">
        <!-- Left Column -->
        <div class="left">
            <?php
            if (isset($_SESSION['user_id'])) {
                // Display earnings
                $user_id = $_SESSION['user_id'];
                $earnings_query = "SELECT total_earnings FROM seller WHERE seller_userid = ?";
                $stmt = $conn->prepare($earnings_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo '<div class="earnings-container mt-4">';
                    echo '<h3>Total Earnings</h3>';
                    echo '<h2 class="text-success">BDT. ' . number_format($row['total_earnings']) . '</h2>';
                    echo '</div>';
                }

                // Display followers
                $followers_query = "SELECT followers_count FROM seller WHERE seller_userid = ?";
                $stmt = $conn->prepare($followers_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo '<div class="followers-container mt-4">';
                    echo '<h3>Total Followers</h3>';
                    echo '<h2 class="text-primary">' . number_format($row['followers_count']) . '</h2>';
                    echo '</div>';
                }
            }
            ?>
            <?php
            if (isset($_SESSION['user_id'])) {
                // Display earnings and followers code as shown above...

                // Add a container for pending orders that will be updated dynamically
                echo '<div id="pending-orders-container" class="pending-orders-container mt-4" onclick="window.location.href=\'lunch/profile/profile.php\'" style="cursor: pointer;"></div>';
                ?>
                
                <script>
                function updatePendingOrders() {
                    fetch('get_pending_orders.php')
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById('pending-orders-container').innerHTML = data;
                        });
                }

                // Update every 5 seconds
                setInterval(updatePendingOrders, 5000);
                
                // Initial update
                updatePendingOrders();
                </script>
                
            <?php
            }
            ?>
        </div>

        <!-- Right Column -->
        <div class="right">
            <!-- Welcome Message -->
            <?php if (isset($_SESSION['username'])): ?>
                <div class="welcome-message">
                    <h1 class="text-center">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                </div>
            <?php else: ?>
                <div class="welcome-message">Please log in</div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-center gap-3 mt-4">
                <?php if (isset($_SESSION['username'])): ?>
                    <form method="POST" class="d-inline">
                        <button type="submit" name="logout" class="btn btn-dark">Logout</button>
                    </form>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php $userId = $_SESSION['user_id']; ?>
                        <?php if (!checkIfSeller($conn, $userId)): ?>
                            <form method="POST" action="/lunch/profile/become_seller.php" class="d-inline">
                                <button type="submit" name="become_seller" class="btn btn-primary">Be a Seller</button>
                            </form>

                        <?php else: ?>
                            <button type="button" id="openModal" class="btn btn-dark">ADD Item</button>

                            <!-- Modal -->
                            <div id="sellerModal" class="modal">
                                <div class="modal-content">
                                    <span id="closeModal" class="close">&times;</span>
                                    <h2>Post Your Food to the community</h2>
                                    <form method="POST" action="/lunch/profile/fill_form.php" id="itemForm">
                                        <div class="mb-3">
                                            <input type="text" name="item_name" class="form-control" id="itemname" placeholder="Enter item name" required>
                                        </div>
                                        <div class="mb-3">
                                            <input type="number" name="price" class="form-control" id="price" placeholder="Price (BDT)" required>
                                        </div>
                                        <div class="mb-3">
                                            <input type="text" name="category" class="form-control" id="category" placeholder="Category (e.g., burger, pizza)" required>
                                        </div>
                                        <div class="modal-buttons">
                                            <button type="submit" class="btn btn-primary">Confirm</button>
                                            <button type="button" id="cancelModal" class="btn btn-secondary">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">Please log in</div>
                    <a href="/lunch/reglog/reglog.html" class="btn btn-dark">Log In</a>
                <?php endif; ?>
            </div>

            <!-- Listed Items -->
            <?php if (isset($_SESSION['username'])): ?>
                <?php
                $user_id = $_SESSION['user_id'];
                $seller_query = "SELECT seller_id FROM seller WHERE seller_userid = ?";
                $stmt = $conn->prepare($seller_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $seller_result = $stmt->get_result();

                if ($seller_result->num_rows > 0) {
                    $seller_row = $seller_result->fetch_assoc();
                    $seller_id = $seller_row['seller_id'];

                    $items_query = "SELECT item_name, item_price, item_image FROM item WHERE seller_id = ?";
                    $stmt = $conn->prepare($items_query);
                    $stmt->bind_param("i", $seller_id);
                    $stmt->execute();
                    $items_result = $stmt->get_result();

                    if ($items_result->num_rows > 0): ?>
                        <div class="container" style="max-width: 600px;">
                            <div class="list-group mt-4">
                                <h3>Your Listed Items:</h3>
                                <?php while ($item = $items_result->fetch_assoc()): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php if ($item['item_image']): ?>
                                            <img src="<?php echo str_replace('./', '/', $item['item_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <?php endif; ?>
                                        <h5 class="ms-3 mb-0"><?php echo htmlspecialchars($item['item_name']); ?></h5>
                                        <span class="badge bg-dark rounded-2">Tk <?php echo htmlspecialchars($item['item_price']); ?></span>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-4" style="max-width: 600px; margin: 0 auto;">
                            You haven't listed any items yet.
                        </div>
                    <?php endif;
                }
                ?>
            <?php endif; ?>

            <!-- Order Management -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_order'], $_POST['order_item_id'])) {
                    $order_item_id = intval($_POST['order_item_id']);
                    $delete_query = "DELETE FROM order_item WHERE order_item_id = ?";
                    $stmt = $conn->prepare($delete_query);
                    $stmt->bind_param("i", $order_item_id);
                    $stmt->execute();
                }

                $orders_query = "
                    SELECT oi.order_item_id, i.item_name 
                    FROM order_item oi 
                    INNER JOIN item i ON oi.item_id = i.item_id 
                    INNER JOIN seller s ON i.seller_id = s.seller_id 
                    WHERE s.seller_userid = ? AND oi.status = 'pending'";
                
                $stmt = $conn->prepare($orders_query);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $order_result = $stmt->get_result();
                ?>

                <div class="container mt-5">
                    <h2 class="mb-4">Manage Your Orders</h2>
                    <?php if ($order_result->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while ($order = $order_result->fetch_assoc()): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($order['item_name']); ?></h5>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_item_id" value="<?php echo $order['order_item_id']; ?>">
                                        <button type="submit" name="complete_order" class="btn btn-primary btn-sm">Complete Order</button>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No pending orders found.</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('sellerModal');
            const openBtn = document.getElementById('openModal');
            const closeBtn = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelModal');
            const itemForm = document.getElementById('itemForm');

            if (openBtn) {
                function openModal() {
                    modal.style.display = 'flex';
                }

                function closeModal() {
                    modal.style.display = 'none';
                }

                openBtn.addEventListener('click', openModal);
                closeBtn.addEventListener('click', closeModal);
                cancelBtn.addEventListener('click', closeModal);

                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                });

                itemForm.addEventListener('submit', function(event) {
                    const price = document.getElementById('price').value;
                    if (isNaN(price) || price < 0) {
                        event.preventDefault();
                        alert('Please enter a valid price');
                    }
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>