<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Instance Configuration</h1>
            <p class="text-gray-600">Configure settings for MFI instances and system templates</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Template Configuration -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Template Configuration</h2>
                <p class="text-gray-600 mb-6">Manage the master template used for new MFI instances</p>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h3 class="font-medium text-gray-900">System Copy Template</h3>
                            <p class="text-sm text-gray-600">Master template in /system_copy/</p>
                        </div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm">
                                Update
                            </button>
                            <button class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200 text-sm">
                                Backup
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h3 class="font-medium text-gray-900">Database Template</h3>
                            <p class="text-sm text-gray-600">nbc_saccos_db_full_backup_20251210_092828.sql</p>
                        </div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm">
                                Replace
                            </button>
                            <button class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200 text-sm">
                                Download
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instance Settings -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Default Instance Settings</h2>
                <p class="text-gray-600 mb-6">Configure default settings applied to new instances</p>
                
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default Database Host</label>
                        <input type="text" value="127.0.0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default Database Port</label>
                        <input type="text" value="5432" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Database Username</label>
                        <input type="text" value="postgres" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        Save Settings
                    </button>
                </form>
            </div>

            <!-- Active Configurations -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:col-span-2">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Instance Configurations</h2>
                <p class="text-gray-600 mb-6">View and manage configurations for all active MFI instances</p>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Database</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">ABC Microfinance Ltd</div>
                                    <div class="text-sm text-gray-500">/mfi/abc_ltd/</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-mono">abc_ltd_db</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></div>
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    2 hours ago
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-red-600 hover:text-red-900 mr-4">Configure</button>
                                    <button class="text-gray-600 hover:text-gray-900">Restart</button>
                                </td>
                            </tr>
                            <!-- More rows would go here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>