(function () {
  "use strict";

  function initSlider(root) {
    const slides = Array.from(root.querySelectorAll("[data-slide]"));
    if (!slides.length) return;

    const btnPrev = root.querySelector("[data-prev]");
    const btnNext = root.querySelector("[data-next]");
    const btnToggle = root.querySelector("[data-toggle]");
    const iconPause = root.querySelector("[data-icon-pause]");
    const iconPlay = root.querySelector("[data-icon-play]");
    const counterEl = root.querySelector("[data-counter]");

    const interval = parseInt(root.getAttribute("data-interval"), 10) || 5000;
    const autoplay = (root.getAttribute("data-autoplay") || "true") === "true";

    // v2.4.0: Respect prefers-reduced-motion.
    // If the visitor has requested reduced motion at the OS/browser level,
    // we disable autoplay regardless of the module setting. The user can still
    // manually start autoplay by clicking the Play control.
    const prefersReducedMotion =
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    const pauseOnHover = (root.getAttribute("data-pause-on-hover") || "true") === "true";

    let index = slides.findIndex((s) => s.classList.contains("is-active"));
    if (index < 0) index = 0;

    let playing = autoplay;

    // v2.4.0: If reduced motion is preferred, start paused (show Play icon).
    if (typeof prefersReducedMotion !== "undefined" && prefersReducedMotion) {
      playing = false;
    }
    let timer = null;
    let wasPlayingBeforeHide = false;

    function updateCounter() {
      if (counterEl) counterEl.textContent = (index + 1) + "/" + slides.length;
    }

    // v2.4.0: Load full-size slide images only when needed.
    function ensureSlideLoaded(targetIndex) {
      const slide = slides[targetIndex];
      if (!slide) return;

      const img = slide.querySelector(".mc-photo-slider__img");
      if (!img) return;

      const alreadyLoaded = img.getAttribute("data-loaded") === "true";
      const fullSrc = img.getAttribute("data-full-src");

      if (alreadyLoaded || !fullSrc) return;

      img.setAttribute("src", fullSrc);
      img.setAttribute("data-loaded", "true");
    }

    function show(i) {
      slides[index].classList.remove("is-active");
      index = (i + slides.length) % slides.length;

      // v2.4.0: Load the target full-size image on demand before showing it.
      ensureSlideLoaded(index);

      slides[index].classList.add("is-active");
      updateCounter();
      if (hasThumbs) setActiveThumb(index, { scroll: true });
    }

    function next() { show(index + 1); }
    function prev() { show(index - 1); }

    function startAutoplay() {
      stopAutoplay();
      timer = window.setInterval(() => next(), interval);
    }

    function stopAutoplay() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    function syncToggleUI() {
      if (!iconPause || !iconPlay) return;

      // Use the hidden attribute (and CSS forces [hidden] to be display:none)
      if (playing) {
        iconPause.hidden = false;
        iconPlay.hidden = true;
      } else {
        iconPause.hidden = true;
        iconPlay.hidden = false;
      }

      if (btnToggle) {
        btnToggle.setAttribute("aria-pressed", playing ? "false" : "true");
        btnToggle.setAttribute("aria-label", playing ? "Pause" : "Play");
      }
    }

    function setPlaying(shouldPlay) {
      playing = shouldPlay;
      syncToggleUI();

      if (playing) startAutoplay();
      else stopAutoplay();
    }

    // Controls
    if (btnNext) btnNext.addEventListener("click", () => next());
    if (btnPrev) btnPrev.addEventListener("click", () => prev());
    if (btnToggle) btnToggle.addEventListener("click", () => setPlaying(!playing));

    // v2.4.0: Thumbnail rail support (optional)
    const hasThumbs = (root.getAttribute("data-has-thumbs") || "false") === "true";
    const thumbsTrack = root.querySelector("[data-thumbs]");
    const thumbButtons = thumbsTrack ? Array.from(thumbsTrack.querySelectorAll("[data-thumb]")) : [];

    function setActiveThumb(newIndex, opts) {
      if (!thumbButtons.length) return;

      thumbButtons.forEach((btn, i) => {
        if (i === newIndex) {
          btn.setAttribute("aria-current", "true");
          btn.tabIndex = 0;
        } else {
          btn.removeAttribute("aria-current");
          btn.tabIndex = -1;
        }
      });

      const activeBtn = thumbButtons[newIndex];
      if (!activeBtn) return;

      const prefersReducedMotionNow =
        window.matchMedia &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches;

      const behavior = prefersReducedMotionNow ? "auto" : "smooth";

      if (opts && opts.scroll) {
        try {
          activeBtn.scrollIntoView({ block: "nearest", inline: "nearest", behavior });
        } catch (e) {
          activeBtn.scrollIntoView();
        }
      }
    }

    if (hasThumbs && thumbButtons.length) {
      // Roving tabindex: only the active thumb is tabbable
      thumbButtons.forEach((btn, i) => { btn.tabIndex = (i === index) ? 0 : -1; });

      thumbButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
          const i = parseInt(btn.getAttribute("data-thumb-index"), 10);
          if (!Number.isFinite(i)) return;
          show(i);
        });

        // Keyboard: Left/Right/Home/End move; Enter/Space activates
        btn.addEventListener("keydown", (e) => {
          const key = e.key;
          const current = parseInt(btn.getAttribute("data-thumb-index"), 10);
          if (!Number.isFinite(current)) return;

          let nextIndex = null;

          if (key === "ArrowRight") nextIndex = (current + 1) % thumbButtons.length;
          else if (key === "ArrowLeft") nextIndex = (current - 1 + thumbButtons.length) % thumbButtons.length;
          else if (key === "Home") nextIndex = 0;
          else if (key === "End") nextIndex = thumbButtons.length - 1;
          else if (key === "Enter" || key === " " || key === "Spacebar") {
            e.preventDefault();
            e.stopPropagation();
            show(current);
            return;
          } else {
            return;
          }

          e.preventDefault();
          e.stopPropagation();
          const targetBtn = thumbButtons[nextIndex];
          if (targetBtn) {
            targetBtn.focus();
            // Activate on arrow movement (common gallery behavior)
            show(nextIndex);
          }
        });
      });

      // Initial state
      setActiveThumb(index, { scroll: false });
    }


    // Pause on hover (configurable)
    if (pauseOnHover) {
      root.addEventListener("mouseenter", () => stopAutoplay());
      root.addEventListener("mouseleave", () => { if (playing) startAutoplay(); });
    }

    // Keyboard support
    root.addEventListener("keydown", (e) => {
      // v2.4.0: Don't hijack Arrow keys when focus is inside thumbnail buttons.
      if (e.target && e.target.closest && (e.target.closest("[data-thumbs]") || e.target.closest("[data-thumb]"))) {
        return;
      }
      const key = e.key;
      if (key === "ArrowRight") { e.preventDefault(); next(); }
      else if (key === "ArrowLeft") { e.preventDefault(); prev(); }
      else if (key === " " || key === "Spacebar") { e.preventDefault(); setPlaying(!playing); }
    });

    // Swipe support
    let touchStartX = 0;
    let touchStartY = 0;
    let touchActive = false;

    root.addEventListener("touchstart", (e) => {
      if (!e.touches || e.touches.length !== 1) return;
      touchActive = true;
      touchStartX = e.touches[0].clientX;
      touchStartY = e.touches[0].clientY;
    }, { passive: true });

    root.addEventListener("touchend", (e) => {
      if (!touchActive) return;
      touchActive = false;

      const touch = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0] : null;
      if (!touch) return;

      const dx = touch.clientX - touchStartX;
      const dy = touch.clientY - touchStartY;

      if (Math.abs(dx) < 40) return;
      if (Math.abs(dy) > Math.abs(dx) * 0.75) return;

      if (dx < 0) next();
      else prev();
    }, { passive: true });

    
    // Pause autoplay when slider is off-screen (IntersectionObserver)
    let isInView = true;
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.target !== root) return;
          if (entry.isIntersecting) {
            isInView = true;
            if (playing) startAutoplay();
          } else {
            isInView = false;
            stopAutoplay();
          }
        });
      }, { threshold: 0.25 });

      observer.observe(root);
    }


    // Visibility pause
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        wasPlayingBeforeHide = playing;
        stopAutoplay();
      } else {
        if (wasPlayingBeforeHide && playing) startAutoplay();
      }
    });

    // Init
    ensureSlideLoaded(index);
    updateCounter();
    syncToggleUI();
    if (playing) startAutoplay();
  }

  function initAll() {
    document.querySelectorAll("[data-mc-photo-slider]").forEach(initSlider);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAll);
  } else {
    initAll();
  }
})();
