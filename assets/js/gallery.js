document.addEventListener('DOMContentLoaded', function () {
  if (window.__galleryInitialized) return;
  window.__galleryInitialized = true;

  const modal = document.getElementById('image-modal');
  if (!modal) return;
  const modalImg = modal.querySelector('.modal-image img');
  const prevBtn = modal.querySelector('.modal-prev');
  const nextBtn = modal.querySelector('.modal-next');
  const closeBtn = modal.querySelector('.modal-close');

  let currentGallery = null;

  function openModalForGallery(images, index, galleryEl) {
    if (!images || !images.length) return;
    currentGallery = { images: images.slice(), index: Number(index) || 0, galleryEl };
    if (currentGallery.index < 0) currentGallery.index = 0;
    if (currentGallery.index >= currentGallery.images.length) currentGallery.index = currentGallery.images.length - 1;
    modalImg.src = currentGallery.images[currentGallery.index];
    modalImg.alt = '';
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    if (currentGallery.galleryEl) {
      const counter = currentGallery.galleryEl.querySelector('.type-gallery-counter');
      if (counter) counter.textContent = (currentGallery.index + 1) + '/' + currentGallery.images.length;
    }
  }

  function closeModal() {
    // reflect current image back into gallery main (if available)
    if (currentGallery && currentGallery.galleryEl) {
      const mainEl = currentGallery.galleryEl.querySelector('.type-gallery-main img');
      if (mainEl) mainEl.src = currentGallery.images[currentGallery.index];
      const counter = currentGallery.galleryEl.querySelector('.type-gallery-counter');
      if (counter) counter.textContent = (currentGallery.index + 1) + '/' + currentGallery.images.length;
      currentGallery.galleryEl.dataset.currentIndex = currentGallery.index;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    currentGallery = null;
  }

  function next() {
    if (!currentGallery) return;
    currentGallery.index = (currentGallery.index + 1) % currentGallery.images.length;
    modalImg.src = currentGallery.images[currentGallery.index];
    if (currentGallery.galleryEl) {
      const counter = currentGallery.galleryEl.querySelector('.type-gallery-counter');
      if (counter) counter.textContent = (currentGallery.index + 1) + '/' + currentGallery.images.length;
    }
  }

  function prev() {
    if (!currentGallery) return;
    currentGallery.index = (currentGallery.index - 1 + currentGallery.images.length) % currentGallery.images.length;
    modalImg.src = currentGallery.images[currentGallery.index];
    if (currentGallery.galleryEl) {
      const counter = currentGallery.galleryEl.querySelector('.type-gallery-counter');
      if (counter) counter.textContent = (currentGallery.index + 1) + '/' + currentGallery.images.length;
    }
  }

  // Attach click handlers to each gallery main frame
  const galleries = Array.from(document.querySelectorAll('.type-gallery'));
  galleries.forEach(g => {
    try {
      const images = JSON.parse(g.dataset.images || '[]');
      if (!images || !images.length) return;
      const main = g.querySelector('.type-gallery-main');
      if (!main) return;
      const mainImg = main.querySelector('img');
      g.dataset.currentIndex = 0;
      mainImg.src = images[0];
      mainImg.alt = '';
      main.addEventListener('click', () => openModalForGallery(images, g.dataset.currentIndex || 0, g));
      main.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          openModalForGallery(images, 0, g);
        }
      });
      // show count if counter exists
      const counter = main.querySelector('.type-gallery-counter');
      if (counter) counter.textContent = '1/' + images.length;
      // create inline prev/next controls for the main frame so users can cycle images without opening modal
      const prevMain = document.createElement('button');
      prevMain.setAttribute('type', 'button');
      prevMain.className = 'main-prev';
      prevMain.setAttribute('aria-label', 'Previous image');
      prevMain.innerHTML = '‹';
      prevMain.addEventListener('click', (e) => {
        e.stopPropagation();
        let idx = Number(g.dataset.currentIndex || 0);
        idx = (idx - 1 + images.length) % images.length;
        g.dataset.currentIndex = idx;
        mainImg.src = images[idx];
        const counter = main.querySelector('.type-gallery-counter');
        if (counter) counter.textContent = (idx + 1) + '/' + images.length;
      });
      
      const nextMain = document.createElement('button');
      nextMain.setAttribute('type', 'button');
      nextMain.className = 'main-next';
      nextMain.setAttribute('aria-label', 'Next image');
      nextMain.innerHTML = '›';
      nextMain.addEventListener('click', (e) => {
        e.stopPropagation();
        let idx = Number(g.dataset.currentIndex || 0);
        idx = (idx + 1) % images.length;
        g.dataset.currentIndex = idx;
        mainImg.src = images[idx];
        const counter = main.querySelector('.type-gallery-counter');
        if (counter) counter.textContent = (idx + 1) + '/' + images.length;
      });
      
      // insert controls into the main frame
      main.appendChild(prevMain);
      main.appendChild(nextMain);
    } catch (err) {
      // invalid JSON, skip
    }
  });

  // Modal controls
  nextBtn.addEventListener('click', (e) => { e.stopPropagation(); next(); });
  prevBtn.addEventListener('click', (e) => { e.stopPropagation(); prev(); });
  closeBtn.addEventListener('click', (e) => { e.stopPropagation(); closeModal(); });

  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (!modal.classList.contains('is-open')) return;
    if (e.key === 'Escape') closeModal();
    if (e.key === 'ArrowRight') next();
    if (e.key === 'ArrowLeft') prev();
  });

});
