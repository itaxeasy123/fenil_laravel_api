<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePdfDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdf_data', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('pan');
            $table->string('gross_salary')->default(0);
            $table->string('less_allowance')->default(0);
            $table->string('total_salary')->default(0);
            $table->string('total_deduction_16')->default(0);
            $table->string('income_chargeable')->default(0);
            $table->string('gross_total_income')->default(0);
            $table->string('total_deduction_80')->default(0);
            $table->string('aggregate_of_deductible')->default(0);
            $table->string('Total_taxable_income')->default(0);
            $table->string('Tax_on_total_income')->default(0);
            $table->string('Health_and_education_cess')->default(0);
            $table->string('Net_tax_payable')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pdf_data');
    }
}
