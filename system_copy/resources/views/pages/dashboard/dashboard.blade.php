<x-app-layout>
    @if($this->tab_id == 1 )
        <livewire:dashboard.dashboard/>
    @endif

    @if($this->tab_id == 2 )
        <livewire:recon-sy.services.services/>
    @endif

    @if($this->tab_id == 3 )
        <livewire:recon-sy.nodes.third-parties-nodes/>
    @endif

    @if($this->tab_id == 4 )
        <livewire:recon-sy.channels.channels/>

    @endif
    @if($this->tab_id == 5 )
        <livewire:recon-sy.files.files/>
    @endif
    @if($this->tab_id == 6 )
        <livewire:recon-sy.clients.clients/>
    @endif
    @if($this->tab_id == 7 )

        <livewire:recon-sy.approvals.approvals-processor/>
    @endif
    @if($this->tab_id == 8 )
        <livewire:recon-sy.settings.users/>
    @endif

</x-app-layout>
