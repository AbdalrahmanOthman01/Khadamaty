<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// --- STRICT LOGIN CHECK ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_required_message'] = "الرجاء تسجيل الدخول أولاً للوصول إلى هذه الصفحة وإنشاء طلب.";
    header("Location: login.php"); // Make sure login.php exists and handles login
    exit();
}
// If we reach here, user is considered logged in.

require_once 'db.php'; // $pdo variable will be available.

// --- Helper function to process item selections for database (PDO version) ---
function process_items_for_pdo($category_key, &$total_qty_for_category) {
    $items_array = [];
    $current_category_total_q = 0;

    if (isset($_POST[$category_key.'_qty']) && is_array($_POST[$category_key.'_qty'])) {
        foreach ($_POST[$category_key.'_qty'] as $item_name_key => $quantity_str) {
            $quantity = filter_var($quantity_str, FILTER_VALIDATE_INT);

            if ($quantity && $quantity > 0) {
                $item_display_name_default = htmlspecialchars($item_name_key); 
                $item_final_name = $item_display_name_default;

                if (isset($_POST[$category_key.'_details'][$item_name_key])) {
                    $details = $_POST[$category_key.'_details'][$item_name_key];
                    if (isset($details['type']) && !empty(trim($details['type']))) { 
                        $item_final_name = $item_display_name_default . ' (' . htmlspecialchars(trim($details['type'])) . ')';
                    } elseif (isset($details['text']) && !empty(trim($details['text']))) { 
                        $item_final_name = htmlspecialchars(trim($details['text']));
                    }
                }
                
                $items_array[] = $item_final_name; // Only item name
                $current_category_total_q += $quantity; 
            }
        }
    }
    
    $total_qty_for_category = $current_category_total_q; 
    return !empty($items_array) ? implode(", ", $items_array) : NULL; 
}


// --- Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_order'])) {
    
    // User ID is already confirmed to be in session from the check above
    $user_id = $_SESSION['user_id']; 
    
    $order_id_value = "ORD-" . strtoupper(uniqid());

    $event_type_value = isset($_POST['event_type_hidden']) ? trim($_POST['event_type_hidden']) : NULL;
    $location_value = isset($_POST['party_location']) ? trim($_POST['party_location']) : NULL;
    $event_date_raw = isset($_POST['party_date_input']) ? trim($_POST['party_date_input']) : NULL;
    $event_date_value = $event_date_raw ?: NULL;
    $start_session_value = isset($_POST['start_time_input']) ? trim($_POST['start_time_input']) : NULL;
    $end_session_value = isset($_POST['end_time_input']) ? trim($_POST['end_time_input']) : NULL;

    $meals_q_db = 0; $drinks_q_db = 0; $deserts_q_db = 0; $clothes_q_db = 0; $accessory_q_db = 0; $media_q_db = 0;

    $meals_text_db = process_items_for_pdo('meals', $meals_q_db);
    $drinks_text_db = process_items_for_pdo('drinks', $drinks_q_db);
    $deserts_text_db = process_items_for_pdo('deserts', $deserts_q_db);
    $clothes_text_db = process_items_for_pdo('clothes', $clothes_q_db);
    $accessory_text_db = process_items_for_pdo('accessory', $accessory_q_db);
    $media_text_db = process_items_for_pdo('media', $media_q_db);

    $errors = [];
    // $user_id check is no longer needed here as it's enforced at the top
    if (empty($event_type_value)) { $errors[] = "نوع الحفلة مطلوب (من الخطوة 1)."; }
    if (empty($location_value)) { $errors[] = "مكان الحفلة مطلوب (من الخطوة 2)."; }
    if (empty($event_date_value)) { $errors[] = "تاريخ الحفلة مطلوب (من الخطوة 3)."; }
    if (empty($start_session_value)) { $errors[] = "وقت بدء الجلسة مطلوب (من الخطوة 3)."; }
    if (empty($end_session_value)) { $errors[] = "وقت انتهاء الجلسة مطلوب (من الخطوة 3)."; }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO orders (order_id, user_id, event_type, location, event_date, start_session, end_session, meals, meals_q, drinks, drinks_q, deserts, deserts_q, clothes, clothes_q, accessory, accessory_q, media, media_q, status) 
                    VALUES (:order_id, :user_id, :event_type, :location, :event_date, :start_session, :end_session, :meals, :meals_q, :drinks, :drinks_q, :deserts, :deserts_q, :clothes, :clothes_q, :accessory, :accessory_q, :media, :media_q, 'pending')";
            
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':order_id', $order_id_value);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':event_type', $event_type_value);
            $stmt->bindParam(':location', $location_value);
            $stmt->bindParam(':event_date', $event_date_value);
            $stmt->bindParam(':start_session', $start_session_value);
            $stmt->bindParam(':end_session', $end_session_value);
            
            $stmt->bindParam(':meals', $meals_text_db);
            $stmt->bindParam(':meals_q', $meals_q_db, PDO::PARAM_INT);
            $stmt->bindParam(':drinks', $drinks_text_db);
            $stmt->bindParam(':drinks_q', $drinks_q_db, PDO::PARAM_INT);
            $stmt->bindParam(':deserts', $deserts_text_db);
            $stmt->bindParam(':deserts_q', $deserts_q_db, PDO::PARAM_INT);
            $stmt->bindParam(':clothes', $clothes_text_db);
            $stmt->bindParam(':clothes_q', $clothes_q_db, PDO::PARAM_INT);
            $stmt->bindParam(':accessory', $accessory_text_db);
            $stmt->bindParam(':accessory_q', $accessory_q_db, PDO::PARAM_INT);
            $stmt->bindParam(':media', $media_text_db);
            $stmt->bindParam(':media_q', $media_q_db, PDO::PARAM_INT);
            
            $stmt->execute();

            // --- MODIFICATION: Set session variable for JavaScript alert on success ---
            $_SESSION['order_success_alert'] = "تم إرسال طلبك بنجاح! رقم الطلب: " . htmlspecialchars($order_id_value);

        } catch (PDOException $e) {
            // For database errors, still use the HTML display
            $_SESSION['message'] = "حدث خطأ أثناء حفظ الطلب: " . $e->getMessage(); 
            $_SESSION['message_type'] = "error";
            error_log("Order creation error: " . $e->getMessage());
        }
    } else {
        // For validation errors, use the HTML display
        $_SESSION['message'] = "الرجاء تصحيح الأخطاء التالية:<br>" . implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
    }
    // PRG Pattern: Redirect to the same page to display messages and prevent resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
$active_menu_link = ($current_page == 'menu.php') ? 'menu.php' : $current_page;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>إنشاء طلب خدمة - خدماتي</title>
    <meta name="description" content="منصة خدماتي - حلول رقمية مبتكرة لتسهيل طلباتك.">
    <meta name="keywords" content="خدماتي, منصة, تصميم, تطوير, خدمات رقمية, طلبات, تسويق">

    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&family=Poppins&family=Raleway&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="assets/fonts/mycustomfont.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="assets/css/menu.css" rel="stylesheet">
    <style>
        .form-message-container { width: 100%; display: flex; justify-content: center; margin-top: 20px; margin-bottom: 0px; }
        .form-message { padding: 15px; border-radius: 4px; font-size: 16px; text-align:center; border-width: 1px; border-style: solid; max-width: 800px; width: 90%; }
        .form-message.success { background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6; } /* Kept for other potential success messages, though order success is now an alert */
        .form-message.error { background-color: #f2dede; color: #a94442; border-color: #ebccd1; }
        .icon.selected { border: 2px solid #007bff !important; background-color: #e7f3ff !important; }
        .icon.selected i { color: #007bff !important; }
    </style>
</head>

<body>
    <header id="header" class="header d-flex align-items-center fixed-top" data-aos="fade-in" data-aos-delay="300">
        <div class="header-div container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
            <a href="index.php" class="logo d-flex align-items-center"><img style="object-fit: cover;" src="img/Untitled-1.png" alt="شعار خدماتي"></a>
            <nav id="navmenu" class="navmenu">
                <ul>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="Order.php" <?php if($active_menu_link == 'Order.php' && $current_page == 'Order.php') echo 'class="active"'; ?>>الصـفـحة الشـخـصـيـة</a></li>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="Order.php" <?php if($active_menu_link == 'Order.php' && $current_page == 'Order.php') echo 'class="active"'; ?>>الطـلـبـات</a></li>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="menu.php" <?php if($active_menu_link == 'menu.php') echo 'class="active"'; ?>>الخـدمــات</a></li>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="index.php#about-section">عن المـنـصـة</a></li>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="index.php" <?php if($active_menu_link == 'index.php' && $current_page == 'index.php' && strpos($_SERVER['REQUEST_URI'], '#') === false) echo 'class="active"'; ?>>الرئـيـسـيـة</a></li>
                </ul>
                <i class="mobile-nav-toggle"></i>
            </nav>
            <input type="checkbox" id="menu-toggle" hidden>
            <label for="menu-toggle" class="menu-icon hidden"><i class="fa-solid fa-bars"></i></label>
            <div class="overlay"></div>
            <div class="menu">
                <ul>
                    <li><a href="#home">الرئيسية</a></li>
                    <li><a href="#about">من نحن</a></li>
                    <li><a href="#services">خدماتنا</a></li>
                    <li><a href="#contact">اتصل بنا</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="main">
        <section id="hero" class="hero section dark-background">
            <img src="assets/img/hero-bg-2.jpg" alt="خلفية رئيسية" class="hero-bg">
            <div class="container"><div class="row gy-4 justify-content-between"><div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-right" data-aos-delay="300"><h2 style="font-family: 'MyCustomFont', sans-serif;">خــدمــاتــي</h2></div></div></div>
            <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none"><defs><path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path></defs><g class="wave1"><use xlink:href="#wave-path" x="50" y="3"></use></g><g class="wave2"><use xlink:href="#wave-path" x="50" y="0"></use></g><g class="wave3"><use xlink:href="#wave-path" x="50" y="9"></use></g></svg>
        </section>

        <!-- Display HTML Error Messages (if any) -->
        <?php if (isset($_SESSION['message']) && isset($_SESSION['message_type']) && $_SESSION['message_type'] === 'error'): ?>
            <div class="form-message-container">
                <div class="form-message <?php echo htmlspecialchars($_SESSION['message_type']); ?>">
                    <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="multiStepOrderForm">
        <div class="containerr">
            <!-- Step 1: Party Type  -->
            <div class="step active one" id="step1" data-aos="fade-in" data-aos-delay="300">
                <div class="right"><img src="img/Party.jpeg" alt="image"></div>
                <div class="left">
                    <h2>نوع الحفلة</h2>
                    <input type="hidden" name="event_type_hidden" id="event_type_hidden_input">
                    <div class="options-flex">
                        <div style="margin-left: 30px;" class="icons">
                            <div class="icon icon-expand" onclick="selectSingleOption(this, 'حفلة تخرج', 'event_type_hidden_input')"><i></i></div>
                            <h4 class="icon-label">حفلة تخرج</h4>
                        </div>
                        <div style="margin-left: 30px;" class="icons">
                            <div class="icon icon-expand" onclick="selectSingleOption(this, 'مؤتمر', 'event_type_hidden_input')"><i></i></div>
                            <h4 class="icon-label">مؤتمر</h4>
                        </div>
                        <div style="margin-left: 30px;" class="icons">
                            <div class="icon icon-expand" onclick="selectSingleOption(this, 'ملتقي علمي', 'event_type_hidden_input')"><i></i></div>
                            <h4 class="icon-label">ملتقي علمي</h4>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="button" class="blob-btn next-btn" onclick="validateAndNextStep()">التالي<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Party Place -->
            <div class="step two" id="step2">
                <div class="right"><img src="img/Where.jpeg" alt="image"></div>
                <div class="left">
                    <h2>مكان الحفلة</h2>
                    <label class="place" for="party-place-select">اختر مكان الحفلة</label>
                    <select id="party-place-select" name="party_location">
                        <option value="">-- اختر مكان الحفلة --</option>
                        <option value="في البيت">في البيت</option>
                        <option value="قاعة أفراح">قاعة أفراح</option>
                        <option value="فيلا">فيلا</option>
                        <option value="مطعم">مطعم</option>
                        <option value="على البحر">على البحر</option>
                    </select>
                    <div class="buttonss">
                        <div class="buttons"><button type="button" class="blob-btn back-btn" onclick="previousStep()">رجوع<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                        <div class="buttons"><button type="button" class="blob-btn next-btn" onclick="validateAndNextStep()">التالي<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Party Date  -->
            <div class="step three" id="step3">
                <div class="right"><img src="img/When.jpeg" alt="image"></div>
                <div class="left">
                    <h2>تاريخ الحفلة</h2>
                    <div class="top-time">
                        <div class="time">
                            <label for="party_date_input_id">اختر تاريخ الحفلة</label>
                            <input type="date" id="party_date_input_id" name="party_date_input">
                        </div>
                        <div class="time">
                            <label for="start_time_input_id">ميعاد البدء</label>
                            <input type="time" id="start_time_input_id" name="start_time_input">
                        </div>
                        <div class="time">
                            <label for="end_time_input_id">ميعاد الانتهاء</label>
                            <input type="time" id="end_time_input_id" name="end_time_input">
                        </div>
                    </div>
                    <div class="buttonss">
                        <div class="buttons"><button type="button" class="blob-btn back-btn" onclick="previousStep()">رجوع<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                        <div class="buttons"><button type="button" class="blob-btn next-btn" onclick="validateAndNextStep()">التالي<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Meals -->
            <div class="step four" id="step4">
                <div class="right"><img src="img/Meals.jpeg" alt="image"></div>
                <div style="margin-top: 10px;" class="left">
                    <h2>وجبات</h2>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'ارز + نص فرخه+سلطات')"><i></i></div>
                        <div class="icon-content">
                            <h4 class="icon-label one ">ارز + نص فرخه+سلطات</h4>
                            <input class="amount" type="number" name="meals_qty[ارز + نص فرخه+سلطات]" placeholder="الكمية" min="0">
                        </div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'ارز + كفته +سلطات')"><i></i></div>
                        <div class="icon-content">
                            <h4 class="icon-label two">ارز + كفته +سلطات</h4>
                            <input class="amount" type="number" name="meals_qty[ارز + كفته +سلطات]" placeholder="الكمية" min="0">
                        </div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'مكرونه + لحمه +سلطات')"><i></i></div>
                        <div class="icon-content">
                            <h4 class="icon-label three">مكرونه + لحمه +سلطات</h4>
                            <input class="amount" type="number" name="meals_qty[مكرونه + لحمه +سلطات]" placeholder="الكمية" min="0">
                        </div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'بيتزا')"><i></i></div>
                        <div class="icon-content">
                            <h4 class="icon-label four">بيتزا</h4>
                            <select name="meals_details[بيتزا][type]">
                                <option value="">نوع البيتزا</option>
                                <option value="مشكل لحوم">مشكل لحوم</option>
                                <option value="مارجريتا">مارجريتا</option>
                            </select>
                            <input class="amount" type="number" name="meals_qty[بيتزا]" placeholder="الكمية" min="0">
                        </div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'أخرى')"><i></i></div>
                        <div class="icon-content">
                            <h4 class="icon-label five">أخرى</h4>
                            <input class="select" type="text" name="meals_details[أخرى][text]" placeholder="حدد الوجبة" style="margin-right: 100px;"  >
                            <input class="amount" type="number" name="meals_qty[أخرى]" placeholder="الكمية" min="0" style="margin-top: -10px;">
                        </div>
                    </div>
                    <div class="buttonss">
                        <div class="buttons"><button type="button" class="blob-btn back-btn" onclick="previousStep()">رجوع<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                        <div class="buttons"><button type="button" class="blob-btn next-btn" onclick="validateAndNextStep()">التالي<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Drinks  -->
            <div class="step five" id="step5">
                <div class="right"><img src="img/Drinks.jpeg" alt="image"></div>
                <div class="left">
                    <h2>المشروبات</h2>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'شاي')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">شاي</h4><input class="amount" type="number" name="drinks_qty[شاي]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'قهوه')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">قهوه</h4><input class="amount" type="number" name="drinks_qty[قهوه]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'نسكافيه')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">نسكافيه</h4><input class="amount" type="number" name="drinks_qty[نسكافيه]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'عصير')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">عصير</h4><input class="amount" type="number" name="drinks_qty[عصير]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'أخرى')"><i></i></div>
                        <div class="icon-content">
                            <h4 class="icon-label">أخرى</h4>
                            <input class="select" type="text" name="drinks_details[أخرى][text]" placeholder="حدد المشروب">
                            <input class="amount" type="number" name="drinks_qty[أخرى]" placeholder="الكمية" min="0" style="margin-top: 5px;">
                        </div>
                    </div>
                    <div class="buttonss">
                        <div class="buttons"><button type="button" class="blob-btn back-btn" onclick="previousStep()">رجوع<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                        <div class="buttons"><button type="button" class="blob-btn next-btn" onclick="validateAndNextStep()">التالي<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Desserts -->
            <div class="step six" id="step6">
                <div class="right"><img src="img/Dessert.jpeg" alt="image"></div>
                <div class="left">
                    <h2>الحلويات</h2>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'جاتوه')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">جاتوه</h4><input class="amount" type="number" name="deserts_qty[جاتوه]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'تورته')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">تورته</h4><input class="amount" type="number" name="deserts_qty[تورته]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'بسبوسه')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">بسبوسه</h4><input class="amount" type="number" name="deserts_qty[بسبوسه]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'هريسه')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">هريسه</h4><input class="amount" type="number" name="deserts_qty[هريسه]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'أخرى')"><i></i></div>
                        <div class="icon-content">
                            <h4 class="icon-label">أخرى</h4>
                            <input class="select" type="text" name="deserts_details[أخرى][text]" placeholder="حدد الحلويات">
                            <input class="amount" type="number" name="deserts_qty[أخرى]" placeholder="الكمية" min="0" style="margin-top: 5px;">
                        </div>
                    </div>
                    <div class="buttonss">
                        <div class="buttons"><button type="button" class="blob-btn back-btn" onclick="previousStep()">رجوع<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                        <div class="buttons"><button type="button" class="blob-btn next-btn" onclick="validateAndNextStep()">التالي<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                    </div>
                </div>
            </div>

            <!-- Step 7: Clothes -->
            <div class="step seven" id="step7">
                <div class="right"><img src="img/Clothes.jpeg" alt="image"></div>
                <div class="left">
                    <h2>الملابس</h2>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'بدلة')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">بدلة</h4><input class="amount" type="number" name="clothes_qty[بدلة]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'فستان')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">فستان</h4><input class="amount" type="number" name="clothes_qty[فستان]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'روب تخرج')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">روب تخرج</h4><input class="amount" type="number" name="clothes_qty[روب تخرج]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'هودي')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">هودي</h4><input class="amount" type="number" name="clothes_qty[هودي]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'تي شيرت')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">تي شيرت</h4><input class="amount" type="number" name="clothes_qty[تي شيرت]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'أخرى')"><i></i></div>
                        <div class="icon-content">
                            <h4 class="icon-label">أخرى</h4>
                            <input class="select" type="text" name="clothes_details[أخرى][text]" placeholder="حدد القطعة">
                            <input class="amount" type="number" name="clothes_qty[أخرى]" placeholder="الكمية" min="0" style="margin-top: 5px;">
                        </div>
                    </div>
                    <div class="buttonss">
                        <div class="buttons"><button type="button" class="blob-btn back-btn" onclick="previousStep()">رجوع<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                        <div class="buttons"><button type="button" class="blob-btn next-btn" onclick="validateAndNextStep()">التالي<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                    </div>
                </div>
            </div>

            <!-- Step 8: Accessories -->
            <div class="step eight" id="step8">
                <div class="right"><img src="img/Accessories.jpeg" alt="image"></div>
                <div class="left">
                    <h2>الاكسسوارات</h2>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'كارت تعريف للاشخاص')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">كارت تعريف للاشخاص</h4><input class="amount" type="number" name="accessory_qty[كارت تعريف للاشخاص]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'شهادات ودروع')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">شهادات ودروع</h4><input class="amount" type="number" name="accessory_qty[شهادات ودروع]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'كاب')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">كاب</h4><input class="amount" type="number" name="accessory_qty[كاب]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'مج')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">مج</h4><input class="amount" type="number" name="accessory_qty[مج]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'أخرى')"><i></i></div>
                        <div class="icon-content">
                            <h4 class="icon-label">أخرى</h4>
                            <input class="select" type="text" name="accessory_details[أخرى][text]" placeholder="حدد القطعة">
                            <input class="amount" type="number" name="accessory_qty[أخرى]" placeholder="الكمية" min="0" style="margin-top: 5px;">
                        </div>
                    </div>
                    <div class="buttonss">
                        <div class="buttons"><button type="button" class="blob-btn back-btn" onclick="previousStep()">رجوع<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                        <div class="buttons"><button type="button" class="blob-btn next-btn" onclick="validateAndNextStep()">التالي<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                    </div>
                </div>
            </div>

            <!-- Step 9: Advertising -->
            <div class="step nine" id="step9">
                <div class="right"><img src="img/5.png" alt="image"></div>
                <div class="left">
                    <h2>دعايا و إعلان</h2>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'لافتات')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">لافتات</h4><input class="amount" type="number" name="media_qty[لافتات]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'بروشور')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">بروشور</h4><input class="amount" type="number" name="media_qty[بروشور]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'اعلانات مموله')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">اعلانات مموله</h4><input class="amount" type="number" name="media_qty[اعلانات مموله]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'تصميمات جرافيكيه')"><i></i></div>
                        <div class="icon-content"><h4 class="icon-label">تصميمات جرافيكيه</h4><input class="amount" type="number" name="media_qty[تصميمات جرافيكيه]" placeholder="الكمية" min="0"></div>
                    </div>
                    <div class="icons">
                        <div class="icon icon-expand" onclick="selectOption(this, 'أخرى')"><i></i></div>
                        <div class="icon-content">
                            <h4 class="icon-label">أخرى</h4>
                            <input class="select" type="text" name="media_details[أخرى][text]" placeholder="حدد الطريقة">
                            <input class="amount" type="number" name="media_qty[أخرى]" placeholder="الكمية" min="0" style="margin-top: 5px;">
                        </div>
                    </div>
                    <div class="buttonss">
                        <div class="buttons"><button type="button" class="blob-btn back-btn" onclick="previousStep()">رجوع<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button></div>
                        <div class="buttons">
                            <button type="submit" name="submit_order" class="blob-btn next-btn">حفظ<span class="blob-btn__inner"><span class="blob-btn__blobs"><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span><span class="blob-btn__blob"></span></span></span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </form> 
    </main>

    <footer id="footer" class="footer dark-background">
        <div class="container footer-top">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6 footer-about">
                    <a href="index.php" class="logo d-flex align-items-center"><img src="img/png.png" alt="شعار خدماتي"></a>
                    <div class="footer-contact pt-3"></div>
                    <div class="social-links d-flex mt-4"><a href=""><i class="bi bi-twitter-x"></i></a><a href=""><i class="bi bi-facebook"></i></a><a href=""><i class="bi bi-instagram"></i></a><a href=""><i class="bi bi-linkedin"></i></a></div>
                </div>
                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>وصـول سـريـع</h4>
                    <ul>
                        <li><a href="index.php" <?php if($active_menu_link == 'index.php' && $current_page == 'index.php' && strpos($_SERVER['REQUEST_URI'], '#') === false) echo 'class="active"'; ?>>الرئـيـسـيـة</a></li>
                        <li><a href="index.php#about-section">عن المـنـصـة</a></li>
                        <li><a href="menu.php" <?php if($active_menu_link == 'menu.php') echo 'class="active"'; ?>>الخـدمــات</a></li>
                        <li><a href="Order.php">الطـلـبـات</a></li>
                        <li><a href="Order.php">الصـفـحة الشـخـصـيـة</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-3 footer-links"><h4>خدماتنا</h4><ul><li><a href="#">تصميم المواقع</a></li><li><a href="#">تطوير المواقع</a></li><li><a href="#">إدارة المنتجات</a></li><li><a href="#">التسويق</a></li><li><a href="#">تصميم جرافيكي</a></li></ul></div>
                <div class="col-lg-4 col-md-12 footer-newsletter">
                    <h4>النشرة البريدية</h4><p>اشترك في النشرة البريدية لتصلك أحدث الأخبار والخدمات!</p>
                    <form action="forms/newsletter.php" method="post" class="php-email-form">
                        <div class="newsletter-form"><input type="email" name="email" placeholder="أدخل بريدك الإلكتروني" required><input type="submit" value="اشترك"></div>
                        <div class="loading">جارٍ الإرسال...</div><div class="error-message"></div><div class="sent-message">تم إرسال طلب الاشتراك. شكرًا لك!</div>
                    </form>
                </div>
            </div>
        </div>
    </footer>

    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <div id="preloader"></div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    
    <script src="assets/js/menu.js"></script> <!-- Your custom JS for step navigation -->

    <script>
        // JS for single choice event type
        function selectSingleOption(iconElement, value, targetHiddenId) {
            const hiddenInput = document.getElementById(targetHiddenId);
            if (!hiddenInput) {
                console.error('Hidden input with ID ' + targetHiddenId + ' not found.');
                return;
            }
            const parentOptionsContainer = iconElement.closest('.options-flex'); 
            if (parentOptionsContainer) {
                parentOptionsContainer.querySelectorAll('.icon.selected').forEach(selectedIcon => {
                   selectedIcon.classList.remove('selected');
                });
            }
            iconElement.classList.add('selected');
            hiddenInput.value = value;
        }

        // JS for multi-choice items (meals, drinks etc.)
        function selectOption(iconElement, itemName) {
            iconElement.classList.toggle('selected');
            const qtyInput = iconElement.closest('.icons').querySelector('.icon-content .amount');
            if (qtyInput && !iconElement.classList.contains('selected')) {
                 // qtyInput.value = ''; // Optional: clear quantity on deselect if desired
            }
        }

        // JS for mobile menu
        const checkbox = document.getElementById('menu-toggle');
        if (checkbox) {
            const links = document.querySelectorAll('.menu a'); 
            const overlay = document.querySelector('.overlay');
            function closeMobileMenu() {
                checkbox.checked = false;
                document.body.classList.remove('no-scroll');
            }
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) {
                    document.body.classList.add('no-scroll');
                } else {
                    document.body.classList.remove('no-scroll');
                }
            });
            links.forEach(link => {
                link.addEventListener('click', closeMobileMenu);
            });
            if (overlay) {
                overlay.addEventListener('click', closeMobileMenu);
            }
        }

        // --- Display success alert if set ---
        <?php if (isset($_SESSION['order_success_alert'])): ?>
            alert("<?php echo addslashes($_SESSION['order_success_alert']); ?>");
            <?php unset($_SESSION['order_success_alert']); // Clear the message after displaying ?>
        <?php endif; ?>
    </script>
</body>
</html>