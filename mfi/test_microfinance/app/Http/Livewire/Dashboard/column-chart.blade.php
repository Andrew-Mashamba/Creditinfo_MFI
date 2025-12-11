<div>
    {{-- Success is as dangerous as failure. --}}
    <div class="" style="height: 450px">
        <livewire:livewire-column-chart
                key="{{ $commoditiesChartModel->reactiveKey() }}"
                :column-chart-model="$commoditiesChartModel"
        />
    </div>

</div>
