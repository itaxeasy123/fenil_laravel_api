<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfData extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'pan',
        'gross_salary',
        'less_allowance',
        'total_salary',
        'total_deduction_16',
        'income_chargeable',
        'gross_total_income',
        'total_deduction_80',
        'aggregate_of_deductible',
        'Total_taxable_income',
        'Tax_on_total_income',
        'Health_and_education_cess',
        'Net_tax_payable',
    ];
}
