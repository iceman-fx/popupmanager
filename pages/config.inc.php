<?php
/*
	Redaxo-Addon Popup-Manager
	Verwaltung: Einstellungen (config)
	v1.0.0
	by Falko Müller @ 2024
*/

/** RexStan: Vars vom Check ausschließen */
/** @var rex_addon $this */
/** @var array $config */
/** @var string $func */
/** @var string $page */
/** @var string $subpage */


//Variablen deklarieren
$form_error = 0;


//Formular dieser Seite verarbeiten
if ($func == "save" && isset($_POST['submit'])):

	//Modulauswahl aufbereiten
	$mods = rex_post('modules');
	$mods = (is_array($mods)) ? implode("#", rex_post('modules')) : '';

	//Konfig speichern
	$res = $this->setConfig('config', [
		'embed_script'				=> rex_post('embed_script'),
        'embed_css'					=> rex_post('embed_css'),
        'editor_class'				=> rex_post('editor_class'),
        'editor_data'				=> rex_post('editor_data'),
		'body_scrollbar'			=> rex_post('body_scrollbar'),
	]);

	//Rückmeldung
	echo ($res) ? rex_view::info($this->i18n('a1837_settings_saved')) : rex_view::warning($this->i18n('a1837_error'));

	//reload Konfig
	$config = $this->getConfig('config');
endif;


//Formular ausgeben
?>


<script>setTimeout(function() { jQuery('.alert-info').fadeOut(); }, 5000);</script>


<form action="index.php?page=<?php echo $page; ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="subpage" value="<?php echo $subpage; ?>" />
<input type="hidden" name="func" value="save" />

<section class="rex-page-section">
	<div class="panel panel-edit">
	
		<header class="panel-heading"><div class="panel-title"><?php echo $this->i18n('a1837_head_config'); ?></div></header>
		
		<div class="panel-body">
                  
            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1837_config_embed'); ?></label></dt>
                <dd>
                    <div class="checkbox toggle">
						<label for="embed_css">
                        	<input type="checkbox" name="embed_css" id="embed_css" value="checked" <?php echo @$config['embed_css']; ?> /> <?php echo $this->i18n('a1837_config_embed_css_info'); ?>
						</label>
                    </div>
					
                    <div class="checkbox toggle">
						<label for="embed_script">
                        	<input type="checkbox" name="embed_script" id="embed_script" value="checked" <?php echo @$config['embed_script']; ?> /> <?php echo $this->i18n('a1837_config_embed_script_info'); ?>
						</label>
                    </div>
					
					<div class="checkbox toggle">
						<label for="body_scrollbar">
							<input type="checkbox" name="body_scrollbar" id="body_scrollbar" value="checked" <?php echo @$config['body_scrollbar']; ?> /> <?php echo $this->i18n('a1837_config_body_scrollbar_info'); ?>
						</label>
					</div>
                </dd>
            </dl>
			
			
			<dl class="spacerline"></dl>
			
			
            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1837_config_editor_class'); ?></label></dt>
                <dd>
                    <input type="text" size="25" name="editor_class" id="editor_class" value="<?php echo popupmanager_helper::maskChar(@$config['editor_class']); ?>" maxlength="200" class="form-control" placeholder="<?php echo popupmanager_helper::maskChar($this->i18n('a1837_config_editor_class_example')); ?>" />
                </dd>
            </dl> 
            
            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1837_config_editor_data'); ?></label></dt>
                <dd>
                    <input type="text" size="25" name="editor_data" id="editor_data" value="<?php echo popupmanager_helper::maskChar(@$config['editor_data']); ?>" maxlength="200" class="form-control" placeholder='<?php echo $this->i18n('a1837_config_editor_data_example'); ?>' />
                </dd>
            </dl>			

		</div>
                
		
		<footer class="panel-footer">
			<div class="rex-form-panel-footer">
				<div class="btn-toolbar">
					<input class="btn btn-save rex-form-aligned" type="submit" name="submit" title="<?php echo $this->i18n('a1837_save'); ?>" value="<?php echo $this->i18n('a1837_save'); ?>" />
				</div>
			</div>
		</footer>
		
	</div>
</section>
	
</form>