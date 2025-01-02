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
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Home</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="cartstyles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body>
        <!--nav bar-->
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
        <!--content-->
        <div class="main">
            <!--left-->
            <div class="left">
                <?php
                    if (isset($_GET['search']) && trim($_GET['search']) !== '') {
                        $search = trim($_GET['search']);
                        
                        header("Location: /lunch/home/home.php?search=" . urlencode($search));
                        exit();
                    }
                ?>
                
                <?php
                    if (isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                        
                        
                        $cart_query = "SELECT cart_id FROM cart WHERE user_id = ?";
                        $cart_stmt = $conn->prepare($cart_query);
                        $cart_stmt->bind_param("i", $user_id);
                        $cart_stmt->execute();
                        $cart_result = $cart_stmt->get_result();
                        
                        if ($cart_result->num_rows > 0) {
                            $cart = $cart_result->fetch_assoc();
                            
                            
                            $items_query = "SELECT ci.*, i.item_name, i.item_price 
                                        FROM cart_item ci 
                                        JOIN item i ON ci.item_id = i.item_id 
                                        WHERE ci.cart_id = ?";
                            $items_stmt = $conn->prepare($items_query);
                            $items_stmt->bind_param("i", $cart['cart_id']);
                            $items_stmt->execute();
                            $items_result = $items_stmt->get_result();
                            
                            if ($items_result->num_rows > 0) {
                                $total = 0;
                                echo '<div class="cart-items">';
                                while ($item = $items_result->fetch_assoc()) {
                                    $subtotal = $item['quantity'] * $item['item_price'];
                                    $total += $subtotal;
                                    ?>
                                    <div class="cart-item d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                        <div class="item-details">
                                            <p><?php echo htmlspecialchars($item['item_name']); ?></p>
                                            <h6 class="mb-0">Unit Price: <?php echo number_format($item['item_price']); ?> Tk </h6>
                                        </div>
                                        <div class="quantity-control d-flex align-items-center">
                                            <form action="/lunch/cart/update_quantity.php" method="POST" class="d-flex align-items-center">
                                                <input type="hidden" name="cart_id" value="<?php echo $cart['cart_id']; ?>">
                                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                                <div class="btn-group" style="border-radius: 3px; transform: scale(0.9);">
                                                    <button type="submit" name="action" value="decrease" class="btn btn-sm btn-outline-secondary" style="border-radius: 3px 0 0 3px;">
                                                        <i class="fas fa-minus fa-xs"></i>
                                                    </button>
                                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                        min="1" max="99" class="form-control form-control-sm" style="width: 45px; border-radius: 0; text-align: center;" readonly>
                                                    <button type="submit" name="action" value="increase" class="btn btn-sm btn-outline-secondary" style="border-radius: 0 3px 3px 0;">
                                                        <i class="fas fa-plus fa-xs"></i>
                                                    </button>
                                                </div>
                                            </form>
                                            <form action="/lunch/cart/remove_item.php" method="POST" class="ms-2">
                                                <input type="hidden" name="cart_id" value="<?php echo $cart['cart_id']; ?>">
                                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        
                                    </div>
                                    <?php
                                }
                            } else {
                                echo '<div class="welcome-message">';
                                echo '<h1 class="text-center">' . htmlspecialchars($_SESSION['username']) . ' your cart is empty</h1>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="welcome-message">';
                            echo '<h1 class="text-center">' . htmlspecialchars($_SESSION['username']) . ' your cart is empty</h1>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="welcome-message">Please log in</div>';
                    }
                    ?>
            </div>
            <div class="right">
                <div class="order-summary">
                    <div class="summary-header">
                        <h2>ORDER SUMMARY</h2>
                    </div>
                    <div class="summary-content">
                        <?php
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id'];
                            
                            
                            $cart_query = "SELECT cart_id FROM cart WHERE user_id = ?";
                            $cart_stmt = $conn->prepare($cart_query);
                            $cart_stmt->bind_param("i", $user_id);
                            $cart_stmt->execute();
                            $cart_result = $cart_stmt->get_result();
                            
                            if ($cart_result->num_rows > 0) {
                                $cart = $cart_result->fetch_assoc();
                                
                                
                                $items_query = "SELECT ci.*, i.item_name, i.item_price, i.seller_id 
                                            FROM cart_item ci 
                                            JOIN item i ON ci.item_id = i.item_id 
                                            WHERE ci.cart_id = ?";
                                $items_stmt = $conn->prepare($items_query);
                                $items_stmt->bind_param("i", $cart['cart_id']);
                                $items_stmt->execute();
                                $items_result = $items_stmt->get_result();
                                
                                if ($items_result->num_rows > 0) {
                                    $total = 0;
                                    $cart_items = array();
                                    
                                    while ($item = $items_result->fetch_assoc()) {
                                        $subtotal = $item['quantity'] * $item['item_price'];
                                        $total += $subtotal;
                                        
                                        
                                        $cart_items[] = array(
                                            'item_id' => $item['item_id'],
                                            'quantity' => $item['quantity'],
                                            'price' => $item['item_price'],
                                            'seller_id' => $item['seller_id']
                                        );
                                        ?>
                                        <div class="summary-item">
                                            <span class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                            <span class="item-price">Tk <?php echo number_format($item['item_price'] * $item['quantity']); ?></span>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <div class="summary-subtotal">
                                        <span>Subtotal</span>
                                        <span>Tk <?php echo number_format($total); ?></span>
                                    </div>
                                    <?php if (!empty($cart_items)) { ?>
                                        <form action="place_order.php" method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                                            <input type="hidden" name="cart_items" value='<?php echo htmlspecialchars(json_encode($cart_items), ENT_QUOTES); ?>'>
                                            <button type="submit" class="place-order-btn">Place Order!</button>
                                        </form>
                                    <?php } ?>
                                    <?php
                                } else {
                                    echo "<p>Your cart is empty.</p>";
                                }
                            } else {
                                echo "<p>Your cart is empty.</p>";
                            }
                        } else {
                            echo "<p>Please log in to view your cart.</p>";
                        }
                        ?>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <script>
            function toggleSearch() {
                const searchForm = document.querySelector('.search-form');
                searchForm.classList.toggle('active');
                
                if (searchForm.classList.contains('active')) {
                    
                    setTimeout(() => {
                        searchForm.querySelector('input').focus();
                    }, 300);
                }
            }

            document.addEventListener('click', function(event) {
                const searchContainer = document.querySelector('.search-container');
                const searchForm = document.querySelector('.search-form');
                
                if (!searchContainer.contains(event.target) && searchForm.classList.contains('active')) {
                    searchForm.classList.remove('active');
                }
            });
        </script>
        <script>
            function placeOrder() {
                alert('Order placed successfully!');
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        </body>
    </html>