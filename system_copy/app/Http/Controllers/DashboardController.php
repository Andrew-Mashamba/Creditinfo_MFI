<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Http;
    use App\Models\DataFeed;
    use Carbon\Carbon;

    class DashboardController extends Controller
    {
     public $tab_id;

        /**
         * Displays the dashboard screen
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
         */
        public function index(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
        {
            $this->tab_id = 1;
            $dataFeed = new DataFeed();

            return view('livewire.dashboard.dashboard', compact('dataFeed'));
        }
    }
