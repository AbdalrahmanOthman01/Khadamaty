<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// --- STRICT LOGIN CHECK ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_required_message'] = "الرجاء تسجيل الدخول أولاً للوصول إلى هذه الصفحة.";
    header("Location: login.php"); // Ensure login.php exists
    exit();
}

require_once 'db.php'; // Your PDO connection ($pdo)

$userId = $_SESSION['user_id'];
$userData = null;
$userOrders = [];

// --- HANDLE ACCOUNT DELETION REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    try {
        $pdo->beginTransaction();

        $stmtDeleteUser = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
        $stmtDeleteUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtDeleteUser->execute();

        $pdo->commit();

        session_unset();
        session_destroy();
        session_start(); 
        $_SESSION['account_deleted_message'] = "تم حذف حسابك بنجاح.";
        header("Location: index.php"); 
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error deleting account for user ID $userId: " . $e->getMessage());
        $_SESSION['profile_action_message'] = "حدث خطأ أثناء محاولة حذف الحساب. يرجى المحاولة مرة أخرى.";
        $_SESSION['profile_action_message_type'] = "error";
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit();
    }
}

// --- HANDLE ORDER DELETION REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_order') {
    if (!isset($_POST['order_id_to_delete']) || empty($_POST['order_id_to_delete'])) {
        $_SESSION['profile_action_message'] = "لم يتم تحديد رقم الطلب للحذف.";
        $_SESSION['profile_action_message_type'] = "error";
        header("Location: " . $_SERVER['PHP_SELF'] . "#Order");
        exit();
    }

    $orderIdToDelete = $_POST['order_id_to_delete'];

    try {
        $pdo->beginTransaction();

        $stmtCheckOrder = $pdo->prepare("SELECT user_id FROM orders WHERE order_id = :order_id AND user_id = :user_id_session");
        $stmtCheckOrder->bindParam(':order_id', $orderIdToDelete, PDO::PARAM_STR);
        $stmtCheckOrder->bindParam(':user_id_session', $userId, PDO::PARAM_INT);
        $stmtCheckOrder->execute();
        $orderOwner = $stmtCheckOrder->fetch();

        if (!$orderOwner) {
            $pdo->rollBack(); 
            $_SESSION['profile_action_message'] = "الطلب غير موجود أو لا تملك صلاحية حذفه.";
            $_SESSION['profile_action_message_type'] = "error";
            header("Location: " . $_SERVER['PHP_SELF'] . "#Order");
            exit();
        }

        $stmtDeleteOrder = $pdo->prepare("DELETE FROM orders WHERE order_id = :order_id AND user_id = :user_id_session");
        $stmtDeleteOrder->bindParam(':order_id', $orderIdToDelete, PDO::PARAM_STR);
        $stmtDeleteOrder->bindParam(':user_id_session', $userId, PDO::PARAM_INT);
        $stmtDeleteOrder->execute();

        if ($stmtDeleteOrder->rowCount() > 0) {
            $pdo->commit();
            $_SESSION['profile_action_message'] = "تم حذف الطلب رقم " . htmlspecialchars($orderIdToDelete) . " بنجاح.";
            $_SESSION['profile_action_message_type'] = "success";
        } else {
            $pdo->rollBack();
            $_SESSION['profile_action_message'] = "لم يتم حذف الطلب. قد يكون تم حذفه بالفعل أو أنك لا تملك الصلاحية.";
            $_SESSION['profile_action_message_type'] = "error";
        }
        
        header("Location: " . $_SERVER['PHP_SELF'] . "#Order");
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error deleting order ID $orderIdToDelete for user ID $userId: " . $e->getMessage());
        $_SESSION['profile_action_message'] = "حدث خطأ أثناء محاولة حذف الطلب. يرجى المحاولة مرة أخرى.";
        $_SESSION['profile_action_message_type'] = "error";
        header("Location: " . $_SERVER['PHP_SELF'] . "#Order");
        exit();
    }
}


$current_page_name = basename($_SERVER['PHP_SELF']);
$userPhone = "غير متوفر"; 
$userDob = "غير متوفر"; 

// --- Fetch User Profile Data ---
try {
    $stmtUser = $pdo->prepare("SELECT id, first_name, last_name, email, phone, gender, image, role, college, created_at FROM users WHERE id = :user_id");
    $stmtUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtUser->execute();
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$userData) { 
        session_unset();
        session_destroy();
        header("Location: login.php?message=session_expired");
        exit();
    } else {
        if (!empty($userData['phone'])) {
            $userPhone = htmlspecialchars($userData['phone']);
        }
    }

} catch (PDOException $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $userData = false; 
}

// --- Fetch User Orders Data ---
try {
    $stmtOrders = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmtOrders->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtOrders->execute();
    $userOrders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching user orders: " . $e->getMessage());
}

function getOrderStatusDisplay($status) {
    switch ($status) {
        case 'pending': return '<span class="label btm-shape status-pending">قيد الانتظار</span>'; // Changed 'جاري التنفيذ' to 'قيد الانتظار' for consistency
        case 'approved': return '<span class="label btm-shape status-approved">مقبول</span>'; // Changed 'مكتمل' to 'مقبول'
        case 'rejected': return '<span class="label btm-shape status-rejected">مرفوض</span>';
        default: return '<span class="label btm-shape">' . htmlspecialchars($status) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>الملف الشخصي والطلبات - خدماتي</title>
    <meta name="description" content="صفحة المستخدم الشخصية وعرض الطلبات في منصة خدماتي.">
    <meta name="keywords" content="ملف شخصي, طلبات, خدماتي, مستخدم">

    <link rel="stylesheet" href="assets/css/framework.css">
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="assets/css/order.css" rel="stylesheet">
    <style>
        .status-pending { background-color: #f29c1d; color: white; }
        .status-approved { background-color: #4caf50; color: white; }
        .status-rejected { background-color: #f44336; color: white; }
        .eye-icon { cursor: pointer; color: #3498db; }
        /* Style for new Logout button to match existing delete button */
        .logout-btn {
            background-color: #3498db; color: white; padding: 6px 15px; border-radius: 4px; border: none; font-size: 14px; text-decoration: none; display: inline-block;
        }
        .logout-btn:hover { background-color: #2980b9; color: white; }

        /* Modal styling replicated from Admin for consistency */
        .modal-overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center;
            z-index: 1050; 
        }
        .modal-content {
            background-color: white; padding: 0; border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            width: 90%; max-width: 700px;
            direction: rtl; display: flex; flex-direction: column; max-height: 80vh;
        }
        #modal h2 span { font-weight: normal; font-size: 0.9em; }
        
        .profile-action-message-container { width: 100%; display: flex; justify-content: center; margin-top: 10px; margin-bottom: 10px; }
        .profile-action-message { padding: 10px 15px; border-radius: 4px; font-size: 0.9em; text-align:center; border-width: 1px; border-style: solid; max-width: 600px; width: 90%; }
        .profile-action-message.error { background-color: #f2dede; color: #a94442; border-color: #ebccd1; }
        .profile-action-message.success { background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6; }
    </style>
</head>
<body class="index-page">

    <header dir="ltr" id="header" class="header d-flex align-items-center fixed-top" data-aos="fade-in" data-aos-delay="300">
        <div class="header-div container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
            <a href="index.php" class="logo d-flex align-items-center"><img style="object-fit: cover;" src="img/Untitled-1.png" alt="شعار خدماتي"></a>
            <nav id="navmenu" class="navmenu">
                <ul>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="#Profile" class="active">الصـفـحة الشـخـصـيـة</a></li>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="#Order" class="active">الطـلـبـات</a></li>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="menu.php">الخـدمــات</a></li>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="index.php#about-section">عن المـنـصـة</a></li>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="index.php">الرئـيـسـيـة</a></li>
                </ul>
                <i class="mobile-nav-toggle"></i>
            </nav>
            <input type="checkbox" id="menu-toggle" hidden>
            <label for="menu-toggle" class="menu-icon hidden"><i class="fa-solid fa-bars"></i></label>
            <div class="overlay"></div>
            <div class="menu"><ul><li><a href="index.php">الرئيسية</a></li><li><a href="index.php#about-section">من نحن</a></li><li><a href="menu.php">خدماتنا</a></li><li><a href="#contact">اتصل بنا</a></li></ul></div>
        </div>
    </header>

    <main class="main">
        <section id="hero" class="hero section dark-background"><img src="assets/img/hero-bg-2.jpg" alt="خلفية الصفحة" class="hero-bg"></section>
        
        <?php if (isset($_SESSION['profile_action_message'])): ?>
            <div class="profile-action-message-container"><div class="profile-action-message <?php echo htmlspecialchars($_SESSION['profile_action_message_type'] ?? 'info'); ?>"><?php echo htmlspecialchars($_SESSION['profile_action_message']); ?></div></div>
            <?php unset($_SESSION['profile_action_message']); unset($_SESSION['profile_action_message_type']); ?>
        <?php endif; ?>

        <div id="Profile" class="page d-flex">
            <div class="content w-full">
                <h1 class="p-relative">الصفحة الشخصية</h1>
                <?php if ($userData): ?>
                <div class="profile-page m-20 d-flex">
                    <div class="overview bg-white d-flex align-center rad-10 m-20 p-12">
                        <div class="avatar-box txt-c p-20">
                            <img class="rad-half mb-10" src="<?php echo !empty($userData['image']) ? htmlspecialchars($userData['image']) : 'img/avatar.png'; ?>" alt="صورة المستخدم" style="width: 120px; height: 120px; object-fit: cover;">
                            <h3 class="m-0"><?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?></h3>
                        </div>
                        <div class="info-box bg-white d-flex p-10 w-full txt-c-mobile">
                            <div class="box d-flex align-center">
                                <h4 class="c-grey fs-15 fw-bold w-full">معلومات عامة</h4>
                                <div class="fs-14"><span class="c-grey">الأسم الأول:</span> <span><?php echo htmlspecialchars($userData['first_name']); ?></span></div>
                                <div class="fs-14"><span class="c-grey">الأسم الأخير:</span> <span><?php echo htmlspecialchars($userData['last_name']); ?></span></div>
                                <div class="fs-14"><span class="c-grey">النوع:</span> <span><?php echo !empty($userData['gender']) ? htmlspecialchars($userData['gender']) : 'غير محدد'; ?></span></div>
                                <div class="fs-14"><span class="c-grey">الكلية:</span> <span><?php echo !empty($userData['college']) ? htmlspecialchars($userData['college']) : 'غير محدد'; ?></span></div>
                            </div>
                            <div class="box d-flex align-center">
                                <h4 class="c-grey fs-15 w-full m-0">المعلومات الشخصية</h4>
                                <div class="fs-14"><span class="c-grey">البريد الإلكتروني:</span> <span><?php echo htmlspecialchars($userData['email']); ?></span></div>
                                <div class="fs-14"><span class="c-grey">رقم الهاتف:</span> <span><?php echo $userPhone; ?></span></div>
                            </div>
                            <!-- FIX: Action buttons container with Logout -->
                            <div class="box d-flex align-center" style="gap: 15px;">
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return confirm('هل أنت متأكد أنك تريد حذف حسابك؟ هذا الإجراء لا يمكن التراجع عنه وسيتم حذف جميع بياناتك.');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_account">
                                    <button type="submit" class="delete" style="padding: 6px 15px; font-size: 14px;">حذف الحساب</button>
                                </form>
                                <a href="logout.php" class="logout-btn">تسجيل الخروج</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <p class="m-20 error-message">حدث خطأ أثناء تحميل بيانات الملف الشخصي. يرجى المحاولة مرة أخرى.</p>
                <?php endif; ?>


                <h1 id="Order" style="margin-top: 100px;" class="p-relative">الطـلـبـات</h1>
                <div style="margin-top: 80px;" class="projects p-20 bg-white rad-10 m-20">
                    <div class="responsive-table">
                        <table class="order fs-15 w-full">
                            <thead><tr><td>رقم الطلب</td><td>تاريخ الطلب</td><td>تفاصيل الطلب</td><td>تاريخ الحدث</td><td>الحالة</td></tr></thead>
                            <tbody>
                                <?php if (!empty($userOrders)): ?>
                                    <?php foreach ($userOrders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($order['created_at']))); ?></td>
                                        <td>
                                            <!-- MODIFIED: onclick passes the full order object -->
                                            <span class="eye-icon" onclick='openModal(<?php echo htmlspecialchars(json_encode($order), ENT_QUOTES, 'UTF-8'); ?>)'>
                                                <i class="fa-solid fa-eye"></i>
                                            </span>
                                        </td>
                                        <td><?php echo !empty($order['event_date']) ? htmlspecialchars($order['event_date']) : 'غير محدد'; ?></td>
                                        <td><?php echo getOrderStatusDisplay($order['status']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align: center;">لا توجد طلبات لعرضها.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- FIX: Modal Structure Replicated from Admin Page -->
    <div class="modal-overlay" id="modal">
        <div class="modal-content">
            <h2 style="text-align: center; position: sticky; top: 0; background: white; padding: 10px 0; z-index: 1; border-bottom: 1px solid #eee;">
                تفاصيل الطلب <span id="modal_display_order_id_header"></span>
            </h2>
            <button aria-label="إغلاق" onclick="closeModal()" style="position: absolute; top: 0; right: 20px; background: transparent; border: none; font-size: 52px; cursor: pointer;">×</button>
            <div style="flex: 1 1 auto; overflow-y: auto;">
                <div class="responsive-table p-20">
                    <table class="fs-15 w-full tafaseel" style="width: 100%; text-align: right;">
                        <thead><tr><th style="width:30%;">الصنف</th><th>المنتج المختار</th><th style="width:15%;">الكمية</th></tr></thead>
                        <tbody id="modal_order_details_body"></tbody>
                    </table>
                </div>
            </div>
            <div style="padding: 15px 20px; text-align: center; border-top: 1px solid #eee; position: sticky; bottom: 0; background: white;">
                <!-- User-specific delete form -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟ هذا الإجراء لا يمكن التراجع عنه.');">
                    <input type="hidden" name="action" value="delete_order">
                    <input type="hidden" name="order_id_to_delete" id="order_id_to_delete_input" value="">
                    <button type="submit" class="delete" style="padding: 10px 45px; border-radius: 21px; font-size: 18px;">حذف الطلب</button>
                </form>
            </div>
        </div>
    </div>


    <footer id="footer" class="footer dark-background">...</footer>
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <div id="preloader"></div>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Hamburger Menu Logic
        const checkbox = document.getElementById('menu-toggle'); if(checkbox){checkbox.addEventListener('change', () => { document.body.classList.toggle('no-scroll', checkbox.checked); });}

        // --- START: MODAL JAVASCRIPT REPLICATED FROM ADMIN PAGE ---
        const modal = document.getElementById('modal');
        const modalTableBody = document.getElementById('modal_order_details_body');

        // Helper function to create a table row but only if the 'value' is not empty.
        function createDetailRow(label, value, quantity = '-') {
            if (value === null || value === undefined || String(value).trim() === '' || String(value).trim() === 'لا يوجد') {
                return ''; 
            }
            const displayValue = String(value);
            const displayQuantity = (quantity !== '-' && !isNaN(quantity) && Number(quantity) > 0) ? Number(quantity) : '-';
            return `<tr><td><strong>${label}</strong></td><td>${displayValue}</td><td style="text-align:center;">${displayQuantity}</td></tr>`;
        }

        // Helper to format date in a readable Arabic format
        function formatArabicDate(dateString) {
            if (!dateString) return '-';
            try {
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateString).toLocaleDateString('ar-EG-u-nu-latn', options);
            } catch (e) { return dateString; }
        }

        function openModal(order) {
            if (!modal || !modalTableBody) return;
            
            // Set modal title and delete button value
            document.getElementById('modal_display_order_id_header').innerText = `(#${order.order_id})`;
            document.getElementById('order_id_to_delete_input').value = order.order_id;
            
            modalTableBody.innerHTML = ''; // Clear previous details

            let html = '';
            html += createDetailRow('تاريخ الطلب', formatArabicDate(order.created_at));
            html += `<tr style="background-color: #f2f2f2;"><td colspan="3" style="text-align:center; font-weight:bold; padding: 5px;">تفاصيل المناسبة</td></tr>`;
            html += createDetailRow('نوع المناسبة', order.event_type);
            html += createDetailRow('تاريخ المناسبة', order.event_date);
            html += createDetailRow('موقع المناسبة', order.location);
            html += createDetailRow('وقت البدء', order.start_session);
            html += createDetailRow('وقت الانتهاء', order.end_session);
            
            html += `<tr style="background-color: #f2f2f2;"><td colspan="3" style="text-align:center; font-weight:bold; padding: 5px;">الخدمات المطلوبة</td></tr>`;
            html += createDetailRow('الوجبات', order.meals, order.meals_q);
            html += createDetailRow('المشروبات', order.drinks, order.drinks_q);
            html += createDetailRow('الحلويات', order.deserts, order.deserts_q);
            html += createDetailRow('الملابس', order.clothes, order.clothes_q);
            html += createDetailRow('الإكسسوارات', order.accessory, order.accessory_q);
            html += createDetailRow('التصوير', order.media, order.media_q);

            modalTableBody.innerHTML = html || '<tr><td colspan="3" style="text-align:center;">لا توجد تفاصيل إضافية لعرضها.</td></tr>';

            modal.style.display = "flex";
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeModal() { 
            if (modal) {
                modal.style.display = "none"; 
            }
            document.body.style.overflow = 'auto'; // Restore background scrolling
        }

        // Close modal if user clicks outside of it (on the semi-transparent overlay)
        window.onclick = function(event) { 
            if (event.target == modal) { 
                closeModal(); 
            } 
        }
        // --- END: MODAL JAVASCRIPT ---

        // Confirmation for Account Deletion
        function confirmDeleteAccount() {
            return confirm("هل أنت متأكد أنك تريد حذف حسابك؟ هذا الإجراء لا يمكن التراجع عنه وسيتم حذف جميع طلباتك وبياناتك.");
        }
    </script>
</body>
</html>