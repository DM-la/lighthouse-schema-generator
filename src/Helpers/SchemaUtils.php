<?php

namespace DM\LighthouseSchemaGenerator\Helpers;

use Illuminate\Support\Facades\Schema;

class SchemaUtils
{
    /**
     * @param  string  $table
     * @param  string  $column
     * @return string
     */
    public static function getColumnType(string $table, string $column): string
    {
        return Schema::getColumnType($table, $column);
    }

    /**
     * Get the column listing for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public static function getColumnListing(string $table): array
    {
        return Schema::getColumnListing($table);
    }
}
