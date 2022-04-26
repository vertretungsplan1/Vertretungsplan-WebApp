/*Vertretungsplan 3.0.0 - Scripts von Malte Slotkowski */
/* Globale Variablen definieren */
var bcsm=false;
var ecsm=false;
var posbef;
var colors;
var lastdata;

/* Document ready */
$(document).ready(function(){
    /* Scroll Initialization */
    $(window).scroll(windowScroll);
    $('#menu .inner .scrollable').scroll(menuInnerScroll);
    posbef=window.scrollY;
    windowScroll();
    /* Preparer & Loader */
    prepare_color_picker();
    loadPlan();
});


/*Scrolling Effects */


function windowScroll(){
    var pos=window.scrollY;
    if(posbef>=pos){
        $("#nav").removeClass("hide");
    }
    else{
        $("#nav").addClass("hide");
    }
    if($("#content #plan .top").length>0){
        if(pos<$("#content #plan .top").outerHeight(true)){
            $("#nav > .inner").css("box-shadow","none");
            $("#nav > .inner").css("border-radius","0");
        }
        else{
            $("#nav > .inner").css("box-shadow","");
            $("#nav > .inner").css("border-radius","");
        }
        if(pos<$("#content #plan .top").outerHeight(true)+remToPx(3.5)){
            $("#nav").removeClass("hide");
        }
    }
    jumperAndFlyday();
    posbef=pos;
}
function menuInnerScroll(){
    var pos=$('#menu .inner .scrollable').scrollTop();
    var perc=pos/remToPx(9.5)<1 ? pos/remToPx(9.5) : 1;
    
    $('#menu .inner .head').css("transform","translateY("+pos+"px)");
    $('#menu .inner .head').css("height",12-(9.5*perc)+"rem");
    $('#menu .inner .head .sn').css("padding-left",2+(2*perc)+"rem");
    $('#menu .inner .head .sn').css("padding-right",2-(1.5*perc)+"rem");
    $('#menu .inner .head .sn').css("padding-bottom",1-(0.5*perc)+"rem");
    $('#menu .inner .head .si').css("opacity",1-perc);
    $('#menu .inner .head .sn.sn-ext').css("opacity",1-perc);
    $('#menu .inner .head .sn.sn-col').css("opacity",perc);
    $('#menu .inner .head .sn').css("white-space","");
    if(perc>0.8){
        $('#menu .inner .head .sn').css("white-space","nowrap");
    }
}
$(window).on("mousemove touchmove",function(event){
    if(bcsm)
        changeBasicColorSelector(event);
    if(ecsm)
        changeExtraColorSelector(event);
});
$(window).on("mouseup touchend",function(){
    bcsm=false;
    color_picker_canvas=null;
    ecsm=false;
    extra_picker_canvas=null;
});

function jumperAndFlyday(){
    var pos=window.scrollY;
    var navspac=$("#nav>.inner").outerHeight(true)+($("#nav>.inner").offset().top-pos);
    for(var a=$('#content #plan .plan-display .day').length-1;a>=0;--a){
        if(($('#content #plan .plan-display .day:eq('+a+')').offset().top-$('#flyday').outerHeight(true)<pos+navspac&&$('#content #plan .plan-display .day:eq('+a+')').offset().top+$('#content #plan .plan-display .day:eq('+a+')').outerHeight()>pos+navspac)||pos<$('#content #plan .plan-display .day:eq(0)').offset().top){
            $('#flyday').removeClass("show");
            break;
        }
        else if($('#content #plan .plan-display .day:eq('+a+')').offset().top+$('#content #plan .plan-display .day:eq('+a+')').outerHeight()<pos+navspac){
            $('#flyday').text($('#content #plan .plan-display .day:eq('+a+')').text());
            $('#flyday').addClass("show");
            break;
        }
    }
    var couldMoveUp=false;
    var couldMoveDwn=false;
    for(var a=0;a<$('#content #plan .plan-display .day').length;++a){
        if($('#content #plan .plan-display .day:eq('+a+')').offset().top<pos){
            $("#content #plan .overlay .day_jumper .jumper.jmpup").addClass("show");
            couldMoveUp=true;
        }
        if($('#content #plan .plan-display .day:eq('+a+')').offset().top>pos){
            $("#content #plan .overlay .day_jumper .jumper.jmpdwn").addClass("show");
            couldMoveDwn=true;
        }
        if(couldMoveUp&&couldMoveDwn)
            break;
    }
    if(!couldMoveUp)
        $("#content #plan .overlay .day_jumper .jumper.jmpup").removeClass("show");
    if(!couldMoveDwn)
        $("#content #plan .overlay .day_jumper .jumper.jmpdwn").removeClass("show");
}
/* Opener and Closer */


function openMenu(){
    disableScroll();
    $('#menu').addClass("show");
}
function closeMenu(){
    enableScroll();
    $('#menu').removeClass("show");
    closeSettings();
}
function openSettings(stgp){
    disableScroll();
    $('#settings .settings-page').removeClass("show");
    $('#settings .settings-page.'+stgp).addClass("show");
    $('#settings').addClass("show");
    $('#menu').addClass("collapse-with-settings");
    $('#menu .inner .menu-items li').removeClass("active");
    $('#menu .inner .menu-items li[data-stgp="'+stgp+'"]').addClass("active");
    $('#settings .top').removeClass('transparent');
    if(stgp==="filter"){
        $('#settings .top .title').text("Filter");
    }
    else if(stgp==="design"){
        $('#settings .top .title').text("Design und Farben");
    }
    else if(stgp==="app"){
        $('#settings .top .title').text("");
        $('#settings .top').addClass('transparent');
    }
}
function closeSettings(){
    $('#settings').removeClass("show");
    $('#settings .settings-page').removeClass("show");
    $('#menu').removeClass("collapse-with-settings");
    $('#menu .inner .menu-items li').removeClass("active");
    if(!$('#menu').hasClass("show"))
        enableScroll();
}
function toggleSettings(stgp){
    if($('#settings').hasClass("show")&&$('#settings .settings-page.'+stgp).hasClass("show"))
        closeSettings();
    else
        openSettings(stgp);
}

function openDetail(elem){
    disableScroll();
    var data=JSON.parse($(elem).find('input.data').val());
    var width=$(elem).outerWidth();
    var height=$(elem).outerHeight();
    var top=($(elem).offset().top-window.scrollY)+$(elem).outerHeight()/2;
    $("#detail_view .inner .top .middle .title").text(data.info[data.asm.class]+" • "+data.info[data.asm.type]);
    $("#detail_view .inner .top .middle .subtitle").text(data.info[data.asm.date]+', '+data.info[data.asm.hour]+'. Stunde');
    $("#detail_view .inner .details").html("");
    for(var a=0;a<data.headers.length;++a){
        if(data.headers[a]!==data.asm.class&&data.headers[a]!==data.asm.hour&&data.headers[a]!==data.asm.type&&data.headers[a]!==data.asm.date&&!untisEmpty(data.info[data.headers[a]]))
            $("#detail_view .inner .details").append('<li><div>'+data.headers[a]+'</div><p>'+data.info[data.headers[a]]+'</p></li>');
    }
    $("#detail_view .inner").css("background-color",colors[data.color])
    $("#detail_view .inner").css("width",width+"px");
    $("#detail_view .inner").css("height",height+"px");
    $("#detail_view .inner").css("top",top+"px");
    setTimeout(function(){
        $("#detail_view").addClass("show");
        $("#detail_view .inner").css("width","");
        $("#detail_view .inner").css("height","");
        $("#detail_view .inner").css("top","");
    },10)
    
}
function closeDetail(){
    $("#detail_view").removeClass("show");
    enableScroll();
}
/* Detail View Extra */
async function shareDetail(){
    var shareData={
        title:'Vertretungsplan Information teilen',
        text:'Entfall für 7E am 22.03.2022, 1 - 2. Stunde. Details im Vertretungsplan'
    } 
    var resultPara = document.querySelector('#detail_view .result');
    try {
        await navigator.share(shareData)
        resultPara.textContent = 'MDN shared successfully'
      } catch(err) {
        resultPara.textContent = 'Error: ' + err
      }
}


/* AJAX Requesters */
function loadPlan(){
    $("#content #plan .plan-display").load("apis/plan/index.php?format=html&igr=1",function(){
        lastdata=JSON.parse($('#content #plan .plan-display .all_data').val());
        colors=lastdata.colors;
        settingsPreparer();
        createColorSheet();
        var hour=moment().hour();
        if(hour<=5)
            $("#content #plan .top h1").html("Gute Nacht");
        else if(hour<=10)
            $("#content #plan .top h1").html("Guten Morgen");
        else if(hour<=13)
            $("#content #plan .top h1").html("Guten Tag");
        else if(hour<=16)
            $("#content #plan .top h1").html("Guten Nachmittag");
        else if(hour<=22)
            $("#content #plan .top h1").html("Guten Abend");
        else
            $("#content #plan .top h1").html("Gute Nacht");
        $("#content #plan .top p").html('Daten geladen: '+moment().format('DD.MM.YYYY, HH:mm'));
    });
}


/* Settings Function */
function settingsPreparer(){
    var data=lastdata;
    $("#settings .settings-page.design section:eq(3) .se-cs ul").html("");
    for(var a=0;a<data.colors.length;++a){
        var tstr="";
        for(var b=0;b<data.types.length;++b){
            if(data.types[b].color==a)
                tstr+=(tstr.length > 0 ? ", " : "")+data.types[b].name;
        }
        if(a==4)
            tstr+=(tstr.length > 0 ? ", " : "")+"alles Andere";
        if(tstr.length>0)
        $("#settings .settings-page.design section:eq(3) .se-cs ul").append('<li style="background-color: '+data.colors[a]+';"><div class="color-name">'+tstr+'</div><div class="color-actions"><button class="ti ti-color-picker"></button><button class="ti ti-x"></button></div></li>');
    }
}







/* Data Applier */




/* Color Picker */


function prepare_color_picker(){
    var cps=$("#color_picker .inner .scrollable .color-selector .bg")[0];
    var ctx1 = cps.getContext('2d');
    var width1 = cps.width;
    var height1 = cps.height;
    var grd1 = ctx1.createLinearGradient(0, 0, 0, height1);
    grd1.addColorStop(0, 'rgba(255, 0, 0, 1)');
    grd1.addColorStop(0.17, 'rgba(255, 255, 0, 1)');
    grd1.addColorStop(0.34, 'rgba(0, 255, 0, 1)');
    grd1.addColorStop(0.51, 'rgba(0, 255, 255, 1)');
    grd1.addColorStop(0.68, 'rgba(0, 0, 255, 1)');
    grd1.addColorStop(0.85, 'rgba(255, 0, 255, 1)');
    grd1.addColorStop(1, 'rgba(255, 0, 0, 1)');
    ctx1.fillStyle = grd1;
    ctx1.fillRect(0,0,width1,height1)
    setExtraColor("#FF0000");
    $("#color_picker .inner .scrollable .value-enterer .cv").on('input propertychange paste',function(event){
        handleColorValueInput(event);
    });
    $("#color_picker .inner .scrollable .color-selector .picker").on("mousedown touchstart",activateBasicColorSelector);
    $("#color_picker .inner .scrollable .big-selector .picker").on("mousedown touchstart",activateExtraColorSelector);

}
function setColorPickerColor(hex,icv){
    if(hex.length==4){
        hex="#"+hex[1]+hex[1]+hex[2]+hex[2]+hex[3]+hex[3];
    }
    var r=Number("0x"+hex[1]+hex[2]);
    var g=Number("0x"+hex[3]+hex[4]);
    var b=Number("0x"+hex[5]+hex[6]);
    var min=Math.min(r,g,b);
    if(r!=g&&g!=b&&r!=b){
        r-=min;
        g-=min;
        b-=min;
    }
    var max=Math.max(r,g,b);
    var cpspos=1;
    var epsposx=1;
    var epsposy=1;
    var mult=0;
    if(max>0){
        mult=255/max;
        r=r*mult;
        g=g*mult;
        b=b*mult;
        if(r>0&&g>0&&b>0){
            cpspos=0;
        }
        else if(r>0&&g>0){
            cpspos=0.17;
            if(r==255&&g<255)
                cpspos=0.17-0.17*(1-(Math.round(g)/255));
            else if(g==255&&r<255)
                cpspos=0.17+0.17*(1-(Math.round(r)/255));
        }
        else if(g>0&&b>0){
            cpspos=0.17*3;
            if(g==255&&b<255)
                cpspos=0.17*3-0.17*(1-(Math.round(b)/255));
            else if(b==255&&g<255)
                cpspos=0.17*3+0.17*(1-(Math.round(g)/255));
        }
        else if(r>0&&b>0){
            cpspos=0.17*5;
            if(b==255&&r<255)
                cpspos=0.17*5-0.17*(1-(Math.round(r)/255));
            else if(r==255&&b<255)
                cpspos=0.17*5+0.17*(1-(Math.round(b)/255));
        }
        else if(r>0){
            cpspos=0;
        }
        else if(g>0){
            cpspos=0.17*2;
        }
        else if(b>0){
            cpspos=0.17*4;
        }
        r=r/mult;
        g=g/mult;
        b=b/mult;
        
    }
    var cps=$("#color_picker .inner .scrollable .color-selector .bg")[0];
    var context=cps.getContext("2d");
    var c=context.getImageData(0,cpspos*cps.height,1,1).data;
    var hex1="#"+rgbToHex(c[0],c[1],c[2]);
    $("#color_picker .inner .scrollable .color-selector .picker").css("top",cpspos*100+"%");
    setExtraColor(hex1);
    if(r!=g&&g!=b&&r!=b){
        r+=min;
        g+=min;
        b+=min;
    }
    max=Math.max(r,g,b);
    if(max>0){
        mult=255/max;
    }
    epsposx=1-(min/255);
    if(mult>0)
        epsposy=1-(1/mult);
    var eps=$("#color_picker .inner .scrollable .big-selector .bg")[0];
    var context=eps.getContext("2d");
    var c=context.getImageData(epsposx*eps.width,epsposy*eps.height,1,1).data;
    $("#color_picker .inner .scrollable .big-selector .picker").css("left",epsposx*100+"%");
    $("#color_picker .inner .scrollable .big-selector .picker").css("top",epsposy*100+"%");
    $("#color_picker .inner .scrollable .result-presentor .result").css("background-color",hex);
    if(!icv)
        $("#color_picker .inner .scrollable .value-enterer .cv").val(hex.toUpperCase());
    var cpdata={};
    try{cpdata=JSON.parse($("#color_picker .inner .scrollable .data").val())}catch(e){}
    cpdata['color']=hex;
    cpdata['cpspos']=cpspos;
    cpdata['epsposx']=epsposx;
    cpdata['epsposy']=epsposy;
    $("#color_picker .inner .scrollable .data").val(JSON.stringify(cpdata));
}
function activateBasicColorSelector(){
    bcsm=true;
}
function changeBasicColorSelector(event){
    var y=(event.type!=="touchmove") ? event.clientY : event.touches[0].clientY;
    y=y-$("#color_picker .inner .scrollable .color-selector").offset().top;
    var perc=y/$("#color_picker .inner .scrollable .color-selector").outerHeight(true);
    if(perc<0)
        perc=0;
    else if(perc>1)
        perc=1;
    $("#color_picker .inner .scrollable .color-selector .picker").css("top",perc*100+"%");
    var cps=$("#color_picker .inner .scrollable .color-selector .bg")[0];
    var context=cps.getContext("2d");
    var pos=cps.height*perc;
    if(pos===cps.height){
        --pos;
    }
    var c=context.getImageData(0,pos,1,1).data;
    var hex="#"+rgbToHex(c[0],c[1],c[2]);
    setExtraColor(hex);
    var cpdata={};
    try{cpdata=JSON.parse($("#color_picker .inner .scrollable .data").val())}catch(e){}
    cpdata['cpspos']=perc;
    var eps=$("#color_picker .inner .scrollable .big-selector .bg")[0];
    var context=eps.getContext("2d");
    var posx=eps.width*cpdata['epsposx'];
    var posy=eps.height*cpdata['epsposy'];
    if(posx===eps.width){
        --posx;
    }
    if(posy===eps.height){
        --posy;
    }
    var c=context.getImageData(posx,posy,1,1).data;
    var hex="#"+rgbToHex(c[0],c[1],c[2]);
    $("#color_picker .inner .scrollable .result-presentor .result").css("background-color",hex);
    $("#color_picker .inner .scrollable .value-enterer .cv").val(hex.toUpperCase());
    cpdata['color']=hex;
    $("#color_picker .inner .scrollable .data").val(JSON.stringify(cpdata));
}
function setExtraColor(hex){
    var eps=$("#color_picker .inner .scrollable .big-selector .bg")[0];
    var ctx2 = eps.getContext('2d');
    var width2 = eps.width;
    var height2 = eps.height;
    ctx2.rect(0, 0, width2, height2);
    ctx2.fillRect(0, 0, width2, height2);
    var grd2 = ctx2.createLinearGradient(0, 0, width2, 0);
    grd2.addColorStop(0, '#FFF');
    grd2.addColorStop(1, hex);
    ctx2.fillStyle = grd2;
    ctx2.fillRect(0, 0, width2, height2);
    var grd3 = ctx2.createLinearGradient(0, 0, 0, height2);
    grd3.addColorStop(0, '#0000');
    grd3.addColorStop(1, '#000');
    ctx2.fillStyle = grd3;
    ctx2.fill();
}
function activateExtraColorSelector(){
    ecsm=true;
}
function changeExtraColorSelector(event){
    var x=(event.type!=="touchmove") ? event.clientX : event.touches[0].clientX;
    var y=(event.type!=="touchmove") ? event.clientY : event.touches[0].clientY;
    x=x-$("#color_picker .inner .scrollable .big-selector").offset().left;
    y=y-$("#color_picker .inner .scrollable .big-selector").offset().top;
    var percy=y/$("#color_picker .inner .scrollable .big-selector").outerHeight(true);
    var percx=x/$("#color_picker .inner .scrollable .big-selector").outerWidth(true);
    if(percx<0)
        percx=0;
    else if(percx>1)
        percx=1;
    if(percy<0)
        percy=0;
    else if(percy>1)
        percy=1;
    $("#color_picker .inner .scrollable .big-selector .picker").css("left",percx*100+"%");
    $("#color_picker .inner .scrollable .big-selector .picker").css("top",percy*100+"%");
    var eps=$("#color_picker .inner .scrollable .big-selector .bg")[0];
    var context=eps.getContext("2d");
    var posx=eps.width*percx;
    var posy=eps.height*percy;
    if(posx===eps.width){
        --posx;
    }
    if(posy===eps.height){
        --posy;
    }
    var c=context.getImageData(posx,posy,1,1).data;
    var hex="#"+rgbToHex(c[0],c[1],c[2]);
    $("#color_picker .inner .scrollable .result-presentor .result").css("background-color",hex);
    $("#color_picker .inner .scrollable .value-enterer .cv").val(hex.toUpperCase());
    var cpdata={};
    try{cpdata=JSON.parse($("#color_picker .inner .scrollable .data").val())}catch(e){}
    cpdata['epsposx']=percx;
    cpdata['epsposy']=percy;
    cpdata['color']=hex;
    $("#color_picker .inner .scrollable .data").val(JSON.stringify(cpdata));
}
function handleColorValueInput(event){
    var elem=event.target;
    $(elem).val($(elem).val().replace(/[^#a-fA-F0-9]/i,""));
    if(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test($(elem).val())){
        setColorPickerColor($(elem).val(),true);
    }
    else if(/^([0-9a-f]{3}|[0-9a-f]{6})$/i.test($(elem).val())){
        setColorPickerColor("#"+$(elem).val(),true);
    }
}


/* Other important functions */


function remToPx(rem) {    
    return rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
}
function rgbToHex(r, g, b) {
    if (r > 255 || g > 255 || b > 255)
        throw "Invalid color component";
    var rh=r.toString(16);
    var gh=g.toString(16);
    var bh=b.toString(16);
    rh=rh.length<2 ? "0"+rh : rh;
    gh=gh.length<2 ? "0"+gh : gh;
    bh=bh.length<2 ? "0"+bh : bh;
    return rh+gh+bh;
}
function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}
function disableScroll(){
    $("html").addClass("stopscroll");
}
function enableScroll(){
    $("html").removeClass("stopscroll");
}
function untisEmpty(str){
    return str===""||str==="---";
}
function createColorSheet(){
    var sheet="";
    for(var a=0;a<colors.length;++a){
        var brightness=Math.round(((hexToRgb(colors[a]).r * 299) +
                                          (hexToRgb(colors[a]).g * 587) +
                                          (hexToRgb(colors[a]).b * 114)) / 1000);
        var col=(brightness > 150) ? 'black' : 'white';
        sheet+="#content #plan .plan-display .changes-table li.color_"+a+"{background-color:"+colors[a]+"; color:"+col+";}";
    }
    $('#color').html(sheet);
}