<?php

namespace Niikhsan\SatusehatIntegrate;

use Illuminate\Support\ServiceProvider;

class SatusehatIntegrateServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish Config
        $this->publishes([
            __DIR__.'/../config/satusehatintegration.php' => config_path('satusehatintegration.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/satusehatintegration.php', 'satusehatintegration');

        // Publish Migrations for Token
        if (! class_exists('CreateSatusehatTokenTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_satusehat_token_table.php.stub' => database_path("/migrations/{$timestamp}_create_satusehat_token_table.php"),
            ], 'migrations');
        }

        // Publish Migrations for Log
        if (! class_exists('CreateSatusehatLogTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_satusehat_log_table.php.stub' => database_path("/migrations/{$timestamp}_create_satusehat_log_table.php"),
            ], 'migrations');
        }

        // Publish Migrations for ICD10
        if (! class_exists('CreateSatusehatIcd10Table')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_satusehat_icd10_table.php.stub' => database_path("/migrations/{$timestamp}_create_satusehat_icd10_table.php"),
            ], 'icd10');
        }

        // Publish ICD-10 csv data
        $this->publishes([
            __DIR__.'/../database/seeders/csv/icd10.csv.stub' => database_path('/seeds/csv/icd10.csv'),
        ], 'icd10');

        // Publish Seeder for ICD10
        if (! class_exists('Icd10Seeder')) {
            $this->publishes([
                __DIR__.'/../database/seeders/Icd10Seeder.php.stub' => database_path('/seeds/Icd10Seeder.php'),
            ], 'icd10');
        }

        // Publish ICD-9 csv data
        $this->publishes([
            __DIR__.'/../database/seeders/csv/icd9.csv.stub' => database_path('/seeds/csv/icd9.csv'),
        ], 'icd9');

        // Publish Seeder for ICD9
        if (! class_exists('Icd9Seeder')) {
            $this->publishes([
                __DIR__.'/../database/seeders/Icd9Seeder.php.stub' => database_path('/seeds/Icd9Seeder.php'),
            ], 'icd9');
        }

    }

    public function register()
    {
        //
    }
}
