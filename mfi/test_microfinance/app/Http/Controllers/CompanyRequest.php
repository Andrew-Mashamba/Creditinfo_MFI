<?php

namespace App\Http\Controllers;

use App\Mail\CompanyRegistration;
use App\Models\institutions;
use App\Services\Security\SecureFileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CompanyRequest extends Controller
{
    /**
     * @var SecureFileUploadService
     */
    protected $fileUploadService;

    public function __construct()
    {
        $this->fileUploadService = new SecureFileUploadService();
    }

    public function index(){
        return view('saccossRegistration');
    }

    public function create(Request $request){
       // Validate all inputs including files with proper security rules
       $validated = $request->validate([
            'admin_email' => 'required|email',
            'manager_email' => 'required|email',
            'phone_number' => 'required|numeric|digits_between:9,15',
            'tin_number' => 'required|string|max:50',
            'tcdc_form' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240', // 10MB max
            'microfinance_license' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240', // 10MB max
            'region' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'wilaya' => 'required|string|max:255',
       ]);

        // Upload TCDC form securely
        $tcdcUpload = $this->fileUploadService->upload(
            $request->file('tcdc_form'),
            'Saccoss-request/tcdc-forms',
            [
                'allowed_extensions' => ['pdf', 'jpeg', 'jpg', 'png'],
                'max_size_kb' => 10240,
                'disk' => 'public',
                'visibility' => 'private',
                'generate_unique_name' => true
            ]
        );

        if (!$tcdcUpload['success']) {
            return back()
                ->withErrors(['tcdc_form' => $tcdcUpload['error']])
                ->withInput();
        }

        // Upload microfinance license securely
        $licenseUpload = $this->fileUploadService->upload(
            $request->file('microfinance_license'),
            'Saccoss-request/licenses',
            [
                'allowed_extensions' => ['pdf', 'jpeg', 'jpg', 'png'],
                'max_size_kb' => 10240,
                'disk' => 'public',
                'visibility' => 'private',
                'generate_unique_name' => true
            ]
        );

        if (!$licenseUpload['success']) {
            // Clean up already uploaded TCDC form
            $this->fileUploadService->delete($tcdcUpload['path'], 'public');

            return back()
                ->withErrors(['microfinance_license' => $licenseUpload['error']])
                ->withInput();
        }

        // Save institution registration to database
        try {
            DB::table('institutions')->insert([
                'admin_email' => $validated['admin_email'],
                'manager_email' => $validated['manager_email'],
                'phone_number' => $validated['phone_number'],
                'tin_number' => $validated['tin_number'],
                'tcdc_form' => $tcdcUpload['path'],  // Secure file path
                'microfinance_license' => $licenseUpload['path'],  // Secure file path
                'status' => 'PENDING',
                'name' => $validated['name'],
                'wilaya' => $validated['wilaya'],
                'region' => $validated['region']
            ]);

            // Send email notification
            try {
                Mail::to('percyegno@gmail.com')->send(new CompanyRegistration('company registration'));
            } catch (\Exception $e) {
                // Log email failure but don't stop the registration process
                Log::error('Failed to send registration email', [
                    'error' => $e->getMessage(),
                    'institution_name' => $validated['name']
                ]);
            }

            session()->flash('message', 'Request has been sent successfully');
            return back();

        } catch (\Exception $e) {
            // Clean up uploaded files if database insertion fails
            $this->fileUploadService->delete($tcdcUpload['path'], 'public');
            $this->fileUploadService->delete($licenseUpload['path'], 'public');

            Log::error('Failed to save institution registration', [
                'error' => $e->getMessage(),
                'institution_name' => $validated['name']
            ]);

            return back()
                ->withErrors(['error' => 'Failed to save registration. Please try again.'])
                ->withInput();
        }



    }
}
