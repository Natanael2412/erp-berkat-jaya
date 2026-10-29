<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->boolean('allows_decimal')->default(false)->after('name');
        });

        // Set satuan yang relevan untuk desimal (seperti kabel meteran, dll)
        DB::table('units')
            ->whereIn(DB::raw('UPPER(code)'), ['M', 'METER', 'KG', 'L', 'LTR'])
            ->update(['allows_decimal' => true]);
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('allows_decimal');
        });
    }
};
