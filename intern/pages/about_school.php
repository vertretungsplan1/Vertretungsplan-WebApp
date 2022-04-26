<?php 
    function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');   
    
        return round(pow(1024, $base - floor($base)), $precision) .''. $suffixes[floor($base)].'B';
    }
?>
<div class="top">
    <div class="pg-info">
        <div class="pg-title">Informationen zur Schule</div>
        <div class="pg-subtitle">Informationen, die Vertretungsplan über diese Schule braucht</div>
    </div>
</div>
<div class="sections" style="display: none;">
    <section>
        <div class="section-title">Name der Schule</div>
        <div class="se se-text">
            <div class="text">
                <input type="text" placeholder="Musterschule Berlin">
                <button class="sv ti ti-device-floppy" onclick="saveName()"></button>
            </div>
        </div>
    </section>
    <section>
        <div class="section-title">Standort der Schule</div>
        <div class="se se-button">
            <button onclick="findLocation()"><i class="ti ti-map-pin"></i><b>Standort der Schule festlegen</b></button>
        </div>
        <div class="section-info"><b>Aktueller Standort: </b>---</div>
    </section>
    <section>
        <div class="section-title">Bild der Schule</div>
        <div class="se se-img">
            <img alt="Bild der Schule" src="<?php echo $dir ?>/../apis/school_image/index.php?s=400">
        </div>
        <div class="se se-button">
            <button onclick="$('.img-upl').click()"><i class="ti ti-upload"></i><b>Neues Bild hochladen</b></button>
            <button onclick="removeImage()"><i class="ti ti-trash"></i><b>Bild entfernen</b></button>
        </div>
        <form enctype="multipart/form-data" style="display: none;" class="se-img-upl">
            <input type="file" accept="image/jpeg, image/png" class="img-upl" onchange="uploadImage(this)" name="image">
        </form>
        <div class="section-info">
            <b>Info: </b>Die maximale Bildgröße liegt bei <b><?php echo formatBytes(min( (int)(ini_get('upload_max_filesize')),(int)(ini_get('post_max_size')),  (int)(ini_get('memory_limit')))*1048576) ?></b><br>
            Erlaubte Dateiformate sind PNG und JPEG. Transparente Bilder können zwar hochgeladen werden, werden jedoch nicht transparent sondern mit schwarzem Hintergrund gespeichert.
        </div>
    </section>
</div>
<script>
    $(document).ready(function(){
        $.getJSON('<?php echo $dir ?>/apis/school/',applydata);
    });
    var saveddata;
    function applydata(data){
        saveddata=data;
        if(data.success){
            $(".sections .se-text input").val(data.school_name);
            while($(".sections section:eq(1) .section-info b").get(0).nextSibling!=null)
                $(".sections section:eq(1) .section-info b").get(0).nextSibling.remove();
            $(".sections section:eq(1) .section-info b").after(data.location.name);
            load_end();
            $(".sections").css("display","");
        }
        else{
            alert("Ein Fehler ist aufgetreten: "+data.message);
        }
    }
    function saveName(){
        var school_name=$(".sections section:eq(0) .se-text input").val();
        $.post('<?php echo $dir ?>/apis/school/name/',{name:school_name},function(data){
            if(data.success){
                saveddata.school_name=data.school_name;
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
    function findLocation(){
        clearMultiwindow();
        setMultiwindowTitle("Standort der Schule festlegen");
        addMultiwindowContent('<div class="sections"><section><div class="se se-text" style="width:100%;"><div class="text" style="max-width:100%;"><input type="text" placeholder="Nach Orten oder Adresse suchen..." onkeypress="if(event.which==13) $(this).parent().find(\'button.search\').click()"><button class="sv search ti ti-search"></button></div></div><div class="se se-ulcl-list"><ul></ul></div></section></div>');
        $("#multiwindow .inner .content .sections .se-text button.search").click(function(){
            var query=$("#multiwindow .inner .content .sections .se-text input").val();
            loadStartMultiwindow();
            $.getJSON('https://nominatim.openstreetmap.org/search',{q:query,format:"json",limit:50},function(data){
                loadEndMultiwindow();
                $("#multiwindow .inner .content .sections .se-ulcl-list .section-info").remove();
                $("#multiwindow .inner .content .sections .se-ulcl-list").prepend('<div class="section-info">Es wurde'+(data.length==1 ? "" : "n")+' <b>'+data.length+' Ergebnis'+(data.length==1 ? "" : "se")+'</b> gefunden</div>');
                $("#multiwindow .inner .content .sections .se-ulcl-list ul").html("");
                for(var a=0;a<data.length;++a){
                    $("#multiwindow .inner .content .sections .se-ulcl-list ul").append('<li data-number="'+a+'">'+data[a].display_name+'</li>');
                }
                $("#multiwindow .inner .content .sections .se-ulcl-list ul li").click(function(){
                    var pos=parseInt($(this).attr("data-number"));
                    $.post('<?php echo $dir ?>/apis/school/location/',{lat:data[pos].lat,lon:data[pos].lon},function(data1){
                        if(data1.success){
                            saveddata.location.name=data[pos].display_name;
                            while($(".sections section:eq(1) .section-info b").get(0).nextSibling!=null)
                                $(".sections section:eq(1) .section-info b").get(0).nextSibling.remove();
                            $(".sections section:eq(1) .section-info b").after(saveddata.location.name);
                            closeMultiwindow();
                        }
                        else{
                            alert("Ein Fehler ist aufgetreten: "+data1.message);
                        }
                    });
                });
            
            });
        });
        openMultiwindow();
    }
    function uploadImage(el){
        var file = el.files[0];
        if(file.type!=="image/jpeg"&&file.type!=="image/png"){
            toast('Ungültiges Dateiformat',toast_mode.error);
        }
        else if(file.size><?php echo min( (int)(ini_get('upload_max_filesize')),(int)(ini_get('post_max_size')),  (int)(ini_get('memory_limit')))*1048576 ?>){
            toast('Das Bild ist zu groß',toast_mode.error);
        }
        else{
            var reader=new FileReader();
            reader.addEventListener("progress", function () {
                clearMultiwindow();
                setMultiwindowTitle("Bild hochladen");
                addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Bild wird geladen...</p>');
                loadStartMultiwindow();
                openMultiwindow();
            }, false);
            reader.addEventListener("load", function () {
                image=reader.result;
                clearMultiwindow();
                setMultiwindowTitle("Bild hochladen");
                addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Möchten Sie folgendes Bild als Bild der Schule festlegen?</p><div class="sections"><section><div class="se se-img" style="text-align:center;"><img alt="Bild der Schule" src="'+image+'" style="display:inline-block;max-width:75%;"></div></section><section><div class="se se-button" style="text-align:center;"><button><i class="ti ti-upload"></i><b>Ja, Hochladen</b></button></div></section></div>');
                $("#multiwindow .inner .content .sections .se-button button").click(function(){
                    loadStartMultiwindow();
                    $.ajax({
                        url: '<?php echo $dir ?>/apis/school/image/upload/',
                        type: 'POST',
                        data: new FormData($("form.se-img-upl")[0]),
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(data){
                            loadEndMultiwindow();
                            if(data.success){
                                $(".sections section .se.se-img img").attr("src","<?php echo $dir ?>/../apis/school_image/index.php?s=400&_="+Date.now());
                                AMP.setState({si_head: "<?php echo $dir ?>/../apis/school_image/index.php?s=400&_="+Date.now()});
                                closeMultiwindow();
                            }
                            else
                                alert("Ein Fehler ist aufgetreten: "+data.message);
                        }});
                    });
                openMultiwindow();
            }, false);
            reader.readAsDataURL(file);
        }
    }
    function removeImage(){
        clearMultiwindow();
        setMultiwindowTitle("Bild entfernen");
        addMultiwindowContent('<p style="text-align:center;margin:0.5rem 0;">Möchten Sie das Bild der Schule entfernen und auf das Standardbild zurücksetzen?</p><div class="sections"><section><div class="se se-button" style="text-align:center;"><button><i class="ti ti-trash"></i><b>Ja, Entfernen</b></button></div></section></div>');
        $("#multiwindow .inner .content .sections .se-button button").click(function(){
            loadStartMultiwindow();
            $.post('<?php echo $dir ?>/apis/school/image/remove/',function(data){
                loadEndMultiwindow();
                if(data.success){
                    $(".sections section .se.se-img img").attr("src","<?php echo $dir ?>/../apis/school_image/index.php?s=400&_="+Date.now());
                    AMP.setState({si_head: "<?php echo $dir ?>/../apis/school_image/index.php?s=400&_="+Date.now()});
                    closeMultiwindow();
                }
                else{
                    alert("Ein Fehler ist aufgetreten: "+data.message);
                }
            });
        });
        openMultiwindow();
    }
</script>