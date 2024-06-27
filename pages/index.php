<?php
/*
	Redaxo-Addon Popup-Manager
	Verwaltung: index
	v1.0.0
	by Falko Müller @ 2024
*/

/** RexStan: Vars vom Check ausschließen */
/** @var rex_addon $this */


//Variablen deklarieren
$mypage = $this->getProperty('package');

$page = rex_request('page', 'string');
$subpage = rex_be_controller::getCurrentPagePart(2);		//Subpages werden aus page-Pfad ausgelesen (getrennt mit einem Slash, z.B. page=demo_addon/subpage -> 2 = zweiter Teil)
	$tmp = rex_request('subpage', 'string');
	$subpage = (!empty($tmp)) ? $tmp : $subpage;
$func = rex_request('func', 'string');

$config = $this->getConfig('config');


//Userrechte prüfen
//$isAdmin = ( is_object(rex::getUser()) AND (rex::getUser()->hasPerm($mypage.'[config]') OR rex::getUser()->isAdmin()) ) ? true : false;



//Seitentitel ausgeben
echo rex_view::title($this->i18n('a1837_title').'<span class="addonversion">'.$this->getProperty('version').'</span>');



//globales Inline-CSS + Javascript
?>
<style type="text/css">
input.rex-form-submit { margin-left: 190px !important; }	/* Rex-Button auf neue (Labelbreite +10) verschieben */
td.name { position: relative; padding-right: 20px !important; }
.nowidth { width: auto !important; }
.togglebox { display: none; margin-top: 8px; font-size: 90%; color: #666; line-height: 130%; }
.toggler { width: 15px; height: 12px; position: absolute; top: 10px; right: 3px; }
.toggler a { display: block; height: 11px; background-image: url(../assets/addons/<?php echo $mypage; ?>/arrows.png); background-repeat: no-repeat; background-position: center -6px; cursor: pointer; }
.required { font-weight: bold; }
.nobold { font-weight: normal; }
.inlinelabel { display: inline !important; width: auto !important; float: none !important; clear: none !important; padding: 0px  !important; margin: 0px !important; font-weight: normal !important; }
.inlineform { display: inline-block !important; }
.form_auto { width: auto !important; }
.form_plz { width: 25%px !important; margin-right: 6px; }
.form_ort { width: 73%px !important; }
.form_25perc { width: 25% !important; min-width: 120px; }
.form_50perc { width: 50% !important; min-width: 120px; }
.form_75perc { width: 75% !important; }
.form_content { display: block; padding-top: 5px; }
.form_readonly { background-color: #EEE; color: #999; }
.form_isoffline { color: #A00; }
.addonversion { margin-left: 7px; }
.radio label, .checkbox label { margin-right: 20px; }
.spacerline { display: block; height: 7px; margin-bottom: 15px; }
.cur-p { cursor: pointer; }
.cur-d { cursor: default; }


.form_2spaltig > div { display: inline-block; width: 49%; }

.datepicker-widget { display: inline-block; vertical-align: middle; /*margin-right: 10px;*/ }
	.datepicker-widget-spacer { display: inline-block; vertical-align: middle; padding: 0px 5px 0px 15px; }
.daterangepicker { box-shadow: 3px 3px 10px 0px rgb(0,0,0, 0.2); }
.daterangepicker .calendar-table th, .daterangepicker .calendar-table td { padding: 2px; /*line-height: 20px;*/ }
@media (max-width: 768px){ .datepicker-widget { margin-right: 0px; } .datepicker-widget-spacer { display: block; margin-top: 7px; padding: 0px; } }

.addon_failed, .addonfailed { color: #F00; font-weight: bold; margin-bottom: 15px; }
.addon_search { width: 100%; background-color: #EEE; }
.addon_search .searchholder { position: relative; display: inline-block; }
	.addon_search .searchholder a { position: absolute; top: 0px; right: 0px; bottom: 0px; cursor: pointer; padding: 5px 3px 0px; }
		.addon_search .searchholder img { vertical-align: top; }
	@-moz-document url-prefix('') { .addon_search .searchholder a { top: 0px; } /* FF-only */ }
.addon_search .border-top { border-top: 1px solid #DFE9E9; }
.addon_search td { width: 46%; padding: 9px !important; font-size: 90%; color: #333; border: none !important; vertical-align: top !important; }
	.addon_search td.td2 { width: 8%; text-align: center; }
	.addon_search td.td3 { text-align: right; }

.addon_search .form-control { padding: 1px 8px; font-size: 13px; float: none; }
.addon_search .form-control-btn { padding: 2px 8px; font-size: 12px; }

.addon_search .input-group.sbeg { margin-left: auto; }
@media (min-width: 768px){ .addon_search .input-group.sbeg { max-width: 180px; } }
		
.addon_search select { margin: 0px; width: 100%; max-width: 230px; display: inline-block; }
	.addon_search select.multiple { height: 60px !important; }
	.addon_search select.form_auto { width: auto !important; max-width: 634px; }
.addon_search input.checkbox { display: inline-block; width: auto; margin: 0px 6px !important; padding: 0px !important; height: auto !important; }
.addon_search input.button { font-weight: bold; margin: 0px !important; width: auto; padding: 0px 4px !important; height: 22px !important; font-size: 0.9em; background: #FFF; border: 1px solid #323232; }
.addon_search label { display: inline-block; width: 90px !important; font-weight: normal; }
	.addon_search label.multiple { vertical-align: top !important; }
	.addon_search label.form_auto { width: auto !important; }
.addon_search a.moreoptions { display: inline-block; vertical-align: sub; }
.addon_search .rightmargin { margin-right: 7px !important; }
.addon_search .btn-group-xs { margin-right: 7px; }

.addon_inlinegroup { display: inline-block; }
.addon_input-group { display: table; }
	.addon_input-group > * { display: table-cell; border-radius: 0px; border: 1px solid #7586a0; margin-left: -1px; }
	.addon_input-group > *:first-child { margin: 0px; }
	.addon_input-group > *:last-child { border-radius: 0px 2px 2px 0px; }
.addon_input-group-field {}
.addon_input-group-btn {}

.addon_search .btn-group-xs { margin-right: 7px; }

.mb-fieldset-inline dl { display: inline-block; width: 100%; max-width: 200px; vertical-align: top; margin: 0px 15px 7px 0px; }
	.mb-fieldset-inline dl.fullwidth { max-width: none; }
	.mb-fieldset-inline dl.w300 { max-width: 300px; }
.mb-fieldset-inline dt { display: block; width: auto; min-width: 0px; padding-top: 0px; padding-right: 0px; }
.mb-fieldset-inline dt label { font-weight: normal; margin-bottom: 2px; min-width: 130px; }

.info { font-size: 0.825em; font-weight: normal; }
.info-labels { display: inline-block; padding: 3px 6px; background: #EAEAEA; margin-right: 5px; font-size: 0.80em; }
	.info-green { background: #360; color: #FFF; }
	.info-red { background: #900; color: #FFF; }
.infoblock { display: block; font-size: 0.825em; margin-top: 7px; }
.textblock { width: auto !important; font-weight: normal; padding-bottom: 10px; }
.charlimitreached { background-color: rgba(255,0,0, 0.15) !important; }
a.copyfromabove { cursor: pointer; }
a.openerlink { display: inline-block; }
	@media (min-width: 992px){ a.openerlink { margin-top: 7px; } }

td.sortbuttons { width: 1%; min-width: 72px; }
.db-order { display: inline; padding: 0px 5px; margin-left: 0px; cursor: pointer; }
.db-order-desc { background-position: center bottom; }.block { display: block; }
span.ajaxNav { cursor: pointer; }
span.ajaxNav:hover { background-color: #666; color: #FFF; }
span.ajaxNavSel { background-color: #CCC; }

.checkbox.toggle label input, .radio.toggle label input { -webkit-appearance: none; -moz-appearance: none; appearance: none; width: 3em; height: 1.5em; background: #ddd; vertical-align: middle; border-radius: 1.6em; position: relative; outline: 0; margin-top: -3px; margin-right: 10px; cursor: pointer; transition: background 0.1s ease-in-out; }
	.checkbox.toggle-dark label input, .radio.toggle-dark label input { background: #CCC; }
	.checkbox.toggle label input::after, .radio.toggle label input::after, .radio.switch label input::before { content: ''; width: 1.5em; height: 1.5em; background: white; position: absolute; border-radius: 1.2em; transform: scale(0.7); left: 0; box-shadow: 0 1px rgba(0, 0, 0, 0.5); transition: left 0.1s ease-in-out; }
.checkbox.toggle label input:checked, .radio.toggle label input:checked { background: #5791CE; }
	.checkbox.toggle label input:checked::after { left: 1.5em; }

.radio.switch label { margin-right: 1.5em; }
.radio.switch label input { width: 1.5em; margin-right: 5px; }
	.radio.switch label input:checked::after { transform: scale(0.5); }
.radio.switch label input::before { background: #5791CE; opacity: 0; box-shadow: none; }
	.radio.switch label input:checked::before { animation: radioswitcheffect 0.65s; }
@keyframes radioswitcheffect { 0% { opacity: 0.75; } 100% { opacity: 0; transform: scale(2.5); } }

/* Checkbox-Toggler small */
.cb-small { zoom: 0.75; }
.cb-small label { margin-right: 0px !important; }
.cb-small label input[type=checkbox].toggle { margin-right: 8px; }


.optionsblock { display: inline-block; vertical-align: top; min-width: 182px; margin: 0px 23px 18px 0px; padding: 7px 14px; transition: all .3s ease; }
	.optionsblock:hover { background: #FFF; }
.optionsblock label { margin-right: 0px !important; }
.optionsblock ul { list-style: none; margin: 0px; padding: 0px;}
.optionsblock li { margin: 0px 0px 5px; }

.faq { margin: 0px !important; cursor: pointer; }
.faq + div { margin: 0px 0px 15px; }
.rex-docs p code { background: #343f50; border-radius: 3px; color: #f3f6fb; line-height: 2; padding: 1px 5px; }

.field-disabled { pointer-events: none; user-select: none; opacity: 0.5; }

.removeMargin { margin: 0px !important; }
.removeTopMargin { margin-top: 0px; }
.removeBottomMargin { margin-bottom: 0px !important; }
.removePadding { padding: 0px; }
.addTopPadding { padding-top: 10px; }

.field-colorinput-group .field-colorinput { position: relative; min-width: 100px; background: #FFF; padding: 0px; }
.field-colorinput-group .field-colorinput input { position: absolute !important; top: 0px; left: 0px; -webkit-appearance: none; -moz-appearance: none; appearance: none; background: #FFF; border: none; width: 100%; height: 100%; padding: 0px; cursor: pointer; }
.field-colorinput-group .field-colorinput input::-webkit-color-swatch-wrapper { padding: 0; }
.field-colorinput-group .field-colorinput input::-webkit-color-swatch { border: none; }

.inpRange { -webkit-appearance: none; -moz-appearance: none; appearance: none; outline: none; padding: 14px 11px; }
.inpRange+span { min-width: 78px; }

/* Mehrspaltigkeit */
@media (min-width: 1200px){
    .panel [class^=col-] dt { padding-left: 2vw; }
    .panel [class^=col-]:first-child dt { padding-left: inherit; }
}

/* Tab-Panels */
.panel .nav { margin-bottom: 0; }
.panel .nav > li > a { border-radius: 4px 4px 0px 0px; border-top-width: 2px; border-color: #9ca5b2 #9ca5b2 #9ca5b2; background: #EEE; color: #555; padding: 7px 10px; }
	.panel .nav > li > a:hover { background: #FFF; color: #555; border-color: #9ca5b2 #9ca5b2 #9ca5b2; }
	.panel .nav > li.active > a, .panel .nav > li.active > a:hover, .panel .nav > li.active > a:focus { background: #FFF; border: 1px solid #9ca5b2; border-bottom-color: transparent; border-top-width: 2px; }	
.panel .tab-content { border: 1px solid #9ca5b2; border-top: none; background: #FFF; }
.panel .tab-pane { position: relative; padding: 20px; }

.panel .tab-pane .optionstoggler { position: absolute; background: #9ca5b2; padding: 0px; line-height: 10px; vertical-align: top; height: 18px; letter-spacing: 1px; color: #FFF; border-radius: 2px; font-size: 12px; font-weight: bold; left: 50%; bottom: -9px; margin: 0px 0px 0px -50px; width: 100px; text-align: center; cursor: pointer; }
	.panel .tab-pane .optionstoggler:hover { background: #5bb585; }    


<?php if (rex_version::compare(rex::getVersion(), '5.13.0-dev', '>=')): ?>
/* DarkMode */
body.rex-theme-dark .checkbox.toggle label input,
body.rex-theme-dark .radio.toggle label input
	{ background: #202b35; }
body.rex-theme-dark .checkbox.toggle label input::after, 
body.rex-theme-dark .radio.toggle label input::after, 
body.rex-theme-dark .radio.switch label input::before
	{ background: #CCC; }

body.rex-theme-dark .checkbox.toggle label input:checked,
body.rex-theme-dark .radio.toggle label input:checked 
	{ background: #409be4; }
body.rex-theme-dark .checkbox.toggle label input:checked::after, 
body.rex-theme-dark .radio.toggle label input:checked::after, 
body.rex-theme-dark .radio.switch label input:checked::before
	{ background: #EEE; }

body.rex-theme-dark .presetlinks a { background: #263c3c; }

/* Tab-Panels */
body.rex-theme-dark .panel .nav > li > a { background: #1f3238; color: rgba(255, 255, 255, 0.75); border-color: rgba(21,28,34, 0.8) rgba(21,28,34, 0.8) rgba(21,28,34, 0.8); }
	body.rex-theme-dark .panel .nav > li > a:hover,
	body.rex-theme-dark .panel .nav > li.active > a, 
	body.rex-theme-dark .panel .nav > li.active > a:hover, 
	body.rex-theme-dark .panel .nav > li.active > a:focus 
		{ background: #365150; border-color: rgba(21,28,34, 0.8) rgba(21,28,34, 0.8); border-bottom-color: #365150; }	
body.rex-theme-dark .panel .tab-content { border-color: rgba(21,28,34, 0.8); background-color: #365150; }

body.rex-theme-dark .panel .tab-pane .optionstoggler { background: #1c282f; border: 1px solid rgba(21,28,34, 0.8); color: rgba(255, 255, 255, 0.75); }
	body.rex-theme-dark .panel .tab-pane .optionstoggler:hover { background: rgba(35,45,57, 0.8); }


@media (prefers-color-scheme: dark){	
	body:not(.rex-theme-light) .checkbox.toggle label input,
	body:not(.rex-theme-light) .radio.toggle label input
		{ background: #202b35; }
	body:not(.rex-theme-light) .checkbox.toggle label input::after, 
	body:not(.rex-theme-light) .radio.toggle label input::after, 
	body:not(.rex-theme-light) .radio.switch label input::before
		{ background: #CCC; }
	
	body:not(.rex-theme-light) .checkbox.toggle label input:checked,
	body:not(.rex-theme-light) .radio.toggle label input:checked 
		{ background: #409be4; }
	body:not(.rex-theme-light) .checkbox.toggle label input:checked::after, 
	body:not(.rex-theme-light) .radio.toggle label input:checked::after, 
	body:not(.rex-theme-light) .radio.switch label input:checked::before
		{ background: #EEE; }
		
	body:not(.rex-theme-light) .presetlinks a { background: #263c3c; }
		
	/* Tab-Panels */
	body:not(.rex-theme-light) .panel .nav > li > a { background: #1f3238; color: rgba(255, 255, 255, 0.75); border-color: rgba(21,28,34, 0.8) rgba(21,28,34, 0.8) rgba(21,28,34, 0.8); }
		body:not(.rex-theme-light) .panel .nav > li > a:hover,
		body:not(.rex-theme-light) .panel .nav > li.active > a, 
		body:not(.rex-theme-light) .panel .nav > li.active > a:hover, 
		body:not(.rex-theme-light) .panel .nav > li.active > a:focus 
			{ background: #365150; border-color: rgba(21,28,34, 0.8) rgba(21,28,34, 0.8); border-bottom-color: #365150; }	
	body:not(.rex-theme-light) .panel .tab-content { border-color: rgba(21,28,34, 0.8); background-color: #365150; }

	body:not(.rex-theme-light) .panel .tab-pane .optionstoggler { background: #1c282f; border: 1px solid rgba(21,28,34, 0.8); color: rgba(255, 255, 255, 0.75); }
		body:not(.rex-theme-light) .panel .tab-pane .optionstoggler:hover { background: rgba(35,45,57, 0.8); }
}
<?php endif; ?>


/* Addon-spezifische Styles */
.presetlinks { display: inline-block; margin-left: 5px; }
.presetlinks a { display: inline-block; padding: 5px; background: #eee; line-height: 1; border-radius: 5px; cursor: pointer; }
	.presetlinks a:hover { background: #e0e3e9; }
.presetlinks img { width: 15px; height: auto; }
</style>

<script type="text/javascript">
setTimeout(function() { jQuery('.alert-info').fadeOut(); }, 5000);			//Rückmeldung ausblenden


//beim Start ausführen
jQuery(function(){
	jQuery.datetimepicker.setLocale('de');
	jQuery('.datepicker-widget input').each(function(){
		lazy = (jQuery(this).attr('data-datepicker-lazy') == 'true' ? true : false);
		mask = (jQuery(this).attr('data-datepicker-mask') == 'true' ? true : false);
		time = (jQuery(this).attr('data-datepicker-time') == 'true' ? true : false);
		format = (time ? 'd.m.Y H:i' : 'd.m.Y');
		
		yearstart = jQuery(this).attr('data-datepicker-yearstart');
			yearstart = (yearstart == 'now' ? <?php echo date("Y"); ?> : parseInt(yearstart));
			yearstart = (Number.isInteger(yearstart) ? yearstart : <?php echo date("Y")-1; ?>);
		yearend   = jQuery(this).attr('data-datepicker-yearend');
			yearend = (yearend == 'now' ? <?php echo date("Y"); ?> : parseInt(yearend));
			yearend = (Number.isInteger(yearend) ? yearend : <?php echo date("Y")+10; ?>);
			
		jQuery(this).datetimepicker({
			format: format, formatDate: 'd.m.Y', formatTime: 'H:i', yearStart: yearstart, yearEnd: yearend, dayOfWeekStart: 1,
			mask: mask, lazyInit: lazy, week: true, timepicker: time, step: 15
		});
	});
	
	jQuery('.datepicker-widget a').click(function(){
		dst = jQuery(this).attr('data-datepicker-dst');
		if (dst != "" && dst != 'undefined') { jQuery(dst).datetimepicker('show'); }
	});	
});


//Rangeslider Values
function showRangeValue(obj, unit=''){
	if (obj) { val = obj.val(); obj.next('span').html('<div>' +val+unit+ '</div>'); }
}
$('.input-group input[type=range]').trigger('onchange');


//Funktionen
function getset(getid, setid)
{	if (getid != "" && setid != "") {
		var getval = jQuery('#'+getid+' option:selected').val();
			if (getval != "") { jQuery('#'+setid).val(getval); }
	}
}

function toggleContent(dst, src)
{	if (dst != "" && dst != 'undefined') {
		jQuery(dst).toggle();
		if (src.length > 0) {
			console.log(src.length);
			if (typeof src == 'string') { src = jQuery(src); }
			src.toggle();
		}
	}
}

function loadAJAX(params, dst, paramNav)
{	if (dst != ""){
		paramNav = parseInt(paramNav);
		if (params != "" && paramNav >= 0) params += '&';
			params += 'limStart='+ encodeURIComponent(paramNav);
		var jlLoader = jQuery('#ajax_loading');
			jlLoader.show();
		jQuery.post("index.php", params, function(resp){ jQuery(dst).html(resp); jlLoader.hide(); });
	}
}

function showRexLoader()
{	rexAjaxLoaderId = setTimeout(function (){
		document.documentElement.style.overflowY = 'hidden'; // freeze scroll position
		document.querySelector('#rex-js-ajax-loader').classList.add('rex-visible');
	}, 200);
}
</script>


<?php
//Unterseite einbinden
switch($subpage):
	case "load-defaultlist":	//AJAX Loader : Default-Liste
								require_once("ajax.load-defaultlist.inc.php");
								break;
                                

	case "default":				//Index = Vorlagen
								require_once("default.inc.php");
								break;

	case "config":				//Einstellungen
								require_once("config.inc.php");
								break;

	case "help":				//Hilfe
								require_once("help.inc.php");
								break;


	default:					//alle anderen Einbindungen direkt per Rex-Subpath
								rex_be_controller::includeCurrentPageSubPath();
								break;
endswitch;
?>


<!-- PLEASE DO NOT REMOVE THIS COPYRIGHT -->
<p><?php echo $this->getProperty('author'); ?></p>
<!-- THANK YOU! -->