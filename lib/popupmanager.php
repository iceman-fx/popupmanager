<?php
/*
	Redaxo-Addon Popup-Manager
	Basisklasse
	v1.0.0
	by Falko Müller @ 2024
*/

class popupmanager {

	//Config des Addons auslesen
    public static function getConfig($sKey=null)
	{	
        global $a1837_mypage;
	
        $aConfig = rex_addon::get($a1837_mypage)->getConfig('config');
	        if ($sKey != ""):
				return (isset($aConfig[$sKey])) ? $aConfig[$sKey] : null;
			endif;
            
        return $aConfig;
    }

    
    //Feld aus DB auslesen (FE)
    public static function getValue($id=null, $sKey=null, $mask=false)
    {
        $return = '';
		
		$id 	= intval($id);
		$sKey 	= popupmanager_helper::maskSql($sKey);
        
        if (!empty($id) && !empty($sKey)):
			$db = rex_sql::factory();
			$db->setQuery("SELECT ".$sKey." FROM ".rex::getTable('1837_popupmanager')." WHERE id = '".$id."' LIMIT 0,1");
			
			if ($db->getRows() > 0):
				$data = $db->getValue($sKey);
				
				@json_decode($data);
				if (json_last_error() === JSON_ERROR_NONE):
					//Wert ist JSON
					$data = json_decode($data, true);
					$data = ($mask && is_array($data)) ? popupmanager_helper::maskArray($data) : $data;
				else:
					//Wert ist normaler Value
					$data = ($mask) ? popupmanager_helper::maskChar($data) : $data;
				endif;
				
				return $data;
			else:
				return $return;
			endif;
        endif;
        
        return $return;
    }


	//Textplatzhalter auslesen
    public static function getText($sKey=null, $from="")
	{	
		global $a1837_mypage;
		
        $text = (!empty($from)) ? $from : rex_file::get(rex_addon::get($a1837_mypage)->getPath('data/textplaceholder.txt'));
        
        if (!empty($text)):
            $data = array();
            
            $text = str_replace(array("\r\n", "\n\r", "\n", "\r"), "\n", $text);
            $text = explode("\n", $text);
            
            foreach ($text as $l):
                $t = explode("=", $l, 2);
                
                if (isset($t[0]) && !empty($t[0])):
                    $data[trim($t[0])] = trim(@$t[1]);
                endif;
            endforeach;            

            unset($text, $l, $t);
            
            if ($sKey != ""):
                return (isset($data[$sKey])) ? $data[$sKey] : '';
            endif;
            
            return $data;
            
        endif;
        
        return;
    }


	//Popup aufbereiten & ausgeben
	public static function embedPopups($ep)
	{	
		global $a1837_mypage;
		$op = $ep->getSubject();
		
		if (rex::isFrontend()):
			$artid  = rex_article::getCurrentId();
			$clid   = rex_clang::getCurrentId();
			
			//alle einzubindenden Popups holen
			$sql_basewhere = "status = 'checked' AND clang_id = '".$clid."' AND ( online_from = '0' OR online_from <= '".time()."' ) AND ( online_to = '0' OR online_to >= '".time()."' )";
			
				//article_mode auswerten
				$sql_artmode = " AND (( article_mode <> 'whitelist' AND article_mode <> 'blacklist' )";
				$sql_artmode .= " OR ( article_mode = 'whitelist' AND FIND_IN_SET('".$artid."', article_ids) > 0 )";
				$sql_artmode .= " OR ( article_mode = 'blacklist' AND FIND_IN_SET('".$artid."', article_ids) <= 0 ))";				
			
	        $db = rex_sql::factory();
			$db->setQuery("SELECT id, media, content, options, device FROM ".rex::getTable('1837_popupmanager')." WHERE ".$sql_basewhere.$sql_artmode);
			
			/*
			dump($artid);
			dump($db);
			dump($db->getArray());
			exit();
			*/
			
			if ($db->getRows() > 0):
				$items = $db->getArray();
				$items = array_filter($items);
			
				//Popups generieren & einbinden
				$popups = '';
					foreach ($items as $item):
						//dump($item);
						
						if (empty($item)) { continue; }
					
						$fragment = new rex_fragment();
						$fragment->setVar('popup', $item, false);
						$popups .= $fragment->parse('popupmanager/popup.php');
					endforeach;				
				
				if (!empty($popups)):
					$op = str_ireplace("</body>", $popups."\n</body>", $op);
				endif;
				
			
				//JS+CSS einbinden
				$embed = '';
					$embed .= (self::getConfig('embed_css') == 'checked') 		? '<link rel="stylesheet" type="text/css" href="'.rex_url::addonAssets($a1837_mypage, 'frontend/style.css').'" />' : '';
					$embed .= (self::getConfig('embed_script') == 'checked') 	? '<script src="'.rex_url::addonAssets($a1837_mypage, 'frontend/script.js').'" defer></script>' : '';
					
				if (!empty($embed)):
					$op = str_ireplace("</body>", $embed."\n</body>", $op);
				endif;
				
			endif;
		
		endif;
		
		return $op;
	}
    

    //Querystring holen
    public static function getQS()
    {
        $qs = popupmanager_helper::textOnly($_SERVER['QUERY_STRING'], true, true);
        $qs = (!empty($qs)) ? '?'.$qs : '';
        
        return $qs;
    }
	
}
?>