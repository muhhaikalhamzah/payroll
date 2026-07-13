<div id="dashboard-ticker" class="dashboard-ticker-container fixed-bottom d-flex align-items-center">
    <div class="ticker-content-wrapper flex-grow-1 overflow-hidden position-relative">
        <!-- Marquee track -->
        <div class="ticker-track" id="ticker-track">
            <!-- Items repeated twice for seamless loop -->
            <div class="ticker-items">
                <span class="ticker-item"><i class="bi bi-info-circle text-primary me-1"></i> 5 karyawan sudah menyelesaikan absensi hari ini</span>
                <span class="ticker-separator">&bull;</span>
                <span class="ticker-item"><i class="bi bi-star-fill text-warning me-1"></i> Setiap tugas yang diselesaikan hari ini adalah langkah maju</span>
                <span class="ticker-separator">&bull;</span>
                <span class="ticker-item"><i class="bi bi-bell-fill text-danger me-1"></i> 2 pengajuan cuti menunggu persetujuan Anda</span>
                <span class="ticker-separator">&bull;</span>
                <span class="ticker-item"><i class="bi bi-heart-fill text-danger me-1"></i> Istirahat sejenak kalau perlu, produktivitas datang dari keseimbangan</span>
                <span class="ticker-separator">&bull;</span>
            </div>
            <div class="ticker-items" aria-hidden="true">
                <span class="ticker-item"><i class="bi bi-info-circle text-primary me-1"></i> 5 karyawan sudah menyelesaikan absensi hari ini</span>
                <span class="ticker-separator">&bull;</span>
                <span class="ticker-item"><i class="bi bi-star-fill text-warning me-1"></i> Setiap tugas yang diselesaikan hari ini adalah langkah maju</span>
                <span class="ticker-separator">&bull;</span>
                <span class="ticker-item"><i class="bi bi-bell-fill text-danger me-1"></i> 2 pengajuan cuti menunggu persetujuan Anda</span>
                <span class="ticker-separator">&bull;</span>
                <span class="ticker-item"><i class="bi bi-heart-fill text-danger me-1"></i> Istirahat sejenak kalau perlu, produktivitas datang dari keseimbangan</span>
                <span class="ticker-separator">&bull;</span>
            </div>
        </div>
        
        <!-- Reduced Motion Fallback -->
        <div class="ticker-reduced-motion" id="ticker-reduced-motion" style="display: none;">
            <div class="reduced-item active"><i class="bi bi-info-circle text-primary me-1"></i> 5 karyawan sudah menyelesaikan absensi hari ini</div>
            <div class="reduced-item"><i class="bi bi-star-fill text-warning me-1"></i> Setiap tugas yang diselesaikan hari ini adalah langkah maju</div>
            <div class="reduced-item"><i class="bi bi-bell-fill text-danger me-1"></i> 2 pengajuan cuti menunggu persetujuan Anda</div>
            <div class="reduced-item"><i class="bi bi-heart-fill text-danger me-1"></i> Istirahat sejenak kalau perlu, produktivitas datang dari keseimbangan</div>
        </div>
    </div>
    
    <button id="close-ticker-btn" class="btn btn-sm text-secondary ms-2 p-1 border-0 position-relative" style="z-index: 1040; cursor: pointer;" aria-label="Close Ticker">
        <i class="bi bi-x-lg"></i>
    </button>

    <style>
        .dashboard-ticker-container {
            height: 36px;
            background-color: var(--bs-body-bg);
            border-top: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            z-index: 1030;
            padding: 0 15px;
            font-size: 13.5px;
        }

        [data-bs-theme="dark"] .dashboard-ticker-container {
            background-color: #1a1d20;
            border-top-color: #2b3035;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
        }

        .ticker-content-wrapper {
            height: 100%;
            display: flex;
            align-items: center;
        }

        .ticker-track {
            display: flex;
            width: fit-content;
            animation: marquee 25s linear infinite;
        }

        .ticker-track:hover {
            animation-play-state: paused; /* Pause on hover */
        }

        .ticker-items {
            display: flex;
            align-items: center;
            white-space: nowrap;
            flex-shrink: 0;
            padding-right: 2rem; /* gap between repetitions */
        }

        .ticker-item {
            color: var(--bs-body-color);
            font-weight: 500;
        }

        .ticker-separator {
            color: var(--bs-secondary-color);
            margin: 0 1.5rem;
            opacity: 0.5;
        }

        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-50% - 1rem)); } 
        }

        /* Reduced Motion Fallback */
        .ticker-reduced-motion {
            width: 100%;
            position: relative;
            height: 100%;
        }
        .reduced-item {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            width: 100%;
            opacity: 0;
            transition: opacity 0.5s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            pointer-events: none;
        }
        .reduced-item.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* Handle User Preference */
        @media (prefers-reduced-motion: reduce) {
            .ticker-track {
                display: none !important;
            }
            .ticker-reduced-motion {
                display: block !important;
            }
        }
    </style>

    <script>
        (function() {
            const initTicker = function() {
                const tickerContainer = document.getElementById('dashboard-ticker');
                const closeBtn = document.getElementById('close-ticker-btn');
                
                if (!tickerContainer || !closeBtn) return;

                // Check session storage
                if (sessionStorage.getItem('ticker_dismissed') === 'true') {
                    tickerContainer.style.display = 'none';
                    return;
                }

                closeBtn.addEventListener('click', function() {
                    tickerContainer.style.display = 'none';
                    sessionStorage.setItem('ticker_dismissed', 'true');
                });

                // Reduced motion logic (fade loop)
                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (prefersReducedMotion) {
                    const items = document.querySelectorAll('.reduced-item');
                    if (items.length > 0) {
                        let currentIndex = 0;
                        setInterval(() => {
                            items[currentIndex].classList.remove('active');
                            currentIndex = (currentIndex + 1) % items.length;
                            items[currentIndex].classList.add('active');
                        }, 4000);
                    }
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initTicker);
            } else {
                initTicker();
            }
        })();
    </script>
</div>
