<?php

namespace App\Http\Controllers\calculator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Traits\ResponseAPI;
use Exception;
use Illuminate\Support\Arr;

class CalculatorController extends Controller
{
    //
    use ResponseAPI;


    public function incomeTaxNewRegime(Request $request)
    {
        try {
            $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/calculators/income-tax/new', $request->all());
            $data = json_decode($response);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function incomeTaxOldRegime(Request $request)
    {
        try {
            $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/calculators/income-tax/old', $request->all());
            $data = json_decode($response);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function advanceIncomeTaxOldRegime(Request $request)
    {
        try {
            $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/calculators/income-tax/advance-tax/old', $request->all());
            $data = json_decode($response);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function advanceIncomeTaxNewRegime(Request $request)
    {
        try {
            $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/calculators/income-tax/advance-tax/new', $request->all());
            $data = json_decode($response);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function calculateTDS(Request $request)
    {
        try {
            $response = Http::withHeaders(withSandBoxHeader())->post(env('SANDBOX_BASE_URL') . '/calculators/tds', $request->all());
            $data = json_decode($response);
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function calFDReturn(Request $request)
    {
        try {
            $interestEarned = 0;
            $yearlyCalculation = null;
            $monthlyCalculation = null;

            if ($request->type === "simple") {
                $interestEarned = ($request->principle * $request->rate * $request->year) / 100;
            } else {
                $calculationData = $this->calculateYearWiseInterestCompounded($request->principle, $request->rate, $request->year, $request->compoundFreqInYear);
                $rate = $request->rate / 100;
                $multiplier = (1 + ($rate / $request->compoundFreqInYear)) ** ($request->year * $request->compoundFreqInYear);
                $total = $request->principle * $multiplier;
                $interestEarned = $total - $request->principle;
            }

            $data = [
                "principle" => $request->principle,
                "interestEarned" => round($interestEarned),
                "yearWiseInterest" => $calculationData['yearlyCalculation'],
                "monthlyCalculation" => $calculationData['monthlyCalculation']
            ];
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function calculateYearWiseInterestCompounded($principle, $rate, $years, $compoundFreqInYear)
    {
        $yearlyCalculation = [];
        $monthlyCalculation = [];

        $rate = $rate / 100;
        $rate = $rate / $compoundFreqInYear;



        $monthlyPrinciple = $principle;
        // $monthlyInterest = interest / 12
        for ($k = 0; $k < $years * 12; $k++) {

            $monthlyInterest = ($principle * $rate) / (12 / $compoundFreqInYear);

            $totalInterest = 0;
            if (($k + 1) % 12 === 0) {
                $i = ($k + 1) / 12;

                $totalInterest = 0;
                for ($j = 0; $j < $compoundFreqInYear; $j++) {

                    $interest = ($principle + $totalInterest) * $rate;
                    $totalInterest += $interest;
                    // $principle = $principle + interest

                    if ($j === $compoundFreqInYear - 1) {
                        array_push($yearlyCalculation, [
                            "year" => $i,
                            "opening_balance" => round($principle),
                            "interest_earned" => round($totalInterest),
                            "closing_balance" => round($principle = $principle + $totalInterest)
                        ]);
                    }
                }
            }
            array_push($monthlyCalculation, [
                "month" => ($k + 1),
                "opening_balance" => round($monthlyPrinciple),
                "interest_earned" => round($monthlyInterest),
                "closing_balance" => round($monthlyPrinciple = $monthlyPrinciple + $monthlyInterest)
            ]);
        }

        $data = [
            'yearlyCalculation' => $yearlyCalculation,
            'monthlyCalculation' => $monthlyCalculation,
        ];
        return $data;
    }
    public function calSimpleInterest(Request $request)
    {
        try {
            $interest = ($request->principle * $request->rate * $request->year) / 100;

            $yearlyCalculation = [];
            $monthlyCalculation = [];

            $interestPerYer = $request->principle * ($request->rate / 100);

            $totalPrinciple = $request->principle;
            for ($i = 0; $i < $request->year * 12; $i++) {
                if (($i + 1) % 12 === 0) {
                    array_push($yearlyCalculation, [
                        "year" => ($i + 1) / 12,
                        "opening_balance" => round($request->principle),
                        "interest_earned" => round($interestPerYer),
                        "closing_balance" => round($principle = $request->principle + $interestPerYer)
                    ]);
                }
                array_push($monthlyCalculation, [
                    "month" => $i + 1,
                    "opening_balance" => round($totalPrinciple),
                    "interest_earned" => round($interestPerYer / 12),
                    "closing_balance" => round($totalPrinciple += ($interestPerYer / 12))
                ]);
            }

            $data = [
                "status" => "success",
                "principle" => $request->principle,
                "interestEarned" => round($interest),
                "yearlyCalculation" => $yearlyCalculation,
                "monthlyCalculation" => $monthlyCalculation
            ];

            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function calCompoundInterest(Request $request)
    {
        try {

            // ${principle, rate, year, compoundFreqInYear} = req.body
            $interestEarned = 0;

            $calculationData = $this->calculateYearWiseInterestCompounded($request->principle, $request->rate, $request->year, $request->compoundFreqInYear);
            $yearlyCalculation = $calculationData['yearlyCalculation'];
            $monthlyCalculation = $calculationData['monthlyCalculation'];

            $rate = $request->rate / 100;
            $multiplier = (1 + ($rate / $request->compoundFreqInYear)) ** ($request->year * $request->compoundFreqInYear);
            $total = $request->principle * $multiplier;
            $interestEarned = $total - $request->principle;

            $data = [
                "status" => "success",
                "principle" => $request->principle,
                "interestEarned" => round($interestEarned),
                "yearWiseInterest" => $yearlyCalculation,
                "monthlyCalculation" => $monthlyCalculation
            ];

            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function emi(Request $request)
    {
        try {

            // let {loanAmount, loanTenure, rate} = req.body
            $rate = $request->rate / 12 / 100;

            $loanTenure = $request->loanTenure * 12;

            $emi = $request->loanAmount * $rate * ((1 + $rate) ** $loanTenure) / (((1 + $rate) ** $loanTenure) - 1);

            $totalAmount = $emi * $loanTenure;

            $monthlyPayment = $this->calculateMonthlyEmiPayment($request->loanAmount, $rate, $loanTenure, $emi);

            $data = [
                "status" => "success",
                "emi" => round($emi),
                "loanAmount" => $request->loanAmount,
                "totalInterest" => round($totalAmount - $request->loanAmount),
                "totalAmount" => round($totalAmount),
                "monthlyPayment" => $monthlyPayment
            ];
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function calculateMonthlyEmiPayment($loanAmount, $rate, $loanTenure, $emi)
    {

        $monthlyCalculation = [];
        $totalLoanAmount = $loanAmount;

        for ($i = 0; $i < $loanTenure * 12; $i++) {
            $towardsInterest = $totalLoanAmount * $rate / (100 * 12);
            $towardsLoan = $emi - $towardsInterest;
            $totalLoanAmount = $totalLoanAmount - $towardsLoan;
            array_push($monthlyCalculation, [
                "month" => $i + 1,
                "emi" => round($emi),
                "towards_loan" => round($emi - $towardsInterest),
                "towards_interest" => round($towardsInterest),
                "outstanding_loan" => round($totalLoanAmount)
            ]);
        }

        return $monthlyCalculation;
    }
    public function homeLoanEligibility(Request $request)
    {
        try {
            return $this->success('data');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function depreciation(Request $request)
    {
        try {
            $residualRate = 1 - (($request->scrapValue / $request->purchasePrice) ** (1 / $request->estimatedUsefulLife));

            $data = [
                "status" => "success",
                "depreciationPercentage" => number_format(($residualRate * 100), 2) . '%',
                "costOfAsset" => $request->purchasePrice
            ];
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function mis(Request $request)
    {
        try {
            $monthlyIncome = $request->investmentAmount * ($request->interestRate / 1200);

            $data = [
                "status" => "success",
                "monthlyIncome" => $monthlyIncome
            ];
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function CAGR(Request $request)
    {
        try {
            return $this->success('data');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function sipGain(Request $request)
    {
        try {
            return $this->success('data');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function npsReturns(Request $request)
    {
        try {
            // let {monthlyInvestment, rate, currentAge} = req.body
            $rate = $request->rate / (100 * 12);
            $year = 60 - $request->currentAge;
            $invested = $request->monthlyInvestment * $year * 12;
            $yearlyGain = [];


            $multiplier = ((1 + $rate) ** ($year * 12) - 1) / $rate;
            $futureValue = $request->monthlyInvestment * $multiplier * (1 + $rate);

            $gains = $futureValue - $invested;

            $thisYearInterest = 0;
            for ($i = 0; $i < $year; $i++) {
                $previousYearInterest = $thisYearInterest;

                $multiplier = ((1 + $rate) ** (($i + 1) * 12) - 1) / $rate;
                $thisYearGain = $request->monthlyInvestment * $multiplier * (1 + $rate);
                $thisYearInterest = $thisYearGain - ($request->monthlyInvestment * 12 * ($i + 1));

                array_push($yearlyGain, [
                    "year" => $i + 1,
                    "investment_amount" => $request->monthlyInvestment * 12,
                    "interest_earned" => round($thisYearInterest - $previousYearInterest),
                    "maturity_amount" => round($thisYearGain)
                ]);
            }

            $data = [
                "status" => "success",
                "total" => round($futureValue),
                "invested" => $invested,
                "gain" => round($gains),
                "yearlyGain" => $yearlyGain
            ];
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function lumpSum(Request $request)
    {
        try {
            $amount = $request->invested * (1 + ($request->rate / 100)) ** $request->year;

            $calculationData = $this->calculateYearWiseInterestCompounded($request->invested, $request->rate, $request->year, 1);
            $yearlyCalculation = $calculationData['yearlyCalculation'];
            $monthlyCalculation = $calculationData['monthlyCalculation'];
            $data = [
                "status" => "success",
                "total" => round($amount),
                "invested" => $request->invested,
                "gain" => round($amount - $request->invested),
                "yearlyCalculation" => $yearlyCalculation,
                "monthlyCalculation" => $monthlyCalculation
            ];
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function recursiveDeposit(Request $request)
    {
        try {
            $sum = 0;
            $rate = $request->rate / 100;

            for ($i = 1; $i <= $request->months; $i++) {
                $sum = $sum + ($request->principle * ((1 + ($rate / 4)) ** (4 * ($i / $request->months))));
            }
            $data = [
                "status" => "success",
                "invested" => round($request->principle * $request->months),
                "interestEarned" => round($request->sum - ($request->principle * $request->months)),
                "amount" => round($request->sum)
            ];
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function hra(Request $request)
    {
        try {
            $hraSanctionedYearly = $request->hra * 12;
            $rentPaidYearly = $request->rentPaid * 12;
            $hraCalculation = $rentPaidYearly - ($request->basic * 12 * 0.10);

            $metroExemption = 0;
            if ($request->metroCity) {
                $metroExemption = $request->basic * 12 * 0.5;
            } else {
                $metroExemption = $request->basic * 12 * 0.4;
            }
            $hraExempted = 0;
            if ($hraCalculation < $metroExemption) {

                if ($hraCalculation < $hraSanctionedYearly)
                    $hraExempted = $hraCalculation;
                else
                    $hraExempted = $hraSanctionedYearly;
            } else {

                if ($metroExemption < $hraSanctionedYearly)
                    $hraExempted = $metroExemption;
                else
                    $hraExempted = $hraSanctionedYearly;
            }

            $data = [
                "status" => "success",
                "hraExempted" => $hraExempted
            ];
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function capitalGainCalculator(Request $request)
    {
        try {
            return $this->success('data');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function gstCalculator(Request $request)
    {
        try {
            $finalAmount = 0;
            $gstAmount = 0;

            if ($request->type === "excluding") {
                $gstAmount = $request->amount * $request->gstRate / 100;
                $finalAmount = $request->amount + $gstAmount;
            } else if ($request->type === "including") {
                $finalAmount = $request->amount;
                $amount = $request->amount / (1 + ($request->gstRate / 100));
                $gstAmount = $finalAmount - $request->amount;
            } else {
                $this->error("type parameter missing");
            }

            $data = [
                "status" => "success",
                "finalAmount" => number_format($finalAmount, 2),
                "gstAmount" => number_format($gstAmount, 2),
                "amount" => number_format($amount, 2),
                "gstType" => $request->type,
                "gstRate" => $request->gstRate . '%'
            ];
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
