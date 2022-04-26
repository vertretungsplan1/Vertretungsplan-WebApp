<!DOCTYPE html>
<html lang="de">
    <head>
        <?php 
            $ver="3.0.0";
            $developer_mode=true;
            $cache=$ver;
            if($developer_mode)
                $cache=time();
        ?>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <title>Vertretungsplan von MBS</title>
        <script src="js/jquery.min.js" type="text/javascript"></script>
        <script src="js/moment.js" type="text/javascript"></script>
        <script async src="js/amp.js"></script>
        <script src="js/script.js?_=<?php echo $cache ?>" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="css/style.css?_=<?php echo $cache ?>">
        <link rel="stylesheet" type="text/css" href="css/tabler-icons.css">
        <style id="color"></style>
    </head>
    <body>
        <nav id="nav">
            <div class="inner">
                <button class="menuToggle" onclick="openMenu()">
                    <div class="ti ti-menu-2"></div>
                </button>
                <div class="buttons_left">
                    <!--<button class="ampel">
                        <i class="ti"></i>
                        <b>Corona-Status</b>
                    </button>-->
                </div>
                <button class="refresh">
                    <i class="ti ti-rotate-clockwise"></i>
                    <b>Aktualisieren</b>
                </button>
            </div>
            <div id="flyday"></div>
        </nav>
        
        <div id="menu" onclick="closeMenu()">
            <div class="inner" onclick="event.stopPropagation()">
                <div class="scrollable">
                    <div class="head">
                        <amp-img class="si" src="https://www.rheingau-gymnasium.de/plan_mbs_12191806072021/apis/school_image/index.php?s=400&f=webp" alt="School Image" layout="responsive"></amp-img>
                        <div class="sn sn-ext">
                            Rheingau-Gymnasium Schöneberg
                        </div>
                        <div class="sn sn-col">
                            Rheingau-Gymnasium Schöneberg
                        </div>
                        <button class="arrow_back ti ti-arrow-narrow-left" onclick="closeMenu()"></button>
                    </div>
                    <ul class="menu-items">
                        <li onclick="toggleSettings('filter')" data-stgp="filter"><i class="ti ti-filter"></i><b>Filter</b></li>
                        <li onclick="toggleSettings('design')" data-stgp="design"><i class="ti ti-droplet-filled"></i><b>Design und Farben</b></li>
                        <li onclick="toggleSettings('app')" data-stgp="app"><i class="ti ti-device-mobile"></i><b>Vertretungsplan App verbinden</b></li>
                    </ul>
                    <ul class="oth-items">
                        <li><div class="info">Vertretungsplan Version:</div><div class="text">3.0.0</div></li>
                    </ul>
                    <div class="branding">
                        <b class="vpl">Vertretungsplan</b>
                        <div class="from"><a>von</a> <b class="mbs">MBS</b></div>
                        <div class="links">
                            <a href="datenschutz/" target="_blank">Datenschutzerklärung und Impressum</a> | <a href="https://vertretungsplan.4lima.de/" target="_blank">Vertretungsplan Website</a>
                        </div>
                        <div class="small">Fragen, Probleme, Ideen?</div>
                        <ul class="contact-possibilities">
                            <a href="mailTo:plan.vertretung@gmail.com"><li><i class="ti ti-mail"></i><b>E-Mail</b></li></a>
                            <a href="https://vertretungsplan.4lima.de/rezension/" target="_blank"><li style="background-color: rgb(183, 153, 6); color:white;"><i class="ti ti-star"></i><b>Bewerten</b></li></a>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="settings" onclick="event.stopPropagation()">
                <div class="top">
                    <div class="arrow_back ti ti-arrow-narrow-left" onclick="closeSettings()">arrow_back</div>
                    <div class="title"></div>
                    <div class="close ti ti-x" onclick="closeSettings()"></div>
                </div>
                <div class="settings-page filter">
                    <section>
                        <div class="section-title">Nach Klasse filtern</div>
                        <div class="se se-dd">
                            <div class="text">Klassenfilter</div>
                            <div class="dd"><div class="ddc">Alle Klassen</div><div class="ddi ti ti-chevron-down"></div></div>
                        </div>
                    </section>
                    <section>
                        <div class="se se-sw sw-on">
                            <div class="text">vergangene Tage ausblenden</div>
                            <div class="sw"><div class="th"></div></div>
                        </div>
                    </section>
                </div>
                <div class="settings-page design">
                    <section>
                        <div class="section-title">Design auswählen</div>
                        <div class="se se-sli">
                            <ul>
                                <li class="active">
                                    <div class="prev">
                                        <object type="image/svg+xml" data="http://raspberrypi/in%20Bearbeitung/Vertretungsplan/versions/v2.5.0/apis/design_prev/index.php?t="></object>
                                    </div>
                                    <div class="name">System</div>
                                </li>
                                <li>
                                    <div class="prev">
                                        <object type="image/svg+xml" data="http://raspberrypi/in%20Bearbeitung/Vertretungsplan/versions/v2.5.0/apis/design_prev/index.php?t=light"></object>
                                    </div>
                                    <div class="name">Hell</div>
                                </li>
                                <li>
                                    <div class="prev">
                                        <object type="image/svg+xml" data="http://raspberrypi/in%20Bearbeitung/Vertretungsplan/versions/v2.5.0/apis/design_prev/index.php?t=dark"></object>
                                    </div>
                                    <div class="name">Nacht</div>
                                </li>
                            </ul>
                        </div>
                    </section>
                    <section>
                        <div class="se se-sw sw-on">
                            <div class="text">Vertretungselemente gruppieren</div>
                            <div class="sw"><div class="th"></div></div>
                        </div>
                    </section>
                    <section>
                        <div class="se se-sw sw-on">
                            <div class="text">Springer zum nächsten Tag anzeigen</div>
                            <div class="sw"><div class="th"></div></div>
                        </div>
                    </section>
                    <section>
                        <div class="section-title">Farben auswählen</div>
                        <div class="se se-cs">
                            <ul>
                            </ul>
                        </div>
                    </section>
                </div>
                <div class="settings-page app">
                    <div class="background">
                        <amp-img src="https://www.rheingau-gymnasium.de/plan_mbs_12191806072021/apis/school_image/index.php?s=1280&f=webp" alt="Rheingau-Gymnasium Schöneberg" layout="responsive"></amp-img>
                    </div>
                    <div class="content">
                        <div class="qr">
                            <object class="code" type="image/svg+xml" data="https://www.rheingau-gymnasium.de/plan_mbs_12191806072021/apis/qr/index.php?url=vertretungsplan://addplan?uid=68747470733a2f2f7777772e726865696e6761752d67796d6e617369756d2e64652f706c616e5f6d62735f31323139313830363037323032312f"></object>
                            <div class="sn">Rheingau-Gymnasium Schöneberg</div>
                            <a href="#" style="display: none;">mit App verbinden</a>
                            <div class="ncapp">
                                <p>Kopiere folgenden Link und öffne ihn im Browser:</p>
                                <div class="copy_link"><input type="text" name="noapp" value="https://app-vpl.2ix.de/index.php?uid=68747470733a2f2f7777772e726865696e6761752d67796d6e617369756d2e64652f706c616e5f6d62735f31323139313830363037323032312f" readonly=""><button class="ti ti-copy"></button></div>
                            </div>
                        </div>
                        <div class="bottom">
                        <div class="ttl">Du hast Vertretungsplan App noch nicht?</div>
                        <p>Lade dir jetzt Vertretungsplan App für dein Android-Gerät herunter!</p>
                        <a href="https://play.google.com/store/apps/details?id=com.mbs.vpl" class="play-store"><amp-img alt="Jetzt bei Google Play" src="https://app-vpl.2ix.de/img/de_badge_web_generic.png" layout="responsive"></amp-img></a>
                        <div class="sml">Google Play und das Google Play-Logo sind Marken von Google LLC.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="color_picker">
            <div class="inner">
                <div class="top">
                    <div class="arrow_back ti ti-arrow-narrow-left"></div>
                    <div class="title">Farbe auswählen</div>
                    <div class="close ti ti-x"></div>
                </div>
                <div class="scrollable">
                <div class="selectors">
                    <div class="big-selector" onclick="changeExtraColorSelector(event,true)">
                        <canvas class="bg"></canvas>
                        <div class="picker"></div>
                    </div>
                    <div class="color-selector" onclick="changeBasicColorSelector(event,true)">
                        <canvas class="bg"></canvas>
                        <div class="picker"></div>
                    </div>
                </div>
                <div class="value-enterer">
                    <input class="cv" type="text" name="cv" maxlength="7" value="#FF0000" placeholder="#000000">
                </div>
                <div class="result-presentor">
                    <div class="result-title">gewählte Farbe:</div>
                    <div class="result"></div>

                </div>
                <button class="btn_select">Auswählen</button>
                <input type="hidden" class="data">
                </div>
            </div>
        </div>
        <div id="detail_view" onclick="closeDetail()">
            <div class="inner" onclick="event.stopPropagation()">
                <div class="top">
                    <div class="arrow_back ti ti-arrow-narrow-left"></div>
                    <div class="middle">
                        <div class="title">7E2 • Entfall</div>
                        <div class="subtitle">22.03.2022, 1 - 2. Stunde</div>
                    </div>
                    <!--<div class="close ti ti-share" onclick="shareDetail()"></div>-->
                    <div class="close ti ti-x" onclick="closeDetail()"></div>
                </div>
                <ul class="details">
                </ul>
            </div>
        </div>
        <div id="content">
            <div id="inner_load"></div>
            <div id="plan">
                <div class="overlay">
                    <div class="day_jumper">
                        <button class="jumper jmpup ti ti-chevron-up"></button>
                        <button class="jumper jmpdwn ti ti-chevron-down"></button>
                    </div>
                </div>
                <div class="top">
                    <h1>Guten</h1>
                    <p>Daten geladen: </p>
                </div>
                <div class="plan-display">
                    
                </div>
            </div>
        </div>
    </body>
</html>