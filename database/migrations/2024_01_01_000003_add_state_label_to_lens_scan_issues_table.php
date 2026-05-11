<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lens_scan_issues', function (Blueprint $table) {
            $table->string('state_label', 100)->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('lens_scan_issues', function (Blueprint $table) {
            $table->dropColumn('state_label');
        });
    }
};
