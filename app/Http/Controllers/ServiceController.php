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

class ServiceController extends Controller
{
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

        // $data = $pdf->getPages()[3]->getDataTm();
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
            return response()->json(['status' => 200, 'message' => "Data Extract Successfully !", 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => 200, 'message' => "Something went wrong,please check pdf !"]);
        }
    }

    public function merge(Request $request)
    {
        File::deleteDirectory(public_path('PDF'));

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

        $path = public_path('PDF/');

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 7777, true, true);
        }

        $pdf->save($path . $fileName);

        return response()->download(public_path('PDF/' . $fileName));
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
