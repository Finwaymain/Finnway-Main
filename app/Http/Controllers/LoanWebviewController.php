<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoanWebviewController extends Controller
{
    public function comingSoon(Request $request)
    {
        $cardType = $request->input('card_type', 'Interest Free Loan');
        $title = $request->input('title', $cardType);
        $name = trim($request->input('name', 'Valued Customer'));
        if (empty($name)) {
            $name = 'Valued Customer';
        }
        $mobile = $request->input('mobile', '');
        $pocketNumber = $request->input('pocket_number', '');
        $amount = $request->input('amount', '');

        // Standardized product details
        $products = [
            'Interest Free Loan' => [
                'title' => 'Interest Free Loan',
                'badge' => '0% Interest',
                'limit' => 'Up to ₹2,00,000',
                'color' => '#16a34a',
                'bg_color' => '#f0fdf4',
                'border_color' => '#bbf7d0',
                'tag' => 'Instant Digital Credit',
                'description' => 'Zero-interest short-term credit facility tailored for your shopping, travel, and personal emergency needs.',
                'features' => [
                    '0% Interest rate for eligible repayment tenures',
                    'Instant approval directly into your Digital Pocket',
                    'No hidden processing or administrative charges',
                    '100% Paperless KYC & digital verification'
                ]
            ],
            '0 CIBIL Loan' => [
                'title' => '0 CIBIL Loan',
                'badge' => 'No Score Required',
                'limit' => 'Up to ₹5,00,000',
                'color' => '#2563eb',
                'bg_color' => '#eff6ff',
                'border_color' => '#bfdbfe',
                'tag' => 'First-Time Credit',
                'description' => 'Accessible credit lines for users with no prior credit history. Eligibility is evaluated using your Fiinway transaction activity.',
                'features' => [
                    'Zero CIBIL score or credit history required',
                    'High credit limit up to ₹5,00,000',
                    'Disbursal directly to wallet or bank account',
                    'Helps build your formal credit history'
                ]
            ],
            'Low CIBIL Loan' => [
                'title' => 'Low CIBIL Loan',
                'badge' => 'Fast Approval',
                'limit' => 'Express Disbursal',
                'color' => '#ea580c',
                'bg_color' => '#fff7ed',
                'border_color' => '#fed7aa',
                'tag' => 'Score Booster Loan',
                'description' => 'Specialized credit assistance for users with low or rebuilding credit profiles with transparent, easy EMIs.',
                'features' => [
                    'Approval designed for low credit score profiles',
                    'Fast-track verification and quick approval',
                    'Structured EMI repayment terms',
                    'Repay on time to boost your official CIBIL score'
                ]
            ],
        ];

        // Match case-insensitively or default
        $selectedProduct = null;
        foreach ($products as $k => $p) {
            if (strcasecmp($k, $cardType) === 0 || strcasecmp(str_replace(' ', '', $k), str_replace(' ', '', $cardType)) === 0) {
                $selectedProduct = $p;
                break;
            }
        }
        if (!$selectedProduct) {
            $selectedProduct = $products['Interest Free Loan'];
        }

        return view('finance.portal', compact(
            'cardType',
            'title',
            'name',
            'mobile',
            'pocketNumber',
            'amount',
            'selectedProduct'
        ));
    }

    public function portal(Request $request)
    {
        return $this->comingSoon($request);
    }
}
