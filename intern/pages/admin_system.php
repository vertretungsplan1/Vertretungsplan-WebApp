<?php 
    require_once __DIR__.'/../../apis/dataFetcher/info.php';
?>
<div class="top">
    <div class="pg-info">
        <div class="pg-title">System</div>
        <div class="pg-subtitle">Wichtige Systemdaten überprüfen und einstellen</div>
    </div>
</div>
<div class="sections" style="display: none;">
    <section>
        <div class="section-title">Vertretungsplan-Version: <?php echo getInfo('version') ?></div>
        <div class="section-info">Es ist eine neuere Version <b>(3.0.1)</b> verfügbar!</div>
        <div class="se se-button">
            <button><i class="ti ti-upload"></i><b>Auf neueste Version aktualisieren</b></button>
            <button onclick="refreshUpdates()"><i class="ti ti-refresh"></i><b>Nach Updates suchen</b></button>
        </div>
    </section>
    <section>
        <div class="section-title">Vertretungsplan Modus</div>
        <div class="se se-button">
            <button data-mode="normal" onclick="setMode(this)"><i class="ti ti-layout-list"></i><b>Vertretungsplan von MBS</b></button>
            <button data-mode="old_plan" onclick="setMode(this)"><i class="ti ti-table"></i><b>alter Vertretungsplan</b></button>
            <button data-mode="maintenance" onclick="setMode(this)"><i class="ti ti-tools"></i><b>Wartungsarbeiten</b></button>
            <button data-mode="deactivated" onclick="setMode(this)"><i class="ti ti-table-off"></i><b>Deaktiviert</b></button>
        </div>
    </section>
    <section>
        <div class="section-title">Vertretungsplan Dateien</div>
        <div class="section-info"><b>Info: </b>Dateien, welche Probleme bereiten, werden oben angezeigt</div>
        <div class="se se-list">
            <ul class="operating_options">
                <button onclick="refreshFiles()"><i class="ti ti-refresh"></i><b>Neu laden</b></button>
            </ul>
            <table>
                <tr>
                    <th></th>
                    <th>Dateiname</th>
                    <th>Leserechte</th>
                    <th>Schreibrechte</th>
                    <th>chmod</th>
                </tr>
            </table>
        </div>
    </section>
</div>
<script>
    $(document).ready(function(){
        $.getJSON('<?php echo $dir ?>/apis/admin/system/',applydata);
    });
    var saveddata;
    function applydata(data){
        saveddata=data;
        if(data.success){
            $(".sections section:eq(0) .section-info").css("display","none");
            $(".sections section:eq(0) .se-button button:eq(0)").css("display","none");
            if(data.update!=false){
                $(".sections section:eq(0) .section-info b").text(data.update);
                $(".sections section:eq(0) .section-info").css("display","");
                $(".sections section:eq(0) .se-button button:eq(0)").css("display","");
            }
            $(".sections section:eq(1) .se-button button").removeClass("blue");
            for(var a=0;a<$(".sections section:eq(1) .se-button button").length;++a){
                if($(".sections section:eq(1) .se-button button:eq("+a+")").attr("data-mode")===data.mode){
                    $(".sections section:eq(1) .se-button button:eq("+a+")").addClass("blue");
                    break;
                }
            }
            $(".sections section:eq(2) .se-list table tr td").parent().remove();
            if(data.files.length>0){
                for(var a=0;a<data.files.length;++a){
                    $(".sections section:eq(2) .se-list table").append('<tr><td class="warner'+(data.files[a].warn ? " wn-on" : "")+'"><div class="ti ti-alert-triangle"></div></td><td>'+data.files[a].name+'</td><td class="ncchecker"><div class="ti ti-circle-'+(data.files[a].readable ? "check" : "x")+'"></div></td><td class="ncchecker"><div class="ti ti-circle-'+(data.files[a].writable ? "check" : "x")+'"></div></td><td>'+data.files[a].chmod+'</td></tr>');
                }
            }
            else{
                $(".sections section:eq(0) .se-list table").append('<tr class="nocont"><td colspan="'+$(".sections section:eq(0) .se-list table th").length+'"><div class="icon ti ti-file-x"></div><div class="title">keine Dateien gefunden</div><div class="subtitle">Wir haben weit und breit nach Dateien gesucht und keine gefunden, was sehr komisch ist...</div></td></tr>')
            }
            load_end();
            $(".sections").css("display","");
        }
        else{
            alert("Ein Fehler ist aufgetreten: "+data.message);
        }
    }
    function refreshUpdates(){
        $.getJSON('<?php echo $dir ?>/apis/admin/system/update/',function(data){
            saveddata.update=data.update;
            applydata(saveddata);
        });
    }
    function refreshFiles(){
        $.getJSON('<?php echo $dir ?>/apis/admin/system/files/',function(data){
            saveddata.files=data.files;
            applydata(saveddata);
        });
    }
    function setMode(elem){
        var mode=$(elem).attr("data-mode");
        $.post('<?php echo $dir ?>/apis/admin/system/mode/',{mode:mode},function(data){
            if(data.success){
                saveddata.mode=mode;
                applydata(saveddata);
            }
            else{
                alert("Ein Fehler ist aufgetreten: "+data.message);
            }
        },'json');
    }
</script>