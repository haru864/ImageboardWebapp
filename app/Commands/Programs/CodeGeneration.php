<?php

namespace Commands\Programs;

use Commands\AbstractCommand;
use Commands\Argument;

class CodeGeneration extends AbstractCommand
{
    protected static ?string $alias = 'code-gen';
    protected static bool $requiredCommandValue = true;

    public static function getArguments(): array
    {
        return [
            (new Argument('name'))->description('Name of the file that is to be generated.')->required(false),
        ];
    }

    public function execute(): int
    {
        $codeGenType = $this->getCommandValue();
        $this->log('Generating code for.......' . $codeGenType);

        if ($codeGenType === 'migration') {
            $migrationName = $this->getArgumentValue('name');
            $this->generateMigrationFile($migrationName);
        }

        return 0;
    }

    private function generateMigrationFile(string $migrationName): void
    {
        $filename = sprintf(
            '%s_%s_%s.php',
            date('Y-m-d'),
            time(),
            $migrationName
        );
        $migrationContent = $this->getMigrationContent($migrationName);
        $path = sprintf("%s/../../Database/Migrations/%s", __DIR__, $filename);
        file_put_contents($path, $migrationContent);
        $this->log("Migration file {$filename} has been generated!");
    }

    private function getMigrationContent(string $migrationName): string
    {
        $className = $this->pascalCase($migrationName);
        return <<<MIGRATION
<?php

namespace Database\Migrations;

use Database;

class {$className} implements Database\SchemaMigration
{
    public function up(): array
    {
        // マイグレーションロジックをここに追加してください
        return [];
    }

    public function down(): array
    {
        // ロールバックロジックを追加してください
        return [];
    }
}
MIGRATION;
    }

    private function pascalCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
