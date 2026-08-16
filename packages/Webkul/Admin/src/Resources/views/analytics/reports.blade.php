<x-admin::layouts>
    <x-slot:title>
        Analytics & Reports
    </x-slot>

    <!-- Header -->
    <div class="scroll-reactive-sticky sticky top-[60px] z-[100] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 mb-4">
        <div class="flex flex-col gap-1">
            <x-admin::breadcrumbs name="leads" />
            <div class="text-xl font-bold dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-slate-700 dark:text-slate-200"></i> Lead Analytics & Team Activity Reports
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800 dark:bg-green-950 dark:text-green-300">
                <span class="h-2 w-2 rounded-full bg-green-500 animate-ping"></span>
                LIVE UPDATING
            </span>
            <a href="{{ route('admin.leads.index') }}" class="secondary-button flex items-center gap-1">
                <i class="fa-solid fa-arrow-left text-xs"></i> Pipeline
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-4">
        <div class="rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Total Leads</div>
            <div class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white" id="valTotal">{{ $data['total_leads'] ?? 0 }}</div>
        </div>
        <div class="rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Converted (Won)</div>
            <div class="mt-1 text-3xl font-extrabold text-green-600 dark:text-green-400" id="valWon">{{ $data['won_leads'] ?? 0 }}</div>
        </div>
        <div class="rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Conversion Rate</div>
            <div class="mt-1 text-3xl font-extrabold text-blue-600 dark:text-blue-400" id="valRate">{{ $data['conversion_rate'] ?? '0%' }}</div>
        </div>
        <div class="rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Stale / Rotten Leads</div>
            <div class="mt-1 text-3xl font-extrabold text-red-600 dark:text-red-400" id="valStale">{{ $data['stale_leads'] ?? 0 }}</div>
        </div>
    </div>

    <!-- GeoIP Map -->
    <div class="rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 mb-4">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-base font-bold dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-blue-600"></i> Geographic Lead IP Location Map
            </h3>
            <span class="rounded bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-300">Leaflet.js Overlay</span>
        </div>
        <div id="geoMap" class="h-80 w-full rounded-lg border border-gray-200 dark:border-gray-700"></div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 mb-4">
        <div class="lg:col-span-2 rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-3 text-base font-bold dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-indigo-600"></i> Lead Acquisition Channels
            </h3>
            <canvas id="sourceChart" class="max-h-64"></canvas>
        </div>
        <div class="rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-3 text-base font-bold dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-bullseye text-emerald-600"></i> Pipeline Health
            </h3>
            <canvas id="stageChart" class="max-h-64"></canvas>
        </div>
    </div>

    <!-- Agent Reporting Table -->
    <div class="rounded-lg border border-gray-300 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-3 text-base font-bold dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-user-tie text-slate-700 dark:text-slate-300"></i> Per-Agent Activity Reporting
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">Sales Representative</th>
                        <th class="px-4 py-3">Assigned Leads</th>
                        <th class="px-4 py-3">Deals Won</th>
                        <th class="px-4 py-3">Total Logged Activities</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($data['agent_performance'] ?? [] as $agent)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3 font-semibold dark:text-white">{{ $agent['name'] }}</td>
                            <td class="px-4 py-3 dark:text-gray-300">{{ $agent['total_assigned'] }} leads</td>
                            <td class="px-4 py-3 font-bold text-green-600 dark:text-green-400">{{ $agent['won_count'] }} won</td>
                            <td class="px-4 py-3 dark:text-gray-300">{{ $agent['activity_count'] }} activities</td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-900 dark:text-green-300"><i class="fa-solid fa-bolt mr-1"></i> Active</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @pushOnce('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const map = L.map('geoMap').setView([20.5937, 78.9629], 4);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                const locs = @json($data['locations'] ?? []);
                locs.forEach(loc => {
                    L.marker([loc.lat, loc.lon])
                        .addTo(map)
                        .bindPopup(`<b>${loc.city}, ${loc.country}</b><br>Lead Volume: ${loc.lead_count} leads`);
                });

                const sources = @json($data['sources'] ?? []);
                const ctxSource = document.getElementById('sourceChart').getContext('2d');
                new Chart(ctxSource, {
                    type: 'bar',
                    data: {
                        labels: sources.map(s => s.name ?? s.source ?? 'Direct'),
                        datasets: [{
                            label: 'Leads Captured',
                            data: sources.map(s => s.total_leads ?? s.count ?? 0),
                            backgroundColor: '#2563eb',
                            borderRadius: 6,
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });

                const ctxStage = document.getElementById('stageChart').getContext('2d');
                new Chart(ctxStage, {
                    type: 'doughnut',
                    data: {
                        labels: ['Converted (Won)', 'Open Leads', 'Stale Leads'],
                        datasets: [{
                            data: [{{ $data['won_leads'] ?? 0 }}, Math.max(0, {{ $data['total_leads'] ?? 0 }} - {{ $data['won_leads'] ?? 0 }} - {{ $data['stale_leads'] ?? 0 }}), {{ $data['stale_leads'] ?? 0 }}],
                            backgroundColor: ['#16a34a', '#2563eb', '#dc2626'],
                        }]
                    },
                    options: { responsive: true }
                });
            });
        </script>
    @endPushOnce
</x-admin::layouts>
