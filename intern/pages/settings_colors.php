<div class="top">
    <div class="pg-info">
        <div class="pg-title">Farbeinstellungen</div>
        <div class="pg-subtitle">Liste an Änderungsarten festlegen</div>
    </div>
</div>
<div class="sections" style="display: none;">
    <section>
        <div class="section-title">Änderungsarten</div>
        <div class="se se-list">
            <ul class="operating_options">
                <button class="green" onclick="addType()"><i class="ti ti-circle-plus"></i><b>Art hinzufügen</b></button>
                <button class="blue" onclick="viewUnknownTypes()"><i class="ti ti-eye"></i><b>unbekannte Arten ansehen (8)</b></button>
                <button onclick="removeSelectedTypes()"><i class="ti ti-trash"></i><b>Ausgewählte entfernen</b></button>
            </ul>
            <table>
                <tr>
                    <th></th>
                    <th>Name und Farbe der Art</th>
                    <th>Aktionen</th>
                </tr>
                <tr>
                    <td class="checker">
                        <div class="box">
                            <div class="ti ti-check"></div>
                        </div>
                    </td>
                    <td class="type">
                        <div style="background-color:#d21414;">Entfall</div>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="ti ti-edit"></button>
                            <button class="ti ti-trash"></button>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </section>
</div>
<style>
.sections section .se.se-list table .type div{
    padding:var(--padding-half);
    border-radius: var(--radius-vpl-3);
    display: inline-block;
    color:white;
    font-weight: bold;
}
.sections section .se.se-list table .type.tp-dark div{
    color:black;
}
.sections section .se.se-ulcl-colors ul li.active{
    display: flex;
    align-items: center;
    font-weight: bold;
}
.sections section .se.se-ulcl-colors ul li.active::after{
    content: "\ea67";
    font-family: "tabler-icons" !important;
    font-style: normal;
    font-weight: normal;
    font-variant: normal;
    text-transform: none;
    line-height: 1;
    margin-left: auto;
    font-size: 1.5rem;
    transform: translateY(-5%);
}
.sections section .se.se-ulcl-add ul li{
    display: flex;
    align-items: center;
}
.sections section .se.se-ulcl-add ul li::after{
    content: "\ea69";
    font-family: "tabler-icons" !important;
    font-style: normal;
    font-weight: normal;
    font-variant: normal;
    text-transform: none;
    line-height: 1;
    margin-left: auto;
    font-size: 1.5rem;
    transform: translateY(-5%);
}
</style>
<script>
    $(document).ready(function(){
        $.getJSON('<?php echo $dir ?>/apis/colors/',applydata);
    });
    var saveddata;
    function applydata(data){
        saveddata=data;
        if(data.success){
            $(".sections section:eq(0) .se-list table tr td").parent().remove();
            $(".sections section:eq(0) .se-list .operating_options button:eq(1) b").text("unbekannte Arten ansehen ("+data.unknown_types.length+")")
            if(data.types.length>0){
                for(var a=0;a<data.types.length;++a){
                    $(".sections section:eq(0) .se-list table").append('<tr><td class="checker"><div class="box" onclick="selectType(this)"><div class="ti ti-check"></div></div></td><td class="type"><div style="background-color:'+data.colors[data.types[a].color]+';">'+data.types[a].name+'</div></td><td><div class="actions"><button class="ti ti-edit" onclick="editType(\''+data.types[a].name+'\','+data.types[a].color+')"></button><button class="ti ti-trash" onclick="removeType(\''+data.types[a].name+'\')"></button></div></td></tr>');
                }
            }
            else{
                $(".sections section:eq(0) .se-list table").append('<tr class="nocont"><td colspan="'+$(".sections section:eq(0) .se-list table th").length+'"><div class="icon ti ti-layout-list"></div><div class="title">keine Arten gefunden</div><div class="subtitle">Wir haben weit und breit nach Arten gesucht und keine gefunden.</div><div class="actions"><button class="green" onclick="addType()"><i class="ti ti-circle-plus"></i><b>Art hinzufügen</b></button><button class="blue" onclick="viewUnknownTypes()"><i class="ti ti-eye"></i><b>unbekannte Arten ansehen ('+data.unknown_types.length+')</b></button></div></td></tr>')
            }
            load_end();
            $(".sections").css("display","");
        }
        else{
            alert("Ein Fehler ist aufgetreten: "+data.message);
        }
    }
    function addType(){
        clearMultiwindow();
        setMultiwindowTitle("Änderungsart hinzufügen");
        addMultiwindowContent('<div class="sections"><section><div class="section-title">Name der Änderungsart</div><div class="se se-text"><div class="text"><input type="text" placeholder="Name hier eingeben..."></div></div></section><section><div class="section-title">Farbe auswählen</div><div class="se se-ulcl-list se-ulcl-colors"><ul></ul></div><div class="se se-button"><button><i class="ti ti-device-floppy"></i><b>Speichern</b></button></div></section></div>');
        var selected_color=4;
        for(var a=0;a<saveddata.colors.length;++a){
            var active="";
            if(a==selected_color)
                active="active";
            $("#multiwindow .inner .content .sections .se-ulcl-list ul").append('<li class="'+active+'" style="color:white;background-color:'+saveddata.colors[a]+';">Farbe '+(a+1)+'</li>');
        }
        $("#multiwindow .inner .content .sections .se-ulcl-list ul li").click(function(){
            $(this).parent().find("li").removeClass("active");
            $(this).addClass("active");
        });
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
                var name=$("#multiwindow .inner .content .sections .se-text input").val()
                if(name.length>0){
                    var color=4;
                    for(var a=0;a<$("#multiwindow .inner .content .sections .se-ulcl-list ul li").length;++a){
                        if($("#multiwindow .inner .content .sections .se-ulcl-list ul li:eq("+a+")").hasClass("active")){
                            color=a;
                            break;
                        }
                    }
                    loadStartMultiwindow();
                    $.post('<?php echo $dir ?>/apis/colors/types/add/',{name:name,color:color},function(data){
                        loadEndMultiwindow();
                        if(data.success){
                            saveddata.types.push({name:name,color:color});
                            saveddata.unknown_types=data.unknown_types;
                            applydata(saveddata);
                            closeMultiwindow();
                        }
                        else
                            alert("Ein Fehler ist aufgetreten: "+data.message);
                    },'json');
                }
                else
                    toast("Der Name kann nicht leer sein",toast_mode.error);
        });
        openMultiwindow();
    }
    function editType(old_name,selected_color){
        clearMultiwindow();
        setMultiwindowTitle("Änderungsart bearbeiten");
        addMultiwindowContent('<div class="sections"><section><div class="section-title">Name der Änderungsart</div><div class="se se-text"><div class="text"><input type="text" placeholder="Name hier eingeben..." value="'+old_name+'"></div></div></section><section><div class="section-title">Farbe auswählen</div><div class="se se-ulcl-list se-ulcl-colors"><ul></ul></div><div class="se se-button"><button><i class="ti ti-device-floppy"></i><b>Speichern</b></button></div></section></div>');
        for(var a=0;a<saveddata.colors.length;++a){
            var active="";
            if(a==selected_color)
                active="active";
            $("#multiwindow .inner .content .sections .se-ulcl-list ul").append('<li class="'+active+'" style="color:white;background-color:'+saveddata.colors[a]+';">Farbe '+(a+1)+'</li>');
        }
        $("#multiwindow .inner .content .sections .se-ulcl-list ul li").click(function(){
            $(this).parent().find("li").removeClass("active");
            $(this).addClass("active");
        });
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
                var name=$("#multiwindow .inner .content .sections .se-text input").val()
                if(name.length>0){
                    var color=4;
                    for(var a=0;a<$("#multiwindow .inner .content .sections .se-ulcl-list ul li").length;++a){
                        if($("#multiwindow .inner .content .sections .se-ulcl-list ul li:eq("+a+")").hasClass("active")){
                            color=a;
                            break;
                        }
                    }
                    loadStartMultiwindow();
                    $.post('<?php echo $dir ?>/apis/colors/types/edit/',{old_name:old_name,name:name,color:color},function(data){
                        loadEndMultiwindow();
                        if(data.success){
                            for(var a=0;a<saveddata.types.length;++a){
                                if(saveddata.types[a].name===old_name){
                                    saveddata.types.splice(a,1,{name:name,color:color});
                                    break;
                                }
                            }
                            saveddata.unknown_types=data.unknown_types;
                            applydata(saveddata);
                            closeMultiwindow();
                        }
                        else
                            alert("Ein Fehler ist aufgetreten: "+data.message);
                    },'json');
                }
                else
                    toast("Der Name kann nicht leer sein",toast_mode.error);
        });
        openMultiwindow();
    }
    function viewUnknownTypes(){
        clearMultiwindow();
        setMultiwindowTitle("unbekannte Änderungsarten");
        addMultiwindowContent('<div class="sections"><section><div class="section-title">unbekannte Änderungsarten hinzufügen</div><div class="se se-ulcl-list se-ulcl-add"><ul></ul></div><div class="se se-button"><button><i class="ti ti-circle-plus"></i><b>Alle hinzufügen</b></button></div></section></div>');
        for(var a=0;a<saveddata.unknown_types.length;++a){
            $("#multiwindow .inner .content .sections .se-ulcl-list ul").append('<li>'+saveddata.unknown_types[a]+'</li>');
        }
        $("#multiwindow .inner .content .sections .se-ulcl-list ul li").click(function(){
            var elem=this;
            for(var a=0;a<$("#multiwindow .inner .content .sections .se-ulcl-list ul li").length;++a){
                if($("#multiwindow .inner .content .sections .se-ulcl-list ul li")[a]===this){
                    loadStartMultiwindow();
                    $.post('<?php echo $dir ?>/apis/colors/types/add/',{name:saveddata.unknown_types[a],color:4},function(data){
                        loadEndMultiwindow();
                        if(data.success){
                            saveddata.types.push({name:saveddata.unknown_types[a],color:4});
                            saveddata.unknown_types=data.unknown_types;
                            viewUnknownTypes();
                            applydata(saveddata);
                        }
                        else
                            alert("Ein Fehler ist aufgetreten: "+data.message);
                    },'json');
                    break;
                }
            }
        });
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
            loadStartMultiwindow();
            $.post('<?php echo $dir ?>/apis/colors/types/add/',{names:saveddata.unknown_types,color:4},function(data){
                loadEndMultiwindow();
                if(data.success){
                    for(var a=0;a<saveddata.unknown_types.length;++a){
                        saveddata.types.push({name:saveddata.unknown_types[a],color:4});
                    }
                    saveddata.unknown_types=data.unknown_types;
                    applydata(saveddata);
                    closeMultiwindow();
                }
                else
                    alert("Ein Fehler ist aufgetreten: "+data.message);
            },'json');
        });
        openMultiwindow();
    }
    function removeType(name){
        clearMultiwindow();
        setMultiwindowTitle("Änderungsart entfernen");
        addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Möchten Sie die Änderungsart <b>'+name+'</b> wirklich entfernen?</p><div class="sections"><section><div class="se se-button" style="text-align:center;"><button><i class="ti ti-trash"></i><b>Ja, Entfernen</b></button></div></section></div>');
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
            loadStartMultiwindow();
            $.post('<?php echo $dir ?>/apis/colors/types/remove/',{name:name},function(data){
                loadEndMultiwindow();
                if(data.success){
                    for(var a=0;a<saveddata.types.length;++a){
                        if(saveddata.types[a].name===name){
                            saveddata.types.splice(a,1);
                            break;
                        }
                    }
                    saveddata.unknown_types=data.unknown_types;
                    applydata(saveddata);
                    closeMultiwindow();
                }
                else
                    alert("Ein Fehler ist aufgetreten: "+data.message);
            },'json');
        });
        openMultiwindow();
    }
    function removeSelectedTypes(){
        var selectedTypes=[];
        var selectedTypesStr="";
        for(var a=1;a<$(".sections section:eq(0) .se-list table tr").length;++a){
            if($(".sections section:eq(0) .se-list table tr:eq("+a+") .checker").hasClass("ch-on")){
                selectedTypes.push(saveddata.types[a-1].name);
                selectedTypesStr+=(selectedTypesStr.length==0 ? "" : ", ")+saveddata.types[a-1].name;
            }
        }
        if(selectedTypes.length>0){
            clearMultiwindow();
            setMultiwindowTitle("Änderungsart(en) entfernen");
            addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Möchten Sie die Änderungsart(en) <b>'+selectedTypesStr+'</b> wirklich entfernen?</p><div class="sections"><section><div class="se se-button" style="text-align:center;"><button><i class="ti ti-trash"></i><b>Ja, Entfernen</b></button></div></section></div>');
            $("#multiwindow .inner .content .sections .se-button button").click(function(){
                    loadStartMultiwindow();
                    $.post('<?php echo $dir ?>/apis/colors/types/remove/',{names:selectedTypes},function(data){
                        loadEndMultiwindow();
                        if(data.success){
                            for(var a=0;a<selectedTypes.length;++a){
                                for(var b=0;b<saveddata.types.length;++b){
                                    if(saveddata.types[b].name.indexOf(selectedTypes[a])!=-1){
                                        saveddata.types.splice(b,1);
                                    }
                                }
                                
                            }
                            saveddata.unknown_types=data.unknown_types;
                            applydata(saveddata);
                            closeMultiwindow();
                        }
                        else
                            alert("Ein Fehler ist aufgetreten: "+data.message);
                },'json');
            });
            openMultiwindow();
        }
        else{
            toast("Es ist nichts ausgewählt");
        }
        
    }
    function selectType(elem){
        elem=$(elem).parent();
        $(elem).toggleClass("ch-on")
    }
</script>