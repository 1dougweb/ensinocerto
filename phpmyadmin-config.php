<?php
/**
 * Configuração personalizada phpMyAdmin
 */

declare(strict_types=1);

// Configuração básica
$cfg['blowfish_secret'] = 'H2OxcGXxflSd8JwrwVlh6KW6s2rER63i';
$cfg['DefaultLang'] = 'pt';
$cfg['ServerDefault'] = 1;

// Configuração do servidor MariaDB
$i = 0;
$i++;
$cfg['Servers'][$i]['auth_type'] = 'cookie';
$cfg['Servers'][$i]['host'] = 'db';
$cfg['Servers'][$i]['port'] = '3306';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = false;

// Configurações de interface
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
$cfg['ShowPhpInfo'] = true;
$cfg['ShowServerInfo'] = true;
$cfg['ShowStats'] = true;
$cfg['ShowTooltip'] = true;
$cfg['ShowTooltipAliasDB'] = false;
$cfg['ShowTooltipAliasTB'] = false;

// Configurações de segurança
$cfg['LoginCookieValidity'] = 3600;
$cfg['CheckConfigurationPermissions'] = false;

// Configurações de upload
$cfg['MaxSizeForInputField'] = 25165824; // 24MB
$cfg['MemoryLimit'] = '512M';
$cfg['ExecTimeLimit'] = 300;

// Configurações de exportação
$cfg['Export']['sql_structure_or_data'] = 'structure_and_data';
$cfg['Export']['sql_max_query_size'] = 50000;

// Configurações de tema
$cfg['ThemeDefault'] = 'pmahomme';
$cfg['NavigationTreeDefaultTabTable'] = 'structure';
$cfg['NavigationTreeDefaultTabTable2'] = '';

// Desabilitar warnings
$cfg['SendErrorReports'] = 'never';
$cfg['ConsoleEnterExecutes'] = false;
?>
