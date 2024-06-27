<?php
/*
	Redaxo-Addon Popup-Manager
	Verwaltung: Hauptseite (Default)
	v1.0.0
	by Falko Müller @ 2024
*/

//Vorgaben
$mainTable = '1837_popupmanager';				//primäre SQL-Tabelle dieses Bereiches


//Paramater
$mode = rex_request('mode');
$id = intval(rex_request('id'));
$form_error = $formvalue_error = 0;


$clang_id = rex_be_controller::getCurrentPagePart(3);													//2. Unterebene = dritter Teil des page-Parameters
	$clang_id = (!empty($clang_id)) ? intval(preg_replace("/.*-([0-9])$/i", "$1", $clang_id)) : 0;		//Auslesen der ClangID
	$clang_id = ($clang_id <= 0) ? 1 : $clang_id;


$_SESSION['as_sbeg_popupmanager'] = 	(!isset($_SESSION['as_sbeg_popupmanager'])) 	? "" : $_SESSION['as_sbeg_popupmanager'];


//Formular dieser Seite verarbeiten
if ($func == "save" && (isset($_POST['submit']) || isset($_POST['submit-apply'])) ):
	//Pflichtfelder prüfen
	$fields = array("f_title");
		foreach ($fields as $field):
			$tmp = rex_post($field);
			$form_error = (empty($tmp)) ? 1 : $form_error;
		endforeach;
		
	//Eingaben prüfen
	$formcontent_error = (empty(rex_post('f_content')) && empty(rex_post('f_media'))) ? 1 : $form_error;
	//$formvalue_error = (!preg_match("/[0-9]{2}\.[0-9]{2}\.[0-9]{4}/i", rex_post('f_date'))) ? 1 : $formvalue_error;
		
		
	if ($form_error):
		//Pflichtfelder fehlen
		echo rex_view::warning($this->i18n('a1837_entry_emptyfields'));
	elseif ($formvalue_error):
		//Eingaben fehlerhaft
		echo rex_view::warning($this->i18n('a1837_entry_invaliddata'));
        $form_error = 1;
	elseif ($formcontent_error):
		//Content nicht gesetzt
		echo rex_view::warning($this->i18n('a1837_entry_emptycontent'));
        $form_error = 1;
	else:
		//Eintrag speichern
		$db = rex_sql::factory();
		$db->setTable(rex::getTable($mainTable));

		$tmp = str_replace("__.__.____ __:__", "", rex_post('f_online_from'));
			$tmp = (!empty($tmp)) ?         strtotime($tmp) : '';
			$db->setValue("online_from", 	$tmp);
		$tmp = str_replace("__.__.____ __:__", "", rex_post('f_online_to'));
			$tmp = (!empty($tmp)) ?         strtotime($tmp) : '';
			$db->setValue("online_to", 	    $tmp);

		$db->setValue("title", 			    rex_post('f_title'));
		$db->setValue("description", 		rex_post('f_description'));
		$db->setValue("media", 	            rex_post('f_media'));
		$db->setValue("content", 	        rex_post('f_content'));
		$db->setValue("article_mode", 	    rex_post('f_article_mode'));
		$db->setValue("article_ids", 	    rex_post('f_article_ids'));
		$db->setValue("device", 	        rex_post('f_device'));
		
		$db->setValue("options", 	        json_encode(rex_post('f_options')) );
				
		$db->setValue("status", 	        rex_post('f_status'));
		
		$db->setValue("clang_id", 	        $clang_id);
		

		if ($id > 0):
			$db->addGlobalUpdateFields();						//Standard Datumsfelder hinzufügen        
			$db->setWhere("id = '".$id."'");
			$dbreturn = $db->update();
			$lastID = $id;

			$form_error = (isset($_POST['submit-apply'])) ? 1 : $form_error;
		else:
			$hash = hash('sha256', base64_encode(uniqid('popman', true).microtime().random_bytes(10)) );
			$db->setValue("hash", $hash);
		
			$db->addGlobalCreateFields();						//Standard Datumsfelder hinzufügen
			$dbreturn = $db->insert();
			$lastID = $db->getLastId();
		endif;

		echo ($dbreturn) ? rex_view::info($this->i18n('a1837_entry_saved')) : rex_view::warning($this->i18n('a1837_error'));
	endif;

elseif ($func == "status" && $id > 0):
	//Status setzen
	$db = rex_sql::factory();
	$db->setQuery("SELECT status FROM ".rex::getTable($mainTable)." WHERE id = '".$id."' LIMIT 0,1"); 
	$dbe = $db->getArray();	//mehrdimensionales Array kommt raus
	
	$newstatus = ($dbe[0]['status'] != "checked") ? "checked" : "";
	
	$db = rex_sql::factory();
	$db->setTable(rex::getTable($mainTable));
	$db->setWhere("id = '".$id."'");

	$db->setValue("status", $newstatus);
	$db->update();
	
elseif ($func == "delete" && $id > 0):
	//Eintrag löschen - mit möglicher Prüfung auf Zuweisung
    /*
	$db = rex_sql::factory();
	$db->setQuery("SELECT id FROM ".rex::getTable($mainTable)." WHERE id_andereTabelle = '".$id."'"); 

	if ($db->getRows() <= 0):
    */
		//löschen
		$db = rex_sql::factory();
		$db->setTable(rex::getTable($mainTable));		
		$db->setWhere("id = '".$id."'");
			
		echo ($db->delete()) ? rex_view::info($this->i18n('a1837_entry_deleted')) : rex_view::warning($this->i18n('a1837_error_deleted'));
	/*
    else:
		//nicht löschen aufgrund gültiger Zuweisung
		echo rex_view::warning($this->i18n('a1837_entry_used'));
	endif;
    */
	
elseif ($func == "duplicate" && $id > 0):
	//Eintrag duplizieren
	$db = rex_sql::factory();
	$db->setQuery("SELECT * FROM ".rex::getTable($mainTable)." WHERE id = '".$id."'"); 
	
	if ($db->getRows() > 0):
		$dbe = $db->getArray();	//mehrdimensionales Array kommt raus
		$db = rex_sql::factory();
		$db->setTable(rex::getTable($mainTable));
		
		foreach ($dbe[0] as $key=>$val):			
			if ($key == 'id') { continue; }
			if ($key == 'status') { continue; }
			if ($key == 'title') { $val = a1837_duplicateName($val); }
			if ($key == 'hash') { $val = hash('sha256', base64_encode(uniqid('popman', true).microtime().random_bytes(10)) ); }
						
			$db->setValue($key, $val);
		endforeach;
		
        $db->addGlobalCreateFields();
		$dbreturn = $db->insert();

		$lastID = $db->getLastId();
	endif;
	
endif;


//Formular oder Liste ausgeben
if ($func == "update" || $func == "insert" || $form_error == 1):
	//gespeicherte Daten aus DB holen
	if (($mode == "update" || $func == "update") && $id > 0):
		$db = rex_sql::factory();
		$db->setQuery("SELECT * FROM ".rex::getTable($mainTable)." WHERE id = '".$id."' LIMIT 0,1"); 
		$dbe = $db->getArray();	//mehrdimensionales Array kommt raus
		
		//Values aufbereiten
		$dbe[0]["options"] = rex_var::toArray($dbe[0]["options"]);					//liefert kein Array zurück wenn leer
			$dbe[0]["options"] = (!is_array($dbe[0]["options"])) ? array() : $dbe[0]["options"];
			
		$dbe[0]["online_from"] =  (!empty($dbe[0]['online_from']))  ? date("d.m.Y H:i", $dbe[0]['online_from']) : '';
		$dbe[0]["online_to"] =    (!empty($dbe[0]['online_to']))    ? date("d.m.Y H:i", $dbe[0]['online_to']) : '';
	endif;
    
	
	//Std.vorgaben der Felder setzen
	if (!isset($dbe) || (is_array($dbe) && count($dbe) <= 0)):
		$db = rex_sql::factory();
		$db->setQuery("SELECT * FROM ".rex::getTable($mainTable)." LIMIT 0,1");
			foreach ($db->getFieldnames() as $fn) { $dbe[0][$fn] = ''; }
	endif;
	//$dbe[0] = array_map('htmlspecialchars', $dbe[0]);

	//Insert-Vorgaben
	if ($mode == "insert" || $id <= 0):
		$dbe[0]["options"] = array();
		
		$dbe[0]['options']['media_position'] 		= 'right';
		$dbe[0]['options']['shadow']				= 'checked';
		$dbe[0]['options']['overlay']				= 'checked';
		$dbe[0]['options']['closebtn'] 				= 'corner';
		$dbe[0]['options']['cookie']				= '1';
		$dbe[0]['options']['bgimage_fill']			= 'cover';
		$dbe[0]['options']['bgimage_position']		= 'cc';
		$dbe[0]['options']['bgcolor']				= '#FFFFFF';
		$dbe[0]['options']['popup_position']		= 'center';
		$dbe[0]['options']['popup_fx']				= 'pmpFX_fadeIn';
		
		$dbe[0]['options']['media_ratio']			= 'equal';
		$dbe[0]['options']['media_width']			= '50';
		$dbe[0]['options']['sizes_width']			= '800';
		$dbe[0]['options']['sizes_padding']			= '30';
		$dbe[0]['options']['sizes_padding_mobile']	= '15';
		$dbe[0]['options']['borderradius']			= '10';
		$dbe[0]['options']['delay']					= '0';
		
		$dbe[0]["online_from"] = 	str_replace("__.__.____ __:__", "", rex_post('f_online_from'));
		$dbe[0]["online_to"] = 		str_replace("__.__.____ __:__", "", rex_post('f_online_to'));		
	endif;
    
	
	//Formular bei Fehleingaben wieder befüllen
	if ($form_error):
		//Formular bei Fehleingaben wieder befüllen
		$dbe[0]['id'] = $id;
		
		$dbe[0]["title"] = 			rex_post('f_title');
		$dbe[0]["description"] = 	rex_post('f_description');
		$dbe[0]["media"] = 			rex_post('f_media');
		$dbe[0]["content"] = 		rex_post('f_content');
		$dbe[0]["options"] = 		rex_post('f_options');
		$dbe[0]["article_mode"] = 	rex_post('f_article_mode');
		$dbe[0]["article_ids"] = 	rex_post('f_article_ids');
		$dbe[0]["device"] = 		rex_post('f_device');

		$dbe[0]["online_from"] = 	str_replace("__.__.____ __:__", "", rex_post('f_online_from'));
		$dbe[0]["online_to"] = 		str_replace("__.__.____ __:__", "", rex_post('f_online_to'));
		
		$dbe[0]["status"] = 		rex_post('f_status');

		$func = $mode;
	endif;
    
	
	//Werte aufbereiten
	//Hash für Vorschau holen
	$hash = @$dbd[0]['hash'];

	//Editor setzen
	$editor_class = @$config['editor_class'];
	$editor_data = @$config['editor_data'];


    
	//Ausgabe: Formular (Update / Insert)
	?>

	<script type="text/javascript">jQuery(function() { jQuery('#f_title').focus(); });</script>
    
    <style type="text/css"></style>
    
    <form action="index.php?page=<?php echo $page; ?>" method="post" enctype="multipart/form-data">
    <!-- <input type="hidden" name="subpage" value="<?php echo $subpage; ?>" /> -->
    <input type="hidden" name="func" value="save" />
    <input type="hidden" name="id" value="<?php echo $dbe[0]['id']; ?>" />
	<input type="hidden" name="mode" value="<?php echo $func; ?>" />
    
    <section class="rex-page-section popupmanager">
        <div class="panel panel-edit">
        
            <header class="panel-heading"><div class="panel-title">
				<?php echo $this->i18n('a1837_head_basics'); ?>
				
				<span class="pull-right btn-group btn-group-xs">
					<?php if (!empty($hash)): ?>
					<a href="http://<?php echo $domain_name.'?hpbo_preview='.$hash; ?>" target="_blank" class="btn btn-default"><i class="rex-icon fa-eye"></i> <?php echo $this->i18n('a1837_bas_preview'); ?></a>
					<?php endif; ?>					
				</span>
				
			</div></header>
            
            <div class="panel-body">
            
				<dl class="rex-form-group form-group">
					<dt><label for=""><?php echo $this->i18n('a1837_std_status'); ?></label></dt>
					<dd>
						<div class="checkbox toggle">
							<label for="f_status"> <input type="checkbox" name="f_status" id="f_status" value="checked" <?php echo $dbe[0]['status']; ?> class="" /> <?php echo $this->i18n('a1837_yes'); ?> </label>
						</div>
					</dd>
				</dl>
			

                <dl class="rex-form-group form-group">
                    <dt><label for=""><?php echo $this->i18n('a1837_std_title'); ?></label></dt>
                    <dd>
                        <input type="text" size="25" name="f_title" id="f_title" value="<?php echo popupmanager_helper::maskChar($dbe[0]['title']); ?>" maxlength="100" class="form-control" />
                    </dd>
                </dl>
                
				
				<dl class="spacerline"></dl>
				<dl class="spacerline"></dl>
				

				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" class="">
						<a href="#content" role="tab" data-toggle="tab" data-tab="content"><?php echo $this->i18n('a1837_subheader_tab1'); ?></a>
					</li>
					<li role="presentation" >
						<a href="#background" role="tab" data-toggle="tab" data-tab="background"><?php echo $this->i18n('a1837_subheader_tab2'); ?></a>
					</li>
					<li role="presentation" >
						<a href="#options" role="tab" data-toggle="tab" data-tab="options"><?php echo $this->i18n('a1837_subheader_tab4'); ?></a>
					</li>
					<li role="presentation" >
						<a href="#dimensions" role="tab" data-toggle="tab" data-tab="dimensions"><?php echo $this->i18n('a1837_subheader_tab3'); ?></a>
					</li>
					<li role="presentation" >
						<a href="#display" role="tab" data-toggle="tab" data-tab="display"><?php echo $this->i18n('a1837_subheader_tab5'); ?></a>
					</li>
				</ul>
                
				<div class="tab-content">

					<!-- Tab: Texte & Bilder -->
					<div role="tabpanel" class="tab-pane fade" id="content">
						
						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_content'); ?></label></dt>
							<dd>
								<textarea rows="4" name="f_content" id="f_content" class="form-control <?php echo $editor_class; ?>" <?php echo $editor_data; ?>><?php echo popupmanager_helper::maskChar($dbe[0]['content']); ?></textarea>
								
								<?php if (preg_match("(ckeditor|cke5-editor)", $editor_class)): ?>
								<div class="checkbox toggle cb-small">
									<label> <input type="checkbox" name="f_options[editor_darkbg]" value="checked" <?php echo @$dbe[0]['options']['editor_darkbg']; ?> onclick="changeCKE_bgCol('f_content')" /> <?php echo $this->i18n('a1837_bas_change_editorbg'); ?> </label>
								</div>								
								<script>
								function changeCKE_bgCol(name='')
								{	if (name != '')
									{	src = $('textarea[name='+name+']');
										id = src.attr('id');
											cke = (src.hasClass('ckeditor') 	? 'cke4' : '');
											cke = (src.hasClass('cke5-editor') 	? 'cke5' : cke);
										
										if (cke == 'cke4') { var ed = $('div#cke_'+id+' iframe').contents().find('.cke_editable'); }
										if (cke == 'cke5') { var ed = src.next('.ck-editor').find('.ck-editor__main'); }
										if (ed.length){
											if (src.hasClass('isDarkEd')) { ed.css('background-color', '#FFFFFF'); src.removeClass('isDarkEd'); ed.removeClass('isDarkEd'); }
											else { ed.css('background-color', '#848382'); src.addClass('isDarkEd'); ed.addClass('isDarkEd'); }
										}
									}
								}								
								$(function(){								
									if ($('input[name="f_options[editor_darkbg]"]').is(':checked')) { setTimeout(function(){ changeCKE_bgCol('f_content'); }, 2000); }
								});
								</script>
								<style>.ck.ck-editor__main.isDarkEd > .ck-editor__editable { background-color: #848382; }</style>
								<?php endif; ?>
							</dd>
						</dl>


						<dl class="spacerline"></dl>
						

						<dl class="rex-form-group form-group">
							<dt><?php echo $this->i18n('a1837_bas_media'); ?>:</dt>
							<dd>
								<?php
								$elem = new rex_form_widget_media_element();
									$elem->setAttribute('class', 'form-control');
									$elem->setAttribute('name', 'f_media');
									$elem->setTypes('jpg,jpeg,gif,png,webp,svg');
									$elem->setValue(@$dbe[0]['media']);
								echo $elem->formatElement();
								?>
							</dd>
						</dl>
						

						<dl class="rex-form-group form-group" id="media_width" style="<?php echo (preg_match("/(left|right)/i", @$dbe[0]['options']['media_position'])) ? '' : 'display: none;'; ?>">
							<dd>
								<label for=""><?php echo $this->i18n('a1837_bas_media_width'); ?> <sup>*1</sup></label>

								<div class="input-group">
									<input name="f_options[media_width]" type="range" min="20" max="80" step="5" value="<?php echo @$dbe[0]['options']['media_width']; ?>" class="form-control inpRange" oninput="showRangeValue($(this), '%')" onchange="showRangeValue($(this), '%')" />
									<span class="input-group-addon"><div><?php echo @$dbe[0]['options']['media_width']; ?>%</div></span>
								</div>
							</dd>
							
						</dl>
																	
								
						<dl class="rex-form-group form-group">
							<dd>
								<div class="mb-fieldset-inline">

									<dl class="w300">
										<dt><?php echo $this->i18n('a1837_bas_media_ratio'); ?></dt>
										<dd>
											<select size="1" name="f_options[media_ratio]" class="form-control removeMargin">
											<?php
											$v = $this->i18n('a1837_bas_media_ratio_vertical');
											
											$arr = array("equal"=>$this->i18n('a1837_bas_media_ratio_equal'), "1-1"=>"1:1", "2-1"=>"2:1", "3-2"=>"3:2", "4-3"=>"4:3", "8-5"=>"8:5", "16-9"=>"16:9", "21-9"=>"21:9", "32-9"=>"32:9",
														 "1-2"=>"1:2 (".$v.")", "2-3"=>"2:3 (".$v.")", "3-4"=>"3:4 (".$v.")", "5-8"=>"5:8 (".$v.")", "9-16"=>"9:16 (".$v.")", "original"=>$this->i18n('a1837_bas_media_ratio_original')								
													);		//, "9-21"=>"9:21 (".$v.")", "9-32"=>"9:32 (".$v.")"
											
											foreach ($arr as $key=>$value):
												$sel = (@$dbe[0]['options']['media_ratio'] == $key) ? 'selected="selected"' : '';
												echo '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
											endforeach;
											?>
											</select>
										</dd>
									</dl>
									
									
									<dl class="w300">
										<dt><?php echo $this->i18n('a1837_bas_media_position'); ?> <sup>*1</sup></dt>
										<dd>
											<select size="1" name="f_options[media_position]" class="form-control removeMargin" id="media_position">
											<?php
											$arr = array("left"		=> $this->i18n('a1837_std_left'), 
														 "top"		=> $this->i18n('a1837_std_top'), 
														 "right"	=> $this->i18n('a1837_std_right'), 
														 "bottom"	=> $this->i18n('a1837_std_bottom') 
													);
											
											foreach ($arr as $key=>$value):
												$sel = (@$dbe[0]['options']['media_position'] == $key) ? 'selected="selected"' : '';
												echo '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
											endforeach;
											?>
											</select>
										</dd>
									</dl>						

								</div>
																
							</dd>
						</dl>
						
						
						<dl class="spacerline"></dl>
						
						
						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_textplaceholder'); ?></label></dt>
							<dd>
								<a href="javascript:;" onclick="$(this).hide().nextAll('div').show();" class="openerlink"><?php echo $this->i18n('a1837_bas_textplaceholder_opener'); ?></a>

								<!-- Textplaceholder vorbereiten -->
								<?php
								$dTph = rex_file::get(rex_addon::get($mypage)->getPath('data/textplaceholder.txt'));
								$tph = @$dbe[0]['options']['textplaceholder'];
									$tph = (empty($tph)) ? $dTph : $tph;
								?>
								<script id="textplaceholder-data" type="text/template"><?php echo $dTph; ?></script>
								
								<div class="hiddencontent">
									<textarea name="f_options[textplaceholder]" id="textplaceholder" rows="5" class="form-control"><?php echo $tph; ?></textarea>
									<span class="infoblock"><a href="javascript:$('#textplaceholder').val($('#textplaceholder-data').html());"><?php echo $this->i18n('a1837_bas_textplaceholder_info'); ?></a></span>
								</div>
							</dd>
							
						</dl>

						
						<dl class="spacerline"></dl>
						
						
						<dl class="rex-form-group form-group removeBottomMargin">
							<dt class="infoblock textblock">
								<?php echo $this->i18n('a1837_text1'); ?>
							</dt>
						</dl>

					</div>
					
					
					
					<!-- Tab: Hintergrund -->
					<div role="tabpanel" class="tab-pane fade" id="background">
						
						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_bg_color'); ?></label></dt>
							<dd>

								<div class="input-group field-colorinput-group">
									<input type="text" name="f_options[bgcolor]" value="<?php echo @$dbe[0]['options']['bgcolor']; ?>" maxlength="7" placeholder="<?php echo $this->i18n('a1837_bas_bg_color_example'); ?>" pattern="^#([A-Fa-f0-9]{6})$" class="form-control">
									<span class="input-group-addon field-colorinput">
										<input type="color" value="<?php echo @$dbe[0]['options']['bgcolor']; ?>" pattern="^#([A-Fa-f0-9]{6})$" class="form-control">
									</span>
								</div>	
							
							</dd>
						</dl>


						<dl class="spacerline"></dl>


						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_bg_image'); ?></label></dt>
							<dd>
								<?php
								$elem = new rex_form_widget_media_element();
									$elem->setAttribute('class', 'form-control');
									$elem->setAttribute('name', 'f_options[bgimage]');
									$elem->setTypes('jpg,jpeg,gif,png,webp,svg');
									$elem->setValue(@$dbe[0]['options']['bgimage']);
								echo $elem->formatElement();
								?>							
							</dd>
						</dl>


						<dl class="rex-form-group form-group">
							<dd>
								<div class="mb-fieldset-inline">

									<dl class="w300">
										<dt><?php echo $this->i18n('a1837_bas_bg_image_fill'); ?></dt>
										<dd>
											<select size="1" name="f_options[bgimage_fill]" class="form-control removeMargin">
											<?php
											$pos = array(""			=> $this->i18n('a1837_bas_bg_image_fill_none'), 
														 "cover"	=> $this->i18n('a1837_bas_bg_image_fill_cover'), 
														 "contain"	=> $this->i18n('a1837_bas_bg_image_fill_contain'), 
														 "repeat"	=> $this->i18n('a1837_bas_bg_image_fill_repeat'), 
														 "repeatX"	=> $this->i18n('a1837_bas_bg_image_fill_repeatx'), 
														 "repeatY"	=> $this->i18n('a1837_bas_bg_image_fill_repeaty')
													);
											
											foreach ($pos as $key=>$value):
												$sel = (@$dbe[0]['options']['bgimage_fill'] == $key) ? 'selected="selected"' : '';
												echo '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
											endforeach;
											?>
											</select>
										</dd>
									</dl>
									
									
									<dl class="w300">
										<dt><?php echo $this->i18n('a1837_bas_bg_image_position'); ?></dt>
										<dd>
											<select size="1" name="f_options[bgimage_position]" class="form-control removeMargin">
											<?php
											$pos = array("cc"	=> $this->i18n('a1837_bas_bg_image_position_cc'), 
														 "lt"	=> $this->i18n('a1837_bas_bg_image_position_lt'), 
														 "rt"	=> $this->i18n('a1837_bas_bg_image_position_rt'), 
														 "rb"	=> $this->i18n('a1837_bas_bg_image_position_rb'), 
														 "lb"	=> $this->i18n('a1837_bas_bg_image_position_lb'), 
														 "ct"	=> $this->i18n('a1837_bas_bg_image_position_ct'), 
														 "cr"	=> $this->i18n('a1837_bas_bg_image_position_cr'), 
														 "cb"	=> $this->i18n('a1837_bas_bg_image_position_cb'), 
														 "cl"	=> $this->i18n('a1837_bas_bg_image_position_cl')
													);
											
											foreach ($pos as $key=>$value):
												$sel = (@$dbe[0]['options']['bgimage_position'] == $key) ? 'selected="selected"' : '';
												echo '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
											endforeach;
											?>
											</select>
										</dd>
									</dl>						

								</div>
																
							</dd>
						</dl>


						<dl class="spacerline"></dl>


						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_bg_intocontent'); ?></label></dt>
							<dd>								
								<div class="checkbox toggle">
									<label> <input type="checkbox" name="f_options[bg_intocontent]" value="checked" <?php echo @$dbe[0]['options']['bg_intocontent']; ?> /> <?php echo $this->i18n('a1837_bas_bg_intocontent_info'); ?> </label>
								</div>
							</dd>
						</dl>

					</div>
					
					
					
					<!-- Tab: Größe & Abstände -->
					<div role="tabpanel" class="tab-pane fade" id="dimensions">
						
						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_sizes_width'); ?></label></dt>
							<dd>
								<div class="input-group">
									<input name="f_options[sizes_width]" type="range" min="200" max="1200" step="5" value="<?php echo @$dbe[0]['options']['sizes_width']; ?>" class="form-control inpRange" oninput="showRangeValue($(this), 'px')" onchange="showRangeValue($(this), 'px')" />
									<span class="input-group-addon"><div><?php echo @$dbe[0]['options']['sizes_width']; ?>px</div></span>
								</div>
								
								<div class="checkbox toggle">
									<label> <input type="checkbox" name="f_options[sizes_fullwidth]" value="checked" <?php echo @$dbe[0]['options']['sizes_fullwidth']; ?> /> <?php echo $this->i18n('a1837_bas_sizes_fullwidth'); ?> </label>
								</div>								
							</dd>
						</dl>
						

						<dl class="spacerline"></dl>						
						

						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_sizes_padding'); ?></label></dt>
							<dd>
								<div class="input-group">
									<input name="f_options[sizes_padding]" type="range" min="0" max="50" step="1" value="<?php echo @$dbe[0]['options']['sizes_padding']; ?>" class="form-control inpRange" oninput="showRangeValue($(this), 'px')" onchange="showRangeValue($(this), 'px')" />
									<span class="input-group-addon"><div><?php echo @$dbe[0]['options']['sizes_padding']; ?>px</div></span>
								</div>
							</dd>
						</dl>
						

						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_sizes_padding_mobile'); ?></label></dt>
							<dd>
								<div class="input-group">
									<input name="f_options[sizes_padding_mobile]" type="range" min="0" max="50" step="1" value="<?php echo @$dbe[0]['options']['sizes_padding_mobile']; ?>" class="form-control inpRange" oninput="showRangeValue($(this), 'px')" onchange="showRangeValue($(this), 'px')" />
									<span class="input-group-addon"><div><?php echo @$dbe[0]['options']['sizes_padding_mobile']; ?>px</div></span>
								</div>
							</dd>
						</dl>
						
						
						<!--
						<dl class="spacerline"></dl>
						
						
						<dl class="rex-form-group form-group removeBottomMargin">
							<dt class="infoblock textblock">
								<?php echo $this->i18n('a1837_text2'); ?>
							</dt>
						</dl>
						-->

					</div>
					
					
					
					<!-- Tab: Design -->
					<div role="tabpanel" class="tab-pane fade" id="options">

						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_options_design'); ?></label></dt>
							<dd>								
								<div class="checkbox toggle">
									<label> <input type="checkbox" name="f_options[shadow]" value="checked" <?php echo @$dbe[0]['options']['shadow']; ?> /> <?php echo $this->i18n('a1837_bas_options_shadow'); ?> </label>
								</div>
								
								<dl class="spacerline"></dl>
								
								<div class="checkbox toggle">
									<label> <input type="checkbox" name="f_options[overlay]" value="checked" <?php echo @$dbe[0]['options']['overlay']; ?> /> <?php echo $this->i18n('a1837_bas_options_overlay'); ?> </label>
								</div>
								<div class="checkbox toggle">
									<label> <input type="checkbox" name="f_options[overlay_blur]" value="checked" <?php echo @$dbe[0]['options']['overlay_blur']; ?> /> <?php echo $this->i18n('a1837_bas_options_overlay_blur'); ?> </label>
								</div>
							</dd>
						</dl>
						
						
						<dl class="rex-form-group form-group">
							<dd>
								<label for=""><?php echo $this->i18n('a1837_bas_options_borderradius'); ?> 
									<div class="presetlinks">
										<a class="presetlink-brnone" data-radius="0" title="<?php echo $this->i18n('a1837_bas_options_borderradius_preset_none'); ?>"><img src="../assets/addons/<?php echo $mypage; ?>/br-none.png" alt="" /></a>
										<a class="presetlink-brsmall" data-radius="10" title="<?php echo $this->i18n('a1837_bas_options_borderradius_preset_small'); ?>"><img src="../assets/addons/<?php echo $mypage; ?>/br-small.png" alt="" /></a>
										<a class="presetlink-brlarge" data-radius="25" title="<?php echo $this->i18n('a1837_bas_options_borderradius_preset_large'); ?>"><img src="../assets/addons/<?php echo $mypage; ?>/br-large.png" alt="" /></a>
									</div>
								</label>
								
								<div class="input-group">
									<input name="f_options[borderradius]" type="range" min="0" max="30" step="1" value="<?php echo @$dbe[0]['options']['borderradius']; ?>" class="form-control inpRange" oninput="showRangeValue($(this), 'px')" onchange="showRangeValue($(this), 'px')" />
									<span class="input-group-addon"><div><?php echo @$dbe[0]['options']['borderradius']; ?>px</div></span>
								</div>
							</dd>
						</dl>


						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_options_fx'); ?></label></dt>
							<dd>
								<select size="1" name="f_options[popup_fx]" class="form-control">
								<?php
								$pos = array("pmpFX_fadeIn"			=> $this->i18n('a1837_bas_options_fx_fade'), 
											 "pmpFX_slideUp"		=> $this->i18n('a1837_bas_options_fx_slideup'),
											 "pmpFX_slideDown"		=> $this->i18n('a1837_bas_options_fx_slidedown'),
											 "pmpFX_slideLeft"		=> $this->i18n('a1837_bas_options_fx_slideleft'),
											 "pmpFX_slideRight"		=> $this->i18n('a1837_bas_options_fx_slideright'),
											 "pmpFX_flipUp"			=> $this->i18n('a1837_bas_options_fx_flipup'),
											 "pmpFX_flipDown"		=> $this->i18n('a1837_bas_options_fx_flipdown'),
											 "pmpFX_flipLeft"		=> $this->i18n('a1837_bas_options_fx_flipleft'),
											 "pmpFX_flipRight"		=> $this->i18n('a1837_bas_options_fx_flipright'),
											 "pmpFX_zommIn"			=> $this->i18n('a1837_bas_options_fx_zoomin'), 
											 "pmpFX_zoomOut"		=> $this->i18n('a1837_bas_options_fx_zoomout')
										);
								
								foreach ($pos as $key=>$value):
									$sel = (@$dbe[0]['options']['popup_fx'] == $key) ? 'selected="selected"' : '';
									echo '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
								endforeach;
								?>
								</select>
							</dd>
						</dl>


						<dl class="spacerline"></dl>
						

						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_options_position'); ?></label></dt>
							<dd>
								<select size="1" name="f_options[popup_position]" class="form-control removeMargin">
								<?php
								$pos = array("top"		=> $this->i18n('a1837_std_top'), 
											 "center"	=> $this->i18n('a1837_std_center'), 
											 "bottom"	=> $this->i18n('a1837_std_bottom'),
											 ""			=> " ",
											 "top0"	=> $this->i18n('a1837_std_top@0'),
											 "bottom0"	=> $this->i18n('a1837_std_bottom@0'),											 
										);
								
								foreach ($pos as $key=>$value):
									$sel = (@$dbe[0]['options']['popup_position'] == $key) ? 'selected="selected"' : '';
									echo '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
								endforeach;
								?>
								</select>
							</dd>
						</dl>						
						
						
						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_options_closebtn'); ?></label></dt>
							<dd>
								<select size="1" name="f_options[closebtn]" class="form-control">
								<?php
								$pos = array("none"		=> $this->i18n('a1837_bas_options_closebtn_none'), 
											 "inner"	=> $this->i18n('a1837_bas_options_closebtn_inner'), 
											 "outer"	=> $this->i18n('a1837_bas_options_closebtn_outer'), 
											 "corner"	=> $this->i18n('a1837_bas_options_closebtn_corner')
										);
								
								foreach ($pos as $key=>$value):
									$sel = (@$dbe[0]['options']['closebtn'] == $key) ? 'selected="selected"' : '';
									echo '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
								endforeach;
								?>
								</select>
							</dd>
						</dl>

					</div>
					
					
					
					<!-- Tab: Anzeige-Einstellungen -->
					<div role="tabpanel" class="tab-pane fade" id="display">
						
						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_display_date'); ?></label></dt>
							<dd>
								<div class="datepicker-widget">
									<div class="input-group">
										<input type="text" size="25" name="f_online_from" id="f_online_from" value="<?php echo $dbe[0]['online_from']; ?>" maxlength="16" class="form-control" data-datepicker-time="true" data-datepicker-mask="true" />
										<span class="input-group-btn">
											<a href="#" class="btn btn-popup" onclick="return false;" title="<?php echo $this->i18n('a1837_calendar'); ?>" data-datepicker-dst="#f_online_from"><i class="rex-icon fa-calendar"></i></a>
										</span>
									</div>
								</div>
								
								<div class="datepicker-widget-spacer"><?php echo $this->i18n('a1837_bas_display_date_to'); ?></div>
								
								<div class="datepicker-widget">
									<div class="input-group">
										<input type="text" size="25" name="f_online_to" id="f_online_to" value="<?php echo $dbe[0]['online_to']; ?>" maxlength="16" class="form-control" data-datepicker-time="true" data-datepicker-mask="true" />
										<span class="input-group-btn">
											<a href="#" class="btn btn-popup" onclick="return false;" title="<?php echo $this->i18n('a1837_calendar'); ?>" data-datepicker-dst="#f_online_to"><i class="rex-icon fa-calendar"></i></a>
										</span>
									</div>
								</div>

								<div class="datepicker-widget-spacer"><a href="javascript:;" onclick="$('#f_online_from').val(''); $('#f_online_to').val(''); "><i class="rex-icon fa-trash"></i></a></div>

							</dd>
						</dl>


						<dl class="spacerline"></dl>
						
									
						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_display_articlemode'); ?></label></dt>
							<dd>
								<div class="radio toggle switch">
									<label for="mode1">
										<input name="f_article_mode" type="radio" value="all" id="mode1" <?php echo ($dbe[0]['article_mode'] != "whitelist" && $dbe[0]['article_mode'] != "blacklist") ? 'checked' : ''; ?> /> <?php echo $this->i18n('a1837_bas_display_articlemode_all'); ?>
									</label>
									<label for="mode2">
										<input name="f_article_mode" type="radio" value="whitelist" id="mode2" <?php echo ($dbe[0]['article_mode'] == "whitelist") ? 'checked' : ''; ?> /> <?php echo $this->i18n('a1837_bas_display_articlemode_whitelist'); ?>
									</label>
									<label for="mode3">
										<input name="f_article_mode" type="radio" value="blacklist" id="mode3" <?php echo ($dbe[0]['article_mode'] == "blacklist") ? 'checked' : ''; ?> /> <?php echo rex_i18n::rawmsg('a1837_bas_display_articlemode_blacklist'); ?>
									</label>
								</div>
							</dd>
						</dl>					
						
							<!-- Optionen für article_mode -->
							<div id="articlelist">
						
								<dl class="rex-form-group form-group">
									<dt><label for=""><?php echo $this->i18n('a1837_bas_display_articlemode_articles'); ?></label></dt>
									<dd>
										<?php
										rex_clang::setCurrentId($clang_id);
										$elem = new rex_form_widget_linklist_element();
											$elem->setAttribute('class', 'form-control');
											$elem->setAttribute('name', 'f_article_ids');
											$elem->setValue($dbe[0]['article_ids']);
										echo $elem->formatElement();
										?>
									</dd>
								</dl>		
								
						
								<dl class="spacerline"></dl>
								
							</div>
						
					
						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_display_device'); ?></label></dt>
							<dd>
								<div class="radio toggle switch">
									<label for="dev1">
										<input name="f_device" type="radio" value="all" id="dev1" <?php echo ($dbe[0]['device'] != "desktop" && $dbe[0]['device'] != "mobile") ? 'checked' : ''; ?> /> <?php echo $this->i18n('a1837_bas_display_device_all'); ?>
									</label>
									<label for="dev2">
										<input name="f_device" type="radio" value="desktop" id="dev2" <?php echo ($dbe[0]['device'] == "desktop") ? 'checked' : ''; ?> /> <?php echo $this->i18n('a1837_bas_display_device_desktop'); ?>
									</label>
									<label for="dev3">
										<input name="f_device" type="radio" value="mobile" id="dev3" <?php echo ($dbe[0]['device'] == "mobile") ? 'checked' : ''; ?> /> <?php echo $this->i18n('a1837_bas_display_device_mobile'); ?>
									</label>
								</div>
							</dd>
						</dl>
						
						
						<dl class="spacerline"></dl>
						
						
						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_display_delay'); ?></label></dt>
							<dd>
								<div class="input-group">
									<input name="f_options[delay]" type="range" min="0" max="5" step="1" value="<?php echo @$dbe[0]['options']['delay']; ?>" class="form-control inpRange" oninput="showRangeValue($(this), 'sek')" onchange="showRangeValue($(this), 'sek')" />
									<span class="input-group-addon"><div><?php echo @$dbe[0]['options']['delay']; ?> sek</div></span>
								</div>
							</dd>
						</dl>						
						
						
						<dl class="rex-form-group form-group">
							<dt><label for=""><?php echo $this->i18n('a1837_bas_display_cookie'); ?></label></dt>
							<dd>
								<select size="1" name="f_options[cookie]" class="form-control">
								<?php	//Basis: 1 Tag (davon Teile möglich als float)
								$pos = array("0"		=> $this->i18n('a1837_bas_display_cookie_none'), 
											 "0.0416667"	=> $this->i18n('a1837_bas_display_cookie_1h'), 
											 "0.0833333"	=> $this->i18n('a1837_bas_display_cookie_2h'), 
											 "0.25"			=> $this->i18n('a1837_bas_display_cookie_6h'), 
											 "0.5"			=> $this->i18n('a1837_bas_display_cookie_12h'),
											 "1"			=> $this->i18n('a1837_bas_display_cookie_1day'),
											 "3"			=> $this->i18n('a1837_bas_display_cookie_3days'),
											 "7"			=> $this->i18n('a1837_bas_display_cookie_7days'),
										);
								
								foreach ($pos as $key=>$value):
									$sel = (@$dbe[0]['options']['cookie'] == $key) ? 'selected="selected"' : '';
									echo '<option value="'.$key.'" '.$sel.'>'.$value.'</option>';
								endforeach;
								?>
								</select>
							</dd>
						</dl>

					</div>
					
				</div>
				
				
				<dl class="spacerline"></dl>

            </div>
			
			
			<script>
			$('.hiddencontent, #articlelist').hide();
			$("input[name=f_article_mode]").click(function(){ v = $(this).val(); dst = $("#articlelist"); dst.hide(); if (v == 'whitelist' || v == 'blacklist') { dst.show(); } });
			$("input[name=f_article_mode]:checked").trigger('click');
			
			//Klick Optionstoggler
			$(document).on("click", '.panel .optionstoggler', function(e) {
				e.preventDefault();
				dst = $(this).parent('div.tab-pane').find('div.hiddenOpt');
					if (!dst.hasClass('animate')) {				//zur Verhinderung von mehrfachem toggle
						dst.addClass('animate');
						dst.slideToggle( function() { $(this).removeClass('animate'); } );
					}
			});

			//Klick Tabs
			$(document).on('rex:ready', function(e,con) {
				$('.panel ul.nav').each(function(){
					dst = $(this).find('li:first-child a').trigger('click');
				});
			});
			
			//zus. Optionen je Bildposition einblenden
			$('#media_position').on('change', function() {
				dstMW = $('#media_width');
				dstMR = $('#media_ratio');
				dstMW.hide(); dstMR.hide();
				if ($(this).val() == 'left' || $(this).val() == 'right') { dstMW.show(); }
				else if ($(this).val() == 'top' || $(this).val() == 'bottom') { dstMR.show(); }
			});
			
			//Presetlinks
			$('.presetlinks > a').click(function(){
				v = parseInt($(this).data('radius'));
				$('input[name="f_options[borderradius]"]').val(v).trigger('oninput');
			})
			</script>			
            
            
            <footer class="panel-footer">
                <div class="rex-form-panel-footer">
                    <div class="btn-toolbar">
                        <input class="btn btn-save rex-form-aligned" type="submit" name="submit" title="<?php echo $this->i18n('a1837_save'); ?>" value="<?php echo $this->i18n('a1837_save'); ?>" />
                        <?php if ($func == "update"): ?>
                        <input class="btn btn-save" type="submit" name="submit-apply" title="<?php echo $this->i18n('a1837_apply'); ?>" value="<?php echo $this->i18n('a1837_apply'); ?>" />
                        <?php endif; ?>
                        <input class="btn btn-abort" type="submit" name="submit-abort" title="<?php echo $this->i18n('a1837_abort'); ?>" value="<?php echo $this->i18n('a1837_abort'); ?>" />
                    </div>
                </div>
            </footer>
            
        </div>
    </section>
    
    </form>


<?php
else:
	//Übersichtsliste laden + ausgeben
	// --> wird per AJAX nachgeladen !!!
	
	$addpath = "index.php?page=".$page;
	?>

    <section class="rex-page-section">
        <div class="panel panel-default">
        
            <header class="panel-heading"><div class="panel-title"><?php echo $this->i18n('a1837_overview').' '.$this->i18n('a1837_default'); ?></div></header>  
              
			<script type="text/javascript">
            jQuery(function() {
                //Ausblenden - Elemente
                jQuery('.search_options').hide();
                
                //Formfeld fokussieren
                jQuery('#s_sbeg').focus();
            
                //Liste - Filtern
                var params = 'page=<?php echo $page; ?>&subpage=load-defaultlist&sbeg=';
                var dst = '#ajax_jlist';
                
                jQuery('#db-order').click(function() {
                    var btn = jQuery(this);
                    btn.toggleClass('db-order-desc');
                        if (btn.hasClass('db-order-desc')) { btn.attr('data-order', 'desc'); } else { btn.attr('data-order', 'asc'); }
                    loadAJAX(params + getSearchParams(), dst, 0);
                });
                
                jQuery('#s_sbeg').keyup(function() {	loadAJAX(params + getSearchParams(), dst, 0); });
                
                jQuery('#s_button').click(function() { loadAJAX(params + getSearchParams(), dst, 0);	});
                jQuery('#s_resetsbeg').click(function() { jQuery('#s_gid').prop("checked", false); jQuery('#s_cat').val(0); jQuery('#s_sbeg').val("");
                                                          loadAJAX(params, dst, 0);	});
                                                                
                jQuery(document).on('click', 'span.ajaxNav', function(){
                    var navsite = jQuery(this).attr('data-navsite');
                    loadAJAX(params + getSearchParams(), dst, navsite);
                    jQuery("body, html").delay(150).animate({scrollTop:0}, 750, 'swing');
                });
                
                function getSearchParams()
                {	var searchparams = tmp = '';
                    searchparams += encodeURIComponent(jQuery('#s_sbeg').val());								//Suchbegriff (param-Name wird in "var params" gesetzt)
                    searchparams += '&order=' + encodeURIComponent(jQuery('#db-order').attr('data-order'));		//Sortierrichtung asc|desc
                    return searchparams;
                }
            });
            </script>

			<!-- Suchbox -->
			<table class="table table-striped addon_search" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<td class="td1" valign="middle">
							&nbsp;
						</td>
						<td class="td2"><img src="/assets/addons/<?php echo $mypage; ?>/indicator.gif" width="16" height="16" border="0" id="ajax_loading" style="display:none;" /></td>
						<td class="td3">

							<div class="input-group sbeg">
								<input class="form-control" type="text" name="s_sbeg" id="s_sbeg" maxlength="50" value="<?php echo popupmanager_helper::maskChar($_SESSION['as_sbeg_popupmanager']); ?>" placeholder="<?php echo $this->i18n('a1837_search_keyword'); ?>">
								<span class="input-group-btn">
									<a class="btn btn-popup form-control-btn" title="<?php echo $this->i18n('a1837_search_reset'); ?>" id="s_resetsbeg"><i class="rex-icon fa-close"></i></a>
								</span>
							</div>
							<input name="submit" type="button" value="<?php echo $this->i18n('a1837_search_submit'); ?>" class="button" id="s_button" style="display:none" />

						</td>
					</tr>
				</tbody>
			</table>


			<!-- Liste -->
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th class="rex-table-icon"><a href="<?php echo $addpath; ?>&func=insert" accesskey="a" title="<?php echo $this->i18n('a1837_new'); ?> [a]"><i class="rex-icon rex-icon-add-template"></i></a></th>
						<th class="rex-table-id">ID</th>
						<th><?php echo $this->i18n('a1837_bas_list_name'); ?> <a class="db-order db-order-desc" id="db-order" data-order="desc"><i class="rex-icon fa-sort"></i></a></th>
						<th><?php echo $this->i18n('a1837_bas_list_device'); ?></th>
						<th><?php echo $this->i18n('a1837_bas_list_displaysettings'); ?></th>
						<th class="rex-table-action" colspan="3"><?php echo $this->i18n('a1837_statusfunc'); ?></th>
					</tr>
				</thead>

				<tbody id="ajax_jlist">
					<script type="text/javascript">jQuery(function() { jQuery('#s_button').trigger('click'); });</script>
				</tbody>
			</table>
            

		</div>
	</section>

<?php
endif;
?>