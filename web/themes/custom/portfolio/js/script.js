(function () {
  const openBtn = document.getElementById('open-resume');
  const closeBtn = document.getElementById('close-resume');
  const modal = document.getElementById('pdfModal');

  if (!openBtn || !modal) return;

  function openModal() {
    modal.hidden = false;
    // trap focus if you want — minimal example:
    closeBtn?.focus();
  }

  function closeModal() {
    modal.hidden = true;
    openBtn.focus();
  }

  openBtn.addEventListener('click', openModal);
  closeBtn?.addEventListener('click', closeModal);

  // close when clicking the backdrop
  modal.addEventListener('click', function (e) {
    if (e.target.dataset && e.target.dataset.close === 'true') {
      closeModal();
    }
  });

  // close on ESC
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) {
      closeModal();
    }
  });
})();

(function(){
  // Mobile nav toggle
  var toggle = document.getElementById('nav-toggle');
  var nav = document.getElementById('primary-nav');
  if(toggle && nav){
    toggle.addEventListener('click', function(){
      document.body.classList.toggle('nav-open');
      nav.classList.toggle('open');
    });
  }

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(function(anchor){
    anchor.addEventListener('click', function(e){
      var target = document.querySelector(this.getAttribute('href'));
      if(target){
        e.preventDefault();
        var offset = 130; // space after scroll
        var targetY = target.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top: targetY, behavior: 'smooth' });

        // close mobile nav
        if(nav.classList.contains('open')) {
          nav.classList.remove('open');
          document.body.classList.remove('nav-open');
        }
      }
    });
  });

  // Simple reveal on scroll
  var elems = document.querySelectorAll('.section, .skill-card, .project-item, .testi-card, .exp-card');
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if(entry.isIntersecting) entry.target.classList.add('inview');
    });
  }, {threshold: 0.12});
  elems.forEach(function(el){ obs.observe(el); });

  // Header sticky on scroll
  document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('.site-header');
    const stickyScrollPoint = 150; // scroll distance in pixels

    window.addEventListener('scroll', function() {
      if (window.scrollY > stickyScrollPoint) {
        header.classList.add('sticky');
      } else {
        header.classList.remove('sticky');
      }
    });
  });


})();
