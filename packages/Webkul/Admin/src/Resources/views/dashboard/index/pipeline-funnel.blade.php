{!! view_render_event('admin.dashboard.index.pipeline_funnel.before') !!}

<!-- Pipeline Funnel Vue Component -->
<v-dashboard-pipeline-funnel>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.index.revenue />
</v-dashboard-pipeline-funnel>

{!! view_render_event('admin.dashboard.index.pipeline_funnel.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-pipeline-funnel-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <x-admin::shimmer.dashboard.index.revenue />
        </template>

        <!-- Pipeline Funnel Section -->
        <template v-else>
            <div class="box-shadow rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        Pipeline Funnel
                    </p>
                </div>

                <div class="mt-4 flex w-full max-w-full flex-col gap-4">
                    <canvas
                        :id="$.uid + '_chart'"
                        class="w-full max-w-full items-end"
                        style="max-height: 300px;"
                    ></canvas>
                    
                    <div class="flex flex-wrap justify-center gap-5 mt-4">
                        <div v-for="stage in report.statistics" :key="stage.stage_id" class="flex items-center gap-2">
                            <span class="h-3.5 w-3.5 rounded-sm opacity-80" :style="{ backgroundColor: stage.color }"></span>

                            <p class="text-xs dark:text-gray-300">
                                @{{ stage.stage_name }} (@{{ stage.count }})
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-pipeline-funnel', {
            template: '#v-dashboard-pipeline-funnel-template',

            data() {
                return {
                    report: [],

                    isLoading: true,

                    chart: undefined,
                }
            },

            mounted() {
                this.getStats({});

                this.$emitter.on('reporting-filter-updated', this.getStats);
            },

            methods: {
                getStats(filters) {
                    this.isLoading = true;

                    var filters = Object.assign({}, filters);

                    filters.type = 'pipeline-funnel';

                    this.$axios.get("{{ route('admin.dashboard.stats') }}", {
                            params: filters
                        })
                        .then(response => {
                            this.report = response.data;

                            this.isLoading = false;

                            setTimeout(() => {
                                this.prepare();
                            }, 0);
                        })
                        .catch(error => {});
                },

                prepare() {
                    if (this.chart) {
                        this.chart.destroy();
                    }

                    if (!this.report.statistics || this.report.statistics.length === 0) {
                        return;
                    }

                    const labels = this.report.statistics.map(stage => stage.stage_name);
                    const data = this.report.statistics.map(stage => stage.count);
                    const colors = this.report.statistics.map(stage => stage.color);

                    this.chart = new Chart(document.getElementById(this.$.uid + '_chart'), {
                        type: 'funnel',

                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: colors,
                            }],
                        },

                        options: {
                            indexAxis: 'y',
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => {
                                            const stage = this.report.statistics[context.dataIndex];
                                            return `${stage.count} leads - ${stage.value}`;
                                        }
                                    }
                                }
                            },
                            maintainAspectRatio: false,
                            responsive: true,
                        }
                    });
                }
            }
        });
    </script>
@endPushOnce
