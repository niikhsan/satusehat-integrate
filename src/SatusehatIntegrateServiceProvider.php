<?php

namespace Niikhsan\SatusehatIntegrate;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use App;

class SatusehatIntegrateServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if($this->app->runningInConsole()) {

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

            // Publish Migrations for ICD9
            if (! class_exists('CreateSatusehatIcd9Table')) {
                $timestamp = date('Y_m_d_His', time());

                $this->publishes([
                    __DIR__.'/../database/migrations/create_satusehat_icd9_table.php.stub' => database_path("/migrations/{$timestamp}_create_satusehat_icd9_table.php"),
                ], 'icd9');
            }

            // Publish ICD-10 csv data
            $this->publishes([
                __DIR__.'/../database/seeds/csv/icd10.csv.stub' => database_path('/seeds/csv/icd10.csv'),
            ], 'icd10');

            // Publish Seeder for ICD10
            if (! class_exists('Icd10Seeder')) {
                $this->publishes([
                    __DIR__.'/../database/seeds/Icd10Seeder.php.stub' => database_path('/seeds/Icd10Seeder.php'),
                ], 'icd10');
            }

            // Publish ICD-9 csv data
            $this->publishes([
                __DIR__.'/../database/seeds/csv/icd9.csv.stub' => database_path('/seeds/csv/icd9.csv'),
            ], 'icd9');

            // Publish Seeder for ICD9
            if (! class_exists('Icd9Seeder')) {
                $this->publishes([
                    __DIR__.'/../database/seeds/Icd9Seeder.php.stub' => database_path('/seeds/Icd9Seeder.php'),
                ], 'icd9');
            }

            $this->registerSeedsFrom(__DIR__.'/database/seeds');
        }

    }

    public function register()
    {
        //
    }

    protected function registerSeedsFrom($path)
    {
        foreach (glob("$path/*.php") as $filename)
        {
            include $filename;
            $classes = get_declared_classes();
            $class = end($classes);

            $command = request()->server('argv', null);
            if (is_array($command)) {
                $command = implode(' ', $command);
                if ($command == "artisan db:seed") {
                    Artisan::call('db:seed', ['--class' => $class]);
                }
            }

        }
    }

    private function customValidation() {
        Validator::extend('alpha_spaces', function ($attribute, $value) {
            // This will only accept alpha and spaces.
            // If you want to accept hyphens use: /^[\pL\s-]+$/u.
            return preg_match('/^[\pL\s]+$/u', $value);
        },'The :attribute should be letters and spaces only');

        Validator::extend('alpha_num_spaces', function ($attribute, $value) {
            // This will only accept alphanumeric and spaces.
            return preg_match('/^[a-zA-Z0-9\s]+$/', $value);
        },'The :attribute should be alphanumeric characters and spaces only');
    }
}
