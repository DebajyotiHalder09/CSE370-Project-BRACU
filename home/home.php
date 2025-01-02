<?php
session_start();
    $conn = new mysqli('localhost', 'root', '', 'lunch');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Home</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="homestyles.css">
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
            
            <div class="left">
                <!--left-->
                <div class="dropdown mb-3">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Filter
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                        <li><h6 class="dropdown-header">By Price</h6></li>
                        <li><a class="dropdown-item" href="?sort=price_asc">Price: Low to High</a></li>
                        <li><a class="dropdown-item" href="?sort=price_desc">Price: High to Low</a></li>  
                    </ul>
                </div>
                <div class="fav_follower">
                    <?php
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id'];
                            
                            $query = "SELECT u.username as seller_username, s.seller_id 
                                FROM fav_follower ff
                                JOIN seller s ON ff.seller_id = s.seller_id
                                JOIN user u ON s.seller_userid = u.id
                                WHERE ff.user_id = ?";
                            
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result) {
                                if ($result->num_rows > 0) {
                                    
                                    echo '<div class="list-group">';
                                    echo '<h4 class="mt-0.1 mb-0.1">Fav Chefs</h4>';
                        
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<div class="list-group-item d-flex justify-content-between align-items-center" 
                                        style="cursor: pointer; transition: background 0.3s ease;" 
                                        onmouseover="this.style.background=\'linear-gradient(180deg, white 0%,rgb(189, 200, 212) 100%)\'" 
                                        onmouseout="this.style.background=\'white\'" 
                                        onclick="window.location.href=\'/lunch/menu/menu.php?seller_id=' . $row['seller_id'] . '\'">';
                                        echo '<span>' . htmlspecialchars($row['seller_username']) . '</span>';
                                        echo '<form action="/lunch/home/unfollow_seller.php" method="POST" style="display:inline;" onclick="event.stopPropagation();">';
                                        echo '<input type="hidden" name="seller_id" value="' . $row['seller_id'] . '">';
                                        echo '<input type="hidden" name="scroll_position" id="scroll-position" value="">';
                                        echo '<button type="submit" class="btn btn-danger btn-sm" 
                                        style="background-color:rgb(247, 90, 90); border-color:rgb(247, 90, 90);" 
                                        onmouseover="this.style.backgroundColor=\'rgb(255, 255, 255)\'; 
                                        this.style.borderColor=\'rgb(247, 90, 90)\';
                                        this.querySelector(\'.fas\').style.color=\'rgb(247, 90, 90)\';" 
                                        onmouseout="this.style.backgroundColor=\'rgb(247, 90, 90)\'; 
                                        this.style.borderColor=\'rgb(247, 90, 90)\';
                                        this.querySelector(\'.fas\').style.color=\'white\';"><i class="fas fa-user-minus"></i></button>';
                                        echo '</form>';
                                        echo '</form>';
                                        echo '</div>';
                                    echo '<script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        var listItems = document.querySelectorAll(".list-group-item");
                                        listItems.forEach(function(item) {
                                            item.addEventListener("click", function(e) {
                                                if (!e.target.closest("form")) {
                                                    var sellerName = this.querySelector("span").textContent;
                                                    window.location.href = "/lunch/home/home.php?search=" + encodeURIComponent(sellerName) + "&submit=true";
                                                }
                                            });
                                        });
                                    });
                                    </script>';
                                    }

                                    echo '</div>';
                                } else {
                                    echo '<div class="alert alert-info" role="alert">';
                                    echo '<h5 class="mb-0">You are not following any sellers yet.</h5>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="alert alert-danger" role="alert">';
                                echo 'Query failed: ' . $conn->error;
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="alert alert-warning" role="alert">';
                            echo '<h5 class="mb-0">Please log in to view your followed sellers.</h5>';
                            echo '</div>';
                        }
                    ?>
                </div>
                
                <div class="fav_category mt-4">
                    <?php
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id'];
                            
                            $query = "SELECT ic.cat_name, ic.cat_id 
                                FROM fav_cat fc
                                JOIN item_catagory ic ON fc.cat_id = ic.cat_id
                                WHERE fc.user_id = ?";
                            
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result) {
                                if ($result->num_rows > 0) {
                                    
                                    echo '<div class="list-group">';
                                    echo '<h4 class="mt-4 mb-3">Category</h4>';
                                    
                                
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<div class="list-group-item d-flex justify-content-between align-items-center" 
                                        style="cursor: pointer; transition: background 0.3s ease;" 
                                        onmouseover="this.style.background=\'linear-gradient(180deg, white 0%,rgb(189, 200, 212) 100%)\'" 
                                        onmouseout="this.style.background=\'white\'" 
                                        onclick="window.location.href=\'/lunch/menu/menu.php?cat_id=' . $row['cat_id'] . '\'">';
                                        echo '<span>' . htmlspecialchars($row['cat_name']) . '</span>'; 
                                        echo '<form action="/lunch/home/remove_category.php" method="POST" style="display:inline;" onclick="event.stopPropagation();">';
                                        echo '<input type="hidden" name="cat_id" value="' . $row['cat_id'] . '">';
                                        echo '<input type="hidden" name="scroll_position" id="scroll-position" value="">';
                                        echo '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-heart-broken"></i></button>';
                                        echo '</form>';
                                        echo '</div>';
                                    }
                                    
                                    echo '<script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        var listItems = document.querySelectorAll(".list-group-item");
                                        listItems.forEach(function(item) {
                                            item.addEventListener("click", function(e) {
                                                if (!e.target.closest("form")) {
                                                    var categoryName = this.querySelector("span").textContent;
                                                    window.location.href = "/lunch/home/home.php?search=" + encodeURIComponent(categoryName) + "&submit=true";
                                                }
                                            });
                                        });
                                    });
                                    </script>';

                                    echo '</div>';
                                } else {
                                    echo '<div class="alert alert-info" role="alert">';
                                    echo '<h5 class="mb-0">You have not added any favorite categories yet.</h5>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="alert alert-danger" role="alert">';
                                echo 'Query failed: ' . $conn->error;
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="alert alert-warning" role="alert">';
                            echo '<h5 class="mb-0">Please log in to view your favorite categories.</h5>';
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>

            <!--right-->
            <div class="right">
                <?php 
                if (isset($_GET['submit']) && isset($_GET['search']) && trim($_GET['search']) !== '') {
                    $search_term = htmlspecialchars(trim($_GET['search']));
                    echo '<h4 class="mb-4">Search results for "' . $search_term . '"</h4>';
                }
                ?>
                <div class="card-container">
                    
                <?php
                $query = "SELECT
                                item.item_id,
                                item.item_name, 
                                item.item_price,
                                item.item_image, 
                                seller.seller_id,
                                user.username AS seller_name, 
                                item_catagory.cat_name AS item_category,
                                item_catagory.cat_id
            FROM 
                item
            INNER JOIN 
                seller ON seller.seller_id = item.seller_id
            INNER JOIN 
                user ON user.id = seller.seller_userid
            INNER JOIN 
                catagory_store ON catagory_store.item_id = item.item_id
            INNER JOIN 
                item_catagory ON item_catagory.cat_id = catagory_store.cat_id";
            
            
            if (isset($_GET['submit']) && isset($_GET['search']) && trim($_GET['search']) !== '') {
                $search = $conn->real_escape_string(trim($_GET['search']));
                $query .= " WHERE 
                    item.item_name LIKE '%$search%' OR 
                    user.username LIKE '%$search%' OR 
                    item_catagory.cat_name LIKE '%$search%' OR 
                    item.item_price LIKE '%$search%'";
            }

            $query .= " GROUP BY item.item_id, item.item_name, item.item_price, item.item_image";
            if (isset($_GET['sort'])) {
                switch ($_GET['sort']) {
                    case 'price_asc':
                        $query .= " ORDER BY item.item_price ASC";
                        break;
                    case 'price_desc':
                        $query .= " ORDER BY item.item_price DESC";
                        break;
                }
            }
            $result = $conn->query($query); 

                if ($result) {
                    if ($result->num_rows > 0) {
                        
                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='card' style='width: 30rem;'>";
                            echo "<img src='" . str_replace('./', '/', $row['item_image']) . "' alt='" . htmlspecialchars($row['item_name']) . "'>";
                            echo "<div class='card-body'>";
                            
                            
                            echo "<h6 class='card-title'>" . htmlspecialchars($row['seller_name']) . " is selling in category: " . htmlspecialchars($row['item_category']) . "</h6>";
                            
                            
                            echo "<div class='d-flex justify-content-between align-items-center'>";
                            echo "<h3 class='card-text mb-2'>" . htmlspecialchars($row['item_name']) . "</h3>";
                            echo "<h2 class='card-text mb-0'>Tk: " . number_format($row['item_price']) . "</h2>";
                            echo "</div>";
                            
                            echo '<div class="d-flex gap-2">';

                            echo '<form action="/lunch/cart/add_to_cart.php" method="POST" class="m-0">';
                            echo '<input type="hidden" name="item_id" value="' . htmlspecialchars($row['item_id']) . '">';
                            echo '<input type="hidden" name="user_id" value="' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '') . '">';
                            echo '<button type="submit" class="btn btn-secondary" 
                            style="background-color:rgb(90, 140, 247); border-color:rgb(90, 140, 247);" 
                            onmouseover="this.style.backgroundColor=\'rgb(255, 255, 255)\'; 
                                         this.style.borderColor=\'rgb(90, 140, 247)\';
                                         this.querySelector(\'.fas\').style.color=\'rgb(90, 140, 247)\';" 
                            onmouseout="this.style.backgroundColor=\'rgb(90, 140, 247)\'; 
                                        this.style.borderColor=\'rgb(90, 140, 247)\';
                                        this.querySelector(\'.fas\').style.color=\'white\';"><i class="fas fa-cart-plus"></i></button>';                            
                                        echo '</form>';                            
                        
                            echo '<form action="/lunch/home/fav_cat.php" method="POST" class="m-0">';
                            echo '<input type="hidden" name="cat_id" value="' . htmlspecialchars($row['cat_id']) . '">';                             
                            echo '<input type="hidden" name="user_id" value="' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '') . '">';
                            echo '<button type="submit" class="btn btn-secondary" 
                                style="background-color:rgb(247, 90, 90); border-color:rgb(247, 90, 90);" 
                                onmouseover="this.style.backgroundColor=\'rgb(255, 255, 255)\'; 
                                            this.style.borderColor=\'rgb(247, 90, 90)\';
                                            this.querySelector(\'.fas\').style.color=\'rgb(247, 90, 90)\';" 
                                onmouseout="this.style.backgroundColor=\'rgb(247, 90, 90)\'; 
                                            this.style.borderColor=\'rgb(247, 90, 90)\';
                                            this.querySelector(\'.fas\').style.color=\'white\';">
                                <i class="fas fa-heart"></i>
                            </button>';
                            echo '</form>';
                            echo '<form action="/lunch/home/fav_seller.php" method="POST" class="m-0">';
                            echo '<input type="hidden" name="seller_id" value="' . htmlspecialchars($row['seller_id']) . '">';
                            echo '<input type="hidden" name="user_id" value="' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '') . '">';
                            echo '<button type="submit" class="btn btn-secondary" 
                            style="background-color:rgb(71, 32, 32); border-color:rgb(71, 32, 32);" 
                            onmouseover="this.style.backgroundColor=\'rgb(255, 255, 255)\'; 
                                         this.style.borderColor=\'rgb(71, 32, 32)\';
                                         this.querySelector(\'.fas\').style.color=\'rgb(71, 32, 32)\';" 
                            onmouseout="this.style.backgroundColor=\'rgb(71, 32, 32)\'; 
                                        this.style.borderColor=\'rgb(71, 32, 32)\';
                                        this.querySelector(\'.fas\').style.color=\'white\';"><i class="fas fa-user-plus"></i></button>';                            
                            echo '</form>';
                            echo '</div>';
                            
                            echo "</div>"; 
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No sellers found</p>";
                    }
                } else {
                    echo "<p>Error: " . $conn->error . "</p>";
                }
                    $conn->close();
                    ?>
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
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const scrollPosition = window.scrollY;
                    localStorage.setItem('scrollPosition', scrollPosition);
                });
            });

            window.addEventListener('load', function() {
                const scrollPosition = localStorage.getItem('scrollPosition');
                if (scrollPosition) {
                    window.scrollTo(0, parseInt(scrollPosition));
                    localStorage.removeItem('scrollPosition');
                }
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>      
        </body>
    </html>