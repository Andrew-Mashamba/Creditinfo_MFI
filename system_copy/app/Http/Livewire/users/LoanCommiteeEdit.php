<?php

namespace App\Http\Livewire\Users;

use App\Models\CommitteeUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Models\Committee;
use App\Models\User;

class LoanCommiteeEdit extends Component
{
    public $committee_id;
    public $loan_category;
    public $description;
    public $user_list = []; // Users already assigned to the committee
    public $users; // All users in the system
    public $committee_list;
    public $committee;

    public function mount()
    {
        $this->committee_list =  Committee::get();
        $this->users = User::all();


    }

    public function toggleUser($userId)
    {
        // Debug to check if committee_id and user_id are valid
        if (is_null($this->committee_id) || is_null($userId)) {
            dd('Committee ID or User ID is null.');
        }

        $exists = DB::table('committee_users')
            ->where('committee_id', $this->committee_id)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            // If the user is already in the committee, remove them
            DB::table('committee_users')
                ->where('committee_id', $this->committee_id)
                ->where('user_id', $userId)
                ->delete();

            // Remove user from user_list
            $this->user_list = array_diff($this->user_list, [$userId]);
        } else {

            // Debugging insert operation



            try {
               $out = DB::table('committee_users')->insert([

                    'committee_id' => $this->committee_id,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                dd($out);
                //Log::error('Error inserting user into committee: ' . $out);

                $this->user_list[] = $userId;
            } catch (\Exception $e) {
                // Log the error message for debugging
                dd('Error inserting user into committee: ' . $e->getMessage());
            }
        }
    }



    public function save()
    {
        // Ensure a committee is selected
        if (!$this->committee_id) {
            session()->flash('message', 'Please select a committee.');
            return;
        }

        // Find the selected committee
        $committee = Committee::findOrFail($this->committee_id);



        // Sync the users with the committee using the committee_users table
        //DB::table('committee_users')->where('committee_id', $this->committee_id)->delete();

        // Insert new user associations
        //dd($this->user_list );

//        foreach ($this->user_list as $userId) {
//            DB::table('committee_users')->insert([
//                'committee_id' => $this->committee_id,
//                'user_id' => $userId,
//                'created_at' => now(),
//                'updated_at' => now(),
//            ]);
//        }

        // Save the description and category
        $committee->update([
            'description' => $this->description,
            'loan_category' => $this->loan_category,
        ]);

        // Success message
        session()->flash('message', 'Committee members updated successfully.');
        session()->flash('alert-class', 'alert-success');
    }

    function updated($key,$value){

        if($key=='committee_id' and $value !='' ){
            // Find the selected committee
            $this->committee = Committee::findOrFail($value);
            $this->loan_category = $this->committee->loan_category;
            $this->user_list = DB::table('committee_users')
                ->where('committee_id', $value)
                ->pluck('user_id')
                ->toArray();
            //dd($this->user_list);
        }
    }


    public function render()
    {


        return view('livewire.users.loan-commitee-edit');
    }
}
