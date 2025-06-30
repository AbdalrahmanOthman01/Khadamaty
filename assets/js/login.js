// استرجاع عناصر النموذج
var useremail = document.getElementById("email");
var userpassword = document.getElementById("password");
var useremail2 = document.getElementById("email2");
var userpassword2 = document.getElementById("password2");
var arr = JSON.parse(localStorage.getItem("info")) || []; // استرجاع البيانات من localStorage

document.addEventListener("DOMContentLoaded", function () {
    const accountButton = document.getElementById("accountButton");
    const loginBox = document.getElementById("loginBox");

    // Toggle login box visibility on account button click
    accountButton.addEventListener("click", function () {
        loginBox.classList.toggle("hidden");
    });

    // Function to simulate login process and close the login box
    function check() {
        const email = document.getElementById("email2").value;
        const password = document.getElementById("password2").value;

        if (email && password) {
            // Perform your authentication logic here
            alert("Login Successful!");

            // Hide the login box after successful login
            loginBox.classList.add("hidden");
        } else {
            alert("Please fill in both email and password.");
        }
    }

    // Attach the login function to the login button
    const loginButton = loginBox.querySelector(".login button");
    loginButton.addEventListener("click", function (e) {
        e.preventDefault(); // Prevent form submission
        check();
    });
});


function addinfo(event) {
    event.preventDefault(); // منع إعادة تحميل الصفحة
    var userinfo = {
        name: document.forms["myForm"]["name"].value, // استرجاع اسم المستخدم
        email: useremail.value,
        password: userpassword.value,
    };
    arr.push(userinfo);
    localStorage.setItem("info", JSON.stringify(arr)); // تخزين البيانات
    emptyfield(); // مسح الحقول بعد التسجيل
    alert("Account is add successfully!"); // إظهار رسالة تأكيد
}

function emptyfield() {
    useremail.value = "";
    userpassword.value = "";
    document.forms["myForm"]["name"].value = ""; // مسح حقل الاسم
}

function check(event) {
    event.preventDefault(); // منع إعادة تحميل الصفحة
    var check = {
        email: useremail2.value,
        password: userpassword2.value
    };

    // التحقق من البيانات المدخلة
    var user = arr.find(user => user.email === check.email && user.password === check.password);

    if (user) {
        // alert(" Account logged in successfully");
        saveUserData(user.email, user.name); // تأكد من تمرير اسم المستخدم هنا
        window.location.href = "index.html"; // الانتقال إلى صفحة التعريف
    } else {
        alert("Error,Wrong password or email");
    }
}
// إضافة مستمع حدث للنموذج لتسجيل الدخول
document.querySelector('.login form').addEventListener('submit', check); // تأكد من أن لديك هذا الرابط للنموذج الصحيح

// إضافة مستمع حدث للنموذج للتسجيل
document.querySelector('.signup form').addEventListener('submit', addinfo); // تأكد من أن لديك هذا الرابط للنموذج الصحيح

// وظيفة لتخزين البيانات في localStorage بعد تسجيل الدخول
function saveUserData(email, name) {
    localStorage.setItem('email', email);
    localStorage.setItem('name', name);
}
document.addEventListener("DOMContentLoaded", function () {
    const accountButton = document.getElementById("accountButton");
    const loginBox = document.getElementById("loginBox");
    const closeLoginBox = document.getElementById("closeLoginBox");

    // Toggle login box visibility on account button click
    accountButton.addEventListener("click", function () {
        loginBox.classList.toggle("hidden");
    });

    // Close the login box when the close button is clicked
    closeLoginBox.addEventListener("click", function () {
        loginBox.classList.add("hidden");
    });

    function check() {
        const email = document.getElementById("email2").value;
        const password = document.getElementById("password2").value;

        if (email && password) {
            alert("Login Successful!");
            loginBox.classList.add("hidden");
        } else {
            alert("Please fill in both email and password.");
        }
    }

    const loginButton = loginBox.querySelector(".login button");
    loginButton.addEventListener("click", function (e) {
        e.preventDefault();
        check();
    });
});
