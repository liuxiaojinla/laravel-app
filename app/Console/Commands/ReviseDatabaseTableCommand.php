<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ReviseDatabaseTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revise:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修正数据表';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $excludeTables = ['menus', 'goods_skus'];
        foreach ($this->getTables() as $tableName) {
            $tableNameComplex = in_array($tableName, $excludeTables) ? $tableName : Str::pluralStudly($tableName);
            if ($tableNameComplex != $tableName) {
                Schema::rename($tableName, $tableNameComplex);
            }

            $this->components->info("$tableNameComplex handling.");

            // 类型处理
            Schema::table($tableNameComplex, function ($table) use ($tableNameComplex) {
                try {
                    if ($columnType = Schema::getColumnType($tableNameComplex, 'updated_at')) {
                        if ($columnType !== 'timestamp') {
                            $this->components->warn("$tableNameComplex dropColumn [updated_at($columnType)] handling.");
                            $table->dropColumn('updated_at');
                        }
                    }
                } catch (InvalidArgumentException $e) {
                }

                try {
                    if ($columnType = Schema::getColumnType($tableNameComplex, 'created_at')) {
                        if ($columnType !== 'timestamp') {
                            $this->components->warn("$tableNameComplex dropColumn [created_at($columnType)] handling.");
                            $table->dropColumn('created_at');
                        }
                    }
                } catch (InvalidArgumentException $e) {
                }
            });

            // 检查表中是否存在update_time字段
            Schema::table($tableNameComplex, function (Blueprint $table) use ($tableNameComplex) {
                // 处理 update_time
                if (Schema::hasColumn($tableNameComplex, 'update_time')) {
                    $this->components->warn("$tableNameComplex dropColumn [update_time] handling.");
                    $table->dropColumn('update_time');
                }
                if (!Schema::hasColumn($tableNameComplex, 'updated_at')) {
                    $this->components->warn("$tableNameComplex addColumn [updated_at] handling.");
                    $table->timestamp('updated_at')->nullable();
                }

                // 处理 create_time
                if (Schema::hasColumn($tableNameComplex, 'create_time')) {
                    $this->components->warn("$tableNameComplex dropColumn [create_time] handling.");
                    $table->dropColumn('create_time');
                }
                if (!Schema::hasColumn($tableNameComplex, 'created_at')) {
                    $this->components->warn("$tableNameComplex addColumn [created_at] handling.");
                    $table->timestamp('created_at')->nullable();
                }

                // 处理 delete_time
                if (Schema::hasColumn($tableNameComplex, 'delete_time')) {
                    $this->components->warn("$tableNameComplex dropColumn [delete_time] handling.");
                    $table->dropColumn('delete_time');
                }
                if (!Schema::hasColumn($tableNameComplex, 'deleted_at')) {
                    $this->components->warn("$tableNameComplex addColumn [deleted_at] handling.");
                    $table->softDeletes();
                }

                // 处理 app_id
                if (Schema::hasColumn($tableNameComplex, 'app_id')) {
                    $this->components->warn("$tableNameComplex dropColumn [app_id] handling.");
                    $table->dropColumn('app_id');
                }
            });
        }

        $this->info('All tables have been checked and updated if necessary.');
    }

    /**
     * @return array
     */
    protected function getTables()
    {
        return array_map(function ($table) {
            return $table['name'];
        }, Schema::getTables());

        // 获取数据库中的所有表
        $tables = DB::select('SHOW TABLES');

        $tableField = "Tables_in_" . $this->laravel['config']['database.connections.' . config('database.default') . '.database'];
        return array_map(function ($table) use ($tableField) {
            return $table->{$tableField};
        }, $tables);
    }
}
