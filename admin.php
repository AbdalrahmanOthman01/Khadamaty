<?php
session_start();

// Part 1: API Logic (Executes only for AJAX requests)
// -----------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] == 'get_details') {
    error_reporting(0);
    ini_set('display_errors', 0);
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized Access'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    require_once 'db.php';
    $conn = $pdo;

    if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No Order ID provided.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $order_id = $_GET['order_id'];

    try {
        $stmt = $conn->prepare("
            SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as client_name
            FROM orders o JOIN users u ON o.user_id = u.id
            WHERE o.order_id = :order_id
        ");
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_STR);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $order], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Order not found.'], JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $e) {
        error_log("API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database Query Failed.'], JSON_UNESCAPED_UNICODE);
    }
    exit();
}


// Part 2: Standard Page Logic (Executes for normal page visits)
// -----------------------------------------------------------------------------

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
$conn = $pdo;
$feedback_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id_to_update'])) {
    $order_id_to_update = $_POST['order_id_to_update'];
    $new_order_status_for_orders_table = "";
    $new_order_status_for_status_table = "";

    if (isset($_POST['accept_order'])) {
        $new_order_status_for_orders_table = "approved";
        $new_order_status_for_status_table = "approved";
    } elseif (isset($_POST['reject_order'])) {
        $new_order_status_for_orders_table = "rejected";
        $new_order_status_for_status_table = "declined";
    }

    if (!empty($new_order_status_for_orders_table)) {
        $conn->beginTransaction();
        try {
            $stmt_orders = $conn->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
            $stmt_orders->bindParam(':status', $new_order_status_for_orders_table, PDO::PARAM_STR);
            $stmt_orders->bindParam(':order_id', $order_id_to_update, PDO::PARAM_STR);
            $stmt_orders->execute();

            $stmt_status = $conn->prepare("INSERT INTO status (order_id, order_status) VALUES (:order_id, :status)");
            $stmt_status->bindParam(':order_id', $order_id_to_update, PDO::PARAM_STR);
            $stmt_status->bindParam(':status', $new_order_status_for_status_table, PDO::PARAM_STR);
            $stmt_status->execute();
            
            $conn->commit();
            $feedback_message = "<p style='color:green; text-align:center; padding:10px; background-color:#e6ffe6; border:1px solid green;'>تم تحديث حالة الطلب بنجاح!</p>";
        } catch (PDOException $e) {
            $conn->rollBack();
            $feedback_message = "<p style='color:red; text-align:center; padding:10px; background-color:#ffe6e6; border:1px solid red;'>خطأ: " . $e->getMessage() . "</p>";
        }
    }
}


// --- Fetch Data for Dashboard Display ---
$current_admin_id = $_SESSION['user_id'];
$admin_profile = null;
try {
    $stmt_prof = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as admin_name, first_name, last_name, email, phone, image FROM users WHERE id = :id AND role = 'admin'");
    $stmt_prof->bindParam(':id', $current_admin_id);
    $stmt_prof->execute();
    $admin_profile = $stmt_prof->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Admin Profile Fetch Error: " . $e->getMessage());
}

// Fetch data for ALL 8 statistics cards
$stats = [
    'total_orders' => 0, 'pending_orders' => 0, 'approved_orders' => 0, 'rejected_orders' => 0,
    'total_clients' => 0, 'total_admins' => 0, 'new_orders_month' => 0, 'new_clients_month' => 0
];
try {
    // 1. Orders stats
    $stmt_orders = $conn->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END) as new_month
        FROM orders
    ");
    $stmt_orders->execute();
    $order_stats = $stmt_orders->fetch(PDO::FETCH_ASSOC);
    if($order_stats){
        $stats['total_orders'] = $order_stats['total'] ?? 0;
        $stats['pending_orders'] = $order_stats['pending'] ?? 0;
        $stats['approved_orders'] = $order_stats['approved'] ?? 0;
        $stats['rejected_orders'] = $order_stats['rejected'] ?? 0;
        $stats['new_orders_month'] = $order_stats['new_month'] ?? 0;
    }

    // 2. Clients (users) stats
    $stmt_clients = $conn->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END) as new_month
        FROM users WHERE role = 'user'
    ");
    $stmt_clients->execute();
    $client_stats = $stmt_clients->fetch(PDO::FETCH_ASSOC);
     if($client_stats){
        $stats['total_clients'] = $client_stats['total'] ?? 0;
        $stats['new_clients_month'] = $client_stats['new_month'] ?? 0;
    }

    // 3. Admins stats
    $stmt_admins = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $stmt_admins->execute();
    $admin_stats = $stmt_admins->fetch(PDO::FETCH_ASSOC);
    if($admin_stats){
        $stats['total_admins'] = $admin_stats['total'] ?? 0;
    }

} catch (PDOException $e) {
    error_log("Admin Stats Fetch Error: " . $e->getMessage());
}

// Fetch order list for the table
$orders_list = [];
try {
    $stmt_list = $conn->prepare("SELECT o.order_id, o.user_id, u.first_name as client_first_name, u.last_name as client_last_name, DATE_FORMAT(o.created_at, '%d %b %Y') as formatted_order_date, o.status FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
    $stmt_list->execute();
    $orders_list = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Orders List Fetch Error: " . $e->getMessage());
}

function getOrderStatusClass($status) { switch ($status) { case 'pending': return 'label btm-shape bg-orange c-white'; case 'approved': return 'label btm-shape bg-green c-white'; case 'rejected': return 'label btm-shape bg-red c-white'; default: return 'label btm-shape bg-grey c-white'; } }
function translateOrderStatus($status) { switch ($status) { case 'pending': return 'قيد الانتظار'; case 'approved': return 'مقبول'; case 'rejected': return 'مرفوض'; default: return htmlspecialchars($status); } }
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Khadamaty - Admin</title>
    <!-- All original CSS links -->
    <link rel="stylesheet" href="assets/css/framework.css">
    <link href="./assets/img/favicon.png" rel="icon">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="assets/css/order1.css" rel="stylesheet">
</head>
<body class="index-page">
    <header dir="ltr" id="header" class="header d-flex align-items-center fixed-top">
        <div class="header-div container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
            <a href="admin.php" class="logo d-flex align-items-center"><img style="object-fit: cover;" src="img/Untitled-1.png" alt="شعار خدماتي"></a>
            <nav id="navmenu" class="navmenu">
                <ul>
                <li><a style="font-family: 'MyCustomFont', sans-serif; color: #ffc107;" href="logout.php">تسجيل الخروج</a></li>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="#Profile" class="active">الصـفـحة الشـخـصـيـة</a></li>
                <li><a style="font-family: 'MyCustomFont', sans-serif;" href="#Order" class="active">الطـلـبـات</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <section id="hero" class="hero section dark-background"><img src="assets/img/hero-bg-2.jpg" alt="" class="hero-bg"></section>
        <?php if (!empty($feedback_message)) echo $feedback_message; ?>
        <div id="Profile" class="page d-flex">
            <div class="content w-full">
                <h1 class="p-relative">لوحة تحكم المشرف</h1>
                <?php if ($admin_profile): ?>
                <div class="profile-page m-20 d-flex">
                    <div class="overview bg-white d-flex align-center rad-10 m-20 p-12">
                        <div class="avatar-box txt-c p-20">
                            <img class="rad-half mb-10" 
                                 src="<?php echo !empty($admin_profile['image']) ? htmlspecialchars($admin_profile['image']) : 'assets/img/avatar.png'; ?>" 
                                 alt="صورة المستخدم" 
                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #eee;">
                            <h3 class="m-0"><?php echo htmlspecialchars($admin_profile['admin_name']); ?></h3>
                        </div>
                        <div class="info-details p-20">
                           <p><strong>الاسم الأول:</strong> <?php echo htmlspecialchars($admin_profile['first_name']); ?></p>
                           <p><strong>الاسم الأخير:</strong> <?php echo htmlspecialchars($admin_profile['last_name']); ?></p>
                           <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($admin_profile['email']); ?></p>
                           <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($admin_profile['phone'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Cards section as requested -->
                <div class="wrapper d-grid gap-20 m-20">
                    <div class="stat-card p-20 bg-white rad-10"><i class="fa-solid fa-cart-shopping c-blue bg-blue-light icon-shape"></i><span class="d-block fw-bold fs-25 mt-10 mb-5"><?php echo $stats['total_orders']; ?></span><span class="c-grey fs-14">إجمالي الطلبات</span></div>
                    <div class="stat-card p-20 bg-white rad-10"><i class="fa-solid fa-clock c-orange bg-orange-light icon-shape"></i><span class="d-block fw-bold fs-25 mt-10 mb-5"><?php echo $stats['pending_orders']; ?></span><span class="c-grey fs-14">الطلبات المعلقة</span></div>
                    <div class="stat-card p-20 bg-white rad-10"><i class="fa-solid fa-circle-check c-green bg-green-light icon-shape"></i><span class="d-block fw-bold fs-25 mt-10 mb-5"><?php echo $stats['approved_orders']; ?></span><span class="c-grey fs-14">الطلبات المقبولة</span></div>
                    <div class="stat-card p-20 bg-white rad-10"><i class="fa-solid fa-circle-xmark c-red bg-red-light icon-shape"></i><span class="d-block fw-bold fs-25 mt-10 mb-5"><?php echo $stats['rejected_orders']; ?></span><span class="c-grey fs-14">الطلبات المرفوضة</span></div>
                    <div class="stat-card p-20 bg-white rad-10"><i class="fa-solid fa-users c-blue bg-blue-light icon-shape"></i><span class="d-block fw-bold fs-25 mt-10 mb-5"><?php echo $stats['total_clients']; ?></span><span class="c-grey fs-14">إجمالي العملاء</span></div>
                    <div class="stat-card p-20 bg-white rad-10"><i class="fa-solid fa-user-shield c-purple bg-purple-light icon-shape"></i><span class="d-block fw-bold fs-25 mt-10 mb-5"><?php echo $stats['total_admins']; ?></span><span class="c-grey fs-14">عدد المشرفين</span></div>
                    <div class="stat-card p-20 bg-white rad-10"><i class="fa-solid fa-calendar-plus c-orange bg-orange-light icon-shape"></i><span class="d-block fw-bold fs-25 mt-10 mb-5"><?php echo $stats['new_orders_month']; ?></span><span class="c-grey fs-14">طلبات هذا الشهر</span></div>
                    <div class="stat-card p-20 bg-white rad-10"><i class="fa-solid fa-user-plus c-red bg-red-light icon-shape"></i><span class="d-block fw-bold fs-25 mt-10 mb-5"><?php echo $stats['new_clients_month']; ?></span><span class="c-grey fs-14">عملاء جدد هذا الشهر</span></div>
                </div>

                <h1 id="Order" style="margin-top: 100px;" class="p-relative">الطـلـبـات</h1>
                <div style="margin-top: 80px;" class="projects p-20 bg-white rad-10 m-20">
                    <div class="responsive-table">
                        <table class="order fs-15 w-full">
                            <thead><tr><td>كود العميل</td><td>اسم العميل</td><td>كود الطلب</td><td>تاريخ الطلب</td><td>تفاصيل</td><td>الحالة</td></tr></thead>
                            <tbody>
                                <?php if (empty($orders_list)): ?>
                                    <tr><td colspan="6" style="text-align: center;">لا توجد طلبات لعرضها حالياً.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($orders_list as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['client_first_name'] . ' ' . $order['client_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['formatted_order_date']); ?></td>
                                        <td><span class="eye-icon" onclick="openModal('<?php echo htmlspecialchars($order['order_id']); ?>')"><i class="fa-solid fa-eye"></i></span></td>
                                        <td><span class="<?php echo getOrderStatusClass($order['status']); ?>"><?php echo translateOrderStatus($order['status']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-overlay" id="modal">
                        <div class="modal-content" style="direction: rtl; display: flex; flex-direction: column; max-height: 80vh;">
                            <h2 style="text-align: center; position: sticky; top: 0; background: white; padding: 10px 0; z-index: 1;">تفاصيل الطلب <span id="modal_display_order_id_header"></span></h2>
                            <button id="closeBtn" aria-label="إغلاق" onclick="closeModal()" style="position: absolute; top: 0; right: 20px; background: transparent; border: none; font-size: 52px; cursor: pointer;">×</button>
                            <div style="flex: 1 1 auto; overflow-y: auto;">
                                <div class="responsive-table p-20"><table class="fs-15 w-full tafaseel" style="width: 100%; text-align: right;"><thead><tr><th style="width:30%;">الصنف</th><th>المنتج المختار</th><th style="width:15%;">الكمية</th></tr></thead><tbody id="modal_order_details_body"></tbody></table></div>
                            </div>
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>#Order">
                                <input type="hidden" name="order_id_to_update" id="modal_order_id" value="">
                                <div style="padding: 20px 10px 10px; text-align: center; border-top: 1px solid #eee;">
                                    <button type="submit" name="reject_order" style="margin-right: 16px; background-color: #f44336; color: white; padding: 10px 45px; border-radius: 21px; font-size: 20px; cursor:pointer;">رفض</button>
                                    <button type="submit" name="accept_order" class="bg-green" style="color: white; padding: 10px 45px; border-radius: 21px; font-size: 20px; cursor:pointer;">قبول</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <div class="m-20 p-20 bg-white rad-10"><p style="text-align:center; color:red;">لم يتم العثور على بيانات المشرف.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer id="footer"></footer>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        const modal = document.getElementById('modal');
        const modalTableBody = document.getElementById('modal_order_details_body');
        function createDetailRow(label, value, quantity = '-') {
            if (value === null || value === undefined || String(value).trim() === '') { return ''; }
            const displayValue = String(value);
            const displayQuantity = (quantity !== '-' && !isNaN(quantity) && Number(quantity) > 0) ? Number(quantity) : '-';
            return `<tr><td><strong>${label}</strong></td><td>${displayValue}</td><td style="text-align:center;">${displayQuantity}</td></tr>`;
        }
        function formatArabicDate(dateString) {
            if (!dateString) return '-';
            try { const options = { year: 'numeric', month: 'long', day: 'numeric' }; return new Date(dateString).toLocaleDateString('ar-EG-u-nu-latn', options); } catch (e) { return dateString; }
        }
        async function openModal(orderId) {
            if (modal) modal.style.display = 'flex';
            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('modal_display_order_id_header').innerText = `(#${orderId})`;
            modalTableBody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 20px;">جاري تحميل التفاصيل...</td></tr>';
            try {
                const response = await fetch(`admin.php?action=get_details&order_id=${orderId}`);
                if (!response.ok) { const errorData = await response.json().catch(() => ({ message: 'Could not parse error from server.' })); throw new Error(errorData.message || `HTTP error! Status: ${response.status}`); }
                const result = await response.json();
                if (result.success) {
                    const details = result.data;
                    let html = '';
                    html += createDetailRow('اسم العميل', details.client_name);
                    html += createDetailRow('تاريخ الطلب', formatArabicDate(details.created_at));
                    html += `<tr style="background-color: #f2f2f2;"><td colspan="3" style="text-align:center; font-weight:bold; padding: 5px;">تفاصيل المناسبة</td></tr>`;
                    html += createDetailRow('نوع المناسبة', details.event_type);
                    html += createDetailRow('تاريخ المناسبة', details.event_date);
                    html += createDetailRow('موقع المناسبة', details.location);
                    html += createDetailRow('وقت البدء', details.start_session);
                    html += createDetailRow('وقت الانتهاء', details.end_session);
                    html += `<tr style="background-color: #f2f2f2;"><td colspan="3" style="text-align:center; font-weight:bold; padding: 5px;">الخدمات المطلوبة</td></tr>`;
                    html += createDetailRow('الوجبات', details.meals, details.meals_q);
                    html += createDetailRow('المشروبات', details.drinks, details.drinks_q);
                    html += createDetailRow('الحلويات', details.deserts, details.deserts_q);
                    html += createDetailRow('الملابس', details.clothes, details.clothes_q);
                    html += createDetailRow('الإكسسوارات', details.accessory, details.accessory_q);
                    html += createDetailRow('التصوير', details.media, details.media_q);
                    if(details.total_cost && parseFloat(details.total_cost) > 0){
                       html += `<tr style="background-color: #e6f7ff;"><td colspan="2" style="font-weight:bold;"><strong>التكلفة الإجمالية</strong></td><td style="text-align:center; font-weight:bold;"><strong>${parseFloat(details.total_cost).toFixed(2)} ريال</strong></td></tr>`;
                    }
                    modalTableBody.innerHTML = html || '<tr><td colspan="3" style="text-align:center;">لا توجد تفاصيل لعرضها.</td></tr>';
                } else {
                    modalTableBody.innerHTML = `<tr><td colspan="3" style="text-align:center; color:red; padding: 20px;">${result.message}</td></tr>`;
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                modalTableBody.innerHTML = `<tr><td colspan="3" style="text-align:center; color:red; padding: 20px;">حدث خطأ أثناء جلب البيانات: ${error.message}</td></tr>`;
            }
        }
        function closeModal() { if (modal) modal.style.display = 'none'; }
        window.onclick = function(event) { if (event.target == modal) { closeModal(); } }
    </script>
</body>
</html>