<?php

namespace ShibuyaKosuke\LaravelDatabaseValidator\Console;

use Illuminate\Console\Command;
use ShibuyaKosuke\LaravelDatabaseUtilities\Models\Column;
use ShibuyaKosuke\LaravelDatabaseUtilities\Models\Table;

class RulePublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rule:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish validation rule for database table.';

    /**
     * @return void
     */
    public function handle(): void
    {
        $tables = Table::query()->where('TABLE_COMMENT', '!=', '')->get();
        $tables->map(function (Table $table) {
            return [
                'table' => $table->TABLE_NAME,
                'columns' => $table->columns->reject(function (Column $column) {
                    return in_array($column->COLUMN_NAME, ['id', 'created_at', 'updated_at', 'deleted_at',]);
                })->map(function (Column $column) {
                    return [
                        'column_name' => $column->COLUMN_NAME,
                        'rules' => $this->getRules($column)
                    ];
                })
            ];
        })->each(function ($column) {
            $file = base_path(sprintf('rules/%s.php', $column['table']));
            $buffers = $column['columns']->map(function ($column) {
                return sprintf("'%s' => [%s]", $column['column_name'], implode(', ', $column['rules']));
            });
            $buffer = sprintf("<?php\n\nreturn [\n    %s\n];", $buffers->implode(",\n    "));
            file_put_contents($file, $buffer);
        });
    }

    protected function getRules(Column $column)
    {
        $rule = [];
        $rule[] = $column->IS_NULLABLE == 'YES' ? 'nullable' : 'required';
        $rule[] = $this->getType($column);
        if ($column->CHARACTER_MAXIMUM_LENGTH) {
            $rule[] = sprintf('max:%d', $column->CHARACTER_MAXIMUM_LENGTH);
        }
        if ($column->belongs_to) {
            $rule[] = sprintf('exists:%s,%s', $column->belongs_to->TABLE_NAME, $column->belongs_to->COLUMN_NAME);
        }
        return array_map(function ($item) {
            return sprintf("'%s'", $item);
        }, $rule);
    }

    protected function getType(Column $column)
    {
        switch ($column->DATA_TYPE) {
            case 'bigint':
            case 'mediumint':
            case 'smallint':
            case 'int':
            case 'tinyint':
                $type = 'integer';
                break;
            case 'float':
            case 'double':
            case 'decimal':
            case 'numeric':
                $type = 'numeric';
                break;
            case 'date':
            case 'datetime':
            case 'timestamp':
                $type = 'date';
                break;
            case 'time':
                $type = 'date_format:"H:i:s"';
                break;
            case 'year':
                $type = 'date_format:"Y"';
                break;
            case 'char':
            case 'varchar':
            case 'tinyblob':
            case 'blob':
            case 'mediumblob':
            case 'longblob':
            case 'tinytext':
            case 'text':
            case 'mediumtext':
            case 'longtext':
                $type = 'string';
                break;
        }
        return $type;
    }
}