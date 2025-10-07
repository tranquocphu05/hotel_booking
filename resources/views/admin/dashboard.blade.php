@extends('layouts.admin')

@section('title','Dashboard')

@section('admin_content')
    <div class="space-y-6">
        <!-- Top stat cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded shadow transform transition-transform hover:-translate-y-1 hover:shadow-lg">
                <div class="text-sm text-gray-500">Customers</div>
                <div class="text-2xl font-bold" data-target="3782">0</div>
                <div class="text-xs text-green-600">+11.01%</div>
            </div>
            <div class="bg-white p-4 rounded shadow transform transition-transform hover:-translate-y-1 hover:shadow-lg">
                <div class="text-sm text-gray-500">Orders</div>
                <div class="text-2xl font-bold" data-target="5359">0</div>
                <div class="text-xs text-red-600">-9.05%</div>
            </div>
            <div class="bg-white p-4 rounded shadow transform transition-transform hover:-translate-y-1 hover:shadow-lg">
                <div class="text-sm text-gray-500">Monthly Target</div>
                <div class="text-2xl font-bold" data-target="75.55">0%</div>
                <div class="text-xs text-gray-500">You earn $3287 today, it's higher than last month.</div>
            </div>
        </div>

        <!-- Charts and stats -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white p-4 rounded shadow">
                <h3 class="font-semibold mb-2">Monthly Sales</h3>
                <div class="h-48 rounded">
                    <canvas id="monthlySalesChart" height="160"></canvas>
                </div>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <h3 class="font-semibold mb-2">Statistics</h3>
                <div class="h-48 rounded">
                    <canvas id="statsChart" height="160"></canvas>
                </div>
            </div>
        </div>

        <!-- Table preview -->
        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold mb-4">Recent Users</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Username</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach(
                        App\Models\User::orderBy('id','desc')->take(5)->get() as $u
                    )
                        <tr>
                            <td class="px-4 py-2">{{ $u->id }}</td>
                            <td class="px-4 py-2">{{ $u->username }}</td>
                            <td class="px-4 py-2">{{ $u->email }}</td>
                            <td class="px-4 py-2">{{ $u->vai_tro }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        // Monthly sales bar chart with smooth animation
        var ctx = document.getElementById('monthlySalesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Sales',
                    data: [120, 300, 150, 250, 180, 200, 260, 90, 160, 340, 280, 70],
                    backgroundColor: 'rgba(59,130,246,0.8)'
                }]
            },
            options: {
                responsive:true,
                maintainAspectRatio:false,
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                },
                scales: {
                    y: { beginAtZero:true }
                }
            }
        });

        // Small area stats chart with smoothing
        var ctx2 = document.getElementById('statsChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: ['Week 1','Week 2','Week 3','Week 4'],
                datasets: [{
                    label: 'Visitors',
                    data: [120, 150, 130, 180],
                    borderColor: 'rgba(99,102,241,0.9)',
                    backgroundColor: 'rgba(99,102,241,0.12)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive:true,
                maintainAspectRatio:false,
                animation: { duration: 1000, easing: 'easeOutQuart' }
            }
        });

        // Smooth count-up using requestAnimationFrame; supports decimals for percent-like values
        function animateCount(el, to) {
            var start = 0;
            var duration = 1200;
            var startTime = null;
            function step(timestamp){
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
                var current = start + (to - start) * eased;
                if (String(el.getAttribute('data-target')).includes('.')) {
                    el.textContent = current.toFixed(2) + '%';
                } else {
                    el.textContent = Math.floor(current).toLocaleString();
                }
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }

        document.querySelectorAll('[data-target]').forEach(function(el){
            var target = parseFloat(el.getAttribute('data-target')) || 0;
            animateCount(el, target);
        });
    });
</script>
@endpush