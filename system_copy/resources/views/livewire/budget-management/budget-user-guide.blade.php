{{-- Budget Management User Guide --}}
<div class="max-w-7xl mx-auto">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg shadow-lg p-8 mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Budget Management User Guide</h1>
        <p class="text-gray-500">Complete guide to using the advanced budget management system</p>
    </div>

    {{-- Quick Navigation --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Quick Navigation</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="#getting-started" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span class="text-sm font-medium">Getting Started</span>
            </a>
            <a href="#allocations" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition">
                <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="text-sm font-medium">Monthly Allocations</span>
            </a>
            <a href="#advances" class="flex items-center p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
                <svg class="w-6 h-6 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="text-sm font-medium">Budget Advances</span>
            </a>
            <a href="#supplementary" class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="text-sm font-medium">Supplementary</span>
            </a>
        </div>
    </div>

    {{-- Getting Started Section --}}
    <div id="getting-started" class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
            <span class="bg-blue-100 text-blue-600 rounded-full w-10 h-10 flex items-center justify-center mr-3">1</span>
            Getting Started
        </h2>
        
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold mb-3">Overview</h3>
                <p class="text-gray-600 mb-4">The Budget Management System provides comprehensive tools for managing organizational budgets with flexibility and control. Key features include:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-600 ml-4">
                    <li>Monthly and quarterly budget allocations</li>
                    <li>Budget advances for urgent needs</li>
                    <li>Supplementary budget requests</li>
                    <li>Automatic and manual rollover options</li>
                    <li>Real-time alerts and monitoring</li>
                    <li>Complete audit trail</li>
                </ul>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded">
                <h4 class="font-semibold text-blue-900 mb-2">Quick Start Steps:</h4>
                <ol class="list-decimal list-inside space-y-2 text-blue-800">
                    <li>Navigate to Budget Management → Allocations</li>
                    <li>Select your budget and year</li>
                    <li>Click "Setup Allocations" to create monthly distributions</li>
                    <li>Monitor utilization through the dashboard</li>
                    <li>Use quick actions for advances, transfers, or supplementary requests</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Monthly Allocations Section --}}
    <div id="allocations" class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
            <span class="bg-green-100 text-green-600 rounded-full w-10 h-10 flex items-center justify-center mr-3">2</span>
            Monthly Allocations
        </h2>

        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold mb-3">Setting Up Allocations</h3>
                <p class="text-gray-600 mb-4">Allocations distribute your annual budget across periods (monthly or quarterly).</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold mb-2">Equal Distribution</h4>
                    <p class="text-sm text-gray-600 mb-3">Divides budget equally across all periods</p>
                    <div class="bg-gray-50 p-3 rounded">
                        <code class="text-xs">
                            Annual: 12,000,000<br>
                            Monthly: 1,000,000 (8.33% each)
                        </code>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold mb-2">Custom Distribution</h4>
                    <p class="text-sm text-gray-600 mb-3">Set different percentages per period</p>
                    <div class="bg-gray-50 p-3 rounded">
                        <code class="text-xs">
                            Q1: 20% each month<br>
                            Q2-Q4: 6.67% each month
                        </code>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded">
                <h4 class="font-semibold text-green-900 mb-2">Rollover Policies:</h4>
                <ul class="space-y-2 text-green-800">
                    <li><strong>AUTOMATIC:</strong> Unused funds automatically move to next period</li>
                    <li><strong>APPROVAL_REQUIRED:</strong> Need approval to roll unused funds</li>
                    <li><strong>NO_ROLLOVER:</strong> Unused funds are forfeited at period end</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Budget Advances Section --}}
    <div id="advances" class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
            <span class="bg-orange-100 text-orange-600 rounded-full w-10 h-10 flex items-center justify-center mr-3">3</span>
            Budget Advances
        </h2>

        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold mb-3">Understanding Budget Advances</h3>
                <p class="text-gray-600 mb-4">Budget advances allow you to borrow funds from future periods to meet current urgent needs.</p>
            </div>

            <div class="bg-orange-50 rounded-lg p-6">
                <h4 class="font-semibold mb-4">How Advances Work:</h4>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <span class="bg-orange-200 text-orange-700 rounded-full w-6 h-6 flex items-center justify-center mr-3 mt-1 flex-shrink-0">1</span>
                        <div>
                            <strong>Request:</strong> Specify amount and source period
                            <p class="text-sm text-gray-600">Example: Need 200,000 in March, borrow from April</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="bg-orange-200 text-orange-700 rounded-full w-6 h-6 flex items-center justify-center mr-3 mt-1 flex-shrink-0">2</span>
                        <div>
                            <strong>Approval:</strong> Request goes through approval workflow
                            <p class="text-sm text-gray-600">Department head → Finance → Final approval</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="bg-orange-200 text-orange-700 rounded-full w-6 h-6 flex items-center justify-center mr-3 mt-1 flex-shrink-0">3</span>
                        <div>
                            <strong>Disbursement:</strong> Funds added to current period
                            <p class="text-sm text-gray-600">Available immediately after approval</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="bg-orange-200 text-orange-700 rounded-full w-6 h-6 flex items-center justify-center mr-3 mt-1 flex-shrink-0">4</span>
                        <div>
                            <strong>Repayment:</strong> Automatic or manual repayment
                            <p class="text-sm text-gray-600">Deducted from source period when it arrives</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-l-4 border-yellow-500 bg-yellow-50 p-4">
                <p class="text-sm"><strong>⚠️ Important:</strong> Advances reduce future period availability. Plan carefully to avoid future shortfalls.</p>
            </div>
        </div>
    </div>

    {{-- Supplementary Requests Section --}}
    <div id="supplementary" class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
            <span class="bg-purple-100 text-purple-600 rounded-full w-10 h-10 flex items-center justify-center mr-3">4</span>
            Supplementary Requests
        </h2>

        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold mb-3">When to Use Supplementary Budgets</h3>
                <p class="text-gray-600 mb-4">Supplementary budgets provide additional allocation when the original budget is insufficient.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div class="bg-purple-50 rounded-lg p-4">
                    <h4 class="font-semibold text-purple-900 mb-2">Urgency Levels</h4>
                    <ul class="space-y-1 text-sm">
                        <li><span class="text-red-600">● CRITICAL:</span> Immediate</li>
                        <li><span class="text-orange-600">● HIGH:</span> Within 48 hours</li>
                        <li><span class="text-yellow-600">● MEDIUM:</span> Within a week</li>
                        <li><span class="text-green-600">● LOW:</span> Regular process</li>
                    </ul>
                </div>

                <div class="bg-purple-50 rounded-lg p-4">
                    <h4 class="font-semibold text-purple-900 mb-2">Funding Sources</h4>
                    <ul class="space-y-1 text-sm">
                        <li>● Reserves</li>
                        <li>● Reallocation</li>
                        <li>● External funding</li>
                        <li>● Other sources</li>
                    </ul>
                </div>

                <div class="bg-purple-50 rounded-lg p-4">
                    <h4 class="font-semibold text-purple-900 mb-2">Required Info</h4>
                    <ul class="space-y-1 text-sm">
                        <li>● Amount needed</li>
                        <li>● Detailed justification</li>
                        <li>● Supporting documents</li>
                        <li>● Impact analysis</li>
                    </ul>
                </div>
            </div>

            <div class="bg-purple-100 rounded-lg p-4">
                <h4 class="font-semibold mb-2">Approval Workflow:</h4>
                <div class="flex items-center justify-between text-sm">
                    <div class="text-center">
                        <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center mb-2 mx-auto">1</div>
                        <span>Request</span>
                    </div>
                    <span>→</span>
                    <div class="text-center">
                        <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center mb-2 mx-auto">2</div>
                        <span>Dept Head</span>
                    </div>
                    <span>→</span>
                    <div class="text-center">
                        <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center mb-2 mx-auto">3</div>
                        <span>Finance</span>
                    </div>
                    <span>→</span>
                    <div class="text-center">
                        <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center mb-2 mx-auto">4</div>
                        <span>Final</span>
                    </div>
                    <span>→</span>
                    <div class="text-center">
                        <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center mb-2 mx-auto">✓</div>
                        <span>Approved</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts & Monitoring Section --}}
    <div id="alerts" class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
            <span class="bg-red-100 text-red-600 rounded-full w-10 h-10 flex items-center justify-center mr-3">5</span>
            Alerts & Monitoring
        </h2>

        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold mb-3">Automatic Alert System</h3>
                <p class="text-gray-600 mb-4">The system automatically monitors budgets and creates alerts for various conditions.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold mb-3">Alert Types</h4>
                    <div class="space-y-2">
                        <div class="flex items-center p-2 bg-red-50 rounded">
                            <span class="bg-red-500 w-3 h-3 rounded-full mr-3"></span>
                            <div class="text-sm">
                                <strong>OVERSPENT:</strong> Budget exceeded 100%
                            </div>
                        </div>
                        <div class="flex items-center p-2 bg-yellow-50 rounded">
                            <span class="bg-yellow-500 w-3 h-3 rounded-full mr-3"></span>
                            <div class="text-sm">
                                <strong>WARNING:</strong> Approaching limit (80%+)
                            </div>
                        </div>
                        <div class="flex items-center p-2 bg-blue-50 rounded">
                            <span class="bg-blue-500 w-3 h-3 rounded-full mr-3"></span>
                            <div class="text-sm">
                                <strong>MILESTONE:</strong> Important events
                            </div>
                        </div>
                        <div class="flex items-center p-2 bg-gray-50 rounded">
                            <span class="bg-gray-500 w-3 h-3 rounded-full mr-3"></span>
                            <div class="text-sm">
                                <strong>PERIOD_END:</strong> Period closing soon
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-semibold mb-3">Alert Actions</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li>✓ View alert details and context</li>
                        <li>✓ Mark as acknowledged</li>
                        <li>✓ Take corrective action</li>
                        <li>✓ Add resolution notes</li>
                        <li>✓ Set up email notifications</li>
                        <li>✓ Export alert history</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Best Practices Section --}}
    <div id="best-practices" class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
            <span class="bg-green-100 text-green-600 rounded-full w-10 h-10 flex items-center justify-center mr-3">6</span>
            Best Practices
        </h2>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Do's ✅</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li>✓ Review allocations monthly</li>
                    <li>✓ Set up alerts for critical thresholds</li>
                    <li>✓ Document all supplementary requests</li>
                    <li>✓ Plan advances with repayment in mind</li>
                    <li>✓ Use rollovers to optimize utilization</li>
                    <li>✓ Monitor utilization trends</li>
                    <li>✓ Keep audit trail complete</li>
                </ul>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Don'ts ❌</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li>✗ Ignore budget alerts</li>
                    <li>✗ Advance more than 30% of future allocation</li>
                    <li>✗ Skip approval workflows</li>
                    <li>✗ Leave rollovers unprocessed</li>
                    <li>✗ Exceed budget without supplementary</li>
                    <li>✗ Delete audit records</li>
                    <li>✗ Modify locked periods</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- FAQ Section --}}
    <div id="faq" class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Frequently Asked Questions</h2>

        <div class="space-y-4">
            <details class="border-b pb-4">
                <summary class="font-semibold cursor-pointer hover:text-blue-600">Q: What happens to unused budget at month end?</summary>
                <p class="mt-2 text-gray-600 pl-4">A: It depends on your rollover policy. With AUTOMATIC, funds roll to next month automatically. With APPROVAL_REQUIRED, you need approval. With NO_ROLLOVER, unused funds are forfeited.</p>
            </details>

            <details class="border-b pb-4">
                <summary class="font-semibold cursor-pointer hover:text-blue-600">Q: Can I borrow from multiple future periods?</summary>
                <p class="mt-2 text-gray-600 pl-4">A: Yes, you can create multiple advance requests from different periods, but each requires separate approval and tracking.</p>
            </details>

            <details class="border-b pb-4">
                <summary class="font-semibold cursor-pointer hover:text-blue-600">Q: How long does supplementary approval take?</summary>
                <p class="mt-2 text-gray-600 pl-4">A: CRITICAL requests are fast-tracked (same day). HIGH takes 48 hours. MEDIUM takes up to a week. LOW follows regular approval timeline.</p>
            </details>

            <details class="border-b pb-4">
                <summary class="font-semibold cursor-pointer hover:text-blue-600">Q: Can I cancel an advance request?</summary>
                <p class="mt-2 text-gray-600 pl-4">A: Yes, if it's still pending approval. Once approved and disbursed, it must be repaid according to the agreement.</p>
            </details>

            <details class="border-b pb-4">
                <summary class="font-semibold cursor-pointer hover:text-blue-600">Q: What triggers an OVERSPENT alert?</summary>
                <p class="mt-2 text-gray-600 pl-4">A: When utilization reaches or exceeds 100% of allocated budget for the period.</p>
            </details>
        </div>
    </div>

    {{-- Support Section --}}
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-8 text-white">
        <h2 class="text-2xl font-bold mb-4">Need Help?</h2>
        <p class="mb-6">If you need additional assistance or have questions not covered in this guide:</p>
        <div class="grid md:grid-cols-3 gap-4">
            <div class="bg-white/10 rounded-lg p-4">
                <h3 class="font-semibold mb-2">📧 Email Support</h3>
                <p class="text-sm">finance@saccos.org</p>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
                <h3 class="font-semibold mb-2">📞 Phone Support</h3>
                <p class="text-sm">+255 123 456 789</p>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
                <h3 class="font-semibold mb-2">💬 Live Chat</h3>
                <p class="text-sm">Available 9am-5pm EAT</p>
            </div>
        </div>
    </div>
</div>

{{-- Floating Action Button for Quick Help --}}
<div class="fixed bottom-6 right-6">
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="bg-blue-900 text-white rounded-full p-4 shadow-lg hover:bg-blue-700 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>
</div>