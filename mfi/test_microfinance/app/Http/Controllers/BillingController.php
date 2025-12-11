<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Service;
use App\Models\Member;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BillingController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index()
    {
        $bills = Bill::with(['sacco', 'member', 'service'])
            ->latest()
            ->paginate(10);

        return view('billing.index', compact('bills'));
    }

    public function create()
    {
        $services = Service::all();
        $members = Member::all();
        return view('billing.create', compact('services', 'members'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sacco_id' => 'required|exists:saccos,id',
            'member_id' => 'required|exists:members,id',
            'service_id' => 'required|exists:services,id',
            'amount' => 'required|numeric|min:0',
            'is_recurring' => 'required|boolean',
            'payment_mode' => 'required|in:1,2,3,4,5',
            'due_date' => 'required|date|after:today',
            'is_mandatory' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $bill = $this->billingService->createBill([
                'sacco_id' => $request->sacco_id,
                'member_id' => $request->member_id,
                'service_id' => $request->service_id,
                'amount' => $request->amount,
                'is_recurring' => $request->is_recurring,
                'payment_mode' => $request->payment_mode,
                'due_date' => $request->due_date,
                'is_mandatory' => $request->is_mandatory ?? false,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('billing.show', $bill)
                ->with('success', 'Bill created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create bill: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Bill $bill)
    {
        $bill->load(['sacco', 'member', 'service', 'payments']);
        return view('billing.show', compact('bill'));
    }

    public function processPayment(Request $request, Bill $bill)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'payment_channel' => 'required|string',
            'payment_ref' => 'required|string|unique:payments,payment_ref'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $payment = $this->billingService->processPayment($bill->control_number, [
                'amount' => $request->amount,
                'payment_channel' => $request->payment_channel,
                'payment_ref' => $request->payment_ref
            ]);

            return redirect()->route('billing.show', $bill)
                ->with('success', 'Payment processed successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to process payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function checkStatus($controlNumber)
    {
        try {
            $status = $this->billingService->getBillStatus($controlNumber);
            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function handlePaymentNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'control_number' => 'required|string|size:13',
            'payment_ref' => 'required|string|unique:payments,payment_ref',
            'amount' => 'required|numeric|min:0',
            'payment_channel' => 'required|string',
            'paid_at' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $notification = $this->billingService->handlePaymentNotification($request->all());
            return response()->json(['message' => 'Payment notification processed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 