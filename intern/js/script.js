/*Vertretungsplan Intern 2.0.0 - Scripts von Malte Slotkowski */
/* Globale Variablen definieren */
$.ajaxSetup ({
    cache: false
});


/* Document ready */



/* Opener and Closer */


function openMenu(){
    disableScroll();
    $('#menu').addClass("show");
}
function closeMenu(){
    enableScroll();
    $('#menu').removeClass("show");
}
function openMultiwindow(){
    $("#multiwindow").addClass("show");
}
function closeMultiwindow(){
    $("#multiwindow").removeClass("show");
}
/* Multiwindow */
function clearMultiwindow(){
    $("#multiwindow .inner .top .title").html("");
    $("#multiwindow .inner .content").html("");
    loadEndMultiwindow();
}
function setMultiwindowTitle(title){
    $("#multiwindow .inner .top .title").text(title);
}
function addMultiwindowContent(content){
    $("#multiwindow .inner .content").append(content);
}
function loadStartMultiwindow(){
    $("#multiwindow .inner .top .load").addClass("show");
}
function loadEndMultiwindow(){
    $("#multiwindow .inner .top .load").removeClass("show");
}

/* Other important functions */


function disableScroll(){
    $("html").addClass("stopscroll");
}
function enableScroll(){
    $("html").removeClass("stopscroll");
}
var toast_mode = {
    default:0,
    error:1,
    success:2,
};
var tt;
function toast(message,mode){
    $("#toast").text(message);
    $("#toast").removeClass("error");
    $("#toast").removeClass("success");
    if(mode==toast_mode.error)
        $("#toast").addClass("error"); 
    else if(mode==toast_mode.success)
        $("#toast").addClass("success"); 
    $("#toast").addClass("show");
    try{clearTimeout(tt)}catch(e){}
    tt=setTimeout(function(){
        $("#toast").removeClass("show");
    },3000);
}
function load_start(){
    $("#load").addClass("show");
}
function load_end(){
    $("#load").removeClass("show");
}