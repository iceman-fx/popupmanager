<?php
/*
	Redaxo-Addon Popup-Manager
	Backend-Funktionen (Addon + Modul)
	v1.0.0
	by Falko Müller @ 2024
*/

//aktive Session prüfen


//globale Variablen


//Funktionen
//prüft die Bezeichnung auf Vorhandensein
function a1837_duplicateName($str)
{	if (!empty($str)):
		$db = rex_sql::factory();
		$db->setQuery("SELECT id, title FROM ".rex::getTable('1837_popupmanager')." WHERE title = '".popupmanager_helper::maskSql($str)."'"); 
		
		if ($db->getRows() > 0):
			$str = $str.rex_i18n::msg('a1837_copiedname');
			$str = a1837_duplicateName($str);
		endif;
	endif;

	return $str;
}

?>