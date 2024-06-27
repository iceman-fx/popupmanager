<?php
/*
	Redaxo-Addon Popup-Manager
	Boot (weitere Konfigurationen & Einbindung)
	v1.0.0
	by Falko Müller @ 2024
*/

/** RexStan: Vars vom Check ausschließen */
/** @var rex_addon $this */


//Variablen deklarieren
$mypage = $this->getProperty('package');


//Userrechte prüfen
$isAdmin = ( is_object(rex::getUser()) AND (rex::getUser()->hasPerm($mypage.'[admin]') OR rex::getUser()->isAdmin()) ) ? true : false;


//Addon Einstellungen
$config = $this->getConfig('config');			                                    //Addon-Konfig einladen


//Funktionen einladen/definieren
//Global für Backend+Frontend
global $a1837_mypage;
$a1837_mypage = $mypage;

global $a1837_darkmode;
$a1837_darkmode = (rex_string::versionCompare(rex::getVersion(), '5.13.0-dev', '>=')) ? true : false;


//Backend
if (rex::isBackend()):
	require_once(rex_path::addon($mypage, "functions/functions_be.inc.php"));
    
	if (rex::getUser()):

		//Sprachauswahl zur Navigation hinzufügen
		$page = $this->getProperty('page');
			if (count(rex_clang::getAll(false)) > 1):
				$cids = rex_clang::getAll();
				foreach ($cids as $id => $cid):
					if (rex::getUser()->getComplexPerm('clang')->hasPerm($id)):
						$page['subpages']['default']['subpages']['clang-'.$id] = ['title' => $cid->getName()];
						$page['subpages']['cat']['subpages']['clang-'.$id] = ['title' => $cid->getName()];
					endif;
				endforeach;
			endif;
		$this->setProperty('page', $page);
		
		
        //AJAX anbinden
        $ajaxPages = array('load-defaultlist');
            if (rex_be_controller::getCurrentPagePart(1) == $mypage && in_array(rex_request('subpage', 'string'), $ajaxPages)):
                rex_extension::register('OUTPUT_FILTER', 'popupmanager_helper::bindAjax');
            endif;
        
	endif;
endif;


//Frontend
if (rex::isFrontend()):
	rex_extension::register('OUTPUT_FILTER', 'popupmanager::embedPopups');
endif;



// Assets im Backend einbinden (z.B. style.css) - es wird eine Versionsangabe angehängt, damit nach einem neuen Release des Addons die Datei nicht aus dem Browsercache verwendet wird
rex_view::addCssFile($this->getAssetsUrl('style.css'));
if ($a1837_darkmode) { rex_view::addCssFile($this->getAssetsUrl('style-darkmode.css')); }

rex_view::addCssFile($this->getAssetsUrl('datepicker/jquery.datetimepicker.min.css'));
rex_view::addJsFile($this->getAssetsUrl('datepicker/jquery.datetimepicker.full.min.js'));
rex_view::addJsFile($this->getAssetsUrl('script.js'));
?>