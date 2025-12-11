<div>
    <div class="mt-2 block" style="height: 150px">

        <livewire:livewire-pie-chart
                key="{{ $theChartModel->reactiveKey() }}"
                :pie-chart-model="$theChartModel"
        />
    </div>
</div>
