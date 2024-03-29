<?php
/**
 * Vector - Modern version of MonoBook with fresh look and many usability
 * improvements.
 *
 * @todo document
 * @file
 * @ingroup Skins
 */
if( !defined( 'MEDIAWIKI' ) ) {
  die( -1 );
}
/**
 * SkinTemplate class for Vector skin
 * @ingroup Skins and others
 */
class SkinBootstrap extends SkinTemplate {
  var $skinname = 'bebootstrapskin', $stylename = 'bootstrapskin',
    $template = 'StrappingTemplate', $useHeadElement = true;
  /**
   * Initializes output page and sets up skin-specific parameters
   * @param $out OutputPage object to initialize
   */
  public function initPage( OutputPage $out ) {
    global $wgLocalStylePath;
    parent::initPage( $out );
    // Append CSS which includes IE only behavior fixes for hover support -
    // this is better than including this in a CSS fille since it doesn't
    // wait for the CSS file to load before fetching the HTC file.
    $min = $this->getRequest()->getFuzzyBool( 'debug' ) ? '' : '.min';
    $out->addHeadItem( 'csshover',
      '<!--[if lt IE 7]><style type="text/css">body{behavior:url("' .
        htmlspecialchars( $wgLocalStylePath ) .
        "/{$this->stylename}/csshover{$min}.htc\")}</style><![endif]-->"
    );

  //Replace the following with your own google analytic info
/*
  $out->addHeadItem( 'analytics',
            '<script type="text/javascript">'."
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-22632659-2']);
  _gaq.push(['_setDomainName', 'blue-eng.km.tu-berlin.de']);
  _gaq.push(['_setAllowHash', 'false']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>"
	);
 */
    $out->addHeadItem('responsive', '<meta name="viewport" content="width=device-width, initial-scale=1.0">');
    $out->addModuleScripts( 'skins.bootstrapskin' );
  }
  /**
   * Load skin and user CSS files in the correct order
   * fixes bug 22916
   * @param $out OutputPage object
   */
  function setupSkinUserCss( OutputPage $out ){
    global $wgResourceModules;
    parent::setupSkinUserCss( $out );
    // FIXME: This is the "proper" way to include CSS
    // however, MediaWiki's ResourceLoader messes up media queries
    // See: https://bugzilla.wikimedia.org/show_bug.cgi?id=38586
    // &: http://stackoverflow.com/questions/11593312/do-media-queries-work-in-mediawiki
    //
    //$out->addModuleStyles( 'skins.strapping' );
    // Instead, we're going to manually add each,
    // so we can use media queries
    foreach ( $wgResourceModules['skins.bootstrapskin']['styles'] as $cssfile => $cssvals ) {
      if (isset($cssvals)) {
        $out->addStyle( $cssfile, $cssvals['media'] );
      } else {
        $out->addStyle( $cssfile );
      }
    }
  }
}
/**
 * QuickTemplate class for Vector skin
 * @ingroup Skins
 */
class StrappingTemplate extends BaseTemplate {
  /* Functions */
  /**
   * Outputs the entire contents of the (X)HTML page
   */
  public function execute() {
    global $wgGroupPermissions;
    global $wgVectorUseIconWatch;
    global $wgSearchPlacement;
    global $wgBootstrapSkinLogoLocation;
    global $wgBootstrapSkinLoginLocation;
    global $wgBootstrapSkinAnonNavbar;
    global $wgBootstrapSkinUseStandardLayout;
    global $wgBootstrapSkinUseSidebar;
    global $wgBootStrapSkinSideBar;
    global $wgTitle;

  if (!$wgSearchPlacement) {
      $wgSearchPlacement['header'] = false;
      $wgSearchPlacement['nav'] = false;
      $wgSearchPlacement['footer'] = false;
    }
    // Build additional attributes for navigation urls
    $nav = $this->data['content_navigation'];
    if ( $wgVectorUseIconWatch ) {
      $mode = $this->getSkin()->getTitle()->userIsWatching() ? 'unwatch' : 'watch';
      if ( isset( $nav['actions'][$mode] ) ) {
        $nav['views'][$mode] = $nav['actions'][$mode];
        $nav['views'][$mode]['class'] = rtrim( 'icon ' . $nav['views'][$mode]['class'], ' ' );
        $nav['views'][$mode]['primary'] = true;
        unset( $nav['actions'][$mode] );
      }
    }
    $xmlID = '';
    foreach ( $nav as $section => $links ) {
      foreach ( $links as $key => $link ) {
        if ( $section == 'views' && !( isset( $link['primary'] ) && $link['primary'] ) ) {
          $link['class'] = rtrim( 'collapsible ' . $link['class'], ' ' );
        }
        $xmlID = isset( $link['id'] ) ? $link['id'] : 'ca-' . $xmlID;
        $nav[$section][$key]['attributes'] =
          ' id="' . Sanitizer::escapeId( $xmlID ) . '"';
        if ( $link['class'] ) {
          $nav[$section][$key]['attributes'] .=
            ' class="' . htmlspecialchars( $link['class'] ) . '"';
          unset( $nav[$section][$key]['class'] );
        }
        if ( isset( $link['tooltiponly'] ) && $link['tooltiponly'] ) {
          $nav[$section][$key]['key'] =
            Linker::tooltip( $xmlID );
        } else {
          $nav[$section][$key]['key'] =
            Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( $xmlID ) );
        }
      }
    }
    $this->data['namespace_urls'] = $nav['namespaces'];
    $this->data['view_urls'] = $nav['views'];
    $this->data['action_urls'] = $nav['actions'];
    $this->data['variant_urls'] = $nav['variants'];
    // Output HTML Page
    $this->html( 'headelement' );
?>

  <div class="row">
      <div class="pull-left">
      <div class="col-md-12">
        <div class="headertitle hidden-xs">
        <a href="http://www.blue-engineering.org/">Blue Engineering</a>
        </div>
        <div class="headersubtitle hidden-xs hidden-sm">
        <p><a href="http://www.blue-engineering.org/">Ingenieurinnen und Ingenieure mit sozialer und ökologischer Verantwortung</a></p>
        </div>
      </div>
      </div>

      <?php
      if ( $wgBootstrapSkinLogoLocation == 'bodycontent' ) {
        $this->renderLogo();
      } ?>
  </div>

<?php if ( $wgGroupPermissions['*']['edit'] || $wgBootstrapSkinAnonNavbar || $this->data['loggedin'] ) { ?>
<nav class="navbar navbar-default hadron" role="navigation">
        <div class="navbar-header home">
            <button type="button" data-toggle="collapse" data-target="#defaultmenu" class="navbar-toggle"><i class="fa fa-bars"></i></button>
        </div><!-- end navbar-header -->
            <div id="defaultmenu" class="navbar-collapse collapse col-md-8 pull-left">
                <ul class="nav navbar-nav">
                    <!-- Mega Menu -->

<li class="dropdown hadron-fw"><a href="#" data-toggle="dropdown" class="dropdown-toggle">Über uns<b class="caret"></b></a>
                        <ul class="dropdown-menu fullwidth">
                            <li class="hadron-content withdesc">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <h3 class="title">Geschichte und Ziele</h3>
                                        <ul>
                                        <li style="text-align: justify;">Blue Engineering wurde im Wintersemester 2008/2009 als Idee für ein Referat in einem Seminar an der TU Berlin erarbeitet und vorgestellt. Wunsch der Studierenden war es eine Möglichkeit zu haben, die soziale und ökologische Verantwortung von Ingenieurinnen und Ingenieuren stärker im Studium zu thematisieren, ihr aber vor allem auch darüber hinaus einen Raum zu geben. Seitdem bieten Studierende und Beschäftigte an verschiedenen Hochschulen studierenden-getriebene Seminar an und gestalten darüber hinaus das Campusleben aktiv mit. So setzen sie immer wieder wertvolle Impulse durch Workshops, Ringvorlesungen, Abendveranstaltungen, Exkursionen und dergleichen mehr. Nächstes Ziel ist eine stärkere Verbindung von Studium und Beruf - hierzu wurde ein Verein gegründet um die Alumni besser zu vernetzen.</li>
                                        <br>
                                        <li><a data-description="" href=":Über_uns" style="border-bottom: solid 1px #337ab7;"><strong>Weitere Informationen zur Geschichte, zum Grundverständnis und zu den Zielen...</strong></a></li>
                                        </ul>
                                        <br>
                                    </div>
                                    <div class="col-sm-4">
                                        <h3 class="title">Verein</h3>
                                        <ul>
                                        <li style="text-align: justify;">Der Blue Engineering Verein zielt darauf ab, die verschiedenen Aktiven an den Hochschulen untereinander zu vernetzen, so dass sie ihre Erfahrungen miteinander teilen und sich gegenseitig bestärken. Zugleich bietet der Verein einen Rahmen, um über die Teilnahme an den Blue Engineering Seminaren hinaus miteinander in Kontakt zu bleiben und so auch das eigene Berufsfeld gemeinsam sozialer und ökologsischer zu gestalten.</li>
                                        <br>
                                        <li style="text-align: justify;">Der Zweck des Vereins ist es, Bildung und Forschung im Bereich einer sozial und ökologisch verantwortungsvollen Technik- und Gesellschaftsgestaltung zu fördern. Dafür sollen unter anderem verstärkt soziale, ökologische und ethische Aspekte der Verantwortung in die Lehre integriert werden. Der Satzungszweck wird insbesondere verwirklicht durch Seminare, Vorträge oder vergleichbare Veranstaltungen innerhalb oder außerhalb der Hochschulen sowie durch Publikationen.</li>
                                        <br>
                                        <li><a data-description="" href=":Verein" style="border-bottom: solid 1px #337ab7;"><strong>Weitere Informationen zum Blue Engineering Verein...</strong></a></li>
                                        </ul>
                                        <br>
                                    </div>
                                    <div class="col-sm-4">
                                        <h3 class="title">Seminar</h3>
                                        <ul>
                                        <li style="text-align: justify;">Das interdisziplinär ausgerichtete Blue Engineering-Seminar bietet angehenden Ingenieur_innen einen Blick über den Tellerrand und eine (inter-)aktive Auseinandersetzung mit ihrer sozialen und ökologischen Verantwortung. Sie erhalten so Gelegenheit sich ihrer eigenen Werte bewusst zu werden und diese mit anderen zu reflektieren. Zudem wird der Lehr-/Lernprozess im wesentlichen auf die Studierenden verlagert, so dass sie die Verantwortung für ein gutes Gelingen des Seminars übernehmen und zugleich bestimmen sie so dessen zukünftige Entwicklung mit.</li>
                                        <br>
                                        <li style="text-align: justify;">Das Seminar ist modular aufgebaut und kann so leicht an die verschiedenen Rahmenbedingungen von Hochschulen angepasst werden.</li>
                                        <br>
                                        <li><a data-description="" href=":TUB:Seminar:Allgemeine_Informationen" style="border-bottom: solid 1px #337ab7;"><strong>Weitere Informationen zur Struktur und zum Konzept des Blue Engineering Seminars...</strong></a></li>
                                        </ul>
                                        <br>
                                </div>
                                </div><!-- end row -->
                            </li><!-- end grid demo -->
                        </ul><!-- end drop down menu -->
</li>

<li class="dropdown hadron-fw"><a href="#" data-toggle="dropdown" class="dropdown-toggle">Seminare vor Ort<b class="caret"></b></a>
                        <ul class="dropdown-menu fullwidth">
                            <li class="hadron-content withdesc">
                                <div class="row">
                                    <div class="col-sm-7">
                                        <h3 class="title">Allgemeine Informationen</h3>
                                        <ul>
                                        <li style="text-align: justify;">Das interdisziplinär ausgerichtete Blue Engineering-Seminar bietet angehenden Ingenieur_innen einen Blick über den Tellerrand und eine (inter-)aktive Auseinandersetzung mit ihrer sozialen und ökologischen Verantwortung. Sie erhalten so Gelegenheit sich ihrer eigenen Werte bewusst zu werden und diese mit anderen zu reflektieren. Zudem wird der Lehr-/Lernprozess im wesentlichen auf die Studierenden verlagert, so dass sie die Verantwortung für ein gutes Gelingen des Seminars übernehmen und zugleich bestimmen sie so dessen zukünftige Entwicklung mit.</li>
                                        <br>
                                        <li style="text-align: justify;">Das Seminar ist modular aufgebaut und kann so leicht an die verschiedenen Rahmenbedingungen von Hochschulen angepasst werden.</li>
                                        <br>
                                        <li><a data-description="" href=":TUB:Seminar:Allgemeine_Informationen" style="border-bottom: solid 1px #337ab7;"><strong>Weitere Informationen zur Struktur und zum Konzept des Blue Engineering Seminars...</strong></a></li>
                                        </ul>
                                        <br>
                                    </div>
                                    <div class="col-sm-5">
                                        <h3 class="title">Seminare vor Ort</h3>
                                        <div class="col-sm-6">
                                        <ul>
                                        <li><a data-description="" href=":TUB:Seminar:Aktuelles_Semester" style="border-bottom: solid 1px #337ab7;"><strong>TU Berlin</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":HTW-B:Start" style="border-bottom: solid 1px #337ab7;"><strong>HTW Berlin</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":SU:Start" style="border-bottom: solid 1px #337ab7;"><strong>Summer School Berlin</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":TU_Delft:Start" style="border-bottom: solid 1px #337ab7;"><strong>TU Delft</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":TUD:Start" style="border-bottom: solid 1px #337ab7;"><strong>TU Dresden</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":HSD:Start" style="border-bottom: solid 1px #337ab7;"><strong>HS Düsseldorf</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":FAU_Nürnberg:Start" style="border-bottom: solid 1px #337ab7;"><strong>FAU Erlangen-Nürnberg</strong></a></li>
                                        <br>
                                        </ul>
                                        </div>
                                        <div class="col-sm-6">
                                        <ul>
                                        <li><a data-description="" href=":HAW-HH:Start" style="border-bottom: solid 1px #337ab7;"><strong>HAW Hamburg</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":TUHH:Start" style="border-bottom: solid 1px #337ab7;"><strong>TU Hamburg</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":HAWK-Hildesheim:Startseite" style="border-bottom: solid 1px #337ab7;"><strong>HAWK Hildesheim - Holzminden - Göttingen</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":TH-KOELN:Start" style="border-bottom: solid 1px #337ab7;"><strong>TH Köln</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":Uni-Paderborn:Start" style="border-bottom: solid 1px #337ab7;"><strong>Universität Paderborn</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":Uni-Rostock:Start"style="border-bottom: solid 1px #337ab7;"><strong>Universität Rostock</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":HSRW:Start"style="border-bottom: solid 1px #337ab7;"><strong>HS Ruhr West</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":Uni-Stuttgart:Start"style="border-bottom: solid 1px #337ab7;"><strong>Universität Stuttgart</strong></a></li>
                                        </ul>
                                      </div>
                                    </div>
                                </div><!-- end row -->
                            </li><!-- end grid demo -->
                        </ul><!-- end drop down menu --></li>

<li class="dropdown hadron-fw"><a href="#" data-toggle="dropdown" class="dropdown-toggle">Baukasten<b class="caret"></b></a>
                        <ul class="dropdown-menu fullwidth">
                            <li class="hadron-content withdesc">
                                <div class="row">
                                    <div class="col-sm-8">
                                        <h3 class="title">Bausteine</h3>
                                        <ul>
                                        <li style="text-align: justify;">Bausteine sind das Kernelement des Blue Engineering-Seminars. Diese inhaltlich und didaktisch gut dokumentierte 30- bis 90-minütige Lern-/Lehreinheiten verlagern sowohl den Lern- als auch den Lehrprozess innerhalb eines Seminars weitestgehend auf die Teilnehmenden. Bausteine schaffen die Balance zwischen Faktenvermittlung und Orientierung/Reflexion/Positionierung der Teilnehmenden zum Beispiel durch simulierten Gerichtsverhandlungen, Talkshows, Stationenlernen, aber auch durch multimediale/multisensuale Wissensspeicher, die ein Thema aus unterschiedlichen Perspektiven aufbereiten sowie durch spielerische Formate, wie z.B. ein Kraftwerksquartett, Mülltrennung - ein Kinderspiel! oder ein Bilderbuch zum Thema Zeitwohlstand. Weitere Themen sind zum Beispiel Peak Everything, Mobilität ohne Öl, Ethikkodizees, der Rebound-Effekt und Gender/Diversity. Mittlerweile bestehen über 150 solcher Bausteine, die regelmäßig in den verschiedenen Blue Engineering Seminaren und außerhalb zum Einsatz kommen.</li>
                                        <br>
                                        <li><a data-description="" href=":Baukasten:Startseite" style="border-bottom: solid 1px #337ab7;"><strong>Baukasten mit allen 150 Bausteinen...</strong></a></li>
                                        </ul>
                                        <br>
                                    </div>
                                    <div class="col-sm-4">
                                        <h3 class="title">Baukasten</h3>
                                        <ul>
                                        <li><a data-description="" href=":Baukasten:Startseite" style="border-bottom: solid 1px #337ab7;"><strong>Baukasten mit allen 150 Bausteinen</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":Baukasten:TUB:Grundbausteine" style="border-bottom: solid 1px #337ab7;"><strong>Grundbausteine an der TU Berlin</strong></a></li>
                                        <br>
                                        <li><a data-description="" href=":Baukasten:Gute_Bausteine" style="border-bottom: solid 1px #337ab7;"><strong>Auswahl an besonders guten, analogen Bausteinen</strong></a></li>
                                        <br>
					<li><a data-description="" href=":Baukasten:Digitales-Starter-Kit" style="border-bottom: solid 1px #337ab7;"><strong>Digitales Starter-Kit</strong></a></li>
                                        <br>
					<li><a data-description="" href=":Baukasten:EN:Digital-Starter-Kit" style="border-bottom: solid 1px #337ab7;"><strong>Digital Starter-Kit in English</strong></a></li>
                                        <br>
                                        </ul>
                                    </div>
                                </div><!-- end row -->
                            </li><!-- end grid demo -->
                        </ul><!-- end drop down menu --></li>

<li class="dropdown hadron-fw"><a data-description="" href=":RAD-AB-Schraube-Locker-Ausstellung:Startseite">RAD AB - Ausstellung</a>

<li class="dropdown hadron-fw"><a data-description="" href="English">English</a>

<li class="dropdown hadron-fw"><a data-description="" href=":Blue_Engineering:Impressum">Kontakt</a>

<li class="dropdown hadron-fw"><a href="#" data-toggle="dropdown" class="dropdown-toggle">System <b class="caret"></b></a>
                        <ul class="dropdown-menu fullwidth">
                            <li class="hadron-content withdesc">
                                <div class="row">
                                    <div class="col-sm-3">
                                      <h3 class="title"><i class="fa fa-book"></i>  Seite</h3>
                                        <ul>
                                        <li><?php $this->renderNavigation( array( 'EDIT' ) );?></li>
                                        <li aria-haspopup="true"><?php $this->renderNavigation( array( 'PAGE' ) );?></li>
                                        </ul>
                                    </div>
                                    <div class="col-sm-3">
                                      <h3 class="title"><i class="fa fa-flash"></i> Benutzer_in</h3>
                                        <ul>
                                        <li aria-haspopup="true"><?php $this->renderNavigation( array( 'PERSONALNAV' ) );?></li>
                                        </ul>
                                    </div>
                                    <div class="col-sm-3">
                                      <h3 class="title"><i class="fa fa-flash"></i> Actions</h3>
                                        <ul>
                                        <li><a href="http://blue-eng.km.tu-berlin.de/index.php/Spezial:Suche"><i class="fa fa-search"></i> Suche</a></li>
                                        <li aria-haspopup="true"><?php $this->renderNavigation( array( 'ACTIONS' ) );?></li>
                                        </ul>
                                    </div>
                                    <div class="col-sm-3">
                                      <h3 class="title"><i class="fa fa-wrench"></i> Werkzeug</h3>
                                        <ul>
                                        <li aria-haspopup="true"><?php if ( !isset( $portals['TOOLBOX'] ) ) {$this->renderNavigation( array( 'TOOLBOX' ) );?></li>
                                        </ul>
                                    </div>
                                </div><!-- end row -->
                            </li><!-- end grid demo -->
                        </ul><!-- end drop down menu -->
      </li><!-- end list elements -->


      <div id="defaultmenu" class="navbar-collapse collapse col-xs-10 pull-right fullwidth">
    <ul>
      <!-- start search bar -->
      <li>
        <ul class="nav navbar-nav fullwidth col-xs-10">
          <li class="hadron-fw">
            <?php $this->renderNavigation( array( 'SEARCH' ) ); ?>
          </li>
        </ul>
      </li>
      <!-- end search bar -->
    </ul>
  </div>
</div><!-- end #navbar-collapse-1 -->
</nav>

        <?php
          if ( $wgBootstrapSkinLogoLocation == 'navbar' ) {
            $this->renderLogo();
          }
          # This content in other languages
          if ( $this->data['language_urls'] ) {
            $this->renderNavigation( array( 'LANGUAGES' ) );
          }
          # Sidebar items to display in navbar
          //$this->renderNavigation( array( 'SEARCH' ) );
          //$this->renderNavigation( array( 'SIDEBARNAV' ) );
          }

        ?>

      </div>
    </div>

      <div class="pull-right">
        <?php
          if ($wgSearchPlacement['header']) {
            $this->renderNavigation( array( 'SEARCH' ) );
          }
          # Personal menu (at the right)
          # $this->renderNavigation( array( 'PERSONAL' ) );
        ?>
      </div>
  </div>
</div>
<?php } ?>
    <div id="mw-page-base" class="noprint"></div>
    <div id="mw-head-base" class="noprint"></div>


    <?php if ($this->data['loggedin']) {
      $userStateClass = "user-loggedin";
    } else {
      $userStateClass = "user-loggedout";
    } ?>

    <?php if ($wgGroupPermissions['*']['edit'] || $this->data['loggedin']) {
      $userStateClass += " editable";
    } else {
      $userStateClass += " not-editable";
    } ?>

    <!-- content -->
    <section id="content" class="mw-body container-fluid <?php echo $userStateClass; ?>">
      <div id="top"></div>
      <div id="mw-js-message" style="display:none;"<?php $this->html( 'userlangattributes' ) ?>></div>
      <?php if ( $this->data['sitenotice'] ): ?>
      <!-- sitenotice -->
      <div id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div>
      <!-- /sitenotice -->
      <?php endif; ?>
      <!-- bodyContent -->
      <div id="bodyContent">
        <?php if( $this->data['newtalk'] ): ?>
        <!-- newtalk -->
        <div class="usermessage"><?php $this->html( 'newtalk' )  ?></div>
        <!-- /newtalk -->
        <?php endif; ?>
        <?php if ( $this->data['showjumplinks'] ): ?>
        <!-- jumpto -->
        <div id="jump-to-nav" class="mw-jump">
          <?php $this->msg( 'jumpto' ) ?> <a href="#mw-head"><?php $this->msg( 'jumptonavigation' ) ?></a>,
          <a href="#p-search"><?php $this->msg( 'jumptosearch' ) ?></a>
        </div>
        <!-- /jumpto -->
        <?php endif; ?>


        <!-- innerbodycontent -->
        <?php # Peek into the body content of articles, to see if a custom layout is used
        if ($wgBootstrapSkinUseStandardLayout || preg_match("/<div.*class.*row.*>/i", $this->data['bodycontent']) && $this->data['articleid']) {
          # If there's a custom layout, the H1 and layout is up to the page ?>
          <div id="innerbodycontent" class="layout">
            <h1 id="firstHeading" class="firstHeading page-header">
              <span dir="auto"><?php $this->html( 'title' ) ?></span>
            </h1>
            <!-- subtitle -->
            <div id="contentSub" <?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
            <!-- /subtitle -->
            <?php if ( $this->data['undelete'] ): ?>
            <!-- undelete -->
            <div id="contentSub2"><?php $this->html( 'undelete' ) ?></div>
            <!-- /undelete -->
            <?php endif; ?>
            <?php $this->html( 'bodycontent' ); ?>
          </div>
        <?php } else {
          # If there's no custom layout, then we automagically add one ?>
          <div id="innerbodycontent" class="row nolayout"><div class="offset1 span10">
            <h1 id="firstHeading" class="firstHeading page-header">
              <span dir="auto"><?php $this->html( 'title' ) ?></span>
            </h1>
            <!-- subtitle -->
            <div id="contentSub" <?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
            <!-- /subtitle -->
            <?php if ( $this->data['undelete'] ): ?>
            <!-- undelete -->
            <div id="contentSub2"><?php $this->html( 'undelete' ) ?></div>
            <!-- /undelete -->
            <?php endif; ?>
            <?php $this->html( 'bodycontent' ); ?>
          </div></div>
        <?php } ?>
        <!-- /innerbodycontent -->

        <?php if ( $this->data['printfooter'] ): ?>
        <!-- printfooter -->
        <div class="printfooter">
        <?php $this->html( 'printfooter' ); ?>
        </div>
        <!-- /printfooter -->
        <?php endif; ?>
        <?php if ( $this->data['catlinks'] ): ?>
        <!-- catlinks -->
        <?php $this->html( 'catlinks' ); ?>
        <!-- /catlinks -->
        <?php endif; ?>
        <?php if ( $this->data['dataAfterContent'] ): ?>
        <!-- dataAfterContent -->
        <?php $this->html( 'dataAfterContent' ); ?>
        <!-- /dataAfterContent -->
        <?php endif; ?>
        <div class="visualClear"></div>
        <!-- debughtml -->
        <?php $this->html( 'debughtml' ); ?>
        <!-- /debughtml -->
      </div>
      <!-- /bodyContent -->
    </section>
    <!-- /content -->

      <!-- footer -->

      <?php
        /* Support a custom footer, or use MediaWiki's default, if footer.php does not exist. */
        $footerfile = dirname(__FILE__).'/footer.php';
        if ( file_exists($footerfile) ):
          ?><div id="footer" class="footer container-fluid custom-footer"><?php
            include( $footerfile );
          ?></div><?php
        else:
      ?>

      <div id="footer" class="footer container-fluid"<?php $this->html( 'userlangattributes' ) ?>>
        <div class="row">

            <ul id="footer-<?php echo $category ?>">
              <?php foreach( $links as $link ): ?>
                <li id="footer-<?php echo $category ?>-<?php echo $link ?>"><?php $this->html( $link ) ?></li>
              <?php endforeach; ?>

        </div>
      </div>
      <!-- /footer -->

<?php endif; ?>

    <?php $this->printTrail(); ?>

  </body>
</html>
<?php
  }
  /**
   * Render logo
   */
  private function renderLogo() {
        $mainPageLink = $this->data['nav_urls']['mainpage']['href'];
        $toolTip = Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) );
?>
              <div class="pull-right">
                <div class="col-xs-1">
				  	<a href="<?php echo htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] ) ?>" <?php echo Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) ) ?>>
						<img src="<?php $this->text( 'logopath' ); ?>" alt="<?php $this->html('sitename'); ?>" style="width: 150px;margin-top: 2.4em;margin-right: 5.5em;margin-bottom: 1.0em;">
					</a>
				</div>
              </div>
<?php
  }
  /**
   * Render one or more navigations elements by name, automatically reveresed
   * when UI is in RTL mode
   *
   * @param $elements array
   */
  private function renderNavigation( $elements ) {
    global $wgVectorUseSimpleSearch;
    global $wgBootstrapSkinLoginLocation;
    global $wgBootstrapSkinDisplaySidebarNavigation;
    global $wgBootstrapSkinSidebarItemsInNavbar;
    // If only one element was given, wrap it in an array, allowing more
    // flexible arguments
    if ( !is_array( $elements ) ) {
      $elements = array( $elements );
    // If there's a series of elements, reverse them when in RTL mode
    } elseif ( $this->data['rtl'] ) {
      $elements = array_reverse( $elements );
    }
    // Render elements
    foreach ( $elements as $name => $element ) {
      echo "\n<!-- {$name} -->\n";
      switch ( $element ) {
        case 'EDIT':
          if ( !array_key_exists('edit', $this->data['content_actions']) ) {
            break;
          }
          $navTemp = $this->data['content_actions']['edit'];
          if ($navTemp) { ?>
                <a id="b-edit" href="<?php echo $navTemp['href']; ?>"><i class="fa fa-pencil"></i> <?php echo $navTemp['text']; ?></a>


          <?php }
        break;

        case 'PAGE':
          $theMsg = 'namespaces';
          $theData = array_merge($this->data['namespace_urls'], $this->data['view_urls']);
          ?>
        <div class="grid-container3">
        <ul>
              <?php
              foreach ( $theData as $link ) {
                  if ( array_key_exists( 'context', $link ) && $link['context'] == 'subject' ) {
              ?>

                  <?php } ?>
              <?php } ?>

                <?php
                foreach ( $theData as $link ) {
                  # Skip a few redundant links
                  if (preg_match('/^ca-(view|edit)$/', $link['id'])) { continue; }
                  ?><li<?php echo $link['attributes'] ?>><a href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?> tabindex="-1"><?php echo htmlspecialchars( $link['text'] ) ?></a></li><?php
                }
          ?></ul></div>

      <?php
        break;

        case 'TOOLBOX':
          $theMsg = 'toolbox';
          $theData = array_reverse($this->getToolbox());
          ?>
              <div class="grid-container3">
              <ul>
                <?php
                  foreach( $theData as $key => $item ) {
                    if (preg_match('/specialpages|whatlinkshere/', $key)) {
                      echo '<li class="divider"></li>';
                    }
                    echo $this->makeListItem( $key, $item );
                  }
                ?>
            </ul>
      </div>

      </li>

          </ul>

          </ul>

          <?php
        break;

        case 'VARIANTS':
          $theMsg = 'variants';
          $theData = $this->data['variant_urls'];
          ?>
          <?php if (count($theData) > 0) { ?>
            <ul class="nav" role="navigation">
              <li class="dropdown" id="p-<?php echo $theMsg; ?>" class="vectorMenu<?php if ( count($theData) == 0 ) echo ' emptyPortlet'; ?>">
                <a data-toggle="dropdown" class="dropdown-toggle" role="button"><?php $this->msg($theMsg) ?> <b class="caret"></b></a>
                <ul aria-labelledby="<?php echo $this->msg($theMsg); ?>" role="menu" class="dropdown-menu" <?php $this->html( 'userlangattributes' ) ?>>
                  <?php foreach ( $theData as $link ): ?>
                    <li<?php echo $link['attributes'] ?>><a href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?> tabindex="-1"><?php echo htmlspecialchars( $link['text'] ) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </li>
            </ul>
          <?php }
        break;

        case 'VIEWS':
          $theMsg = 'views';
          $theData = $this->data['view_urls'];
          ?>
          <?php if (count($theData) > 0) { ?>
            <ul class="nav" role="navigation">
              <li class="dropdown" id="p-<?php echo $theMsg; ?>" class="vectorMenu<?php if ( count($theData) == 0 ) echo ' emptyPortlet'; ?>">
                <a data-toggle="dropdown" class="dropdown-toggle" role="button"><?php $this->msg($theMsg) ?> <b class="caret"></b></a>
                <ul aria-labelledby="<?php echo $this->msg($theMsg); ?>" role="menu" class="dropdown-menu" <?php $this->html( 'userlangattributes' ) ?>>
                  <?php foreach ( $theData as $link ): ?>
                    <li<?php echo $link['attributes'] ?>><a href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?> tabindex="-1"><?php echo htmlspecialchars( $link['text'] ) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </li>
            </ul>
          <?php }
        break;

        case 'ACTIONS':
          $theMsg = 'actions';
          $theData = array_reverse($this->data['action_urls']);

          if (count($theData) > 0) {
            ?>
        <div class="grid-container3">
                <ul>
                  <?php foreach ( $theData as $link ):
                    if (preg_match('/MovePage/', $link['href'])) {
                      echo '<li class="divider"></li>';
                    }
                    ?>

                    <li<?php echo $link['attributes'] ?>><a href="<?php echo htmlspecialchars( $link['href'] ) ?>" <?php echo $link['key'] ?> tabindex="-1"><?php echo htmlspecialchars( $link['text'] ) ?></a></li>

                  <?php endforeach; ?>
                </ul>
        </div>
            <?php
          }
        break;

        case 'PERSONAL':
          $theMsg = 'personaltools';
          $theData = $this->getPersonalTools();
          $theTitle = $this->data['username'];
          $showPersonal = true;
          foreach ( $theData as $key => $item ) {
            if ( !preg_match('/(notifications|login|createaccount)/', $key) ) {
              $showPersonal = true;
            }
          }
          ?>

            <li id="p-notifications" class="vectorMenu<?php if ( count($theData) == 0 ) echo ' emptyPortlet'; ?>">
            <?php if ( array_key_exists('notifications', $theData) ) {
              echo $this->makeListItem( 'notifications', $theData['notifications'] );
            } ?>
            </li>
            <?php if ( $wgBootstrapSkinLoginLocation == 'navbar' ): ?>
            <li class="dropdown" id="p-createaccount" class="vectorMenu<?php if ( count($theData) == 0 ) echo ' emptyPortlet'; ?>">
              <?php if ( array_key_exists('createaccount', $theData) ) {
                echo $this->makeListItem( 'createaccount', $theData['createaccount'] );
              } ?>
            </li>
            <li class="dropdown" id="p-login" class="vectorMenu<?php if ( count($theData) == 0 ) echo ' emptyPortlet'; ?>">
            <?php if ( array_key_exists('login', $theData) ) {
                echo $this->makeListItem( 'login', $theData['login'] );
            } ?>
            </li>
            <?php endif; ?>
            <?php
            if ( $showPersonal = true):
            ?>
            <li class="dropdown" id="p-<?php echo $theMsg; ?>" class="vectorMenu<?php if ( !$showPersonal ) echo ' emptyPortlet'; ?>">
              <a data-toggle="dropdown" class="dropdown-toggle" role="button">
                <i class="icon-user"></i>
                <?php echo $theTitle; ?> <b class="caret"></b></a>
              <ul aria-labelledby="<?php echo $this->msg($theMsg); ?>" role="menu" class="dropdown-menu" <?php $this->html( 'userlangattributes' ) ?>>
              <?php foreach( $theData as $key => $item ) {
                if (preg_match('/preferences|logout/', $key)) {
                  echo '<li class="divider"></li>';
                } else if ( preg_match('/(notifications|login|createaccount)/', $key) ) {
                  continue;
                }
                echo $this->makeListItem( $key, $item );
              } ?>

              </ul>

            </li>

            <?php endif; ?>
          </ul>
      <?php
        break;
        case 'PERSONALNAV':
          ?>
        <div class="grid-container3">
          <ul>
          <?php foreach ( $this->getPersonalTools() as $key => $item ) { echo $this->makeListItem( $key, $item ); }?>
          </ul>
        </div>


          <?php
        break;
        case 'SIDEBARNAV':
          foreach ( $this->data['sidebar'] as $name => $content ) {
            if ( !$content ) {
              continue;
            }
            if ( !in_array( $name, $wgBootstrapSkinSidebarItemsInNavbar ) ) {
                    continue;
            }
            $msgObj = wfMessage( $name );
            $name = htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $name ); ?>
          <ul class="nav" role="navigation">
          <li class="dropdown" id="p-<?php echo $name; ?>" class="vectorMenu">
          <a data-toggle="dropdown" class="dropdown-toggle" role="menu"><?php echo htmlspecialchars( $name ); ?> <b class="caret"></b></a>
          <ul aria-labelledby="<?php echo htmlspecialchars( $name ); ?>" role="menu" class="dropdown-menu" <?php $this->html( 'userlangattributes' ) ?>><?php
            # This is a rather hacky way to name the nav.
            # (There are probably bugs here...)
            foreach( $content as $key => $val ) {
              $navClasses = '';
              if (array_key_exists('view', $this->data['content_navigation']['views']) && $this->data['content_navigation']['views']['view']['href'] == $val['href']) {
                $navClasses = 'active';
              }?>

                <li class="<?php echo $navClasses ?>"><?php echo $this->makeLink($key, $val); ?></li><?php
            }
          }?>
         </li>
         </ul></ul><?php
        break;

  /*
	case 'SEARCH': ?>
     <div class="input-group has-light hidden-xs hidden-sm">
            <form class="navbar-search" action="<?php $this->text( 'wgScript' ); ?>" id="searchform">
					<input id="searchInput" class="form-control" type="search" accesskey="f" title="<?php $this->text('searchtitle'); ?>" placeholder="<?php $this->msg('search'); ?>" name="search" value="<?php echo htmlspecialchars ($this->data['search']); ?>">
					<span class="input-group-btn">
					<button type="submit" name="go" title="Gehe direkt zu der Seite, die exakt dem eingegebenen Namen entspricht." id="mw-searchButton" class="btn btn-default">
						<span class="glyphicon glyphicon-search"></span>
					</button>
					</span>
            </form>
     </div>
          <?php
        break;

        case 'SIDEBAR':
          foreach ( $this->data['sidebar'] as $name => $content ) {
            if ( !isset($content) ) {
              continue;
            }
            if ( in_array( $name, $wgBootstrapSkinSidebarItemsInNavbar ) ) {
                    continue;
            }
            $msgObj = wfMessage( $name );
            $name = htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $name );
            if ( $wgBootstrapSkinDisplaySidebarNavigation ) { ?>
              <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" role="button"><?php echo htmlspecialchars( $name ); ?><b class="caret"></b></a>
                <ul aria-labelledby="<?php echo htmlspecialchars( $name ); ?>" role="menu" class="dropdown-menu"><?php
            }
            # This is a rather hacky way to name the nav.
            # (There are probably bugs here...)
            foreach( $content as $key => $val ) {
              $navClasses = '';
              if (array_key_exists('view', $this->data['content_navigation']['views']) && $this->data['content_navigation']['views']['view']['href'] == $val['href']) {
                $navClasses = 'active';
              }?>

                <li class="<?php echo $navClasses ?>"><?php echo $this->makeLink($key, $val); ?></li><?php
            }
            if ( $wgBootstrapSkinDisplaySidebarNavigation ) {?>                </ul>              </li><?php
            }          }
        break;
		*/
        case 'LANGUAGES':
          $theMsg = 'otherlanguages';
          $theData = $this->data['language_urls']; ?>
          <ul class="nav" role="navigation">
            <li class="dropdown" id="p-<?php echo $theMsg; ?>" class="vectorMenu<?php if ( count($theData) == 0 ) echo ' emptyPortlet'; ?>">
              <a data-toggle="dropdown" class="dropdown-toggle brand" role="menu"><?php echo $this->html($theMsg) ?> <b class="caret"></b></a>
              <ul aria-labelledby="<?php echo $this->msg($theMsg); ?>" role="menu" class="dropdown-menu" <?php $this->html( 'userlangattributes' ) ?>>

              <?php foreach( $content as $key => $val ) { ?>
                <li class="<?php echo $navClasses ?>"><?php echo $this->makeLink($key, $val, $options); ?></li><?php
              }?>

              </ul>            </li>
          </ul><?php
          break;
      }
      echo "\n<!-- /{$name} -->\n";
    }
  }
}


/*
<li class="dropdown hadron-fw"><a href="#" data-toggle="dropdown" class="dropdown-toggle">Baukasten<b class="caret"></b></a>
                        <ul class="dropdown-menu fullwidth">
                            <li class="hadron-content withdesc">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p>Dies hier sind alles noch Platzhaltertext und Platzhalterlinks, die sich im Laufe der nächsten Tage/Wochen füllen werden.</p>
                                        <p>Wir gehen davon aus, dass wir bis Weihnachten eine vollfunktionsfähige Webseite habe. Rückmeldungen bitte an admin@blue-engineering.org</p>
                                    </div>
                             <!--   <div class="col-sm-3">
                                        <ul>
                                        <li><h3 class="title">Beschreibung</h3></li>
                                        <li><a data-description="" href=":Baukasten:Grundgedanken">Grundgedanken</a></li>
                                        <li><a data-description="" href=":Baukasten:Themen">Themen</a></li>
                                        <li><a data-description="" href=":Baukasten:Didaktik">Didaktik</a></li>
                                        </ul>
                                    </div> -->
                                    <div class="col-sm-3">
                                        <ul>
                                        <li><h3 class="title">Bausteine</h3></li>
                                        <li><a data-description="" href=":Baukasten:Bausteine Beschreibung">Beschreibung</a></li>
                                        <li><a data-description="" href=":Baukasten:Ausgzeichnete Bausteine">Ausgzeichnete</a></li>
                                        <li><a data-description="" href=":Category:Bausteine">Liste aller Bausteine</a></li>
                                        </ul>
                                    </div>
                                    <div class="col-sm-3">
                                        <ul>
                                        <li><h3 class="title">Wissensspeicher</h3></li>
                                        <li><a data-description="" href=":Baukasten:Wissensspeicher Beschreibung">Beschreibung</a></li>
                                        <li><a data-description="" href=":Baukasten:Ausgezeichnete Wissensspeicher">Ausgezeichnete</a></li>
                                        <li><a data-description="" href=":Category:Wissensspeicher">Liste aller Wissensspeicher</a></li>
                                        </ul>
                                    </div>
                                    <div class="col-sm-3">
                                        <ul>
                                        <li><h3 class="title">Sonstige</h3></li>
                                        <li><a data-description="" href=":Baukasten:Spiele">Spiele</a></li>
                                        <li><a data-description="" href=":Baukasten:E-Learning">E-Learning</a></li>
                                        <li><a data-description="" href=":Baukasten:Exkursionen">Exkursionen</a></li>
                                        </ul>
                                    </div>
                                </div><!-- end row -->
                            </li><!-- end grid demo -->
                        </ul><!-- end drop down menu -->
      </li><!-- end list elements -->
  <!-- end list elements -->
*/
