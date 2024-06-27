<?php
/*
	Redaxo-Addon Popup-Manager
	Verwaltung: Hilfe
	v1.0.0
	by Falko Müller @ 2024
*/

/** RexStan: Vars vom Check ausschließen */
/** @var rex_addon $this */
/** @var array $config */
/** @var string $func */
/** @var string $page */
/** @var string $subpage */


//Vorgaben
?>

<style>
.faq { margin: 0px !important; cursor: pointer; }
.faq + div { margin: 0px 0px 15px; }
</style>

<section class="rex-page-section">
<div class="panel panel-default">

<header class="panel-heading"><div class="panel-title"><?php echo $this->i18n('a1837_head_help'); ?></div></header>

<div class="panel-body">
    <div class="rex-docs">
        <div class="rex-docs-sidebar">
            <nav class="rex-nav-toc">
                <ul>
					<li><a href="#start">Allgemein</a>
					<li><a href="#design">Gestaltung von Popups</a>
					<li><a href="#display">Anzeige-Einstellungen</a>
					<li><a href="#config">Einstellungen</a>
					<!--<li><a href="#faq">FAQ</a>-->
				</ul>
            </nav>
        </div>

                
<div class="rex-docs-content">
<h1>Addon: <?php echo $this->i18n('a1837_title'); ?></h1>


<!-- Alkgemein -->
<a name="start"></a>

<p>Mit dieser Erweiterung können beliebig viele Popups erstellt werden, welche anschließend auf der Homepage (Frontend) automatisch angezeigt werden.<br>
	Sofern die Redaxo-interne Mehrsprachigkeit aktiv ist, erfolgt die Definition der Popups je Sprachversion.
</p>
<p>Zur Gestaltung der jeweiligen Popups stehen verschiedene Optionen zur Verfügung. <br>
	Über die Anzeige-Einstellungen kann zusätzlich die Ausgabe eingeschränkt werden.</p>
	
	
<p>&nbsp;</p>



<!-- Gestaltung -->
<a name="design"></a>
<h2>Gestaltung von Popups</h2>

<p>Für die Gestaltung der  Popups stehen verschiedene Optionen zur Verfügung, welche nach Gruppen sortiert in der Eingabemaske des jeweiligen Popups bereitstehen.</p>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
    <th width="200" scope="col">Bereich</th>
    <th scope="col">Erklärung</th>
</tr>
  <tr>
    <td valign="top"><strong>Texte &amp; Bilder.</strong></td>
    <td valign="top">Hinterlegung Ihres Popup-Textes und/oder -Bildes.
    	<br>
    	<br>
    	<strong>Hinweis: </strong><br>
    	Bei Auswahl des Bildformates &quot;Höhe der höchsten Spalte&quot; passt sich die Bildgröße automatisch der Höhe des Textbereiches an.<br>
    	Sollte sich das Bild oberhalb oder unterhalb des Textes befinden, so ist die Auswahl eines festen Bildformates sinnvoller.</td>
  </tr>
  <tr>
    <td valign="top"><strong>Hintergrund</strong></td>
    <td valign="top">Definition des Popup-Hintergrundes (Farbe und/oder Bild)</td>
  </tr>
  <tr>
    <td valign="top"><strong>Design</strong></td>
    <td valign="top">Weitere Optionen für die Darstellung und Funktion des Popups.<br>
    	U.a. Design-Anpassungen, Position des Popups im Frontend &amp; Position der Schließen-Schaltfläche.</td>
  </tr>
  
  <tr>
    <td valign="top"><strong>Größen &amp; Abstände<br>
    </strong></td>
    <td valign="top">Definition der Größe des Popups sowie der Innenabstände des Textbereiches.<br>
    	Bei Auswahl der Bildposition Links/Rechts kann hier zusätzlich die Breite des Bildes definiert werden.</td>
  </tr>
  </table>
  

<p>&nbsp;</p>


<!-- Anzeige-Einstellungen -->
<a name="display"></a>
<h2>Anzeige-Einstellungen von Popups</h2>

<p>Über die Anzeige-Einstellungen eines jeden Popups kann die Ausgabe im Frontend individuell eingestellt werden.</p>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th width="200" scope="col">Option</th>
    <th scope="col">Erklärung</th>
</tr>
  <tr>
    <td valign="top"><strong>Anzeigen vom ...</strong></td>
    <td valign="top">Zeitgesteuerte Anzeige des Popups</td>
  </tr>
  <tr>
    <td valign="top"><strong>Anzeige auf Seiten</strong></td>
    <td valign="top">Festlegung der Popupanzeige für alle Homepage-Seiten oder nur auf ausgewählten Seiten.<br>
    	Wenn ausgewählte Seiten gewählt wird, so sind zusätzlich die gewünschten Seiten auszuwählen.</td>
  </tr>
  <tr>
    <td valign="top"><strong>Ansicht (Viewport)<br>
    </strong></td>
    <td valign="top">Darstellung des Popups auf dem gewünschten Viewport, wodurch u.a. getrennte Ausgabedesigns je nach Viewport definiert werden können.<br>
    	Die Steuerung erfolgt über die CSS-Definition.</td>
  </tr>
  <tr>
    <td valign="top"><strong>Zeitverzögerte Ansicht</strong></td>
    <td valign="top">Verzögerung beim Einblenden des Popups (0 sek. = sofortige Anzeige)</td>
  </tr>
  <tr>
    <td valign="top"><strong>Popup geschlossen halten</strong></td>
    <td valign="top">Die gewünschte Dauer, nach welcher das Popup erneut wieder angezeigt wird, nachdem es zuvor geschlossen wurde.</td>
  </tr>
  </table>


<p>&nbsp;</p>



<!-- Einstellungen -->
<a name="config"></a>
<h2>Einstellungen</h2>    

<p>Über die globalen Einstellungen definieren Sie u.a. die Einbindung des Texteditors als auch die Ausgabe der CSS- und Javascript-Vorgaben.</p>
<p>Sofern die benötigten CSS- &amp; JS-Dateien nicht automatisch eingebunden werden sollen, so deaktivieren Sie einfach die entsprechenden Auswahlen und speichern Ihre Einstellungen.<br>
	Die Auswahl &quot;Scrollbar der Webseite ausblenden&quot; deaktiviert bei sichtbarem Popup zusätzlich die Scrollmöglichkeit des Frontend-Bodys.</p>
<p>&nbsp;</p>



<!-- FAQ -->
<!--
<a name="faq"></a>
<h2>FAQ:</h2>

<p class="faq text-danger" data-toggle="collapse" data-target="#f001"><span class="caret"></span> xxxxx</p>
<div id="f001" class="collapse">xxxx</div>
-->




<p>&nbsp;</p>
<!-- Fragen / Probleme -->
<h3>Fragen, Wünsche, Probleme?</h3>
Du hast einen Fehler gefunden oder ein nettes Feature parat?<br>
Lege ein Issue unter <a href="<?php echo $this->getProperty('supportpage'); ?>" target="_blank"><?php echo $this->getProperty('supportpage'); ?></a> an. 


</div>
</div>

</div>
</div>
</section>