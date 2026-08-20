<div class="flex flex-col gap-4" id="lead-audit-container">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Audit History</h3>
    </div>

    <!-- The actual audits will be loaded here via JS/Vue if you have a Vue component,
         or we can just render a simple list fetched via AJAX here -->
    
    <div class="bg-white dark:bg-gray-900 shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Old Value</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Value</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700" id="audit-table-body">
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Loading audit history...</td>
                </tr>
            </tbody>
        </table>
        
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 dark:bg-gray-900 dark:border-gray-700" id="audit-pagination">
            <!-- Pagination goes here -->
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loadAudits = (page = 1) => {
            fetch(`{{ route('admin.leads.audits.index', $lead->id) }}?page=${page}`)
                .then(response => response.json())
                .then(result => {
                    const tbody = document.getElementById('audit-table-body');
                    const pagination = document.getElementById('audit-pagination');
                    tbody.innerHTML = '';
                    
                    if (result.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No audit history found.</td></tr>';
                        return;
                    }

                    result.data.forEach(audit => {
                        const date = new Date(audit.created_at).toLocaleString();
                        const userName = audit.user ? audit.user.name : 'System';
                        const row = `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${date}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${userName}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        ${audit.action}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${audit.field || '-'}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 break-all max-w-xs">${audit.old_value || '-'}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 break-all max-w-xs">${audit.new_value || '-'}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${audit.source}</td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                    
                    // Simple pagination logic
                    if (result.meta.last_page > 1) {
                        let paginationHtml = '<div class="flex justify-between items-center">';
                        paginationHtml += `<span class="text-sm text-gray-700 dark:text-gray-400">Showing page ${result.meta.current_page} of ${result.meta.last_page} (${result.meta.total} total results)</span>`;
                        paginationHtml += '<div class="space-x-2">';
                        if (result.meta.current_page > 1) {
                            paginationHtml += `<button onclick="loadAudits(${result.meta.current_page - 1})" class="px-3 py-1 border rounded text-sm bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-600 dark:text-gray-300">Previous</button>`;
                        }
                        if (result.meta.current_page < result.meta.last_page) {
                            paginationHtml += `<button onclick="loadAudits(${result.meta.current_page + 1})" class="px-3 py-1 border rounded text-sm bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-600 dark:text-gray-300">Next</button>`;
                        }
                        paginationHtml += '</div></div>';
                        pagination.innerHTML = paginationHtml;
                    } else {
                        pagination.innerHTML = '';
                    }
                })
                .catch(error => {
                    console.error('Error fetching audits:', error);
                    document.getElementById('audit-table-body').innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-sm text-red-500">Failed to load audit history.</td></tr>';
                });
        };

        // We only want to load audits when the audit tab is selected.
        // Assuming there is a tab click mechanism that changes URL or we can just load it if 'tab=audit'.
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'audit') {
            loadAudits();
        }
        
        // Also listen for tab clicks. Since x-admin::activities handles tabs, 
        // we can add a simple listener to the audit tab button.
        const auditTabBtn = document.querySelector('[data-tab="audit"]');
        if (auditTabBtn) {
            auditTabBtn.addEventListener('click', () => loadAudits());
        } else {
            // fallback, try to observe changes or just load once 
            setTimeout(() => {
                const retryBtn = document.querySelector('[data-tab="audit"]');
                if (retryBtn) retryBtn.addEventListener('click', () => loadAudits());
            }, 1000);
        }
        
        // Make the function available globally for inline onclick handlers
        window.loadAudits = loadAudits;
    });
</script>
