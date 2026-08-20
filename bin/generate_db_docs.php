<?php

$metadata = json_decode(file_get_contents(__DIR__.'/../docs/metadata/system_metadata.json'), true);
$tables = $metadata['tables'];

$entitiesMd = "# Database Entities\n\nThis document describes all tables in the system.\n\n";
$relationshipsMd = "# Database Relationships\n\nThis document describes all foreign key relationships in the system.\n\n";

foreach ($tables as $tableName => $tableData) {
    $entitiesMd .= "## `{$tableName}`\n\n";
    $entitiesMd .= "| Column | Type | Nullable | Default |\n";
    $entitiesMd .= "|--------|------|----------|---------|\n";
    
    foreach ($tableData['columns'] as $col) {
        $nullable = $col['nullable'] ? 'Yes' : 'No';
        $default = $col['default'] ?? 'NULL';
        if (is_array($default)) { $default = json_encode($default); }
        $entitiesMd .= "| `{$col['name']}` | `{$col['type']}` | {$nullable} | {$default} |\n";
    }
    $entitiesMd .= "\n";
    
    if (!empty($tableData['foreign_keys'])) {
        $relationshipsMd .= "## `{$tableName}` Relationships\n\n";
        $relationshipsMd .= "| Local Column | Foreign Table | Foreign Column |\n";
        $relationshipsMd .= "|--------------|---------------|----------------|\n";
        foreach ($tableData['foreign_keys'] as $fk) {
            $local = is_array($fk['columns']) ? implode(', ', $fk['columns']) : $fk['columns'];
            $foreignTab = $fk['foreign_table'];
            $foreignCol = is_array($fk['foreign_columns']) ? implode(', ', $fk['foreign_columns']) : $fk['foreign_columns'];
            $relationshipsMd .= "| `{$local}` | `{$foreignTab}` | `{$foreignCol}` |\n";
        }
        $relationshipsMd .= "\n";
    }
}

if (!is_dir(__DIR__.'/../docs/database')) {
    mkdir(__DIR__.'/../docs/database', 0777, true);
}

file_put_contents(__DIR__.'/../docs/database/entities.md', $entitiesMd);
file_put_contents(__DIR__.'/../docs/database/relationships.md', $relationshipsMd);

echo "Database documentation generated successfully.\n";
