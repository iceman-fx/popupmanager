<?php
/*
	Redaxo-Addon Popup-Manager
	Verwaltung: AJAX Loader - Popupliste
	v1.0.0
	by Falko Müller @ 2024
*/

//Vorgaben
$mainTable = '1837_popupmanager';				//primäre SQL-Tabelle dieses Bereiches


//Paramater
$page = rex_request('page', 'string');
$subpage = "";							//ggf. manuell setzen

$clang_id = rex_be_controller::getCurrentPagePart(3);													//2. Unterebene = dritter Teil des page-Parameters
	$clang_id = (!empty($clang_id)) ? intval(preg_replace("/.*-([0-9])$/i", "$1", $clang_id)) : 0;		//Auslesen der ClangID
	$clang_id = ($clang_id <= 0) ? 1 : $clang_id;

$sbeg = trim(urldecode(rex_request('sbeg')));

$order = (strtolower(rex_request('order')) == 'desc') ? 'DESC' : 'ASC';

$limStart = rex_request('limStart', 'int');


//Sessionwerte zurücksetzen
$_SESSION['as_sbeg_popupmanager'] = "";


//AJAX begin
echo '<!-- ###AJAX### -->';


//SQL erstellen und Filterung berücksichtigen
$sql = "SELECT * FROM ".rex::getTable($mainTable);
$sql_where = " WHERE 1";


//Eingrenzung: Suchbegriff
if (!empty($sbeg)):
	$_SESSION['as_sbeg_arttmpl'] = $sbeg;
	$sql_where .= " AND ( 
					BINARY LOWER(title) like LOWER('%".popupmanager_helper::maskSql($sbeg)."%') 
					OR BINARY LOWER(description) like LOWER('%".popupmanager_helper::maskSql($sbeg)."%')
					)";
					//BINARY sorgt für einen Binärvergleich, wodurch Umlaute auch als Umlaute gewertet werden (ohne BINARY ist ein Ä = A)
					//LOWER sorgt für einen Vergleich auf Basis von Kleinbuchstaben (ohne LOWER würde das BINARY nach Groß/Klein unterscheiden)
					//DATE_FORMAT wandelt den Wert in eine andere Schreibweise um (damit kann der gespeicherte Wert vom gesuchten Wert abweichen) --> DATE_FORMAT(`date`, '%e.%m.%Y')
					//FROM_UNIXTIME arbeit wie DATE-FORMAT, aber benötigt als Quelle einen timestamp
					//		OR ( FROM_UNIXTIME(`date`, '%e.%m.%Y') like '".aFM_maskSql($sbeg)."%' OR FROM_UNIXTIME(`date`, '%d.%m.%Y') like '".aFM_maskSql($sbeg)."%' )
endif;


//Eingrenzung: Sprache (clangID)
$sql_where .= ($clang_id > 0) ? " AND clang_id = '".$clang_id."'" : '';


//Sortierung
$sql_where .= " ORDER BY title ".$order.", id ASC";


//Limit
$limStart = ($limStart > 0) ? $limStart : 0;
$limCount = 25;
$sql_limit = " LIMIT ".($limStart * $limCount).",".$limCount;


//SQL zwischenspeichern
//$_SESSION['as_sql_arttmpl'] = $sql.$sql_where;


//Ergebnisse nachladen
$db = rex_sql::factory();
$db->setQuery($sql.$sql_where.$sql_limit);
$addPath = "index.php?page=".$page;

	/*
	echo "<tr><td colspan='10'>$sql$sql_where$sql_limit</td></tr>";
	echo "<tr><td colspan='10'>Anzahl Datensätze: ".$db->getRows()."</td></tr>";
	*/
	

            if ($db->getRows() > 0):
                //Liste ausgeben
                for ($i=0; $i < $db->getRows(); $i++):
					$eid = intval($db->getValue('id'));
					$editPath = $addPath.'&amp;func=update&amp;id='.$eid;

					$status = ($db->getValue('status') == "checked") ? 'online' : 'offline';
					
					$title = $db->getValue('title');
                        $title = popupmanager_helper::maskChar($title);
                      
					$dev = $db->getValue('device');
					$device = $this->i18n('a1837_bas_list_device_all');
						$device = ($dev == 'desktop')	? $this->i18n('a1837_bas_list_device_desktop') : $device;
						$device = ($dev == 'mobile')	? $this->i18n('a1837_bas_list_device_mobile') : $device;
					
					//$options = json_decode($db->getValue('options'), true);					
					
					$display = '';					
						//article_mode auswerten
						/*
						$artmode = $db->getValue('article_mode');
							$tmp = $this->i18n('a1837_bas_list_articlemode_all');
							$tmp = ($artmode == 'whitelist') ? $this->i18n('a1837_bas_list_articlemode_whitelist') : $tmp;
							$tmp = ($artmode == 'blacklist') ? rex_i18n::rawmsg('a1837_bas_list_articlemode_blacklist') : $tmp;
							
						$display .= (!empty($tmp)) ? '<div class="info">'.$this->i18n('a1837_bas_list_articlemode').': '.$tmp.'</div>' : '';
						*/
						
						//online from-to auswerten
						$of	= intval($db->getValue('online_from'));
						$ot	= intval($db->getValue('online_to'));
						
						$onfrom = (!empty($of)) ? date('d.m.Y (H:i)', $of) : '';
						$onto 	= (!empty($ot)) ? date('d.m.Y (H:i)', $ot) : '';
							$onfrom = (empty($onto) && !empty($onfrom)) ? 'ab '.$onfrom : $onfrom;
							$onto 	= (empty($onfrom) && !empty($onto)) ? 'bis '.$onto : $onto;
							$onto 	= (!empty($onfrom) && !empty($onto)) ? '- '.$onto : $onto;
							
						$display_status = (( $of == 0 OR $of <= time() ) AND ( $ot == 0 OR $ot >= time() )) ? '' : 'rex-offline';
						
						$display .= (!empty($onfrom) || !empty($onto)) ? '<div class="info">'.$this->i18n('a1837_bas_list_visible').'<br><span class="'.$display_status.'">'.$onfrom.' '.$onto.'</span></div>' : '';
					
                    					
                    
                    //Ausgabe
                    ?>
                        
                    <tr id="entry<?php echo $eid; ?>" class="<?php echo $css; ?>">
                        <td class="rex-table-icon"><a href="<?php echo $editPath; ?>" title="<?php echo $this->i18n('a1837_edit'); ?>"><i class="rex-icon rex-icon-article"></i></a></td>
                        <td class="rex-table-id"><?php echo $eid; ?></td>
                        <td data-title="<?php echo $this->i18n('a1837_bas_list_name'); ?>"><a href="<?php echo $editPath; ?>"><?php echo $title; ?></a></td>
                        <td data-title="<?php echo $this->i18n('a1837_bas_list_device'); ?>"><?php echo $device; ?></td>
                        <td data-title="<?php echo $this->i18n('a1837_bas_list_displaysettings'); ?>"><?php echo $display; ?></td>
                        
                        <td class="rex-table-action"><a href="<?php echo $editPath; ?>"><i class="rex-icon rex-icon-edit"></i> <?php echo $this->i18n('a1837_edit'); ?></a></td>
                        <td class="rex-table-action"><a href="<?php echo $addPath; ?>&func=duplicate&id=<?php echo $eid; ?>"><i class="rex-icon rex-icon-duplicate"></i> <?php echo $this->i18n('a1837_duplicate'); ?></a></td>
                        <td class="rex-table-action"><a href="<?php echo $addPath; ?>&func=delete&id=<?php echo $eid; ?>" data-confirm="<?php echo $this->i18n('a1837_delete'); ?> ?"><span class="rex-offline"><i class="rex-icon rex-icon-delete"></i> <?php echo $this->i18n('a1837_delete'); ?></span></a></td>
                        <td class="rex-table-action"><a href="<?php echo $addPath; ?>&func=status&id=<?php echo $eid; ?>" class="rex-<?php echo $status; ?>"><i class="rex-icon rex-icon-<?php echo $status; ?>"></i> <?php echo ($status == "online") ? $this->i18n('a1837_online') : $this->i18n('a1837_offline'); ?></a></td>
                    </tr>
                    
                    <?php
					$db->next();
                endfor;
				
				
				//Seitenschaltung generieren
				$dbl = rex_sql::factory();
				$dbl->setQuery($sql.$sql_where);
					$maxEntry = $dbl->getRows();
					$maxSite = ceil($maxEntry / $limCount);

				if ($dbl->getRows() > $limCount):
					echo '<tr><td colspan="10" align="center"><ul class="addon_list-pagination pagination">';
					
					for ($i=0; $i<$maxSite; $i++):
						$sel = ($i == $limStart) ? 'ajaxNavSel' : '';
						$selLI = ($i == $limStart) ? 'active' : '';
						echo '<li class="rex-page '.$selLI.'"><span class="ajaxNav '.$sel.'" data-navsite="'.$i.'">'.($i+1).'</span></li>';
					endfor;
					
					echo '</ul></td></tr>';
				endif;
				
            else:
                ?>
                
                    <tr>
                        <td colspan="10" align="center"> - <?php echo $this->i18n('a1837_search_notfound'); ?> -</td>
                    </tr>
                
                <?php
            endif;

//AJAX end
echo '<!-- ###/AJAX### -->';
?>