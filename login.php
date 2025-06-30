<?php
session_start();
require_once 'db.php';

$signup_success = $signup_error = $login_error = "";

// Handle Signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = $_POST['gender'] ?? null;
    $college = $_POST['faculty'] ?? null; // 'faculty' from the form maps to 'college' in the DB
    $password = $_POST['password'] ?? '';
    $image_path = null;

    if ($first_name && $last_name && $email && $password && $gender && $college && $phone) {
        // Image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = basename($_FILES['image']['name']);
            $destPath = $uploadDir . uniqid() . '_' . $fileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $image_path = $destPath;
            }
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password, gender, college, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$first_name, $last_name, $email, $phone, $hashed_password, $gender, $college, $image_path]);
            $signup_success = "تم التسجيل بنجاح!";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $signup_error = "البريد الإلكتروني مستخدم بالفعل.";
            } else {
                $signup_error = "خطأ في التسجيل: " . $e->getMessage();
            }
        }
    } else {
        $signup_error = "يرجى ملء جميع الحقول المطلوبة.";
    }
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // --- FIX #1: Changed $_POST['email2'] to $_POST['email'] ---
    $email = trim($_POST['email'] ?? '');
    // --- FIX #1: Changed $_POST['password2'] to $_POST['password'] ---
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin' || $user['role'] === 'admin_view') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $login_error = "بيانات الدخول غير صحيحة.";
        }
    } else {
        $login_error = "يرجى إدخال البريد الإلكتروني وكلمة المرور.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول / إنشاء حساب</title>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
        .msg { margin: 10px 0; padding: 10px; border-radius: 5px; text-align: center; }
        .success { background: #e0ffe0; color: #080; }
        .error { background: #ffe0e0; color: #900; }
    </style>
</head>
<body>
    <div class="main">  	
        <input type="checkbox" id="chk" aria-hidden="true">
        <div class="signup">
            <form method="POST" enctype="multipart/form-data" autocomplete="off">
                <label for="chk" aria-hidden="true">Sign up</label>
                <?php if($signup_success): ?>
                    <div class="msg success"><?= htmlspecialchars($signup_success) ?></div>
                <?php elseif($signup_error): ?>
                    <div class="msg error"><?= htmlspecialchars($signup_error) ?></div>
                <?php endif; ?>
                <div class="row">
                    <input type="text" name="first_name" placeholder="الاسم الاول" required>
                    <input type="text" name="last_name" placeholder="الاسم الاخير" required>
                </div>
                <div class="row">
                    <input type="email" name="email" placeholder="الايميل" required>
                    <input type="tel" name="phone" placeholder="رقم الهاتف" required>
                </div>
                <div class="row">
                    <select name="gender" required>
                        <option value="" disabled selected>النوع</option>
                        <option value="ذكر">ذكر</option>
                        <option value="أنثى">أنثى</option>
                    </select>
                    <select name="faculty" required>
                        <option value="" disabled selected>الكلية</option>
                        <option value="الذكاء الاصطناعي">كلية الذكاء الاصطناعي</option>
                        <option value="العلوم">كلية العلوم</option>
                        <option value="التجارة">كلية التجارة</option>
                        <option value="الاعلام">كلية الاعلام</option>
                        <option value="الاقتصاد المنزلي">كلية الاقتصاد المنزلي</option>
                        <option value="الهندسة">كلية الهندسة</option>
                        <option value="الهندسة الالكترونية">كلية الهندسة الالكترونية</option>
                        <option value="الطب">كلية الطب</option>
                        <option value="الطب البيطري">كلية الطب البيطري</option>
                        <option value="طب الاسنان">كلية طب الاسنان</option>
                        <option value="تكنولوجيا العلوم الطبية">كلية تكنولوجيا العلوم</option>
                    </select>
                </div>
                <div class="row1">
                    <input type="password" name="password" placeholder="الباسورد" required>
                </div>
                <div class="upload-container">
                    <!-- --- FIX #2: Added name="image" to the file input --- -->
                    <input type="file" id="photoInput" name="image" accept="image/*" required style="display: none;">
                    <label for="photoInput" id="uploadLabel" class="upload-button">أختر صورة</label>
                </div>
                <button type="submit" name="signup">Sign up</button>
            </form>
        </div>
        <div class="login">
            <form method="POST" autocomplete="off">
                <label for="chk" aria-hidden="true">Login</label>
                <?php if($login_error): ?>
                    <div class="msg error"><?= htmlspecialchars($login_error) ?></div>
                <?php endif; ?>
                <!-- NOTE: The input names here are 'email' and 'password' -->
                <div class="row">
                    <input type="email" name="email" placeholder="الايميل" required>
                </div>
                 <div class="row1">
                    <input type="password" name="password" placeholder="الباسورد" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('photoInput').addEventListener('change', function() {
            const label = document.getElementById('uploadLabel');
            if (this.files && this.files.length > 0) {
                label.textContent = 'الصورة هي: ' + this.files[0].name;
            } else {
                label.textContent = 'أختر صورة';
            }
        });
    </script>
</body>
</html>