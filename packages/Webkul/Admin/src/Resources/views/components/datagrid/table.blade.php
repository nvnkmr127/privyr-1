@props(['isMultiRow' => false])

<v-datagrid-table
    :is-loading="isLoading"
    :available="available"
    :applied="applied"
    @selectAll="selectAll"
    @sort="sort"
    @actionSuccess="get"
>
    {{ $slot }}
</v-datagrid-table>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-datagrid-table-template"
    >
        <div class="w-full">
            <!-- Table view for larger screens, Card view for mobile -->
            <div class="table-responsive box-shadow rounded-t-0 grid w-full overflow-x-auto border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
                <!-- Table Header - Always visible on all screens -->
                <slot
                    name="header"
                    :is-loading="isLoading"
                    :available="available"
                    :applied="applied"
                    :select-all="selectAll"
                    :sort="sort"
                    :perform-action="performAction"
                >
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.head :isMultiRow="$isMultiRow" />
                    </template>

                    <template v-else>
                        <div
                            class="row grid min-h-[47px] items-center gap-2.5 border-b bg-gray-50 px-4 py-2.5 text-black dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 max-lg:hidden"
                            :style="`grid-template-columns: ${gridTemplateColumns}`"
                        >
                            <!-- Mass Actions -->
                            <div class="flex items-center sticky left-4 z-20 bg-gray-50 dark:bg-gray-900" v-if="available.massActions.length">
                                <label for="mass_action_select_all_records" class="flex items-center">
                                    <input
                                        type="checkbox"
                                        name="mass_action_select_all_records"
                                        id="mass_action_select_all_records"
                                        class="peer hidden"
                                        :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                        @change="selectAll"
                                    >

                                    <span
                                        class="icon-checkbox-outline cursor-pointer rounded-md text-2xl text-gray-500 peer-checked:text-brandColor"
                                        :class="[
                                            applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checkbox-select peer-checked:text-brandColor ' : (
                                                applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-multiple peer-checked:brandColor' : ''
                                            ),
                                        ]"
                                    >
                                    </span>
                                </label>
                            </div>

                            <!-- Columns -->
                            <template v-for="column in available.columns">
                                <div
                                    class="flex items-center gap-1.5 truncate"
                                    :class="{'cursor-pointer select-none hover:text-gray-800 dark:hover:text-white': column.sortable}"
                                    @click="sort(column)"
                                    v-if="column.visibility"
                                    :title="column.label"
                                > 
                                    <p class="truncate" v-html="column.label"></p>

                                    <i
                                        class="align-text-bottom text-base text-gray-600 dark:text-gray-300 shrink-0"
                                        :class="[applied.sort.order === 'asc' ? 'icon-stats-down': 'icon-stats-up']"
                                        v-if="column.index == applied.sort.column"
                                    ></i>
                                </div>
                            </template>

                            <!-- Actions -->
                            <p
                                class="text-end sticky right-4 z-20 bg-gray-50 dark:bg-gray-900"
                                v-if="available.actions.length"
                            >
                                @lang('admin::app.components.datagrid.table.actions')
                            </p>
                        </div>
                        
                        <!-- Mobile Sort/Filter Header -->
                        <div class="hidden border-b bg-gray-50 px-4 py-3 text-black dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 max-lg:block">
                            <div class="flex items-center justify-between">
                                <!-- Mass Actions for Mobile -->
                                <div v-if="available.massActions.length">
                                    <label for="mass_action_select_all_records">
                                        <input
                                            type="checkbox"
                                            name="mass_action_select_all_records"
                                            id="mass_action_select_all_records"
                                            class="peer hidden"
                                            :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                            @change="selectAll"
                                        >
    
                                        <span
                                            class="icon-checkbox-outline cursor-pointer rounded-md text-2xl text-gray-500 peer-checked:text-brandColor"
                                            :class="[
                                                applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checkbox-select peer-checked:text-brandColor ' : (
                                                    applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-multiple peer-checked:brandColor' : ''
                                                ),
                                            ]"
                                        >
                                        </span>
                                    </label>
                                </div>
                                
                                <!-- Mobile Sort Dropdown -->
                                <div class="flex w-full justify-end" v-if="available.columns.some(column => column.sortable)">
                                    <x-admin::dropdown position="bottom-{{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'left' : 'right' }}">
                                        <x-slot:toggle>
                                            <div class="flex items-center gap-1">
                                                <button
                                                    type="button"
                                                    class="inline-flex w-full max-w-max cursor-pointer appearance-none items-center justify-between gap-x-2 rounded-md border bg-white px-2.5 py-1.5 text-center leading-6 text-gray-600 transition-all marker:shadow hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                                >
                                                    <span>
                                                        Sort
                                                    </span>
                    
                                                    <span class="icon-down-arrow text-2xl"></span>
                                                </button>
                                            </div>
                                        </x-slot>
                
                                        <x-slot:menu>
                                            <x-admin::dropdown.menu.item
                                                v-for="column in available.columns.filter(column => column.sortable && column.visibility)"
                                                @click="sort(column)"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <span v-html="column.label"></span>
                                                    <i
                                                        class="align-text-bottom text-base text-gray-600 dark:text-gray-300"
                                                        :class="[applied.sort.order === 'asc' ? 'icon-stats-down': 'icon-stats-up']"
                                                        v-if="column.index == applied.sort.column"
                                                    ></i>
                                                </div>
                                            </x-admin::dropdown.menu.item>
                                        </x-slot>
                                    </x-admin::dropdown>
                                </div>
                            </div>
                        </div>
                    </template>
                </slot>

                <slot
                    name="body"
                    :is-loading="isLoading"
                    :available="available"
                    :applied="applied"
                    :select-all="selectAll"
                    :sort="sort"
                    :perform-action="performAction"
                >
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.body :isMultiRow="$isMultiRow" />
                    </template>

                    <template v-else>
                        <template v-if="available.records.length">
                            <!-- Desktop View -->
                            <div
                                class="row group grid items-center gap-2.5 border-b px-4 py-2.5 text-black transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950 max-lg:hidden cursor-pointer"
                                v-for="record in available.records"
                                :style="`grid-template-columns: ${gridTemplateColumns}`"
                                @click="rowClick($event, record)"
                            >
                                <!-- Mass Actions -->
                                <div class="flex items-center sticky left-4 z-10 bg-white group-hover:bg-gray-50 dark:bg-gray-900 transition-colors" v-if="available.massActions.length">
                                    <label :for="`mass_action_select_record_${record[available.meta.primary_column]}`" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :name="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                            :value="record[available.meta.primary_column]"
                                            :id="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                            class="peer hidden"
                                            v-model="applied.massActions.indices"
                                        >

                                        <span class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-500 peer-checked:text-brandColor">
                                        </span>
                                    </label>
                                </div>

                                <!-- Columns -->
                                <template v-for="column in available.columns">
                                    <div
                                        class="truncate"
                                        :title="String(record[column.index] || '').replace(/(<([^>]+)>)/gi, '')"
                                        v-html="record[column.index]"
                                        v-if="column.visibility"
                                    >
                                    </div>
                                </template>

                                <!-- Actions -->
                                <p
                                    class="flex h-full items-center justify-end gap-1 flex-nowrap shrink-0 sticky right-4 z-10 bg-white group-hover:bg-gray-50 dark:bg-gray-900 transition-colors"
                                    v-if="available.actions.length"
                                >
                                    <span
                                        class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                        :class="action.icon"
                                        v-text="! action.icon ? action.title : ''"
                                        v-for="action in record.actions"
                                        @click="performAction(action)"
                                    >
                                    </span>
                                </p>
                            </div>
                            
                            <!-- Mobile Card View -->
                            <div
                                class="hidden border-b px-4 py-4 text-black dark:border-gray-800 dark:text-gray-300 max-lg:block cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-950 transition-all"
                                v-for="record in available.records"
                                @click="rowClick($event, record)"
                            >
                                <div class="mb-2 flex items-center justify-between">
                                    <!-- Mass Actions for Mobile Cards -->
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <p v-if="available.massActions.length">
                                            <label :for="`mass_action_select_record_${record[available.meta.primary_column]}`">
                                                <input
                                                    type="checkbox"
                                                    :name="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                                    :value="record[available.meta.primary_column]"
                                                    :id="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                                    class="peer hidden"
                                                    v-model="applied.massActions.indices"
                                                >
        
                                                <span class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-500 peer-checked:text-brandColor">
                                                </span>
                                            </label>
                                        </p>

                                        <!-- Actions for Mobile -->
                                        <div
                                            class="flex w-full items-center justify-end"
                                            v-if="available.actions.length"
                                        >
                                            <span
                                                class="dark:hover:bg-gray-80 cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200"
                                                :class="action.icon"
                                                v-text="! action.icon ? action.title : ''"
                                                v-for="action in record.actions"
                                                @click="performAction(action)"
                                            >
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Content -->
                                <div class="grid gap-2">
                                    <template v-for="(column, index) in available.columns.filter(c => c.visibility)">
                                        <div class="flex flex-wrap items-baseline gap-x-2" v-if="index < 4">
                                            <span class="text-slate-600 dark:text-gray-300" v-html="column.label + ':'"></span>
                                            <span class="break-words font-medium text-slate-900 dark:text-white" v-html="record[column.index]"></span>
                                        </div>
                                    </template>

                                    <details class="group mt-1" v-if="available.columns.filter(c => c.visibility).length > 4">
                                        <summary class="cursor-pointer text-sm font-medium text-brandColor outline-none hover:underline mb-2">
                                            More Details
                                        </summary>
                                        <div class="grid gap-2 pt-1">
                                            <template v-for="(column, index) in available.columns.filter(c => c.visibility)">
                                                <div class="flex flex-wrap items-baseline gap-x-2" v-if="index >= 4">
                                                    <span class="text-slate-600 dark:text-gray-300" v-html="column.label + ':'"></span>
                                                    <span class="break-words font-medium text-slate-900 dark:text-white" v-html="record[column.index]"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </details>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="row grid border-b px-4 py-4 text-center text-gray-600 dark:border-gray-800 dark:text-gray-300">
                                <p>
                                    @lang('admin::app.components.datagrid.table.no-records-available')
                                </p>
                            </div>
                        </template>
                    </template>
                </slot>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-datagrid-table', {
            template: '#v-datagrid-table-template',

            props: ['isLoading', 'available', 'applied'],
            
            computed: {
                gridsCount() {
                    let count = this.available.columns.filter((column) => column.visibility).length;

                    if (this.available.actions.length) {
                        ++count;
                    }

                    if (this.available.massActions.length) {
                        ++count;
                    }

                    return count;
                },
                gridTemplateColumns() {
                    let columns = [];
                    if (this.available.massActions.length) {
                        columns.push('40px'); // Checkbox
                    }
                    for (let column of this.available.columns) {
                        if (!column.visibility) continue;
                        
                        let width = 'minmax(150px, 1fr)'; // Fallback

                        // Primary columns
                        if (['id'].includes(column.index)) {
                            width = 'minmax(60px, max-content)';
                        } else if (['title', 'sales_person', 'name'].includes(column.index)) {
                            width = 'minmax(200px, 2fr)';
                        } else if (['lead_value'].includes(column.index)) {
                            width = 'minmax(130px, 1.5fr)';
                        } else if (['stage'].includes(column.index)) {
                            width = 'minmax(140px, 1fr)';
                        }
                        // Secondary columns
                        else if (['lead_source_name', 'lead_type_name', 'tag_name', 'rotten_lead'].includes(column.index)) {
                            width = 'minmax(120px, 1fr)';
                        } else if (['expected_close_date', 'created_at'].includes(column.index)) {
                            width = 'minmax(110px, max-content)';
                        }
                        // Utility columns
                        else if (['quick_actions'].includes(column.index)) {
                            width = 'minmax(90px, max-content)';
                        }
                        
                        // Allow backend override
                        if (column.width) {
                            width = column.width;
                        }

                        columns.push(width);
                    }
                    if (this.available.actions.length) {
                        columns.push('minmax(80px, max-content)'); // Row Actions
                    }
                    return columns.join(' ');
                }
            },

            methods: {
                /**
                 * Handle row click event to trigger view action.
                 * 
                 * @param {Event} event 
                 * @param {object} record 
                 * @returns {void}
                 */
                rowClick(event, record) {
                    // Prevent row click if the user is interacting with inner elements like links, inputs, or buttons
                    if (event.target.closest('a, button, input, label, .icon-checkbox-outline, .icon-checkbox-select, .icon-delete, .icon-eye')) {
                        return;
                    }

                    let viewAction = record.actions.find(action => action.icon === 'icon-eye' || action.title === 'View');
                    
                    if (viewAction) {
                        this.performAction(viewAction);
                    }
                },

                /**
                 * Select all records in the datagrid.
                 *
                 * @returns {void}
                 */
                selectAll() {
                    this.$emit('selectAll');
                },

                /**
                 * Perform a sorting operation on the specified column.
                 *
                 * @param {object} column
                 * @returns {void}
                 */
                sort(column) {
                    this.$emit('sort', column);
                },

                /**
                 * Perform the specified action.
                 *
                 * @param {object} action
                 * @returns {void}
                 */
                performAction(action) {
                    const method = action.method.toLowerCase();

                    switch (method) {
                        case 'get':
                            window.location.href = action.url;

                            break;

                        case 'post':
                        case 'put':
                        case 'patch':
                        case 'delete':
                            this.$emitter.emit('open-confirm-modal', {
                                agree: () => {
                                    this.$axios[method](action.url)
                                        .then(response => {
                                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                            this.$emit('actionSuccess', response.data);
                                        })
                                        .catch((error) => {
                                            this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });

                                            this.$emit('actionError', error.response.data);
                                        });
                                }
                            });

                            break;

                        default:
                            console.error('Method not supported.');

                            break;
                    }
                },
            },
        });
    </script>
@endpushOnce
