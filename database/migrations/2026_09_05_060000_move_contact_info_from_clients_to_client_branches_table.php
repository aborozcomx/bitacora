<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('client_branches', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('address');
            $table->string('email')->nullable()->after('phone');
        });

        // Copy existing contact information from client to its branches if available
        $clients = DB::table('clients')->get();
        foreach ($clients as $client) {
            if (! empty($client->contact_name) || ! empty($client->email)) {
                DB::table('client_branches')
                    ->where('client_id', $client->id)
                    ->whereNull('contact_name')
                    ->update([
                        'contact_name' => $client->contact_name,
                        'email' => $client->email,
                    ]);
            }
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'phone', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('code');
            $table->string('phone')->nullable()->after('contact_name');
            $table->string('email')->nullable()->after('phone');
        });

        Schema::table('client_branches', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'email']);
        });
    }
};
