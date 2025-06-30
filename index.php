<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>khadamaty</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Bootslander
  * Template URL: https://bootstrapmade.com/bootslander-free-bootstrap-landing-page-template/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top" data-aos="fade-in" data-aos-delay="300">
    <div class="header-div container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.php" class="logo d-flex align-items-center">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img src="assets/img/logo.png" alt=""> -->
        <img style="object-fit: cover;" src="img/Untitled-1.png" alt="">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a  style="font-family: 'MyCustomFont', sans-serif;" href="Order.php">الصـفـحة الشـخـصـيـة</a></li>
          <li><a  style="font-family: 'MyCustomFont', sans-serif;" href="Order.php">الطـلـبـات</a></li>
          <li><a  style="font-family: 'MyCustomFont', sans-serif;" href="menu.php">الخـدمــات</a></li>
          <li><a  style="font-family: 'MyCustomFont', sans-serif;" href="#about">عن المـنـصـة</a></li>
          <li><a  style="font-family: 'MyCustomFont', sans-serif;" href="index.php" class="active">الرئـيـسـيـة</a></li>
        </ul>
        <i class="mobile-nav-toggle"></i>
      </nav>

      <input type="checkbox" id="menu-toggle" hidden>

      <!-- زر الفتح -->
      <label for="menu-toggle" class="menu-icon hidden">
        <i class="fa-solid fa-bars"></i>
      </label>
      
      <!-- الخلفية المعتمة -->
      <div class="overlay"></div>
      
      <!-- القائمة -->
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

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">
      <img src="assets/img/hero-bg-2.jpg" alt="" class="hero-bg">

      <div class="container">
        <div class="row gy-4 justify-content-between">

          <div  class="col-lg-4 order-lg-last hero-img" data-aos="fade-left" data-aos-delay="300">
            <img src="img/22.png"  alt="">
          </div>

          <div class="col-lg-6  d-flex flex-column justify-content-center title" data-aos="fade-right" data-aos-delay="300">

            <img class="image" src="img/png.png" alt="">
            <p>مـقــدمـة مــن</p>
            <div class="container">

              <div class="row gy-4">
                <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="400">
                  <img class="circle" src="img/e3lam.png" alt="Tyler">
                </div>
                <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="400">
                  <img class="circle" src="img/ai.png" alt="Tyler">
                </div>
                <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="400">
                  <img class="circle" src="img/ektesad.png" alt="Tyler">
                </div>
              </div>

            </div>

          </div>

        </div>
      </div>

      <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28 " preserveAspectRatio="none">
        <defs>
          <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path>
        </defs>
        <g class="wave1">
          <use xlink:href="#wave-path" x="50" y="3"></use>
        </g>
        <g class="wave2">
          <use xlink:href="#wave-path" x="50" y="0"></use>
        </g>
        <g class="wave3">
          <use xlink:href="#wave-path" x="50" y="9"></use>
        </g>
      </svg>

    </section>
    <!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row align-items-xl-center gy-5">
          <div class="col-xl-7">
            <div class="row gy-4 icon-boxes">

              <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="icon-box">
                  <i class="fas fa-user-cog fa-3x"></i>
                  <h3>سهولة في الاستخدام</h3>
                  <p>تم تصميم المنصة لتكون سهلة الاستخدام وسريعة الوصول إلى الخدمات المطلوبة. كل شيء واضح ومنظم بشكل يضمن لك تجربة مريحة وسهلة أثناء التصفح</p>
                </div>
              </div> <!-- End Icon Box -->

              <div style="margin-bottom: 40px;" class="col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="icon-box">
                  <i class="fas fa-paint-brush fa-3x"></i>
                  <h3>أناقة في التصميم</h3>
                  <p>نحن نولي اهتمامًا خاصًا لجعل تصميم المنصة أنيقًا ومتناسقًا، بحيث يوفر لك تجربة بصرية مريحة. نحرص على أن تكون كل التفاصيل دقيقة وجميلة لتتناسب مع جميع أنواع الفعاليات</p>
                </div>
              </div> <!-- End Icon Box -->

              <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="icon-box">
                  <i class="fas fa-cogs fa-3x"></i>
                  <h3>احترافية في التنفيذ</h3>
                  <p>نحرص على تقديم خدمات عالية الجودة وتنفيذ كل جزء من الفعالية بإتقان. فرقنا المتخصصة تتعامل مع كل مهمة بحرفية لضمان أن تكون الفعالية أكثر من مجرد حدث، بل تجربة استثنائية</p>
                </div>
              </div> <!-- End Icon Box -->

              <div class="col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="icon-box">
                  <i class="fas fa-concierge-bell fa-3x"></i>
                  <h3>تنوع الخدمات</h3>
                  <p>سواء كنت تحتاج إلى خدمات الطعام، الملابس، الإكسسوارات، التصوير أو الدعاية، ستجد كل ما تحتاجه هنا في مكان واحد. المنصة تقدم لك مجموعة شاملة من الخدمات التي تلبي جميع احتياجاتك بأعلى معايير الجودة</p>
                </div>
              </div> <!-- End Icon Box -->

            </div>
          </div>
          <div style="direction: rtl;text-align: right; padding: 0px 20px;" class="col-xl-5 content">
            <h3 >عن المـنـصـة</h3>
            <h2>مـنــصــة خــدمــاتــي</h2>
            <p style="direction: rtl;text-align: right;">هو رفيقك الأول لتنظيم كل أنواع الفعاليات! سواء كنت بتجهز لحفلة تخرج، مؤتمر، أو حتى ملتقى علمي – تقدر تلاقي كل الخدمات اللي تحتاجها في مكان واحد: من الطعام والملابس، للإكسسوارات والتصوير والدعاية. سهولة في الاستخدام، أناقة في التصميم، واحترافية في التنفيذ..</p>
            <a href="#" class="read-more"><span>اقرأ المـزيــد </span><i class="bi bi-arrow-left"></i></a>
          </div>

        </div>
      </div>

    </section>
    <!-- /About Section -->

    <!-- Features Section -->
    <section id="features" class="features section">

      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5">
            <h2>الخــدمــات الي نــقــدمها</h2>
          </div>
        </div>
    
        <div class="row gy-4 mb-5">
    
          <!-- Feature 1: زمان و مكان -->
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="features-item">
              <i class="bi bi-clock" style="color: #ffbb2c;"></i>
              <h3><a href="" class="stretched-link">زمان و مكان</a></h3>
            </div>
          </div><!-- End Feature Item -->
    
          <!-- Feature 2: طعام -->
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="features-item">
              <i class="bi bi-cup-straw" style="color: #5578ff;"></i>
              <h3><a href="" class="stretched-link">طعام</a></h3>
            </div>
          </div><!-- End Feature Item -->
    
          <!-- Feature 3: ملابس -->
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="features-item">
              <i class="bi bi-person-fill" style="color: #e80368;"></i>
              <h3><a href="" class="stretched-link">ملابس</a></h3>
            </div>
          </div><!-- End Feature Item -->
    
          <!-- Feature 4: أكسيسوار -->
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
            <div class="features-item">
              <i class="bi bi-gem" style="color: #e361ff;"></i>
              <h3><a href="" class="stretched-link">أكسيسوار</a></h3>
            </div>
          </div><!-- End Feature Item -->
    
          <!-- Feature 5: دعايا و إعلان -->
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
            <div class="features-item">
              <i class="bi bi-megaphone" style="color: #47aeff;"></i>
              <h3><a href="" class="stretched-link">دعايا و إعلان</a></h3>
            </div>
          </div><!-- End Feature Item -->
    
          <!-- Feature 6: تصوير و مونتاج -->
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
            <div class="features-item">
              <i class="bi bi-camera-video" style="color: #ffa76e;"></i>
              <h3><a href="" class="stretched-link">تصوير و مونتاج</a></h3>
            </div>
          </div><!-- End Feature Item -->
    
        </div>
    
      </div>
    
    </section>
    <!-- /Features Section -->
    
    <div class="wrapper">
      <a class="cta" href="menu.php">
        <span>ابدأ رحلتك</span>
        <span>
          <svg width="66px" height="43px" viewBox="0 0 66 43" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <g id="arrow" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
              <path class="one" d="M40.1543933,3.89485454 L43.9763149,0.139296592 C44.1708311,-0.0518420739 44.4826329,-0.0518571125 44.6771675,0.139262789 L65.6916134,20.7848311 C66.0855801,21.1718824 66.0911863,21.8050225 65.704135,22.1989893 C65.7000188,22.2031791 65.6958657,22.2073326 65.6916762,22.2114492 L44.677098,42.8607841 C44.4825957,43.0519059 44.1708242,43.0519358 43.9762853,42.8608513 L40.1545186,39.1069479 C39.9575152,38.9134427 39.9546793,38.5968729 40.1481845,38.3998695 C40.1502893,38.3977268 40.1524132,38.395603 40.1545562,38.3934985 L56.9937789,21.8567812 C57.1908028,21.6632968 57.193672,21.3467273 57.0001876,21.1497035 C56.9980647,21.1475418 56.9959223,21.1453995 56.9937605,21.1432767 L40.1545208,4.60825197 C39.9574869,4.41477773 39.9546013,4.09820839 40.1480756,3.90117456 C40.1501626,3.89904911 40.1522686,3.89694235 40.1543933,3.89485454 Z" fill="#FFFFFF"></path>
              <path class="two" d="M20.1543933,3.89485454 L23.9763149,0.139296592 C24.1708311,-0.0518420739 24.4826329,-0.0518571125 24.6771675,0.139262789 L45.6916134,20.7848311 C46.0855801,21.1718824 46.0911863,21.8050225 45.704135,22.1989893 C45.7000188,22.2031791 45.6958657,22.2073326 45.6916762,22.2114492 L24.677098,42.8607841 C24.4825957,43.0519059 24.1708242,43.0519358 23.9762853,42.8608513 L20.1545186,39.1069479 C19.9575152,38.9134427 19.9546793,38.5968729 20.1481845,38.3998695 C20.1502893,38.3977268 20.1524132,38.395603 20.1545562,38.3934985 L36.9937789,21.8567812 C37.1908028,21.6632968 37.193672,21.3467273 37.0001876,21.1497035 C36.9980647,21.1475418 36.9959223,21.1453995 36.9937605,21.1432767 L20.1545208,4.60825197 C19.9574869,4.41477773 19.9546013,4.09820839 20.1480756,3.90117456 C20.1501626,3.89904911 20.1522686,3.89694235 20.1543933,3.89485454 Z" fill="#FFFFFF"></path>
              <path class="three" d="M0.154393339,3.89485454 L3.97631488,0.139296592 C4.17083111,-0.0518420739 4.48263286,-0.0518571125 4.67716753,0.139262789 L25.6916134,20.7848311 C26.0855801,21.1718824 26.0911863,21.8050225 25.704135,22.1989893 C25.7000188,22.2031791 25.6958657,22.2073326 25.6916762,22.2114492 L4.67709797,42.8607841 C4.48259567,43.0519059 4.17082418,43.0519358 3.97628526,42.8608513 L0.154518591,39.1069479 C-0.0424848215,38.9134427 -0.0453206733,38.5968729 0.148184538,38.3998695 C0.150289256,38.3977268 0.152413239,38.395603 0.154556228,38.3934985 L16.9937789,21.8567812 C17.1908028,21.6632968 17.193672,21.3467273 17.0001876,21.1497035 C16.9980647,21.1475418 16.9959223,21.1453995 16.9937605,21.1432767 L0.15452076,4.60825197 C-0.0425130651,4.41477773 -0.0453986756,4.09820839 0.148075568,3.90117456 C0.150162624,3.89904911 0.152268631,3.89694235 0.154393339,3.89485454 Z" fill="#FFFFFF"></path>
            </g>
          </svg>
        </span> 
      </a>
    </div>

  </main>

  <footer id="footer" class="footer dark-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.php" class="logo d-flex align-items-center">
            <img src="img/png.png" alt="">
          </a>
          <div class="footer-contact pt-3">
            
          </div>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>وصـول سـريـع</h4>
          <ul>
          <li><a  style="font-family: 'MyCustomFont', sans-serif;" href="#hero" class="active">الرئـيـسـيـة</a></li>
          <li><a  style="font-family: 'MyCustomFont', sans-serif;" href="#about">عن المـنـصـة</a></li>
          <li><a  style="font-family: 'MyCustomFont', sans-serif;" href="#team">الخـدمــات</a></li>
          <li><a  style="font-family: 'MyCustomFont', sans-serif;" href="#gallery">الطـلـبـات</a></li>
          <li><a  style="font-family: 'MyCustomFont', sans-serif;" href="#features">الصـفـحة الشـخـصـيـة</a></li>
          </ul>
        </div>


        <div class="col-lg-4 col-md-12 footer-newsletter">
          <h4>Our Newsletter</h4>
          <p>Subscribe to our newsletter and receive the latest news about our products and services!</p>
          <form action="forms/newsletter.php" method="post" class="php-email-form">
            <div class="newsletter-form"><input type="email" name="email"><input type="submit" value="Subscribe"></div>
            <div class="loading">Loading</div>
            <div class="error-message"></div>
            <div class="sent-message">Your subscription request has been sent. Thank you!</div>
          </form>
        </div>

      </div>
    </div>
    <div style="text align : center;" class="copyright">
        <p>&copy; 2025 YourName. All rights reserved.</p>
    </div>
    

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <script>
    const checkbox = document.getElementById('menu-toggle');
    const links = document.querySelectorAll('.menu a');
    const overlay = document.querySelector('.overlay');
  
    function closeMenu() {
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
      link.addEventListener('click', closeMenu);
    });
  
    overlay.addEventListener('click', closeMenu);
  </script>
  

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>