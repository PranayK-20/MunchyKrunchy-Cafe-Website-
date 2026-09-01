<?php
    include('Connection.php');

    $selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
    $menuItems = [];
    $categories = [];

    $categoryQuery = "SELECT DISTINCT item_category FROM menu WHERE item_category IS NOT NULL AND item_category <> '' ORDER BY item_category ASC";
    $categoryResult = mysqli_query($conn, $categoryQuery);
    if ($categoryResult) {
        while ($row = mysqli_fetch_assoc($categoryResult)) {
            $categories[] = $row['item_category'];
        }
    }

    if ($selectedCategory !== '') {
        $query = "SELECT * FROM menu WHERE item_category = ? ORDER BY item_id ASC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $selectedCategory);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $query = "SELECT * FROM menu ORDER BY item_id ASC";
        $result = mysqli_query($conn, $query);
    }

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $menuItems[] = $row;
        }
    } elseif ($result === false) {
        echo "Error: {$conn->error}";
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Munchy Krunchy</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
            <div class="Order">
                <nav>
                    <ul>
                        <li><img src="MunchyKrunchyLogo.jpeg" alt="Munchy Krunchy Logo" class="logo"></li>
                        <li><a href="MunchyKrunchy.php">Home</a></li>
                        <li><a href="MunchyKrunchy.php#about">About Us</a></li>
                        <li><a href="Login.php">Admin</a></li>
                        <li class="cart"><a href="cart.php"><img src="cart.png" alt="Cart"></a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <header class="header">
            <h1>Welcome to Munchy Krunchy</h1>
        </header>
        <section class="menu">
            <h2>Menu</h2>
            <form method="GET" action="MunchyKrunchy.php" class="category-form">
                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category) { ?>
                        <option value="<?php echo $category; ?>" <?php echo ($selectedCategory === $category) ? 'selected' : ''; ?>>
                            <?php echo $category; ?>
                        </option>
                    <?php } ?>
                </select>
            </form>
            <?php if (!empty($menuItems)) { ?>
                <div class="menu-grid">
                    <?php foreach ($menuItems as $item) { ?>
                        <div class="menu-card">
                            <div class="menu-card-top">
                                <h3><?php echo $item['menu_items']; ?></h3>
                                <p><?php echo $item['description']; ?></p>
                                <div class="menu-meta">
                                    <span><?php echo $item['veg_nonveg']; ?></span>
                                    <span><?php echo $item['item_category']; ?></span>
                                </div>
                            </div>
                            <div class="menu-card-bottom">
                                <strong class="menu-price">Rs. <?php echo number_format($item['price'], 2); ?></strong>
                                <div class="menu-actions">
                                    <a href="cart.php?add_item=<?php echo $item['item_id']; ?>" class="btn btn-secondary">Add to Cart</a>
                                    <a href="Order.php?item_id=<?php echo $item['item_id']; ?>" class="btn btn-primary">Order Item</a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

            <section id="about" class="about-section">
                <div class="about-grid">
                    <div class="about-text">
                        <h1><span class="section-tag">About Us</span></h1>
                        <p>Welcome to Munchy Krunchy, your friendly neighbourhood place for tasty snacks and meals. We focus on fresh ingredients, quick service, and a menu made for every craving.</p>
                        <div class="about-highlights">
                            <div>
                                <strong>Fresh Ingredients</strong>
                                <p>Daily-prepared dishes with quality produce.</p>
                            </div>
                        </div>
                    </div>
                    <div class="about-details">
                        <div class="about-card">
                            <h3>Contact</h3>
                            <address>
                                Shop No. 1, Ground floor, Navghar Rd,<br>
                                Bhayandar, Shirdi Nagar,<br>
                                Bhayandar East, Mira Bhayandar,<br>
                                Maharashtra 401105
                            </address>
                            <p><strong>Phone:</strong> <a href="tel:+919137459086">091374 59086</a></p>
                        </div>
                        <div class="about-card">
                            <h3>Opening Hours</h3>
                            <ul type="circle">
                                <li>Monday: 10 am–10 pm</li>
                                <li>Tuesday: 10 am–10 pm</li>
                                <li>Wednesday: 10 am–10 pm</li>
                                <li>Thursday: 10 am–10 pm</li>
                                <li>Friday: 10 am–10 pm</li>
                                <li>Saturday: 10 am–10 pm</li>
                                <li>Sunday: 10 am–10 pm</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <?php } else { ?>
                <p>No items available right now.</p>
            <?php } ?>
            <footer class="footer">
                <p>Copyright &copy; 2026 Munchy Krunchy. All rights reserved.</p>
            </footer>
        </section>
    </body>
</html>