<div class="p-6">
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Portal Configuration</h2>
        <p class="text-gray-600">Configure portal settings, security, and access controls</p>
    </div>

    <!-- Portal Settings Form -->
    <div class="space-y-6">
        <!-- General Settings -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-md font-medium text-gray-900 mb-4">General Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Portal URL</label>
                    <input type="text" value="https://portal.nbc-saccos.co.tz" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" 
                           readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Portal Status</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <option value="active">Active</option>
                        <option value="maintenance">Maintenance Mode</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-md font-medium text-gray-900 mb-4">Security Settings</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Two-Factor Authentication</h4>
                        <p class="text-sm text-gray-600">Require 2FA for all portal users</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Session Timeout</h4>
                        <p class="text-sm text-gray-600">Auto-logout after inactivity</p>
                    </div>
                    <select class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                        <option value="15">15 minutes</option>
                        <option value="30" selected>30 minutes</option>
                        <option value="60">1 hour</option>
                        <option value="120">2 hours</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Integration Settings -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-md font-medium text-gray-900 mb-4">Integration Settings</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">SMS Notifications</h4>
                        <p class="text-sm text-gray-600">Enable SMS notifications for portal activities</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Email Notifications</h4>
                        <p class="text-sm text-gray-600">Enable email notifications for portal activities</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Reset to Default
            </button>
            <button class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Save Changes
            </button>
        </div>
    </div>
</div> 