<div class="search-bar position-relative d-none d-md-flex align-items-center flex-grow-1 mx-4" id="global-search-wrapper" style="max-width: 350px;">
    <!-- Burst Container (overflow visible) -->
    <div id="coin-burst-container" class="position-absolute w-100 h-100" style="pointer-events: none; z-index: 10;"></div>
    
    <div class="input-group w-100 search-input-group" style="overflow: hidden; position: relative;">
        <!-- Trail Container (overflow hidden inside input group) -->
        <div id="coin-trail-container" class="position-absolute w-100 h-100" style="pointer-events: none; z-index: 10;"></div>
        
        <span class="input-group-text bg-transparent border-end-0 text-muted position-relative z-2 px-2" id="search-icon-addon">
            <i class="bi bi-search" id="search-magnify-icon" style="transition: color 0.3s ease;"></i>
        </span>
        <input type="text" id="global-search-input" class="form-control form-control-sm border-start-0 border-end-0 shadow-none bg-transparent position-relative z-2 px-0" placeholder="@lang('common.search')" autocomplete="off">
        <span class="input-group-text bg-transparent border-start-0 text-warning position-relative z-2 px-2" id="search-coin-addon">
            <i class="bi bi-coin"></i>
        </span>
    </div>
    
    <!-- Dropdown for suggestions -->
    <div class="search-suggestions dropdown-menu w-100 mt-1 shadow-lg border-0" id="global-search-suggestions" style="position: absolute; top: 100%; left: 0; z-index: 1050; display: none; max-height: 400px; overflow-y: auto;">
        <!-- Suggestions will be populated via AJAX -->
        <div class="p-3 text-center text-muted" id="search-loading" style="display: none;">
            <div class="spinner-border spinner-border-sm text-warning" role="status"></div>
            <span class="ms-2">@lang('common.searching')</span>
        </div>
        <div id="search-results-container"></div>
    </div>
</div>

<style>
    /* Styling Dasar Search Bar */
    #global-search-wrapper {
        position: relative;
    }
    
    .search-input-group {
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        transition: all 0.3s ease;
        background: #ffffff; /* Ubah warna search menjadi putih */
        position: relative;
        z-index: 2;
        height: 36px;
    }
    
    [data-bs-theme="dark"] .search-input-group {
        border-color: rgba(255, 255, 255, 0.15);
        background: #1a1d20; /* Dark mode solid background */
    }

    .search-input-group:focus-within {
        border-color: #FAC775; /* Amber Focus Ring */
        box-shadow: 0 0 0 0.25rem rgba(250, 199, 117, 0.25);
    }

    /* Change magnify icon color on focus */
    .search-input-group:focus-within #search-magnify-icon {
        color: #FAC775 !important;
    }

    #global-search-input {
        color: #000 !important; /* Force black in light mode */
        -webkit-text-fill-color: #000 !important;
        font-size: 0.95rem;
    }

    [data-bs-theme="dark"] #global-search-input,
    body.dark-mode #global-search-input {
        color: #fff !important; /* Force white in dark mode */
        -webkit-text-fill-color: #fff !important;
    }

    #global-search-input::placeholder {
        color: #6c757d;
        opacity: 1;
    }
    
    [data-bs-theme="dark"] #global-search-input::placeholder {
        color: #adb5bd;
    }

    /* --- Coin Animations --- */
    .coin-anim-element {
        position: absolute;
        pointer-events: none;
        color: #FAC775; /* Amber */
        opacity: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (prefers-reduced-motion: no-preference) {
        @keyframes coinBurst {
            0% { transform: translate(0, 0) scale(0.3) rotate(0deg); opacity: 1; }
            100% { transform: translate(var(--tx), var(--ty)) scale(1.2) rotate(var(--rot)); opacity: 0; }
        }

        @keyframes coinTrail {
            0% { transform: translate(0, 0) scale(0.8) rotate(0deg); opacity: 0.8; }
            100% { transform: translate(var(--tx), -30px) scale(0.4) rotate(var(--rot)); opacity: 0; }
        }

        .anim-burst {
            animation: coinBurst 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }

        .anim-trail {
            animation: coinTrail 0.7s ease-out forwards;
        }
    }
    
    @media (prefers-reduced-motion: reduce) {
        .coin-anim-element {
            display: none !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('global-search-input');
        const burstContainer = document.getElementById('coin-burst-container');
        const trailContainer = document.getElementById('coin-trail-container');
        
        // Prefers reduced motion check
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        let hasBurst = false;
        
        // 1. Coin Burst Animation on Focus
        searchInput.addEventListener('focus', () => {
            if (prefersReducedMotion) return;
            if (hasBurst) return; // Only burst once per focus session
            hasBurst = true;
            
            // Create 4-6 coins bursting outwards
            const numCoins = Math.floor(Math.random() * 3) + 4;
            const rect = burstContainer.getBoundingClientRect();
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            for (let i = 0; i < numCoins; i++) {
                const coin = document.createElement('i');
                coin.className = 'bi bi-coin coin-anim-element anim-burst';
                
                // Random burst directions
                const angle = (Math.random() * Math.PI) + Math.PI; // Upwards semi-circle (PI to 2PI)
                const distance = Math.random() * 40 + 30; // 30px to 70px distance
                
                // Allow some side bursts too
                const tx = (Math.random() > 0.5 ? 1 : -1) * (Math.random() * 50 + 20) + 'px';
                const ty = - (Math.random() * 40 + 20) + 'px';
                const rot = (Math.random() * 360 - 180) + 'deg';
                
                coin.style.setProperty('--tx', tx);
                coin.style.setProperty('--ty', ty);
                coin.style.setProperty('--rot', rot);
                
                // Start from center of the container
                coin.style.left = `calc(50% - 8px)`;
                coin.style.top = `calc(50% - 8px)`;
                coin.style.fontSize = '16px';
                
                burstContainer.appendChild(coin);
                
                // Cleanup after animation
                setTimeout(() => {
                    if (coin.parentNode) coin.parentNode.removeChild(coin);
                }, 600);
            }
        });
        
        searchInput.addEventListener('blur', () => {
            hasBurst = false; // Reset burst capability on blur
        });

        // 3. Backend Integration (AJAX Search)
        const suggestionsBox = document.getElementById('global-search-suggestions');
        const resultsContainer = document.getElementById('search-results-container');
        const searchLoading = document.getElementById('search-loading');
        
        // Coin Trail Animation & AJAX Search State
        let searchTimeout;
        let lastTrailTime = 0;
        const TRAIL_THROTTLE_MS = 90;
        
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            const rawValue = e.target.value;
            
            // Handle animations first (throttled trail)
            const now = Date.now();
            if (!prefersReducedMotion && now - lastTrailTime >= TRAIL_THROTTLE_MS && rawValue.length > 0) {
                lastTrailTime = now;
                const coin = document.createElement('i');
                coin.className = 'bi bi-coin coin-anim-element anim-trail';
                const inputWidth = searchInput.clientWidth - 80;
                const maxChars = inputWidth / 8;
                const effectiveLength = Math.min(rawValue.length, maxChars);
                const baseX = 40 + (effectiveLength * 8);
                const tx = (Math.random() * 20 - 10) + 'px';
                const rot = (Math.random() * 180 - 90) + 'deg';
                coin.style.setProperty('--tx', tx);
                coin.style.setProperty('--rot', rot);
                coin.style.left = `${baseX}px`;
                coin.style.top = `60%`;
                coin.style.fontSize = '12px';
                trailContainer.appendChild(coin);
                setTimeout(() => { if (coin.parentNode) coin.parentNode.removeChild(coin); }, 700);
            }
            
            // Handle AJAX Search with debounce
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                suggestionsBox.style.display = 'none';
                return;
            }
            
            suggestionsBox.style.display = 'block';
            resultsContainer.innerHTML = '';
            searchLoading.style.display = 'block';
            
            searchTimeout = setTimeout(() => {
                fetch(`{{ route('global.search') }}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    searchLoading.style.display = 'none';
                    resultsContainer.innerHTML = '';
                    
                    if (data.length === 0) {
                        resultsContainer.innerHTML = '<div class="p-3 text-center text-muted">Tidak ada hasil ditemukan.</div>';
                        return;
                    }
                    
                    data.forEach(item => {
                        const iconColor = item.type === 'Menu' ? 'text-primary' : (item.type === 'Karyawan' ? 'text-success' : 'text-warning');
                        const html = `
                            <a href="${item.url}" class="dropdown-item d-flex align-items-center py-2 px-3 border-bottom">
                                <div class="icon-circle bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi ${item.icon} ${iconColor} fs-5"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-truncate">${item.title}</div>
                                    <div class="small text-muted text-truncate">${item.subtitle}</div>
                                </div>
                                <span class="badge bg-secondary ms-2" style="font-size: 0.65em;">${item.type}</span>
                            </a>
                        `;
                        resultsContainer.insertAdjacentHTML('beforeend', html);
                    });
                })
                .catch(error => {
                    searchLoading.style.display = 'none';
                    resultsContainer.innerHTML = '<div class="p-3 text-center text-danger">Terjadi kesalahan saat mencari data.</div>';
                    console.error('Search error:', error);
                });
            }, 500); // 500ms debounce
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.style.display = 'none';
            }
        });
        
        // Show suggestions again on focus if there's text
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length >= 2 && resultsContainer.innerHTML !== '') {
                suggestionsBox.style.display = 'block';
            }
        });
    });
</script>
