<?php
/*
	Redaxo-Addon Popup-Manager
	Installation
	v1.0
	by Falko Müller @ 2024
*/

/** RexStan: Vars vom Check ausschließen */
/** @var rex_addon $this */


//Variablen deklarieren
$mypage = $this->getProperty('package');
$error = "";


//Vorgaben vornehmen
if (!$this->hasConfig()):
	$this->setConfig('config', [
		'embed_script'				=> 'checked',
		'embed_css'					=> 'checked',
        'editor_class'				=> 'ckeditor',
        'editor_data'				=> 'data-ckeditor-profile="default"',
		'body_scrollbar'			=> 'checked',		
	]);
endif;


//Datenbank-Einträge vornehmen
rex_sql_table::get(rex::getTable('1837_popupmanager'))
	->ensureColumn(new rex_sql_column('id', 'int(100)', false, null, 'auto_increment'))
	->ensureColumn(new rex_sql_column('title', 'varchar(255)'))
	->ensureColumn(new rex_sql_column('description', 'text'))
	->ensureColumn(new rex_sql_column('hash', 'text'))
	->ensureColumn(new rex_sql_column('online_from', 'int(30)'))
	->ensureColumn(new rex_sql_column('online_to', 'int(30)'))	
	->ensureColumn(new rex_sql_column('media', 'varchar(255)'))	
	->ensureColumn(new rex_sql_column('content', 'text'))
	->ensureColumn(new rex_sql_column('options', 'text'))	
	->ensureColumn(new rex_sql_column('article_mode', 'varchar(20)'))
	->ensureColumn(new rex_sql_column('article_ids', 'text'))
	->ensureColumn(new rex_sql_column('device', 'varchar(20)'))
	->ensureColumn(new rex_sql_column('clang_id', 'int(100)'))	
	->ensureColumn(new rex_sql_column('status', 'varchar(10)'))	
	->ensureGlobalColumns()
	->setPrimaryKey('id')
	->ensure();


//Module anlegen
?>