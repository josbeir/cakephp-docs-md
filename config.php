<?php
declare(strict_types=1);

/**
 * CakePHP Documentation Global Configuration
 *
 * This file contains the global configuration for the CakePHP documentation
 * conversion process, including branch definitions and conversion mappings.
 */

return [
    /**
     * Repository Configuration
     */
    'repository' => [
        'url' => 'https://github.com/cakephp/docs.git',
        'temp_base_dir' => 'temp/branches',
    ],

    /**
     * Branches to fetch from the repository
     *
     * List of branch names that should be fetched from the CakePHP docs repository.
     * These correspond to different versions of the CakePHP framework.
     */
    'branches' => [
        '2.x',
        '3.x',
        '4.x',
        '5.x',
    ],

    /**
     * Conversion Mappings
     *
     * Maps source directories (from fetched branches) to destination directories
     * for the converted markdown documentation.
     *
     * Format: 'source_path' => 'destination_path'
     *
     * Source paths are relative to the temp directory structure created by fetch-branches.php
     * Destination paths are relative to the project root where converted docs should be placed.
     */
    'conversion_mappings' => [
        // CakePHP 2.x
        'temp/branches/2.x/en' => 'docs/2/en',

        // CakePHP 3.x
        'temp/branches/3.x/en' => 'docs/3/en',

        // CakePHP 4.x
        'temp/branches/4.x/en' => 'docs/4/en',

        // CakePHP 5.x (latest)
        'temp/branches/5.x/en' => 'docs/5/en',
    ],
];
