<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Cycle\Database;
use Cycle\Database\Config;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;
use Cycle\Schema;
use Cycle\Annotated;
use Cycle\Annotated\Locator\TokenizerEmbeddingLocator;
use Cycle\Annotated\Locator\TokenizerEntityLocator;
use Cycle\ORM;
use Cycle\Migrations;

$dbal = new Database\DatabaseManager(
    new Config\DatabaseConfig([
        'default' => 'default',
        'databases' => [
            'default' => ['connection' => 'sqlite']
        ],
        'connections' => [
            'sqlite' => new Config\SQLiteDriverConfig(
                connection: new Config\SQLite\FileConnectionConfig(
                    database: __DIR__ . '/../database.sqlite'
                ),
                queryCache: true,
            ),
        ]
    ])
);

$config = new Migrations\Config\MigrationConfig([
    'directory' => __DIR__ . '/../migrations/', // where to store migrations
    'table'     => 'migrations',                // database table to store migration status
    'safe'      => true                         // When set to true no confirmation will be requested on migration run.
]);

$migrator = new Migrations\Migrator($config, $dbal, new Migrations\FileRepository($config));

// Init migration table
$migrator->configure();

while(($migrated = $migrator->run()) !== null) {
    $status = match($migrated->getState()->getStatus()) {
        Migrations\State::STATUS_UNDEFINED => 'undefined',
        Migrations\State::STATUS_PENDING => 'pending',
        Migrations\State::STATUS_EXECUTED => 'executed',
        default => 'unknown',
    };

    echo "{$migrated->getState()->getName()} migrated to {$status}\n";
}

$finder = (new Finder())->files()->in([__DIR__ . '/../src/Entity']);
$classLocator = new ClassLocator($finder);

$embeddingLocator = new TokenizerEmbeddingLocator($classLocator);
$entityLocator = new TokenizerEntityLocator($classLocator);

$schema = (new Schema\Compiler())->compile(new Schema\Registry($dbal), [
    new Schema\Generator\ResetTables(),             // Reconfigure table schemas (deletes columns if necessary)
    new Annotated\Embeddings($embeddingLocator),    // Recognize embeddable entities
    new Annotated\Entities($entityLocator),         // Identify attributed entities
    new Annotated\TableInheritance(),               // Setup Single Table or Joined Table Inheritance
    new Annotated\MergeColumns(),                   // Integrate table #[Column] attributes
    new Schema\Generator\GenerateRelations(),       // Define entity relationships
    new Schema\Generator\GenerateModifiers(),       // Apply schema modifications
    new Schema\Generator\ValidateEntities(),        // Ensure entity schemas adhere to conventions
    new Schema\Generator\RenderTables(),            // Create table schemas
    new Schema\Generator\RenderRelations(),         // Establish keys and indexes for relationships
    new Schema\Generator\RenderModifiers(),         // Implement schema modifications
    new Schema\Generator\ForeignKeys(),             // Define foreign key constraints
    new Annotated\MergeIndexes(),                   // Merge table index attributes
//    new Schema\Generator\SyncTables(),              // Align table changes with the database
    new GenerateMigrations($migrator->getRepository(), $migrator->getConfig()),  // generate migrations
    new Schema\Generator\GenerateTypecast(),        // Typecast non-string columns
]);

$orm = new ORM\ORM(new ORM\Factory($dbal), new ORM\Schema($schema));

return $orm;