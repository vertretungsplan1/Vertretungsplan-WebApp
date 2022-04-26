<amp-img id="bg" src="<?php echo $dir ?>/../apis/school_image/index.php?s=1280" alt="School Image" layout="responsive"></amp-img>
<div id="bgf"></div>
<div class="top">
    <div class="pg-info">
        <div class="pg-title">Guten Tag, Malte Slotkowski</div>
        <div class="pg-subtitle">Das wichtigste im Überblick</div>
    </div>
</div>
<div class="boxes" style="display: none;">
    <div class="box">
        <div class="title">allgemeine Einstellungen</div>
        <div class="icon ti ti-settings"></div>
        <div class="content">
            <p><b>Datenserver: </b><a>rheingau-gymnasium.de</a></p>
            <p><b>Anzahl an Spalten: </b><a>Datum und 7 weitere</a></p>
            <p><b>Spalten ohne Datentyp: </b><a>Datum und 1 weitere</a></p>
        </div>
        <button class="goto ti ti-arrow-narrow-right"></button>
    </div>
    <div class="box">
        <div class="title">Filtereinstellungen</div>
        <div class="icon ti ti-filter"></div>
        <div class="content">
            <p><b>Klassen: </b><a>7E und 20 weitere</a></p>
        </div>
        <button class="goto ti ti-arrow-narrow-right"></button>
    </div>
    <div class="box">
        <div class="title">Farbeinstellungen</div>
        <div class="icon ti ti-droplet-filled"></div>
        <div class="content">
            <p><b>Änderungsarten: </b><a>Entfall und 12 weitere</a></p>
            <p><b>unbekannte Änderungsarten: </b><a>Entfall und 12 weitere</a></p>
        </div>
        <button class="goto ti ti-arrow-narrow-right"></button>
    </div>
    <div class="box">
        <div class="title">Informationen zur Schule</div>
        <div class="icon ti ti-school"></div>
        <div class="content">
            <p><b>Name der Schule: </b><a>Rheingau-Gymnasium Schöneberg</a></p>
            <p><b>Standort der Schule: </b><a>Schwalbacher Str. 3-4, 12161 Berlin</a></p>
        </div>
        <button class="goto ti ti-arrow-narrow-right"></button>
    </div>
    <div class="box">
        <div class="title">Benutzer für Intern</div>
        <div class="icon ti ti-users"></div>
        <div class="content">
            <p><b>Benutzer: </b><a>5</a></p>
            <p><b>Administratoren: </b><a>2</a></p>
        </div>
        <button class="goto ti ti-arrow-narrow-right"></button>
    </div>
    <div class="box">
        <div class="title">System</div>
        <div class="icon ti ti-server-2"></div>
        <div class="content">
            <p><b>aktuelle Planversion: </b><a>3.0.0</a></p>
            <p><b>Update verfügbar: </b><a>ja (3.0.1)</a></p>
            <p><b>Vertretungsplan Modus: </b><a>Normal</a></p>
            <p><b>Problematische Dateien: </b><a>0</a></p>
        </div>
        <button class="goto ti ti-arrow-narrow-right"></button>
    </div>

</div>
<style>
    #content{
        padding:1rem 3rem;
    }
    #content .top .pg-info .pg-title,#content .top .pg-info .pg-subtitle{
        color:#fff;
    }
    #load .load_icon svg circle{
        stroke: #fff;
    }
    #bg{
        position: fixed;
        top:0;
        left: 0;
        z-index: -1;
        width: 100%;
        height: 100%;
        transform: scale(1.2);
    }
    #bg img{
        object-fit: cover;
    }
    #bg.start{
        transform: scale(1);
        animation: bg-in ease 1s;
    }
    @keyframes bg-in {
        0%{transform: scale(1.2);}
        100%{transform: scale(1);}
    }
    #bgf{
        position: fixed;
        top:0;
        left: 0;
        z-index: -1;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.1);
    }
    #nav{
        background-color: transparent;
        box-shadow:none;
    }
    #nav button,#nav .lgninfo,#nav .lgninfo .dd{
        background-color: rgba(0,0,0,0.3);
        box-shadow: none;
        color:white;
    }
    #nav button:active,#nav .lgninfo:not(.open):active{
        background-color: rgba(0,0,0,0.4);
    }
    #content .boxes{
        display: grid;
        gap:2rem;
        grid-template-columns: repeat(4, minmax(calc(25% - 2rem),calc(100% - 2rem)));
    }
    #content .boxes .box{
        background-color: rgba(0,0,0,0.3);
        color:white;
        min-height: 15rem;
        position:relative;
        border-radius: var(--radius-vpl-1);
        overflow: hidden;
        padding-bottom: 1.5rem;
        grid-column: span 1;
        animation: box-in ease 1s calc(var(--data-pos) * 0.05s) forwards;
        opacity: 0;
    }
    @keyframes box-in {
        0%{opacity: 0;transform: translateY(10rem);}
        100%{opacity: 1;transform: translateY(0rem);}
    }
    #content .boxes .box .title{
        padding:var(--padding-normal);
        text-align: center;
        font-size:0.9rem;
        font-weight: bold;
        text-transform: uppercase;
        background-color: rgba(255,255,255,0.1);
        border-radius: var(--radius-vpl-2);
    }
    #content .boxes .box .icon{
        position:absolute;
        bottom:0.5rem;
        right:0.5rem;
        font-size: 7rem;
        opacity: 0.4;
    }
    #content .boxes .box .content{
        padding:var(--padding-normal);
    }
    #content .boxes .box .goto{
        background-color: rgba(0,0,0,0.5);
        padding:var(--padding-normal);
        color:white;
        cursor: pointer;
        font-size: 1.5rem;
        border-top-left-radius: var(--radius-vpl-1);
        position: absolute;
        bottom:0;
        right: 0;
    }
    @supports ((-webkit-backdrop-filter: blur(2rem)) or (backdrop-filter: blur(2rem))) {
        #nav button,#nav .lgninfo{
            background-color: rgba(0,0,0,0.1);
            backdrop-filter: blur(1rem);
            -webkit-backdrop-filter: blur(1rem);
        }
        #content .boxes .box{
            background-color: rgba(0,0,0,0.2);
            backdrop-filter: blur(5rem);
            -webkit-backdrop-filter: blur(5rem);
        }
    }
</style>
<script>
    $(document).ready(function(){
        $.getJSON('<?php echo $dir ?>/apis/start/',applydata);
    });
    var saveddata;
    function applydata(data){
        saveddata=data;
        if(data.success){
            $("#content .boxes .box").css("display","none");
            var cb=0;
            if(data.hasOwnProperty("settings")){
                $("#content .boxes .box:eq(0)").css("display","");
                $("#content .boxes .box:eq(0)").css("--data-pos",cb);
                $("#content .boxes .box:eq(0) .content p:eq(0) a").text(data.settings.datasrv);
                $("#content .boxes .box:eq(0) .content p:eq(1) a").text(data.settings.columns);
                $("#content .boxes .box:eq(0) .content p:eq(2) a").text(data.settings.columns_without_asm);
                ++cb;
            }
            if(data.hasOwnProperty("filter")){
                $("#content .boxes .box:eq(1)").css("display","");
                $("#content .boxes .box:eq(1)").css("--data-pos",cb);
                $("#content .boxes .box:eq(1) .content p:eq(0) a").text(data.filter.classes);
                ++cb;
            }
            if(data.hasOwnProperty("colors")){
                $("#content .boxes .box:eq(2)").css("display","");
                $("#content .boxes .box:eq(2)").css("--data-pos",cb);
                $("#content .boxes .box:eq(2) .content p:eq(0) a").text(data.colors.types);
                $("#content .boxes .box:eq(2) .content p:eq(1) a").text(data.colors.unknown_types);
                ++cb;
            }
            if(data.hasOwnProperty("school")){
                $("#content .boxes .box:eq(3)").css("display","");
                $("#content .boxes .box:eq(3)").css("--data-pos",cb);
                $("#content .boxes .box:eq(3) .content p:eq(0) a").text(data.school.school_name);
                $("#content .boxes .box:eq(3) .content p:eq(1) a").text(data.school.location);
                ++cb;
            }
            if(data.hasOwnProperty("admin_users")){
                $("#content .boxes .box:eq(4)").css("display","");
                $("#content .boxes .box:eq(4)").css("--data-pos",cb);
                $("#content .boxes .box:eq(4) .content p:eq(0) a").text(data.admin_users.users);
                $("#content .boxes .box:eq(4) .content p:eq(1) a").text(data.admin_users.admins);
                ++cb;
            }
            if(data.hasOwnProperty("admin_system")){
                $("#content .boxes .box:eq(5)").css("display","");
                $("#content .boxes .box:eq(5)").css("--data-pos",cb);
                $("#content .boxes .box:eq(5) .content p:eq(0) a").text(data.admin_system.version);
                $("#content .boxes .box:eq(5) .content p:eq(1) a").text(data.admin_system.update);
                $("#content .boxes .box:eq(5) .content p:eq(2) a").text(data.admin_system.mode);
                $("#content .boxes .box:eq(5) .content p:eq(3) a").text(data.admin_system.files_warn);
                ++cb;
            }
            for(var a=0;a<$("#content .boxes .box .content p a").length;++a){
                if($("#content .boxes .box .content p a:eq("+a+")").text()==="")
                    $("#content .boxes .box .content p a:eq("+a+")").parent().css("display","none");
            }
            load_end();
            $("#bg").addClass("start");
            $(".boxes").css("display","");
        }
        else{
            alert("Ein Fehler ist aufgetreten: "+data.message);
        }
    }
</script>