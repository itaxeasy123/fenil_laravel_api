<?php

namespace App\Http\Controllers;

use App\Models\PdfData;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Validator;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Traits\ResponseAPI;

class ServiceController extends Controller
{
     use ResponseAPI;
    public function extract(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf' => 'required|mimes:pdf',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 401);
        }

        $parser = new Parser();
        $pdf = $parser->parseFile($request->pdf);

        // $data = $pdf->getText();

        // $data = $pdf->getPages()[4]->getDataTm();
        // return $data;

        // $data[] = $pdf->getPages()[0]->getDataTm()[16][1];
        // $data[] = $pdf->getPages()[0]->getDataTm()[18][1];
        try {

            $data['Name'] = $pdf->getPages()[2]->getDataTm()[11][1];
            $data['Address'] = $pdf->getPages()[2]->getDataTm()[12][1] . " " . $pdf->getPages()[2]->getDataTm()[13][1];
            $data['PAN_of_the_Employee'] = $pdf->getPages()[2]->getDataTm()[19][1];
            $data['Gross_Salary'] = $pdf->getPages()[2]->getDataTm()[46][1];
            $data['Less_Allowances_to_the_extent_exempt_under_section_10'] = $pdf->getPages()[3]->getDataTm()[15][1];
            $data['Total_amount_of_salary_received_from_current_employer_[1(d)-2(h)]'] = $pdf->getPages()[3]->getDataTm()[17][1];
            $data['Total_amount_of_deductions_under_section_16_[4(a)+4(b)+4(c)]'] = $pdf->getPages()[3]->getDataTm()[35][1];
            $data['Income_chargeable_under_the_head_Salaries_[(3+1(e)-5]'] = $pdf->getPages()[3]->getDataTm()[36][1];
            $data['Gross_total_income_(6+8)'] = $pdf->getPages()[3]->getDataTm()[49][1];
            $data['Total_deduction_under_section_80C,_80CCC_and_80CCD(1)'] = ($pdf->getPages()[3]->getDataTm()[73][1] < 150000) ? $pdf->getPages()[3]->getDataTm()[73][1] : 150000;
            $data['Aggregate_of_deductible_amount_under_Chapter_VI-A_[10(d)+10(e)+10(f)+10(g)+10(h)+10(i)+10(j)+10(l)]'] = $pdf->getPages()[4]->getDataTm()[39][1];
            $data['Total_taxable_income_(9-11)'] = $pdf->getPages()[4]->getDataTm()[44][1];

            $total = $pdf->getPages()[4]->getDataTm()[44][1];

            switch ($total) {
                case ($total > 250000 && $total <= 500000):
                    $total = (5 * $total) / 100;
                    break;

                case ($total > 500000 && $total <= 750000):
                    $total = (10 * $total) / 100;
                    break;

                case ($total > 750000 && $total <= 1000000):
                    $total = (15 * $total) / 100;
                    break;

                case ($total > 1000000 && $total <= 1250000):
                    $total = (20 * $total) / 100;
                    break;

                case ($total > 1250000 && $total <= 1500000):
                    $total = (25 * $total) / 100;
                    break;

                case ($total > 1500000):
                    $total = (30 * $total) / 100;
                    break;

                default:
                    $total = $total;
                    break;
            }

            $data['Tax_on_total_income'] = $total;
            $data['Health_and_education_cess'] = "";
            $data['Net_tax_payable_(17-18)'] = $pdf->getPages()[4]->getDataTm()[56][1];

            PdfData::create([
                'name' => $pdf->getPages()[2]->getDataTm()[11][1],
                'address' => $pdf->getPages()[2]->getDataTm()[12][1] . " " . $pdf->getPages()[2]->getDataTm()[13][1],
                'pan' => $pdf->getPages()[2]->getDataTm()[19][1],
                'gross_salary' =>  $pdf->getPages()[2]->getDataTm()[46][1],
                'less_allowance' =>  $pdf->getPages()[3]->getDataTm()[15][1],
                'total_salary' =>  $pdf->getPages()[3]->getDataTm()[17][1],
                'total_deduction_16' =>  $pdf->getPages()[3]->getDataTm()[35][1],
                'income_chargeable' =>  $pdf->getPages()[3]->getDataTm()[36][1],
                'gross_total_income' =>  $pdf->getPages()[3]->getDataTm()[49][1],
                'total_deduction_80' => ($pdf->getPages()[3]->getDataTm()[73][1] < 150000) ? $pdf->getPages()[3]->getDataTm()[73][1] : 150000,
                'aggregate_of_deductible' =>  $pdf->getPages()[4]->getDataTm()[39][1],
                'Total_taxable_income' => $pdf->getPages()[4]->getDataTm()[44][1],
                'Tax_on_total_income' =>  $total,
                'Health_and_education_cess' =>  "",
                'Net_tax_payable' =>   $pdf->getPages()[4]->getDataTm()[56][1]
            ]);
            $data['Salary_as_per_provisions_contained_in_section_17(1)'] = $pdf->getPages()[2]->getDataTm()[37][1];
            $data['Value_of_perquisites_under_section_17(2)'] = $pdf->getPages()[2]->getDataTm()[39][1];
            $data['Profits_in_lieu_of_salary_under_section_17(3)'] = $pdf->getPages()[2]->getDataTm()[43][1];
            $data['Reported_total_amount_of_salary_received_from_other_employer(s)'] = $pdf->getPages()[2]->getDataTm()[49][1];
            $data['Travel_concession_or_assistance_under_section_10(5)'] = $pdf->getPages()[2]->getDataTm()[52][1];
            $data['Death-cum-retirement_gratuity_under_section_10(10)'] = $pdf->getPages()[2]->getDataTm()[55][1];
            $data['Commuted_value_of_pension_under_section_10(10A)'] = $pdf->getPages()[2]->getDataTm()[64][1];
            $data['Cash_equivalent_of_leave_salary_encashment_under_section_10(10AA)'] = $pdf->getPages()[2]->getDataTm()[67][1];
            $data['House_rent_allowance_under_section_10(13A)'] = $pdf->getPages()[2]->getDataTm()[66][1];
            $data['Total_amount_of_any_other_exemption_under_section_10'] = $pdf->getPages()[3]->getDataTm()[14][1];
            $data['Total_amount_of_exemption_claimed_under_section_10[2(a)+2(b)+2(c)+2(d)+2(e)+2(g)]0.00(h)'] = $pdf->getPages()[3]->getDataTm()[15][1];
            $data['Standard_deduction_under_section_16(ia)'] = $pdf->getPages()[3]->getDataTm()[26][1];
            $data['Entertainment_allowance_under_section_16(ii)'] = $pdf->getPages()[3]->getDataTm()[23][1];
            $data['Tax_on_employment_under_section_16(iii)'] = $pdf->getPages()[3]->getDataTm()[29][1];
            $data['Income_(or_admissible_loss)_from_house_property_reported_by_employee_offered_for_TDS'] = $pdf->getPages()[3]->getDataTm()[42][1];
            $data['Income_under_the_head_Other_Sources_offered_for_TDS'] = $pdf->getPages()[3]->getDataTm()[46][1];
            $data['Total_amount_of_other_income_reported_by_the_employee'] = $pdf->getPages()[3]->getDataTm()[55][1];
            $data['Deduction_in_respect_of_life_insurance_premia,_contributions_to_provident_fund_etc._under_section_80C'] = $pdf->getPages()[3]->getDataTm()[68][1];
            $data['Deduction_in_respect_of_contribution_to_certain_pension_funds_under_section_80CCC'] = $pdf->getPages()[3]->getDataTm()[71][1];
            $data['Deduction_in_respect_of_contribution_by_taxpayer_to_pension_scheme_under_section_80CCD_(1)'] = $pdf->getPages()[3]->getDataTm()[78][1];
            $data['Total_deduction_under_section_80C,_80CCC_and_80CCD(1)'] = $pdf->getPages()[3]->getDataTm()[73][1];
            $data['Deductions_in_respect_of_amount_paid/deposited_to_notified_pension_scheme_under_section_80CCD_(1B)'] = $pdf->getPages()[3]->getDataTm()[59][1];
            $data['Deduction_in_respect_of_contribution_by_Employer_to_pension_scheme_under_section_80CCD_(2)'] = $pdf->getPages()[4]->getDataTm()[19][1];
            $data['Deduction_in_respect_of_health_insurance_premia_under_section_80D'] = $pdf->getPages()[4]->getDataTm()[23][1];
            $data['Deduction_in_respect_of_interest_on_loan_taken_for_higher_education_under_section_80E'] = $pdf->getPages()[4]->getDataTm()[25][1];
            $data['Total_Deduction_in_respect_of_donations_to_certain_funds,_charitable_institutions,_etc._under_section_80G'] = $pdf->getPages()[4]->getDataTm()[11][1];
            $data['Deduction_in_respect_of_interest_on_deposits_in_savings_account_under_section_80TTA'] = $pdf->getPages()[4]->getDataTm()[81][1];
            $data['Total_of_amount_deductible_under_any_other_provision(s)_of_Chapter_VI-A'] = $pdf->getPages()[4]->getDataTm()[34][1];
            $data['Rebate_under_section_87A,_if_applicable'] = $pdf->getPages()[4]->getDataTm()[48][1];
            $data['Surcharge,_wherever_applicable'] = $pdf->getPages()[4]->getDataTm()[51][1];
            $data['Tax_payable_(13+15+16-14)'] = $pdf->getPages()[4]->getDataTm()[52][1];
            $data['Less:_Relief_under_section_89_(attach_details)'] = $pdf->getPages()[4]->getDataTm()[53][1];
            return $this->success($data);
        } catch (Exception $e) {
            return $this->error("Something went wrong,please check pdf !", 401);
        }
    }

    public function extractData(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'form_data' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 401);
            }

        } catch (Exception $e) {
            return response()->json(['status' => 200, 'message' => "Something went wrong,please check pdf !"]);
        }
    }

    public function getExtractData()
    {
        try {
            $data = PdfData::all();
            return response()->json(['status' => 200, 'message' => "Data Extract Successfully !", 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => 200, 'message' => "Something went wrong,please check pdf !"]);
        }
    }

    public function merge(Request $request)
    {
        $file = new Filesystem;
        $file->cleanDirectory(public_path('PDFTemp'));

        $validator = Validator::make($request->all(), [
            'pdfs' => 'required',
            'pdfs.*' => 'required|mimes:pdf',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 401);
        }

        $pdf = PDFMerger::init();

        foreach ($request->file('pdfs') as $value) {
            $pdf->addPDF($value->getPathName(), 'all');
        }

        $fileName = time() . '.pdf';
        $pdf->merge();

        $path = public_path('PDFTemp/');

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $pdf->save($path . $fileName);

        return response()->download(public_path('PDFTemp/' . $fileName));
    }

    public function imageToPdf(Request $request)
    {
        File::deleteDirectory(public_path('images'));
        $validator = Validator::make($request->all(), [
            'images' => 'required',
            'images.*' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 401);
        }

        foreach ($request->file('images') as $image) {
            $imageName = time() . '.' . $image->extension();
            $image->move(public_path('images'), $imageName);
            $images[] = $imageName;
        }

        $data = ['images' => $images];
        $pdf = PDF::loadView('Pdf.images', $data);

        return $pdf->download("imagetopdf.pdf");
    }

    public function compress(Request $request)
    {
        return "Work in Progress !";
    }

    public function extractInvoice(Request $request)
    {
        return "Work in Progress !";
        // $validator = Validator::make($request->all(), [
        //     'pdf' => 'required|mimes:pdf',
        // ]);

        // if ($validator->fails()) {
        //     return response($validator->errors(), 401);
        // }

        // echo (new TesseractOCR('http://localhost/itaxesay/fenil_laravel_api/public/demo.jpg.jpg'))->run();
        //     echo (new TesseractOCR('http://localhost/itaxesay/fenil_laravel_api/public/demo.jpg.jpg'))
        // ->executable('C:/Program Files (x86)/Tesseract-OCR/tesseract')
        // ->run();

        $image = $request->image;
        $imageName = time() . '.' . $image->extension();
        $image->move(public_path(), $imageName);
        shell_exec('"C:\\Program Files (x86)\\Tesseract-OCR\\tesseract" "C:\\xampp\\htdocs\\itaxesay\\fenil_laravel_api\\public\\' . $imageName . '" C:\\xampp\\htdocs\\itaxesay\\fenil_laravel_api\\public\\out');

        $myfile = fopen(asset('out.txt'), "r") or die("Unable to open file!");
        return fread($myfile, filesize("out.txt"));
        fclose($myfile);
        // return (new TesseractOCR(asset('demo.jpg.jpg')))
        //     ->run();
        // $parser = new Parser();
        // $pdf = $parser->parseFile($request->pdf);

        // // $data = $pdf->getText();

        // $data = $pdf->getPages()[0]->getDataTm();
        // return $data;
    }
}
