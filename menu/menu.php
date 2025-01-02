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
        <title>Menu</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="menustyles.css">
        <link rel="stylesheet" href="homestyles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            
        </style>
    </head>
    <body>
        <!-- Navigation Bar -->
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
                <div class="collapse navbar-collapse justify-content-end align-center"
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

        <!-- Main Content -->
        <div class="main">
            <?php
            $query = "SELECT cat_name, cat_image, cat_id FROM item_catagory";
            $result = $conn->query($query); 

            if ($result) {
                if ($result->num_rows > 0) {
                    echo '<div class="container">'; 
                    echo '<div class="row g-4">'; 
                    
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="col-md-4">'; 
                        echo '<div class="card h-100" style="width: 18rem;">';
                        echo "<img src='" . str_replace('./', '/', $row['cat_image']) . "' alt='" . htmlspecialchars($row['cat_name']) . "' class='card-img-top'>";
                        echo '<div class="card-body d-flex flex-column justify-content-end">';
                        // Updated link to redirect to home.php with category search
                        echo '<a href="/lunch/home/home.php?search=' . urlencode($row['cat_name']) . '&submit=true" 
                              class="btn btn-dark mt-auto" 
                              style="background-color: rgb(37, 40, 43); border-color: rgb(37, 40, 43);"
                              onmouseover="this.style.backgroundColor=\'white\'; this.style.color=\'rgb(37, 40, 43)\';" 
                              onmouseout="this.style.backgroundColor=\'rgb(37, 40, 43)\'; this.style.color=\'white\';">' 
                              . htmlspecialchars($row['cat_name']) . '</a>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>'; 
                    echo '</div>';
                }
            }
            ?>
        </div>

        <!-- Scripts -->
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>      
    </body>
</html>