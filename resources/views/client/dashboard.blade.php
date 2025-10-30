{{-- resources/views/client/dashboard.blade.php (Hoặc home.blade.php) --}}

@extends('layouts.base') 
{{-- extends file layout vừa sửa --}}

{{-- ĐẨY HEADER LÊN @yield('fullwidth_header') --}}
@section('fullwidth_header')
    @include('client.header.header') 
@endsection

@push('scripts')
    <style>
        /* Lớp phủ canvas tuyết rơi, không chặn tương tác */
        #snow-canvas {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 10;
        }
    </style>
    <script>
        (function () {
            const isReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (isReduced) return;

            let canvas = document.getElementById('snow-canvas');
            if (!canvas) {
                canvas = document.createElement('canvas');
                canvas.id = 'snow-canvas';
                document.body.appendChild(canvas);
            }
            const ctx = canvas.getContext('2d');

            const DPR = Math.min(window.devicePixelRatio || 1, 2);
            function resize() {
                const w = window.innerWidth;
                const h = window.innerHeight;
                canvas.width = Math.floor(w * DPR);
                canvas.height = Math.floor(h * DPR);
                canvas.style.width = w + 'px';
                canvas.style.height = h + 'px';
            }
            resize();
            window.addEventListener('resize', resize);

            // Cấu hình bông tuyết
            const FLAKE_DENSITY = 0.0016; // số bông trên mỗi px^2 viewport
            const MAX_FLAKES = Math.max(80, Math.min(220, Math.floor(window.innerWidth * window.innerHeight * FLAKE_DENSITY)));
            const flakes = [];

            function rand(min, max) { return Math.random() * (max - min) + min; }

            function makeFlake() {
                const size = rand(1.2, 3.2);
                return {
                    x: rand(0, canvas.width),
                    y: rand(-canvas.height, 0),
                    r: size * DPR,
                    // vận tốc dọc nhẹ, ~1–2px mỗi khung tại 60fps
                    vy: rand(0.06, 0.11) * DPR * (0.8 + size / 3),
                    // vận tốc ngang rất nhẹ
                    vx: rand(-0.05, 0.05) * DPR,
                    swing: rand(0.4, 1.0),
                    phase: rand(0, Math.PI * 2),
                    alpha: rand(0.6, 0.95)
                };
            }

            for (let i = 0; i < MAX_FLAKES; i++) {
                flakes.push(makeFlake());
            }

            let lastTime = performance.now();
            function drawFlake(f) {
                const armLen = Math.max(2, f.r * 2.2);
                ctx.save();
                ctx.translate(f.x, f.y);
                ctx.strokeStyle = 'rgba(255,255,255,' + f.alpha + ')';
                ctx.lineWidth = Math.max(0.8, f.r * 0.35);
                // Vẽ 3 trục quay 60° (tạo 6 nhánh)
                for (let i = 0; i < 3; i++) {
                    ctx.rotate(Math.PI / 3);
                    ctx.beginPath();
                    ctx.moveTo(-armLen, 0);
                    ctx.lineTo(armLen, 0);
                    ctx.stroke();
                }
                ctx.restore();
            }

            function tick(now) {
                const dt = Math.min(33, now - lastTime);
                lastTime = now;

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                for (let i = 0; i < flakes.length; i++) {
                    const f = flakes[i];
                    // dao động ngang nhẹ
                    f.phase += 0.002 * dt * f.swing;
                    f.x += (Math.sin(f.phase) * 0.25 + f.vx) * dt;
                    f.y += f.vy * dt;

                    // vòng lại khi vượt đáy / biên
                    if (f.y - f.r > canvas.height) {
                        flakes[i] = makeFlake();
                        flakes[i].y = -f.r;
                        continue;
                    }
                    if (f.x < -10) f.x = canvas.width + 10;
                    if (f.x > canvas.width + 10) f.x = -10;

                    drawFlake(f);
                }

                ctx.globalAlpha = 1;
                requestAnimationFrame(tick);
            }

            requestAnimationFrame(tick);

            // Dọn dẹp khi rời trang (SPA/partial nav)
            window.addEventListener('beforeunload', () => {
                window.removeEventListener('resize', resize);
                if (canvas && canvas.parentNode) canvas.parentNode.removeChild(canvas);
            });
        })();
    </script>
@endpush

{{-- NỘI DUNG CHÍNH (Chứa các khối giới hạn) LÊN @yield('content') --}}
@section('content')
    <div class="main">
        {{-- Bằng cách @include ở đây, nội dung giới hạn sẽ nằm trong div.main --}}
        @include('client.content.content')
    </div>
@endsection
