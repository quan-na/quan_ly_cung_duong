
var globalControl = (function () {
    var _this = {};
    $( document ).ajaxError(function( event, jqxhr, settings, thrownError ) {
        if ( settings.url != "/authenticate" && jqxhr.status == 403 ) {
            console.log("not logged in.");
        }
    });
    return _this;
})();
