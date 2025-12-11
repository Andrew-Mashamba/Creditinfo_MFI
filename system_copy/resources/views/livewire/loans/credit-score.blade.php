{{-- CREDIT SCORE SECTION --}}
<p for="stability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">CREDIT SCORE</p>

<div id="stability" class="w-full bg-gray-50 rounded rounded-lg shadow-sm p-1 mb-4">
    <div class="w-full bg-white rounded-lg shadow-sm p-2 flex flex-col items-center justify-center">
        <canvas id="demo" width="400" height="200"></canvas>
        <div id="preview-textfield" class="text-center mt-2 font-bold"></div>

        <!-- Additional Credit Score Information -->
        <div class="w-full mt-3 text-center">
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="bg-gray-50 p-2 rounded">
                    <div class="font-semibold text-gray-700">Score</div>
                    <div class="text-lg font-bold credit-score-value">
                        {{ $creditScoreValue }}
                    </div>
                </div>
                <div class="bg-gray-50 p-2 rounded">
                    <div class="font-semibold text-gray-700">Grade</div>
                    <div class="text-lg font-bold credit-score-grade">
                        {{ $creditScoreGrade }}
                    </div>
                </div>
            </div>

            <div class="mt-2 p-2 bg-gray-50 rounded">
                <div class="font-semibold text-gray-700 text-xs">Risk Level</div>
                <div class="text-sm font-medium credit-score-risk">
                    {{ $creditScoreRisk }}
                </div>
            </div>

            <div class="mt-2 p-2 bg-gray-50 rounded">
                <div class="font-semibold text-gray-700 text-xs">Trend</div>
                <div class="text-sm font-medium text-gray-600">
                    {{ $creditScoreTrend }}
                </div>
            </div>

            @if(isset($creditScore['reasons']) && is_array($creditScore['reasons']))
            <div class="mt-2 p-2 bg-gray-50 rounded">
                <div class="font-semibold text-gray-700 text-xs">Key Factors</div>
                <div class="text-xs text-gray-600">
                    @foreach(array_slice($creditScore['reasons'], 0, 2) as $reason)
                    <div class="mb-1">• {{ $reason }}</div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
document.addEventListener('livewire:load', function () {
    initializeCreditScoreGauge();
});

document.addEventListener('livewire:update', function () {
    initializeCreditScoreGauge();
});

function initializeCreditScoreGauge() {
    // Credit Score Gauge Implementation
    const canvas = document.getElementById('demo');
    if (canvas && typeof Gauge !== 'undefined') {
        const creditScore = parseInt(document.querySelector('.credit-score-value')?.textContent || '500');
        
        const gaugeConfig = {
            angle: 0.15,
            lineWidth: 0.44,
            radiusScale: 1,
            pointer: {
                length: 0.6,
                strokeWidth: 0.035,
                color: '#000000'
            },
            limitMax: false,
            limitMin: false,
            colorStart: '#6FADCF',
            colorStop: '#8FC0DA',
            strokeColor: '#E0E0E0',
            generateGradient: true,
            highDpiSupport: true,
            percentColors: [[0.0, "#ff0000" ], [0.50, "#f9c802"], [1.0, "#a9d70b"]],
            renderTicks: {
                divisions: 5,
                divWidth: 1.1,
                divLength: 0.7,
                divColor: '#333333',
                subDivisions: 3,
                subLength: 0.5,
                subWidth: 0.6,
                subColor: '#666666'
            }
        };
        
        // Create gauge
        const gauge = new Gauge(canvas).setOptions(gaugeConfig);
        
        // Set the credit score (normalized to 0-1 range, assuming max score is 1000)
        gauge.maxValue = 1000;
        gauge.setMinValue(0);
        gauge.animationSpeed = 32;
        gauge.set(creditScore);
        
        // Update preview text
        const previewText = document.getElementById('preview-textfield');
        if (previewText) {
            previewText.textContent = `Credit Score: ${creditScore}`;
        }
    }
}

// Initialize on page load if Livewire is already loaded
if (window.Livewire) {
    initializeCreditScoreGauge();
}
</script>