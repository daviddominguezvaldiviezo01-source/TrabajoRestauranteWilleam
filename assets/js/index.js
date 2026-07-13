setTimeout(() => {
                const t = document.getElementById('toastMsg');
                if (t) t.style.display = 'none';
            }, 3000);

function filtrarProductos() {
            const q = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.producto-item').forEach(el => {
                const n = el.querySelector('[data-nombre]')?.getAttribute('data-nombre') || '';
                el.style.display = n.includes(q) ? '' : 'none';
            });
        }

        (function() {
            const slides = document.querySelectorAll('.hero-slide');
            if (!slides.length || slides.length === 1) return;
            let activeIndex = 0;
            setInterval(() => {
                slides[activeIndex].classList.remove('active');
                activeIndex = (activeIndex + 1) % slides.length;
                slides[activeIndex].classList.add('active');
            }, 2000);
        })();

        (function() {
            const track = document.querySelector('.promo-track');
            const carousel = document.querySelector('.promo-carousel');
            const dotsContainer = document.querySelector('.promo-dots');
            const section = document.querySelector('.promociones-section');
            if (!track || !carousel || !dotsContainer || !section) return;

            // ── Recolectamos las tarjetas originales (antes de clonar) ──
            const originalCards = Array.from(track.querySelectorAll('.promo-card'));
            if (!originalCards.length) {
                section.style.display = 'none';
                return;
            }

            const total = originalCards.length; // número real de tarjetas
            let currentIndex = 0; // índice dentro de los originales (0 … total-1)
            let autoScrollTimer = null;
            let isTransitioning = false;

            // ── Clonamos todas las tarjetas al inicio y al final para el loop ──
            // Estructura: [clones-fin | originales | clones-inicio]
            originalCards.forEach(card => {
                const cloneEnd = card.cloneNode(true);
                cloneEnd.setAttribute('aria-hidden', 'true');
                cloneEnd.classList.add('promo-clone');
                track.appendChild(cloneEnd);
            });
            originalCards.forEach(card => {
                const cloneStart = card.cloneNode(true);
                cloneStart.setAttribute('aria-hidden', 'true');
                cloneStart.classList.add('promo-clone');
                track.insertBefore(cloneStart, track.firstChild);
            });

            // ── Todas las tarjetas en el DOM (clones-inicio + originales + clones-fin) ──
            const getAllCards = () => Array.from(track.querySelectorAll('.promo-card'));

            // El índice DOM real del primer original es `total` (por los clones del inicio)
            const domIndexOf = (realIndex) => total + realIndex;

            // ── Mueve el track sin animación ──
            const jumpTo = (domIdx) => {
                const cards = getAllCards();
                const card = cards[domIdx];
                if (!card) return;
                track.classList.remove('animating');
                track.style.transform = `translateX(-${card.offsetLeft}px)`;
            };

            // ── Mueve el track con animación ──
            const slideTo = (domIdx) => {
                const cards = getAllCards();
                const card = cards[domIdx];
                if (!card) return;
                track.classList.add('animating');
                track.style.transform = `translateX(-${card.offsetLeft}px)`;
            };

            // ── Actualiza los dots según el índice real ──
            const updateDots = () => {
                const dots = dotsContainer.querySelectorAll('.promo-dot');
                dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
            };

            // ── Construye los dots (solo una vez, para los originales) ──
            const buildDots = () => {
                dotsContainer.innerHTML = '';
                originalCards.forEach((_, i) => {
                    const dot = document.createElement('span');
                    dot.className = 'promo-dot' + (i === currentIndex ? ' active' : '');
                    dot.dataset.index = i;
                    dot.addEventListener('click', () => {
                        if (isTransitioning) return;
                        currentIndex = i;
                        slideTo(domIndexOf(currentIndex));
                        updateDots();
                        restartAutoScroll();
                    });
                    dotsContainer.appendChild(dot);
                });
            };

            // ── Avanza un paso (con lógica de loop infinito) ──
            const next = () => {
                if (isTransitioning) return;
                isTransitioning = true;
                const nextDom = domIndexOf(currentIndex) + 1;
                slideTo(nextDom);
            };

            // ── Al terminar la transición, detecta si está en clon y salta ──
            track.addEventListener('transitionend', () => {
                const cards = getAllCards();
                let domIdx = domIndexOf(currentIndex);

                // ¿Llegamos a los clones del final? -> teleport al inicio real
                if (domIdx + 1 >= total * 2) {
                    currentIndex = 0;
                    jumpTo(domIndexOf(currentIndex));
                } else {
                    // Actualizamos currentIndex según el desplazamiento real
                    // (el slideTo anterior ya movió un paso)
                    const currentOffset = -parseFloat(track.style.transform.replace('translateX(', ''));
                    // Detectamos qué tarjeta real corresponde
                    const realCards = getAllCards().slice(total, total * 2);
                    let closest = 0;
                    let minDiff = Infinity;
                    realCards.forEach((card, i) => {
                        const diff = Math.abs(card.offsetLeft - currentOffset);
                        if (diff < minDiff) {
                            minDiff = diff;
                            closest = i;
                        }
                    });
                    currentIndex = closest;
                }
                updateDots();
                isTransitioning = false;
            });

            // ── Errores de imagen: ocultamos la card ──
            window.handlePromoImageError = (img) => {
                const card = img.closest('.promo-card');
                if (card) card.style.display = 'none';
            };

            window.handleProductImageError = (img) => {
                img.style.display = 'none';
                if (img.nextElementSibling) {
                    img.nextElementSibling.style.display = 'flex';
                    img.nextElementSibling.classList.remove('d-none');
                }
            };

            // ── Refresco al cambiar tamaño (recalcula offsets) ──
            window.addEventListener('resize', () => {
                jumpTo(domIndexOf(currentIndex));
            });

            // ── Auto Scroll ──
            let scrollSpeed = 2000; // Tiempo de paso reducido (2.5 segundos)
            const startAutoScroll = () => {
                if (autoScrollTimer) clearInterval(autoScrollTimer);
                autoScrollTimer = setInterval(next, scrollSpeed);
            };
            const restartAutoScroll = () => {
                startAutoScroll();
            };

            carousel.addEventListener('mouseenter', () => clearInterval(autoScrollTimer));
            carousel.addEventListener('mouseleave', startAutoScroll);

            // ── Inicialización ──
            buildDots();
            jumpTo(domIndexOf(currentIndex)); // posiciona en el primer original sin animación
            startAutoScroll();
        })();