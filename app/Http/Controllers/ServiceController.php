<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Smalot\PdfParser\Parser;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use Barryvdh\DomPDF\Facade\Pdf;;

class ServiceController extends Controller
{
    public function extract(Request $request)
    { {

            $parser = new Parser();
            $pdf = $parser->parseFile($request->pdf);

            // $data = $pdf->getText();

            // $data = $pdf->getPages()[2]->getDataTm();
            // return $data;

            // $data[] = $pdf->getPages()[0]->getDataTm()[16][1];
            // $data[] = $pdf->getPages()[0]->getDataTm()[18][1];
            $data['Name'] = $pdf->getPages()[2]->getDataTm()[11][1];
            $data['Address'] = $pdf->getPages()[2]->getDataTm()[12][1] . " " . $pdf->getPages()[2]->getDataTm()[13][1];
            $data['PAN of the Employee'] = $pdf->getPages()[2]->getDataTm()[19][1];
            $data['Gross Salary'] = $pdf->getPages()[2]->getDataTm()[46][1];
            $data['Less: Allowances to the extent exempt under section 10'] = $pdf->getPages()[3]->getDataTm()[15][1];
            $data['Total amount of salary received from current employer
            [1(d)-2(h)]'] = $pdf->getPages()[3]->getDataTm()[17][1];
            $data['Total amount of deductions under section 16 [4(a)+4(b)+4(c)]'] = $pdf->getPages()[3]->getDataTm()[35][1];
            $data['Income chargeable under the head "Salaries" [(3+1(e)-5]'] = $pdf->getPages()[3]->getDataTm()[36][1];
            $data['Gross total income (6+8)'] = $pdf->getPages()[3]->getDataTm()[49][1];
            $data['Aggregate of deductible amount under Chapter VI-A
            [10(d)+10(e)+10(f)+10(g)+10(h)+10(i)+10(j)+10(l)]'] = $pdf->getPages()[4]->getDataTm()[39][1];
            $data['Total taxable income (9-11)'] = $pdf->getPages()[4]->getDataTm()[44][1];

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

            $data['Tax on total income'] = $total;
            $data['Health and education cess'] = "";
            $data['Net tax payable (17-18)'] = $pdf->getPages()[4]->getDataTm()[56][1];
            return $data;
            // return $request->all();
        }
    }

    public function merge(Request $request)
    {
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
        $pdf->save(public_path($fileName));

        return response()->download(public_path($fileName));
    }

    public function imageToPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required',
            'images.*' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 401);
        }

        // $images = $request->file('images');
        foreach ($request->file('images') as $image) {
            $imageName = time() . '.' . $image->extension();
            $image->move(public_path('images'), $imageName);
            $images[] = $imageName;
        }

        $data = ['images' => $images];
        $pdf = PDF::loadView('Pdf.images', $data);
        // $fileName = time() . '.pdf';
        // $pdf->save($fileName);
        // dd($fileName);
        // return view('Pdf.images',$data);
        return $pdf->download("123.pdf");
    }

    public function compress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required',
            // 'images.*' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 401);
        }
    }
}
