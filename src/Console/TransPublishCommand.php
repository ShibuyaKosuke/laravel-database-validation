<?php

namespace ShibuyaKosuke\LaravelDatabaseValidator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;
use ShibuyaKosuke\LaravelDatabaseUtilities\Models\Column;
use ShibuyaKosuke\LaravelDatabaseUtilities\Models\Table;

class TransPublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trans:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish translation files from database table.';

    /**
     * @return void
     */
    public function handle(): void
    {
        $file = resource_path(sprintf('lang/%s/tables.php', Lang::getLocale()));
        if ($this->tables($file)) {
            $this->info(sprintf('Success: %s', $file));
        }
        $file = resource_path(sprintf('lang/%s/columns.php', Lang::getLocale()));
        if ($this->columns($file)) {
            $this->info(sprintf('Success: %s', $file));
        }
    }

    protected function tables($file)
    {
        $table_translations = Table::query()
            ->where('TABLE_COMMENT', '!=', '')
            ->get()
            ->map(function (Table $table) {
                return sprintf("'%s' => '%s'", $table->TABLE_NAME, $table->TABLE_COMMENT);
            })->implode(",\n    ");

        $contents = sprintf("<?php\n\nreturn [\n    %s\n];", $table_translations);
        return file_put_contents($file, $contents);
    }

    protected function columns($file)
    {
        $tables = Table::query()
            ->where('TABLE_COMMENT', '!=', '')
            ->get()
            ->map(function (Table $table) {
                $columns = $table->columns->map(function (Column $column) {
                    return sprintf("        '%s' => '%s'", $column->COLUMN_NAME, $column->COLUMN_COMMENT);
                })->implode(",\n");
                return sprintf("    '%s' => [\n%s\n    ]", $table->TABLE_NAME, $columns);
            })
            ->implode(",\n");

        $contents = sprintf("<?php\n\nreturn [\n%s\n];\n", $tables);
        return file_put_contents($file, $contents);
    }
}