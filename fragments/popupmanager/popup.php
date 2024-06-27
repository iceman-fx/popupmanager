<?php
/*
	Redaxo-Addon Popup-Manager
	Fragment: Popupvorlage (FE)
	v1.0.0
	by Falko Müller @ 2024
	
	Variablen-Nutzung: echo $this->myVar;
*/

//Vorgaben
$mainTable = "1837_popupmanager";
$popup = $this->popup;
    //dump($popup);

$self_url = preg_replace("/^(.*)\?.*/", "$1", $_SERVER['REQUEST_URI']);


//Parameter
$uid = (isset($popup['id']) && !empty($popup['id'])) ? $popup['id'] : uniqid().ceil(rand(0,9999999));


//Funktionen
if (!function_exists('mod_getSvgDimensions')):
function mod_getSvgDimensions($media)
{   $return = array('width'=>'', 'height'=>'');

    if (!empty($media)):
        $mObj = rex_media::get($media);

        if ($mObj->getType() == 'image/svg+xml'):
            $xml = simplexml_load_file(rex_path::media($media));

            $viewBox = $xml['viewBox'] ? $xml['viewBox']->__toString() : 0;
            $viewBox = preg_split('/[\s,]+/', $viewBox);
            $width = (float) ($viewBox[2] - $viewBox[0] ?? 0);
            $height = (float) ($viewBox[3] - $viewBox[1] ?? 0);

            if (!$height && !$width) {
                $width = $xml['width'] ? $xml['width']->__toString() : 0;
                $height = $xml['height'] ? $xml['height']->__toString() : 0;
            }

            $return['width']    = $width;
            $return['height']   = $height;
        endif;
    endif;
    
    return $return;
}
endif;


//Ausgabe
$options = @$popup['options'];
	$options = (!empty($options)) ? json_decode($options, true) : array();
	//dump($options);

$mediawidth = intval(@$options['media_width']);


//Texte-Platzhalter
$texts = @$options['textplaceholder'];
    $texts = popupmanager::getText('', $texts);


//Hintergrund aufbereiten
$cssBG = $styleBG = '';
$styleBG .= (!empty(@$options['bgcolor'])) ? ' background-color: '.$options['bgcolor'].';' : '';
if (!empty(@$options['bgimage'])):
	$img = (preg_match("/(.svg)$/i", $options['bgimage'])) ? '/media/'.$options['bgimage'] : rex_media_manager::getUrl('web_2000w', $options['bgimage']);
	
	$styleBG .= ' background-image: url('.$img.');';
	$cssBG .= (!empty(@$options['bgimage_fill']))		? ' bgifill-'.$options['bgimage_fill'] : '';
	$cssBG .= (!empty(@$options['bgimage_position']))	? ' bgipos-'.$options['bgimage_position'] : '';
endif;


//Content aufbereiten
$content = @$popup['content'];
	$cssC = $styleC = "";
	
	//Padding aufbereiten
	$padd = intval(@$options['sizes_padding']);
	$paddM = intval(@$options['sizes_padding_mobile']);
		$padding = ($paddM > 0) ? $paddM : $padd;
		$padding = ($padd > 0 && @$popup['device'] == 'desktop') ? $padd : $padding;
	$styleC .= ($padding > 0) ? ' padding: '.$padding.'px;' : '';
	
	//media_width aufbereiten
	$styleC .= (preg_match("/(left|right)/", @$options['media_position']) && $mediawidth > 0) ? ' max-width: calc(100% - '.$mediawidth.'%);' : '';
	
	//Hintergrund hinzufügen
	if (@$options['bg_intocontent'] == 'checked'):
		$styleC .= $styleBG;
		$cssC 	.= $cssBG;
	endif;
	
	$styleC = (!empty($styleC)) ? 'style="'.trim($styleC).'"' : '';
	
$content = (!empty($content)) ? '<div class="pmp-content '.$cssC.'" '.$styleC.'><div>'.$content.'</div></div>' : '';


//Medium (Bild) aufbereiten
$media = $styleIM = ""; $img = @$popup['media'];

if (!empty($img) && preg_match("/(.jpg|.jpeg|.png|.gif|.webp|.svg)$/i", $img)):
	//Mediadaten holen
	$mObj = rex_media::get($img);
		$mWidth = $mObj->getWidth();
		$mHeight = $mObj->getHeight();
			if (preg_match("/(.svg)$/i", $img)):
				$d = mod_getSvgDimensions($img);
				$mWidth     = $d['width'];
				$mHeight    = $d['height'];
			endif;
		
		$mTitle = $mAlt = htmlspecialchars($mObj->getTitle());
			//$mTitle = (empty($title)) ? $mTitle : $alttitle;
			
		//media_width aufbereiten
		$styleIM .= (preg_match("/(left|right)/", @$options['media_position']) && $mediawidth > 0) ? 'max-width: '.$mediawidth.'%;' : '';
		
		$styleIM = (!empty($styleIM)) ? 'style="'.trim($styleIM).'"' : '';
		
		//media_ratio aufbereiten
		$ratio = @$options['media_ratio'];
		$cssIM = (!empty($ratio)) ? 'pmp-format-'.$ratio : '';	
		
		
		//Media ausgeben
		$imgpath = (preg_match("/(.svg)$/i", $img)) ? '/media/'.$img : rex_media_manager::getUrl('web_600w', $img);
		
		$mediaimg = '<img src="'.$imgpath.'" alt="'.$mTitle.'" width="'.$mWidth.'" height="'.$mHeight.'" />';
		$media .= '<div class="pmp-image '.$cssIM.'" '.$styleIM.'>';

			if (preg_match("/(.svg)$/i", $img)):
				$media .= $mediaimg;
			else:
				$media .= '
					<picture>
						<source media="(min-width: 1600px)" srcset="'.rex_media_manager::getUrl('web_2000w', $img).'">
						<source media="(min-width: 1200px)" srcset="'.rex_media_manager::getUrl('web_1600w', $img).'">
						<source media="(min-width: 900px)" srcset="'.rex_media_manager::getUrl('web_1200w', $img).'">
						<source media="(min-width: 600px)" srcset="'.rex_media_manager::getUrl('web_900w', $img).'">
						<source media="(min-width: 300px)" srcset="'.rex_media_manager::getUrl('web_600w', $img).'">
						'.$mediaimg.'
					</picture>
				';
			endif;

		$media .= '</div>';
endif;


//Overlays aufbereiten
$cssO = (@$options['overlay'] == 'checked') ? 'pmp-overlay-color ' : '';
	$cssO .= (@$options['overlay_blur'] == 'checked') ? 'pmp-overlay-blur ' : '';
$overlay = (!empty($cssO)) ? '<div class="pmp-overlay '.$cssO.'"></div>' : '';

//Closebutton aufbereiten
$closebtn = '<button type="button" class="pmp-close" tabindex="0" title="'.@$texts['close_btn'].'" aria-label="close"><i class="fas fa-times"></i></button>';

//Einblendeffekt aufbereiten
$fx = 'pmpFX_fadeIn';
	$fx = (!empty(@$options['popup_fx'])) ? @$options['popup_fx'] : $fx;


//weitere CSS-Einstellungen
$cssP = $cssI = $styleW = $styleI = $styleS = '';

$cssP .= (!empty($media))												? ' hasimage' : '';
$cssP .= (!empty($content))												? ' hastext' : '';
$cssP .= (!empty($overlay))												? ' hasoverlay' : '';

$cssP .= (preg_match("/(desktop|mobile)/", @$popup['device'])) 			? ' only-'.$popup['device'] : '';
$cssP .= (preg_match("/(top|bottom)/", @$options['popup_position'])) 	? ' position-'.$options['popup_position'] : '';
$cssP .= (!empty($media) && !empty(@$options['media_position'])) 		? ' media-'.$options['media_position'] : '';

$cssP .= (@$options['sizes_fullwidth'] == 'checked')					? ' fullwidth' : '';
$cssP .= (@$options['shadow'] == 'checked')								? ' shadow' : '';

if (preg_match("/(inner|outer|corner)/", @$options['closebtn'])):
	$cssP .= ' closebtn-'.$options['closebtn'];
else:
	$closebtn = '';
endif;

$width = intval(@$options['sizes_width']);
$styleW .= ($width > 0 && @$options['sizes_fullwidth'] != 'checked') 	? ' max-width: '.$width.'px;' : '';


//Hintergrund hinzufügen
if (@$options['bg_intocontent'] != 'checked'):
	$styleI .= $styleBG;
	$cssI 	.= $cssBG;
endif;


//borderradius aufbereiten
$br = intval(@$options['borderradius']);
$styleS .= ($br > 0) ? ' border-radius: '.$br.'px;' : '';	


$styleW = (!empty($styleW)) ? 'style="'.trim($styleW).'"' : '';
$styleI = (!empty($styleI)) ? 'style="'.trim($styleI).'"' : '';
$styleS = (!empty($styleS)) ? 'style="'.trim($styleS).'"' : '';


//weitere DATA-Einstellungen
$dataP = '';
$dataP .= (!empty(@$options['delay'])) ? ' data-delay="'.$options['delay'].'"' : '';
$dataP .= ' data-padding="'.intval(@$options['sizes_padding']).'"';
$dataP .= ' data-paddingM="'.intval(@$options['sizes_padding_mobile']).'"';
$dataP .= (popupmanager::getConfig('body_scrollbar') == 'checked') ? ' data-bodyscrollbar="hide"' : '';
$dataP .= (!empty(@$options['cookie'])) ? ' data-cookie="'.@$options['cookie'].'"' : '';
?>

<div class="pm-popup <?php echo $cssP; ?>" id="pmp-<?php echo $uid; ?>" <?php echo $dataP; ?> tabindex="-1">
	<div class="pmp-wrapper <?php echo $fx; ?>" <?php echo $styleW; ?> tabindex="-1" aria-modal="true" role="dialog" title="<?php echo @$texts['popup_title']; ?>">
		<div class="pmp-scrollarea" <?php echo $styleS; ?>>
		<div class="pmp-inner <?php echo $cssI; ?>" <?php echo $styleI; ?>>
				<?php echo $media; ?>
				<?php echo $content; ?>
			</div>
		</div>
		
		<?php echo $closebtn; ?>
	</div>	
	
	<?php echo $overlay; ?>
</div>