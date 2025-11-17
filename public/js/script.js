

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  initializeEvents();
  initializeTrending();
  setupMobileMenu();
  setupSearch();
  setupCategoryFilters();
});

function initializeEvents() {
  const eventsGrid = document.getElementById('eventsGrid');
  if (!eventsGrid) return;
  renderEvents(events);
}

function initializeTrending() {
  const trendingGrid = document.getElementById('trendingGrid');
  if (!trendingGrid) return;
  
  const trendingEvents = events.filter(e => e.trending).slice(0, 4);
  trendingGrid.innerHTML = trendingEvents.map(event => `
    <div class="trending-card">
      <img src="${event.image}" alt="${event.title}">
      <div class="trending-card-content">
        <div>
          <span class="trending-badge">🔥 TRENDING</span>
          <h3 class="trending-card-title">${event.title}</h3>
          <p class="text-sm text-white/70">📅 ${event.date}</p>
        </div>
        <div class="flex items-center justify-between">
          <span class="event-price">${event.price}</span>
          <button class="event-btn" onclick="bookEvent(${event.id})">Book</button>
        </div>
      </div>
    </div>
  `).join('');
}

// Book Event Function
function bookEvent(eventId) {
  const event = events.find(e => e.id === eventId);
  if (event) {
    alert(`Booking ticket for: ${event.title}\n\nPrice: ${event.price}\nDate: ${event.date}\nLocation: ${event.location}`);
  }
}

// Mobile Menu Toggle
function setupMobileMenu() {
  const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
  const mobileMenu = document.querySelector('.mobile-menu');

  if (mobileMenuBtn && mobileMenu) {
    mobileMenuBtn.addEventListener('click', function() {
      mobileMenu.classList.toggle('hidden');
    });

    const mobileLinks = mobileMenu.querySelectorAll('a');
    mobileLinks.forEach(link => {
      link.addEventListener('click', function() {
        mobileMenu.classList.add('hidden');
      });
    });
  }
}

function setupSearch() {
  const searchInput = document.getElementById('searchInput');
  const searchBtn = document.getElementById('searchBtn');
  
  if (searchInput) {
    searchInput.addEventListener('keyup', function(e) {
      const query = this.value.toLowerCase();
      if (query.length > 0) {
        filterEvents(query);
      } else {
        renderEvents(events);
      }
    });
  }
  
  if (searchBtn) {
    searchBtn.addEventListener('click', function() {
      handleSearch();
    });
  }
}

function handleSearch() {
  const query = document.getElementById('searchInput').value.toLowerCase();
  if (query.length > 0) {
    filterEvents(query);
    document.getElementById('events').scrollIntoView({ behavior: 'smooth' });
  }
}

function resetFilter() {
  document.getElementById('searchInput').value = '';
  renderEvents(events);
}

// Filter Events by Search
function filterEvents(query) {
  const filtered = events.filter(event =>
    event.title.toLowerCase().includes(query) ||
    event.location.toLowerCase().includes(query) ||
    event.category.toLowerCase().includes(query)
  );
  
  renderEvents(filtered.length > 0 ? filtered : events);
}

// Category Filter
function setupCategoryFilters() {
  const categoryBtns = document.querySelectorAll('.category-btn');
  categoryBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const category = this.getAttribute('data-category');
      const filtered = category === 'all' ? events : events.filter(e => e.category === category);
      renderEvents(filtered);
      document.getElementById('events').scrollIntoView({ behavior: 'smooth' });
    });
  });
}

function renderEvents(filteredEvents) {
  const eventsGrid = document.getElementById('eventsGrid');
  if (!eventsGrid) return;

  if (filteredEvents.length === 0) {
    eventsGrid.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-white/70">No events found. Try a different search.</p></div>';
    return;
  }

  eventsGrid.innerHTML = filteredEvents.map(event => `
    <div class="event-card">
      <img src="${event.image}" alt="${event.title}">
      <div class="event-card-body">
        <h3 class="event-card-title">${event.title}</h3>
        <div class="event-card-meta">
          <span>📅 ${event.date}</span>
          <span>📍 ${event.location}</span>
        </div>
        <p class="event-card-desc">Join us for an unforgettable experience at this amazing ${event.category} event.</p>
        <div class="event-card-footer">
          <span class="event-price">${event.price}</span>
          <button class="event-btn" onclick="bookEvent(${event.id})">Book Ticket</button>
        </div>
      </div>
    </div>
  `).join('');
}

function handleNewsletterSignup(e) {
  e.preventDefault();
  const email = e.target.querySelector('input[type="email"]').value;
  alert(`Thank you for subscribing with ${email}! Check your inbox for updates.`);
  e.target.reset();
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const href = this.getAttribute('href');
    if (href !== '#' && document.querySelector(href)) {
      e.preventDefault();
      document.querySelector(href).scrollIntoView({
        behavior: 'smooth'
      });
    }
  });
});

// ===== Landing Page Interactions (User) =====
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        initLandingPage();
    });

    function initLandingPage() {
        setupLandingSearch();
        setupFeatureReveal();
        setupCounters();
        setupHeaderOnScroll();
    }

    // Submit on Enter dan autofocus search di landing
    function setupLandingSearch() {
        var forms = document.getElementsByClassName('landing-search-form');
        for (var i = 0; i < forms.length; i++) {
            var form = forms[i];
            var input = form.querySelector('.landing-search-input') || form.querySelector('input[type="text"]');
            if (input) {
                // autofocus saat load
                setTimeout(function () { input.focus(); }, 200);
                // submit saat Enter
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.form.submit();
                    }
                });
            }
        }
    }

    // Efek reveal untuk feature-card saat masuk viewport
    function setupFeatureReveal() {
        var cards = document.getElementsByClassName('feature-card');
        if (!('IntersectionObserver' in window) || cards.length === 0) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('opacity-100');
                    entry.target.classList.remove('opacity-0');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        Array.prototype.forEach.call(cards, function (card) {
            card.classList.add('transition-opacity', 'opacity-0');
            observer.observe(card);
        });
    }

    // Animasi angka statistik jika elemen memiliki attribute data-count
    function setupCounters() {
        var counters = document.querySelectorAll('[data-count]');
        counters.forEach(function (el) {
            var target = parseInt(el.getAttribute('data-count'), 10);
            if (isNaN(target)) return;
            animateNumber(el, target, 900);
        });
    }

    function animateNumber(el, target, duration) {
        var start = 0;
        var startTime = null;

        function step(ts) {
            if (!startTime) startTime = ts;
            var progress = Math.min((ts - startTime) / duration, 1);
            var value = Math.floor(progress * target);
            el.textContent = value.toLocaleString('en-US') + '+';
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    }

    // Header glow saat scroll
    function setupHeaderOnScroll() {
        var header = document.querySelector('header.liquid-glass-header');
        if (!header) return;
        var last = 0;
        window.addEventListener('scroll', function () {
            var y = window.scrollY || document.documentElement.scrollTop;
            if (y > 8 && last <= 8) {
                header.style.boxShadow = '0 4px 16px rgba(0,0,0,0.25)';
            } else if (y <= 8 && last > 8) {
                header.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
            }
            last = y;
        });
    }
})();
