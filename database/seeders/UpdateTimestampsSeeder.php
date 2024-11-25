<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateTimestampsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $timestamp = '2024-10-07 10:00:00';

        DB::table('team_assignments')->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::table('sprints')->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::table('phases')->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::table('phase_assignments')->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::table('teams')->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::table('user_stories')->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::table('user_stories_assignments')->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::table('audit_logs')->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
