<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\University;

class ImportUniversities extends Command
{
    protected $signature = 'import:universities';
    protected $description = 'Import universities from external API';

    public function handle()
    {
        $this->info('Fetching university data...');

        $response = Http::get('http://universities.hipolabs.com/search?country=Indonesia');

        if (!$response->successful()) {
            $this->error('Failed to fetch universities.');
            return;
        }

        $universities = $response->json();

        foreach ($universities as $uni) {
            University::updateOrCreate(
                ['name' => $uni['name']],
                [
                    'slug'     => Str::slug($uni['name']),
                    'city'     => $uni['state-province'],
                    'province' => null,
                    'website'  => $uni['web_pages'][0] ?? null,
                ]
            );
        }

        $this->info('✅ Universities imported successfully: ' . count($universities));
    }
}
