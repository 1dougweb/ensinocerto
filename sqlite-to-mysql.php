<?php
/**
 * Script para converter SQLite para MySQL dump
 */

require 'vendor/autoload.php';

$sqliteDb = new PDO('sqlite:database/database.sqlite');
$sqliteDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$output = "-- MySQL dump gerado do SQLite\n";
$output .= "-- Data: " . date('Y-m-d H:i:s') . "\n\n";

$output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

// Obter todas as tabelas
$tables = $sqliteDb->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "Processando tabela: $table\n";
    
    // Obter estrutura da tabela
    $createStmt = $sqliteDb->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
    
    // Converter CREATE TABLE do SQLite para MySQL
    $mysqlCreate = convertSqliteToMysql($createStmt, $table);
    $output .= $mysqlCreate . "\n\n";
    
    // Obter dados
    $rows = $sqliteDb->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($rows)) {
        $columns = array_keys($rows[0]);
        $columnList = '`' . implode('`, `', $columns) . '`';
        
        $output .= "INSERT INTO `$table` ($columnList) VALUES\n";
        
        $values = [];
        foreach ($rows as $row) {
            $escapedValues = array_map(function($value) {
                if ($value === null) return 'NULL';
                return "'" . addslashes($value) . "'";
            }, $row);
            $values[] = '(' . implode(', ', $escapedValues) . ')';
        }
        
        $output .= implode(",\n", $values) . ";\n\n";
    }
}

$output .= "SET FOREIGN_KEY_CHECKS=1;\n";

// Salvar arquivo
file_put_contents('database/dump.sql', $output);
echo "Dump MySQL salvo em: database/dump.sql\n";

function convertSqliteToMysql($sqliteCreate, $tableName) {
    // Conversão básica SQLite -> MySQL
    $mysql = $sqliteCreate;
    
    // Remover IF NOT EXISTS (se houver)
    $mysql = str_replace('IF NOT EXISTS', '', $mysql);
    
    // Converter tipos de dados
    $mysql = preg_replace('/INTEGER PRIMARY KEY AUTOINCREMENT/i', 'INT AUTO_INCREMENT PRIMARY KEY', $mysql);
    $mysql = preg_replace('/INTEGER/i', 'INT', $mysql);
    $mysql = preg_replace('/REAL/i', 'DECIMAL(10,2)', $mysql);
    $mysql = preg_replace('/TEXT/i', 'TEXT', $mysql);
    
    // Adicionar ENGINE
    $mysql = rtrim($mysql, ';') . ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
    
    return $mysql;
}
?>
