<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                'name' => 'Membership Registration',
                'code' => 'REG',
                'description' => 'Initial registration fee for new members',
                'is_mandatory' => true,
                'lower_limit' => 50000,
                'upper_limit' => 100000,
                'payment_mode' => '2',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Monthly Membership Fee',
                'code' => 'MMF',
                'description' => 'Monthly membership contribution',
                'is_mandatory' => true,
                'lower_limit' => 10000,
                'upper_limit' => 50000,
                'payment_mode' => '2',
                'is_recurring' => true,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Emergency Loan',
                'code' => 'EML',
                'description' => 'Short-term emergency loan facility',
                'is_mandatory' => false,
                'lower_limit' => 100000,
                'upper_limit' => 1000000,
                'payment_mode' => '1',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Development Loan',
                'code' => 'DVL',
                'description' => 'Long-term development loan',
                'is_mandatory' => false,
                'lower_limit' => 500000,
                'upper_limit' => 5000000,
                'payment_mode' => '1',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Education Loan',
                'code' => 'EDL',
                'description' => 'Loan for educational purposes',
                'is_mandatory' => false,
                'lower_limit' => 200000,
                'upper_limit' => 2000000,
                'payment_mode' => '1',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Business Loan',
                'code' => 'BSL',
                'description' => 'Loan for business development',
                'is_mandatory' => false,
                'lower_limit' => 300000,
                'upper_limit' => 3000000,
                'payment_mode' => '1',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Voluntary Savings',
                'code' => 'VSA',
                'description' => 'Voluntary savings contribution',
                'is_mandatory' => false,
                'lower_limit' => 5000,
                'upper_limit' => null,
                'payment_mode' => '5',
                'is_recurring' => true,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Fixed Deposit',
                'code' => 'FXD',
                'description' => 'Fixed deposit savings account',
                'is_mandatory' => false,
                'lower_limit' => 100000,
                'upper_limit' => null,
                'payment_mode' => '3',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Insurance Premium',
                'code' => 'INS',
                'description' => 'Member insurance coverage premium',
                'is_mandatory' => true,
                'lower_limit' => 5000,
                'upper_limit' => 50000,
                'payment_mode' => '2',
                'is_recurring' => true,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Share Capital',
                'code' => 'SHC',
                'description' => 'Share capital contribution',
                'is_mandatory' => true,
                'lower_limit' => 100000,
                'upper_limit' => 1000000,
                'payment_mode' => '2',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Late Payment Fee',
                'code' => 'LPF',
                'description' => 'Penalty for late payments',
                'is_mandatory' => false,
                'lower_limit' => 5000,
                'upper_limit' => 50000,
                'payment_mode' => '2',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Loan Processing Fee',
                'code' => 'LNF',
                'description' => 'Fee for processing loan applications',
                'is_mandatory' => true,
                'lower_limit' => 10000,
                'upper_limit' => 100000,
                'payment_mode' => '2',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Annual General Meeting Fee',
                'code' => 'AGM',
                'description' => 'Fee for annual general meeting attendance',
                'is_mandatory' => true,
                'lower_limit' => 5000,
                'upper_limit' => 20000,
                'payment_mode' => '2',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Document Processing Fee',
                'code' => 'DPF',
                'description' => 'Fee for processing member documents',
                'is_mandatory' => false,
                'lower_limit' => 2000,
                'upper_limit' => 10000,
                'payment_mode' => '2',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()],
            [
                'name' => 'Withdrawal Fee',
                'code' => 'WDF',
                'description' => 'Fee for withdrawing savings',
                'is_mandatory' => false,
                'lower_limit' => 1000,
                'upper_limit' => 5000,
                'payment_mode' => '2',
                'is_recurring' => false,
                'created_at' => now(),
                'updated_at' => now()]
        ];

        foreach ($services as $service) {
            Service::create($service);
    }
}
}