/**
* Template Name: Bootslander
* Template URL: https://bootstrapmade.com/bootslander-free-bootstrap-landing-page-template/
* Updated: Aug 07 2024 with Bootstrap v5.3.3
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/

(function() {
  "use strict";

  /**
   * Apply .scrolled class to the body as the page is scrolled down
   */
  function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;
    window.scrollY > 100 ? selectBody.classList.add('scrolled') : selectBody.classList.remove('scrolled');
  }

  document.addEventListener('scroll', toggleScrolled);
  window.addEventListener('load', toggleScrolled);


  /**
   * Toggle mobile nav dropdowns
   */
  document.querySelectorAll('.navmenu .toggle-dropdown').forEach(navmenu => {
    navmenu.addEventListener('click', function(e) {
      e.preventDefault();
      this.parentNode.classList.toggle('active');
      this.parentNode.nextElementSibling.classList.toggle('dropdown-active');
      e.stopImmediatePropagation();
    });
  });

  /**
   * Preloader
   */
  const preloader = document.querySelector('#preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.remove();
    });
  }

  /**
   * Scroll top button
   */
  let scrollTop = document.querySelector('.scroll-top');

  function toggleScrollTop() {
    if (scrollTop) {
      window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
    }
  }
  scrollTop.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  window.addEventListener('load', toggleScrollTop);
  document.addEventListener('scroll', toggleScrollTop);

  /**
   * Animation on scroll function and init
   */
  function aosInit() {
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });
  }
  window.addEventListener('load', aosInit);

  /**
   * Initiate glightbox
   */
  const glightbox = GLightbox({
    selector: '.glightbox'
  });

  /**
   * Initiate Pure Counter
   */
  new PureCounter();

  /**
   * Init swiper sliders
   */
  function initSwiper() {
    document.querySelectorAll(".init-swiper").forEach(function(swiperElement) {
      let config = JSON.parse(
        swiperElement.querySelector(".swiper-config").innerHTML.trim()
      );

      if (swiperElement.classList.contains("swiper-tab")) {
        initSwiperWithCustomPagination(swiperElement, config);
      } else {
        new Swiper(swiperElement, config);
      }
    });
  }

  window.addEventListener("load", initSwiper);

  /**
   * Frequently Asked Questions Toggle
   */
  document.querySelectorAll('.faq-item h3, .faq-item .faq-toggle').forEach((faqItem) => {
    faqItem.addEventListener('click', () => {
      faqItem.parentNode.classList.toggle('faq-active');
    });
  });

  /**
   * Correct scrolling position upon page load for URLs containing hash links.
   */
  window.addEventListener('load', function(e) {
    if (window.location.hash) {
      if (document.querySelector(window.location.hash)) {
        setTimeout(() => {
          let section = document.querySelector(window.location.hash);
          let scrollMarginTop = getComputedStyle(section).scrollMarginTop;
          window.scrollTo({
            top: section.offsetTop - parseInt(scrollMarginTop),
            behavior: 'smooth'
          });
        }, 100);
      }
    }
  });

  /**
   * Navmenu Scrollspy
   */
  let navmenulinks = document.querySelectorAll('.navmenu a');

  function navmenuScrollspy() {
    navmenulinks.forEach(navmenulink => {
      if (!navmenulink.hash) return;
      let section = document.querySelector(navmenulink.hash);
      if (!section) return;
      let position = window.scrollY + 200;
      if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
        document.querySelectorAll('.navmenu a.active').forEach(link => link.classList.remove('active'));
        navmenulink.classList.add('active');
      } else {
        navmenulink.classList.remove('active');
      }
    })
  }
  window.addEventListener('load', navmenuScrollspy);
  document.addEventListener('scroll', navmenuScrollspy);

})();

let selectedOptions = [];

function selectOption(element, value) {
  const siblings = element.parentElement.children;

  // إزالة التحديد من الإخوة (إن كان تكرارًا في قائمة متعددة)
  for (let sibling of siblings) {
      sibling.classList.remove('selected');
      sibling.classList.remove('hover');  // إزالة hover من العناصر الأخرى
  }

  // إضافة التحديد إلى العنصر المحدد
  element.classList.add('selected');
  
  // إضافة hover للأيقونة المحددة
  element.classList.add('hover');

  // إزالة hover بعد فترة قصيرة (لإعطاء تأثير hover)
  setTimeout(() => {
      element.classList.remove('hover');
  }, 300);  // يمكن تعديل 300 لتحديد الوقت الذي يبقى فيه التأثير
}



function selectSingleOption(element, value) {
    const options = document.querySelectorAll('.options-flex .icon');
    options.forEach(el => el.classList.remove('selected'));

    element.classList.add('selected');

    // هنا نحذف كل القيم القديمة ونضيف الحالية فقط
    selectedOptions = [value];

    console.log("Selected:", value);
}

function validateAndNextStep() {
  const currentStep = document.querySelector('.step.active');
  if (!currentStep) return;

  const nextStep = currentStep.nextElementSibling;
  if (nextStep) {
      // Remove active class from the current step
      currentStep.classList.remove('active');
      
      // Add fade-in effect to the next step and set it as active
      nextStep.classList.add('active');
      nextStep.classList.add('fade-in');

      // Remove fade-in effect after the animation ends
      nextStep.addEventListener('animationend', () => {
          nextStep.classList.remove('fade-in');
      });
  }
}



function previousStep() {
    const currentStep = document.querySelector('.step.active');
    if (!currentStep) return;

    const previousStep = currentStep.previousElementSibling;
    if (previousStep) {
        currentStep.classList.remove('active');
        previousStep.classList.add('active');
    }
}

function submitForm() {
    if (selectedOptions.length === 0) {
        alert("يرجى اختيار خيار واحد على الأقل قبل الإرسال.");
        return;
    }

    const logoPrint = document.getElementById('logoPrint')?.checked || document.getElementById('logoPrint2')?.checked;
    alert('تم إرسال الطلب!\nالخيارات المختارة: ' + selectedOptions.join(', ') + '\nطباعة الشعار: ' + (logoPrint ? 'نعم' : 'لا'));
}

function showSubmitButtonInLastStep() {
    const allSteps = document.querySelectorAll('.step');

    allSteps.forEach(step => {
        const submitBtn = step.querySelector('.submit-btn');
        if (submitBtn) {
            submitBtn.style.display = 'none';
        }
    });

    const lastStep = document.querySelector('.step:last-of-type');
    const lastSubmitBtn = lastStep?.querySelector('.submit-btn');
    if (lastSubmitBtn) {
        lastSubmitBtn.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', showSubmitButtonInLastStep);

// -------------------------------------
// BootstrapMade Template JS Enhancements
// -------------------------------------

(function () {
    "use strict";

    function toggleScrolled() {
        const selectBody = document.querySelector('body');
        const selectHeader = document.querySelector('#header');

        if (!selectHeader || (!selectHeader.classList.contains('scroll-up-sticky') &&
            !selectHeader.classList.contains('sticky-top') &&
            !selectHeader.classList.contains('fixed-top'))) return;

        window.scrollY > 100 ? selectBody.classList.add('scrolled') : selectBody.classList.remove('scrolled');
    }

    const preloader = document.querySelector('#preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            preloader.remove();
        });
    }

    const scrollTop = document.querySelector('.scroll-top');
    function toggleScrollTop() {
        if (scrollTop) {
            window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
        }
    }

    if (scrollTop) {
        scrollTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    window.addEventListener('load', toggleScrollTop);
    document.addEventListener('scroll', toggleScrollTop);

    function aosInit() {
        AOS.init({
            duration: 600,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
    }

    window.addEventListener('load', aosInit);

    const glightbox = GLightbox({
        selector: '.glightbox'
    });

    new PureCounter();

    function initSwiper() {
        document.querySelectorAll(".init-swiper").forEach(function (swiperElement) {
            let configElement = swiperElement.querySelector(".swiper-config");
            if (configElement) {
                try {
                    let config = JSON.parse(configElement.innerHTML.trim());
                    if (swiperElement.classList.contains("swiper-tab")) {
                        initSwiperWithCustomPagination(swiperElement, config);
                    } else {
                        new Swiper(swiperElement, config);
                    }
                } catch (e) {
                    console.error("فشل في قراءة إعدادات السلايدر", e);
                }
            }
        });
    }

    window.addEventListener("load", initSwiper);

    document.querySelectorAll('.faq-item h3, .faq-item .faq-toggle').forEach((faqItem) => {
        faqItem.addEventListener('click', () => {
            faqItem.parentNode.classList.toggle('faq-active');
        });
    });

    window.addEventListener('load', function () {
        if (window.location.hash) {
            let section = document.querySelector(window.location.hash);
            if (section) {
                setTimeout(() => {
                    let scrollMarginTop = getComputedStyle(section).scrollMarginTop;
                    window.scrollTo({
                        top: section.offsetTop - parseInt(scrollMarginTop),
                        behavior: 'smooth'
                    });
                }, 100);
            }
        }
    });

    const navmenulinks = document.querySelectorAll('.navmenu a');

    function navmenuScrollspy() {
        navmenulinks.forEach(navmenulink => {
            if (!navmenulink.hash) return;

            let section = document.querySelector(navmenulink.hash);
            if (!section) return;

            let position = window.scrollY + 200;
            if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
                document.querySelectorAll('.navmenu a.active').forEach(link => link.classList.remove('active'));
                navmenulink.classList.add('active');
            } else {
                navmenulink.classList.remove('active');
            }
        });
    }

    window.addEventListener('load', navmenuScrollspy);
    document.addEventListener('scroll', navmenuScrollspy);
})();


function redirectToHome() {
  window.location.href = '../../index.html';
}