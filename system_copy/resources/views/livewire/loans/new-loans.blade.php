<div class="bg-white rounded-2xl p-4">
    @if(Session::get('currentloanID'))
   
        <livewire:loans.loan-process />
    @else

        <livewire:loans.new-loans-table />
    @endif
</div>
