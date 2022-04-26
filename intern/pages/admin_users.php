<div class="top">
    <div class="pg-info">
        <div class="pg-title">Benutzer für Intern</div>
        <div class="pg-subtitle">Die Benutzer für dieses Tool verwalten</div>
    </div>
</div>
<div class="sections" style="display: none;">
    <section>
        <div class="se se-list">
            <ul class="operating_options">
                <button class="green" onclick="addUser()"><i class="ti ti-circle-plus"></i><b>Benutzer hinzufügen</b></button>
                <button onclick="removeSelectedUsers()"><i class="ti ti-trash"></i><b>Ausgewählte entfernen</b></button>
                <button onclick="resetPasswordsSelectedUsers()"><i class="ti ti-key"></i><b>Passwort von Ausgewählten zurücksetzen</b></button>
            </ul>
            <table>
                <tr>
                    <th></th>
                    <th>vollständiger Name</th>
                    <th>Benutzername</th>
                    <th>Administrator</th>
                    <th>Aktionen</th>
                </tr>
                <tr>
                    <td class="checker">
                        <div class="box">
                            <div class="ti ti-check"></div>
                        </div>
                    </td>
                    <td>Malte Slotkowski</td>
                    <td>vpladmin</td>
                    <td class="ncchecker"><div class="ti ti-circle-check"></div></td>
                    <td>
                        <div class="actions">
                            <button class="ti ti-edit"></button>
                            <button class="ti ti-key"></button>
                            <button class="ti ti-trash"></button>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </section>
</div>
<script>
    $(document).ready(function(){
        $.getJSON('<?php echo $dir ?>/apis/admin/users/',applydata);
    });
    var saveddata;
    function applydata(data){
        saveddata=data;
        if(data.success){
            $(".sections section:eq(0) .se-list table tr td").parent().remove();
            if(data.users.length>0){
                for(var a=0;a<data.users.length;++a){
                    $(".sections section:eq(0) .se-list table").append('<tr>'+(data.users[a].is_yourself ? '<td>' : '<td class="checker"><div class="box" onclick="selectUser(this)"><div class="ti ti-check"></div></div>')+'</td><td>'+data.users[a].full_name+'</td><td>'+data.users[a].name+'</td><td class="ncchecker"><div class="ti ti-circle-'+(data.users[a].is_admin ? "check" : "x")+'"></div></td><td>'+(data.users[a].is_yourself  ? '<i>keine Aktionen möglich</i>' : '<div class="actions"><button class="ti ti-edit" onclick="editUser(\''+data.users[a].name+'\')"></button><button class="ti ti-key" onclick="resetPasswordUser(\''+data.users[a].name+'\')"></button><button class="ti ti-trash" onclick="removeUser(\''+data.users[a].name+'\')"></button></div>')+'</td></tr>');
                }
            }
            else{
                $(".sections section:eq(0) .se-list table").append('<tr class="nocont"><td colspan="'+$(".sections section:eq(0) .se-list table th").length+'"><div class="icon ti ti-users"></div><div class="title">keine Benutzer gefunden</div><div class="subtitle">Wir haben weit und breit nach Benutzern gesucht und keine gefunden, was sehr komisch ist...</div><div class="actions"><button class="green" onclick="addUser()"><i class="ti ti-circle-plus"></i><b>Benutzer hinzufügen</b></button></div></td></tr>')
            }
            load_end();
            $(".sections").css("display","");
        }
        else{
            alert("Ein Fehler ist aufgetreten: "+data.message);
        }
    }
    function addUser(){
        clearMultiwindow();
        setMultiwindowTitle("Benutzer hinzufügen");
        addMultiwindowContent('<div class="sections"><section><div class="section-title">Benutzername</div><div class="se se-text"><div class="text"><input type="text" placeholder="Benutzername hier eingeben..."></div></div></section><section><div class="section-title">vollständiger Name</div><div class="se se-text"><div class="text"><input type="text" placeholder="vollständigen Namen hier eingeben..."></div></div></section><section><div class="se se-sw"><div class="text">Administrator</div><div class="sw" onclick="$(this).parent().toggleClass(\'sw-on\')"><div class="th"></div></div></div><div class="se se-button"><button><i class="ti ti-device-floppy"></i><b>Speichern</b></button></div></section></div>');
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
                var name=$("#multiwindow .inner .content .sections .se-text:eq(0) input").val();
                var full_name=$("#multiwindow .inner .content .sections .se-text:eq(1) input").val();
                var is_admin=$("#multiwindow .inner .content .sections .se-sw").hasClass("sw-on") ? "1" : "0";
                if(name.length>0&&full_name.length>0){
                    loadStartMultiwindow();
                    $.post('<?php echo $dir ?>/apis/admin/users/add/',{name:name,full_name:full_name,is_admin:is_admin},function(data){
                        if(data.success){
                            saveddata.users.push({name:name,full_name:full_name,is_admin:is_admin==="1"});
                            applydata(saveddata);
                            closeMultiwindow();
                        }
                        else
                            alert("Ein Fehler ist aufgetreten: "+data.message);
                    },'json');
                }
                else
                    toast("Beide Namen können nicht leer sein",toast_mode.error);
                
        });
        openMultiwindow();
    }
    function editUser(name){
        clearMultiwindow();
        var info;
        for(var a=0;a<saveddata.users.length;++a){
            if(saveddata.users[a].name===name){
                info=saveddata.users[a];
                break;
            }
        }
        if(info===null){
            toast("Benutzer nicht gefunden",toast_mode.error);
            return;
        }
        setMultiwindowTitle("Benutzer bearbeiten");
        addMultiwindowContent('<div class="sections"><section><div class="section-title">Benutzername</div><div class="se se-text"><div class="text"><input type="text" readonly disabled placeholder="Benutzername hier eingeben..." value="'+info.name+'"></div></div></section><section><div class="section-title">vollständiger Name</div><div class="se se-text"><div class="text"><input type="text" placeholder="vollständigen Namen hier eingeben..." value="'+info.full_name+'"></div></div></section><section><div class="se se-sw'+(info.is_admin ? " sw-on" : "")+'"><div class="text">Administrator</div><div class="sw" onclick="$(this).parent().toggleClass(\'sw-on\')"><div class="th"></div></div></div><div class="se se-button"><button><i class="ti ti-device-floppy"></i><b>Speichern</b></button></div></section></div>');
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
                var full_name=$("#multiwindow .inner .content .sections .se-text:eq(1) input").val();
                var is_admin=$("#multiwindow .inner .content .sections .se-sw").hasClass("sw-on") ? "1" : "0";
                if(name.length>0&&full_name.length>0){
                    loadStartMultiwindow();
                    $.post('<?php echo $dir ?>/apis/admin/users/edit/',{name:name,full_name:full_name,is_admin:is_admin},function(data){
                        if(data.success){
                            for(var a=0;a<saveddata.users.length;++a){
                                if(saveddata.users[a].name===name){
                                    saveddata.users.splice(a,1,{name:name,full_name:full_name,is_admin:is_admin});
                                    break;
                                }
                            }
                            applydata(saveddata);
                            closeMultiwindow();
                        }
                        else
                            alert("Ein Fehler ist aufgetreten: "+data.message);
                    },'json');
                }
                else
                    toast("Beide Namen können nicht leer sein",toast_mode.error);
                
        });
        openMultiwindow();
    }
    function resetPasswordUser(name){
        clearMultiwindow();
        var info;
        for(var a=0;a<saveddata.users.length;++a){
            if(saveddata.users[a].name===name){
                info=saveddata.users[a];
                break;
            }
        }
        if(info===null){
            toast("Benutzer nicht gefunden",toast_mode.error);
            return;
        }
        setMultiwindowTitle("Passwort von Benutzer zurücksetzen");
        addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Möchten Sie das Passwort für den Benutzer <b>'+info.full_name+'</b> wirklich zurücksetzen?</p><div class="sections"><section><div class="se se-button" style="text-align:center;"><button><i class="ti ti-key"></i><b>Ja, Zurücksetzen</b></button></div></section></div>');
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
            loadStartMultiwindow();
            $.post('<?php echo $dir ?>/apis/admin/users/pwreset/',{name:name},function(data){
                if(data.success)
                    closeMultiwindow();
                else
                    alert("Ein Fehler ist aufgetreten: "+data.message);
            },'json');
                
        });
        openMultiwindow();
    }
    function removeUser(name){
        clearMultiwindow();
        var info;
        for(var a=0;a<saveddata.users.length;++a){
            if(saveddata.users[a].name===name){
                info=saveddata.users[a];
                break;
            }
        }
        if(info===null){
            toast("Benutzer nicht gefunden",toast_mode.error);
            return;
        }
        setMultiwindowTitle("Benutzer entfernen");
        addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Möchten Sie den Benutzer <b>'+info.full_name+'</b> wirklich entfernen?</p><div class="sections"><section><div class="se se-button" style="text-align:center;"><button><i class="ti ti-trash"></i><b>Ja, Entfernen</b></button></div></section></div>');
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
            loadStartMultiwindow();
            $.post('<?php echo $dir ?>/apis/admin/users/remove/',{name:name},function(data){
                if(data.success){
                    for(var a=0;a<saveddata.users.length;++a){
                        if(saveddata.users[a].name===name){
                            saveddata.users.splice(a,1);
                            break;
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
    function resetPasswordsSelectedUsers(){
        var selectedUsers=[];
        var selectedUsersStr="";
        for(var a=1;a<$(".sections section:eq(0) .se-list table tr").length;++a){
            if($(".sections section:eq(0) .se-list table tr:eq("+a+") .checker").hasClass("ch-on")){
                selectedUsers.push(saveddata.users[a-1].name);
                selectedUsersStr+=(selectedUsersStr.length==0 ? "" : ", ")+saveddata.users[a-1].full_name;
            }
        }
        if(selectedUsers.length>0){
            clearMultiwindow();
            setMultiwindowTitle("Änderungsart(en) löschen");
            addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Möchten Sie die Passwörter für die Benutzer <b>'+selectedUsersStr+'</b> wirklich zurücksetzen?</p><div class="sections"><section><div class="se se-button" style="text-align:center;"><button><i class="ti ti-key"></i><b>Ja, Zurücksetzen</b></button></div></section></div>');
            $("#multiwindow .inner .content .sections .se-button button").click(function(){
                    loadStartMultiwindow();
                    $.post('<?php echo $dir ?>/apis/admin/users/pwreset/',{names:selectedUsers},function(data){
                        loadEndMultiwindow();
                        if(data.success)
                            closeMultiwindow();
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
    function removeSelectedUsers(){
        var selectedUsers=[];
        var selectedUsersStr="";
        for(var a=1;a<$(".sections section:eq(0) .se-list table tr").length;++a){
            if($(".sections section:eq(0) .se-list table tr:eq("+a+") .checker").hasClass("ch-on")){
                selectedUsers.push(saveddata.users[a-1].name);
                selectedUsersStr+=(selectedUsersStr.length==0 ? "" : ", ")+saveddata.users[a-1].full_name;
            }
        }
        if(selectedUsers.length>0){
            clearMultiwindow();
            setMultiwindowTitle("Benutzer löschen");
            addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Möchten Sie den/die Benutzer <b>'+selectedUsersStr+'</b> wirklich löschen?</p><div class="sections"><section><div class="se se-button" style="text-align:center;"><button><i class="ti ti-trash"></i><b>Ja, Entfernen</b></button></div></section></div>');
            $("#multiwindow .inner .content .sections .se-button button").click(function(){
                    loadStartMultiwindow();
                    $.post('<?php echo $dir ?>/apis/admin/users/remove/',{names:selectedUsers},function(data){
                        loadEndMultiwindow();
                        if(data.success){
                            for(var a=0;a<selectedUsers.length;++a){
                                for(var b=0;b<saveddata.users.length;++b){
                                    if(saveddata.users[b].name.indexOf(selectedUsers[a])!=-1){
                                        saveddata.users.splice(b,1);
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
    function selectUser(elem){
        elem=$(elem).parent();
        $(elem).toggleClass("ch-on")
    }
    
</script>