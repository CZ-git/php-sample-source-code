var mapDragFlag;
$(document).on('pageshow', function(){
    mapDragFlag = false;
    // left menu panel sliding when swiper right event is occured
    $(".ui-page").on( "swiperight", function( e ) {
        if ( $("#nav-panel").hasClass("ui-panel-open") != true ){
            if ( e.type === "swiperight" && !mapDragFlag) {
//                $( "#nav-panel" ).panel( "open" );
                $.mobile.activePage.find('#nav-panel').panel("open");
            }
        }
    });
});