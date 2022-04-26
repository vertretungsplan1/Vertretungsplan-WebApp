<div class="top">
    <div class="pg-info">
        <div class="pg-title">Filtereinstellungen</div>
        <div class="pg-subtitle">Liste an Klassen festlegen</div>
    </div>
</div>
<div class="sections" style="display:none;">
    <section>
        <div class="section-title">Klassen</div>
        <div class="se se-list">
            <ul class="operating_options">
                <button class="green" onclick="addClass()"><i class="ti ti-circle-plus"></i><b>Klasse hinzufügen</b></button>
                <button onclick="removeSelectedClasses()"><i class="ti ti-trash"></i><b>Ausgewählte entfernen</b></button>
            </ul>
            <table>
                <tr class="nodrag nodrop">
                    <th></th>
                    <th></th>
                    <th>Klassenname</th>
                    <th>Aktionen</th>
                </tr>
                
            </table>
        </div>
    </section>
</div>
<script>
    $(document).ready(function(){
        $.getJSON('<?php echo $dir ?>/apis/filter/',applydata);
    });
    var saveddata;
    function applydata(data){
        saveddata=data;
        if(data.success){
            $(".sections section:eq(0) .se-list table tr td").parent().remove();
            if(data.classes.length>0){
                for(var a=0;a<data.classes.length;++a){
                    $(".sections section:eq(0) .se-list table").append('<tr data-class="'+data.classes[a]+'" id="class_'+a+'"><td class="dragger"><div class="ti ti-grip-vertical"></div></td><td class="checker"><div class="box" onclick="selectClass(this)"><div class="ti ti-check"></div></div></td><td>'+data.classes[a]+'</td><td><div class="actions"><button class="ti ti-edit" onclick="editClass(\''+data.classes[a]+'\')"></button><button class="ti ti-trash" onclick="removeClass(\''+data.classes[a]+'\')"></button></div></td></tr>');
                }
                $(".sections section:eq(0) .se-list table").tableDnD({
                    onDrop: changeOrder,
                    dragHandle: ".dragger"
                });
            }
            else{
                $(".sections section:eq(0) .se-list table").append('<tr class="nocont"><td colspan="'+$(".sections section:eq(0) .se-list table th").length+'"><div class="icon ti ti-users"></div><div class="title">keine Klassen gefunden</div><div class="subtitle">Wir haben weit und breit nach Klassen gesucht und keine gefunden.</div><div class="actions"><button class="green" onclick="addClass()"><i class="ti ti-circle-plus"></i><b>Klasse hinzufügen</b></button></div></td></tr>')
            }
            load_end();
            $(".sections").css("display","");
        }
        else{
            alert("Ein Fehler ist aufgetreten: "+data.message);
        }
    }
    function addClass(){
        clearMultiwindow();
        setMultiwindowTitle("Klasse hinzufügen");
        addMultiwindowContent('<div class="sections"><section><div class="section-title">Name der Klasse</div><div class="se se-text"><div class="text"><input type="text" placeholder="Name hier eingeben..."></div></div><div class="se se-button"><button><i class="ti ti-device-floppy"></i><b>Speichern</b></button></div></section></div>');
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
                var name=$("#multiwindow .inner .content .sections .se-text input").val();
                if(name.length>0){
                    loadStartMultiwindow();
                    $.post('<?php echo $dir ?>/apis/filter/classes/add/',{name:name},function(data){
                        loadEndMultiwindow();
                        if(data.success){
                            saveddata.classes.push(name);
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
    function editClass(old_name){
        clearMultiwindow();
        setMultiwindowTitle("Klasse bearbeiten");
        addMultiwindowContent('<div class="sections"><section><div class="section-title">Name der Klasse</div><div class="se se-text"><div class="text"><input type="text" placeholder="Name hier eingeben..." value="'+old_name+'"></div></div><div class="se se-button"><button><i class="ti ti-device-floppy"></i><b>Speichern</b></button></div></section></div>');
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
                var name=$("#multiwindow .inner .content .sections .se-text input").val();
                if(name.length>0){
                    loadStartMultiwindow();
                    $.post('<?php echo $dir ?>/apis/filter/classes/edit/',{old_name:old_name,name:name},function(data){
                        loadEndMultiwindow();
                        if(data.success){
                            if(saveddata.classes.indexOf(old_name)!=-1)
                                saveddata.classes.splice(saveddata.classes.indexOf(old_name),1,name);
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
    function removeClass(name){
        clearMultiwindow();
        setMultiwindowTitle("Klasse entfernen");
        addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Möchten Sie die Klasse <b>'+name+'</b> wirklich entfernen?</p><div class="sections"><section><div class="se se-button" style="text-align:center;"><button><i class="ti ti-trash"></i><b>Ja, Entfernen</b></button></div></section></div>');
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
            loadStartMultiwindow();
            $.post('<?php echo $dir ?>/apis/filter/classes/remove/',{name:name},function(data){
                loadEndMultiwindow();
                if(data.success){
                    if(saveddata.classes.indexOf(name)!=-1)
                        saveddata.classes.splice(saveddata.classes.indexOf(name),1);
                    applydata(saveddata);
                    closeMultiwindow();
                }
                else
                    alert("Ein Fehler ist aufgetreten: "+data.message);
            },'json');
        });
        openMultiwindow();
    }
    function removeSelectedClasses(){
        var selectedClasses=[];
        var selectedClassesStr="";
        for(var a=1;a<$(".sections section:eq(0) .se-list table tr").length;++a){
            if($(".sections section:eq(0) .se-list table tr:eq("+a+") .checker").hasClass("ch-on")){
                selectedClasses.push(saveddata.classes[a-1]);
                selectedClassesStr+=(selectedClassesStr.length==0 ? "" : ", ")+saveddata.classes[a-1];
            }
        }
        if(selectedClasses.length>0){
            clearMultiwindow();
            setMultiwindowTitle("Klasse(n) entfernen");
            addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Möchten Sie die Klasse(n) <b>'+selectedClassesStr+'</b> wirklich entfernen?</p><div class="sections"><section><div class="se se-button" style="text-align:center;"><button><i class="ti ti-trash"></i><b>Ja, Entfernen</b></button></div></section></div>');
            $("#multiwindow .inner .content .sections .se-button button").click(function(){
                loadStartMultiwindow();
                $.post('<?php echo $dir ?>/apis/filter/classes/remove/',{names:selectedClasses},function(data){
                    loadEndMultiwindow();
                    if(data.success){
                        for(var a=0;a<selectedClasses.length;++a){
                            if(saveddata.classes.indexOf(selectedClasses[a])!=-1){
                                saveddata.classes.splice(saveddata.classes.indexOf(selectedClasses[a]),1);
                            }
                        }
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
    function selectClass(elem){
        elem=$(elem).parent();
        $(elem).toggleClass("ch-on")
    }
    function changeOrder(){
        var new_order=[];
        for(var a=1;a<$(".sections section:eq(0) .se-list table tr").length;++a){
            new_order.push($(".sections section:eq(0) .se-list table tr:eq("+a+")").attr("data-class"));
            toast("Erfolgreich gespeichert",toast_mode.success);
        }
        $.post('<?php echo $dir ?>/apis/filter/classes/order/',{order:new_order},function(data){
            if(data.success){
                saveddata.classes=new_order;
                closeMultiwindow();
            }
            else
                alert("Ein Fehler ist aufgetreten: "+data.message);
        },'json');
    }
</script>