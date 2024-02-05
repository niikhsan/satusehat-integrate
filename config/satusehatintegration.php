<?php

return [

    /*
     * This is the name of the table that will be created by the migration and
     * used by the Activity model shipped with this package.
     */
    'master_loinc_table_name' => 'master_loinc',
    'log_table_name' => 'satusehat_log',
    'token_table_name' => 'satusehat_token',
    'icd10_table_name' => 'satusehat_icd10',
    'icd9_table_name' => 'satusehat_icd9',
    'encounter_table_name' => 'satusehat_encounter',
    'procedure_table_name' => 'satusehat_procedure',
    'composition_table_name' => 'satusehat_composition', 
    'observationnadi_table_name' => 'satusehat_observationnadi',
    'observationrespirasi_table_name' => 'satusehat_observationrespirasi', 
    'observationsystol_table_name' => 'satusehat_observationsystol',
    'observationdiastol_table_name' => 'satusehat_observationdiastol', 
    'observationsuhu_table_name' => 'satusehat_observationsuhu',

    /*
     * This is the database connection that will be used by the migration and
     * the Activity model shipped with this following Laravel's database.default
     * If not set, it will use mysql instead.
     */
    'database_connection' => env('DB_CONNECTION', 'mysql'),
];
