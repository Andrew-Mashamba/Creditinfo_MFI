<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Share Purchase',
                'code' => 'SHC',
                'description' => 'Purchase of SACCO shares by members',
                'is_mandatory' => true,
                'lower_limit' => 1000,
                'upper_limit' => 1000000,
                'isRecurring' => 1,
                'paymentMode' => '5', // Infinity
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Loan Application Fee',
                'code' => 'LON',
                'description' => 'Application fee for SACCO loans',
                'is_mandatory' => false,
                'lower_limit' => 50000,
                'upper_limit' => 50000000,
                'isRecurring' => 0,
                'paymentMode' => '3', // Exact
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Loan Repayment',
                'code' => 'REP',
                'description' => 'Repayment of SACCO loans',
                'is_mandatory' => true,
                'lower_limit' => 1000,
                'upper_limit' => 1000000,
                'isRecurring' => 1,
                'paymentMode' => '4', // Limited
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Savings Deposit',
                'code' => 'SAV',
                'description' => 'Deposit to savings account',
                'is_mandatory' => false,
                'lower_limit' => 1000,
                'upper_limit' => 10000000,
                'isRecurring' => 1,
                'paymentMode' => '5', // Infinity
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Registration Fee',
                'code' => 'REG',
                'description' => 'New member registration fee',
                'is_mandatory' => true,
                'lower_limit' => 5000,
                'upper_limit' => 50000,
                'isRecurring' => 0,
                'paymentMode' => '3', // Exact
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Annual Subscription',
                'code' => 'SUB',
                'description' => 'Annual membership subscription fee',
                'is_mandatory' => true,
                'lower_limit' => 10000,
                'upper_limit' => 100000,
                'isRecurring' => 1,
                'paymentMode' => '3', // Exact
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Insurance Premium',
                'code' => 'INS',
                'description' => 'Member insurance premium payment',
                'is_mandatory' => false,
                'lower_limit' => 5000,
                'upper_limit' => 500000,
                'isRecurring' => 1,
                'paymentMode' => '2', // Full
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Emergency Loan Repayment',
                'code' => 'EMG',
                'description' => 'Emergency loan repayment by members',
                'is_mandatory' => false,
                'lower_limit' => 10000,
                'upper_limit' => 1000000,
                'isRecurring' => 1,
                'paymentMode' => '1', // Partial
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Penalty Fee',
                'code' => 'PEN',
                'description' => 'Penalty fee for late payments or violations',
                'is_mandatory' => true,
                'lower_limit' => 1000,
                'upper_limit' => 100000,
                'isRecurring' => 0,
                'paymentMode' => '3', // Exact
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Building Fund Contribution',
                'code' => 'BLD',
                'description' => 'Contribution towards SACCO building or infrastructure projects',
                'is_mandatory' => false,
                'lower_limit' => 1000,
                'upper_limit' => 5000000,
                'isRecurring' => 1,
                'paymentMode' => '5', // Infinity
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Education Fund',
                'code' => 'EDU',
                'description' => 'Contribution to the SACCO education support fund',
                'is_mandatory' => false,
                'lower_limit' => 1000,
                'upper_limit' => 2000000,
                'isRecurring' => 1,
                'paymentMode' => '5', // Infinity
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Welfare Fund',
                'code' => 'WEL',
                'description' => 'Contribution to member welfare fund for emergencies',
                'is_mandatory' => false,
                'lower_limit' => 5000,
                'upper_limit' => 500000,
                'isRecurring' => 1,
                'paymentMode' => '5', // Infinity
                'created_at' => now(),
                'updated_at' => now()]
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['code' => $service['code']],
                $service
            );
    }
}
}