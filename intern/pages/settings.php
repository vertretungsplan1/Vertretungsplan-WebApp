<div class="top">
    <div class="pg-info">
        <div class="pg-title">Einstellungen</div>
        <div class="pg-subtitle">allgemeine Einstellungen zum Plan</div>
    </div>
    <!--<ul class="subpages">
        <li class="active">Example</li>
        <li>Example</li>
        <li>Example</li>
        <li>Example</li>
    </ul>-->
</div>
<div class="sections" style="display: none;">
    <section>
        <div class="section-title">Daten-Link (Alter Plan)</div>
        <div class="se se-text">
            <div class="text">
                <input type="url" placeholder="https://...">
                <button class="sv ti ti-device-floppy" onclick="saveURL()"></button>
            </div>
        </div>
    </section>
    <section>
        <div class="section-title">Spalten</div>
        <div class="se se-list">
            <table>
                <tr>
                    <th>Spaltenname</th>
                    <th>Im Vertretungsplan anzeigen</th>
                    <th>Datentyp zuweisen</th>
                </tr>
                <tr>
                    <td>Klasse(n)</td>
                    <td><div class="switch sw-on"><div class="th"></div></div></td>
                    <td><div class="dropdown"><b>keiner</b><i class="ti ti-chevron-down"></i></div></td>
                </tr>
            </table>
        </div>
    </section>
</div>
<script>
    $(document).ready(function(){
        $.getJSON('<?php echo $dir ?>/apis/settings/',applydata);
    });
    var saveddata;
    var svav=[];
    function applydata(data){
        saveddata=data;
        if(data.success){
            $(".sections section:eq(0) .se-text input").val(data.plan_url);
            $(".sections section:eq(1) .se-list table tr td").parent().remove();
            var asvalues=[];
            var askeys=[];
            $.each( data.asm, function( key, val ) {
                askeys.push(key);
                asvalues.push(val);
            });
            if(data.columns.length>0){
                for(var a=0;a<data.columns.length;++a){
                    var as_type="keiner";
                    if(asvalues.length>0){
                        for(var b=0;b<data.asms.length;++b){
                            if(askeys.indexOf(data.asms[b].kname)!=-1&&asvalues[askeys.indexOf(data.asms[b].kname)].indexOf(data.columns[a].name)!=-1){
                                as_type=data.asms[b].name;
                                break;
                            }
                        }
                    }
                    $(".sections section:eq(1) .se-list table").append('<tr><td>'+data.columns[a].name+'</td><td><div class="switch'+(data.columns[a].visible ? " sw-on" : "")+'" onclick="changeColumnVisibility(\''+data.columns[a].name+'\',event)"><div class="th"></div></div></td><td><div class="dropdown" onclick="openAsmSelector(\''+data.columns[a].name+'\')"><b>'+as_type+'</b><i class="ti ti-chevron-down"></i></div></td></tr>');
                }
            }
            else{
                $(".sections section:eq(1) .se-list table").append('<tr class="nocont"><td colspan="'+$(".sections section:eq(1) .se-list table th").length+'"><div class="icon ti ti-columns"></div><div class="title">keine Spalten gefunden</div><div class="subtitle">Wir haben weit und breit nach Spalten gesucht und keine gefunden. Bitte überprüfen Sie den Daten-Link auf Korrektheit.</div></td></tr>')
            }
            load_end();
            $(".sections").css("display","");
        }
        else{
            alert("Ein Fehler ist aufgetreten: "+data.message);
        }
    }
    function saveURL(){
        var plan_url=$(".sections section:eq(0) .se-text input").val();
        if(isUrlValid(plan_url)){
            $.post('<?php echo $dir ?>/apis/settings/plan_url/',{url:plan_url},function(data){
                if(data.success){
                    saveddata.plan_url=data.plan_url;
                    saveddata.columns=data.columns;
                    applydata(saveddata);
                    $(".sections section:eq(0) .se-text").addClass("saved");
                    setTimeout(function(){
                        $(".sections section:eq(0) .se-text").removeClass("saved");
                    },3000);
                }
                else{
                    $(".sections section:eq(0) .se-text").addClass("error");
                    setTimeout(function(){
                        $(".sections section:eq(0) .se-text").removeClass("error");
                    },3000); 
                    alert("Ein Fehler ist aufgetreten: "+data.message);
                }
            },'json');
        }
        else{
            $(".sections section:eq(0) .se-text").addClass("error");
            setTimeout(function(){
                $(".sections section:eq(0) .se-text").removeClass("error");
            },3000);
            toast("Ungültige URL",toast_mode.error);
        }
    }
    function changeColumnVisibility(column,event){
        var elem=event.target;
        var visibility="1";
        if($(elem).hasClass("sw-on"))
            visibility="0";
        $(elem).addClass("acti");
        $.post('<?php echo $dir ?>/apis/settings/columns/visibility/',{column_name:column,visibility:visibility},function(data){
            $(elem).removeClass("acti");
            if(data.success){
                for(var a=0;a<saveddata.columns.length;++a){
                    if(saveddata.columns[a].name===column){
                        saveddata.columns[a].visible=visibility==="1";
                        break;
                    }
                }
                $(elem).removeClass("sw-on");
                if(visibility==="1")
                    $(elem).addClass("sw-on");
            }
            else{
                alert("Ein Fehler ist aufgetreten: "+data.message);
            }
        },'json');
    }
    function openAsmSelector(column){
        clearMultiwindow();
        setMultiwindowTitle("Datentyp zuweisen");
        var valid_asms=[];
        
        for(var a=0;a<saveddata.asms.length;++a){
            if(saveddata.asms[a].multipurpose)
                valid_asms.push(saveddata.asms[a]);
            else if(!saveddata.asm.hasOwnProperty(saveddata.asms[a].kname)||saveddata.asm[saveddata.asms[a].kname].indexOf(column)!=-1||saveddata.asm[saveddata.asms[a].kname]==="false")
                valid_asms.push(saveddata.asms[a]);
        }
        addMultiwindowContent('<div class="sections"><section><div class="se se-ulcl-list"><ul><li onclick="setAsm(\''+column+'\',\'false\')">keiner</li></ul></div></section></div>');
        for(var a=0;a<valid_asms.length;++a){
            var active="";
            if(saveddata.asm.hasOwnProperty(valid_asms[a].kname))
                active=saveddata.asm[valid_asms[a].kname].indexOf(column)!=-1 ? "active" : "";
            $("#multiwindow .inner .content .sections .se-ulcl-list ul").append('<li class="'+active+'" onclick="setAsm(\''+column+'\',\''+valid_asms[a].kname+'\')">'+valid_asms[a].name+'</li>');
        }
        if($("#multiwindow .inner .content .sections .se-ulcl-list ul li.active").length==0)
        $("#multiwindow .inner .content .sections .se-ulcl-list ul li:eq(0)").addClass("active");
        openMultiwindow();
    }
    function setAsm(column,kname){
        $.post('<?php echo $dir ?>/apis/settings/columns/asm/',{column_name:column,asm_name:kname},function(data){
            if(data.success){
                saveddata.asm=data.asm;
                saveddata.asms=data.asms;
                applydata(saveddata);
                closeMultiwindow();
            }
            else{
                alert("Ein Fehler ist aufgetreten: "+data.message);
            }
        },'json');
    }
    function isUrlValid(userInput) {
        var res = userInput.match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g);
        if(res == null)
            return false;
        else
            return true;
    }
</script>