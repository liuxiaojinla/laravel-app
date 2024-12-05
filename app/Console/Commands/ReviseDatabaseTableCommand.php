<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
        foreach ($this->getTables() as $tableName) {
            $tableNameComplex = Str::pluralStudly($tableName);
            if ($tableNameComplex != $tableName) {
                Schema::rename($tableName, $tableNameComplex);
            }

            // 检查表中是否存在update_time字段
            Schema::table($tableNameComplex, function (Blueprint $table) use ($tableNameComplex) {
                if (Schema::hasColumn($tableNameComplex, 'update_time')) {
                    $table->dropColumn('update_time');
                    $table->dropColumn('create_time');

                    if (!Schema::hasColumn($tableNameComplex, 'updated_at')) {
                        $table->timestamps();
                    }
                } elseif (Schema::hasColumn($tableNameComplex, 'create_time')) {
                    $table->dropColumn('create_time');
                } elseif (!Schema::hasColumn($tableNameComplex, 'created_at')) {
                    $table->timestamps();
                }

                if (Schema::hasColumn($tableNameComplex, 'delete_time')) {
                    $table->dropColumn('delete_time');

                    if (!Schema::hasColumn($tableNameComplex, 'deleted_at')) {
                        $table->softDeletes();
                    }
                }elseif (!Schema::hasColumn($tableNameComplex, 'deleted_at')) {
                    $table->softDeletes();
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
