<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class ServiceController extends Controller
{
    public function extract(Request $request)
    {

        $parser = new Parser();
        $pdf = $parser->parseFile($request->pdf);

        // $data = $pdf->getText();

        $data[] = $pdf->getPages()[0]->getDataTm()[16][1];
        $data[] = $pdf->getPages()[0]->getDataTm()[18][1];
        return $data;
        // return $request->all();
    }
}
