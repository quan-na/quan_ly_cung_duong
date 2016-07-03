(function() {
    globalControl.assignMenuActions({
        'create_cung_duong': function() {
            if ($("div.create_cung_duong-view").length == 0) {
                var theDiv = $("<div class='container create_cung_duong-view'/>").insertAfter($("div.home-view"));
                globalControl.loadControl("/html/form/create_cung_duong.html", theDiv, {});
            }
            $("body").children("div.container").addClass("hidden");
            $("body").children("div.create_cung_duong-view").removeClass("hidden");
        },
        'list_cung_duong': function() {
            if ($("div.list_cung_duong-view").length == 0) {
                var theDiv = $("<div class='container list_cung_duong-view'/>").insertAfter($("div.home-view"));
                globalControl.loadControl("/html/form/list_cung_duong.html", theDiv, {});
            }
            $("body").children("div.container").addClass("hidden");
            $("body").children("div.list_cung_duong-view").removeClass("hidden");
        },
        'list_muc_cung_duong': function() {
            if ($("div.list_muc_cung_duong-view").length == 0) {
                var theDiv = $("<div class='container list_muc_cung_duong-view'/>").insertAfter($("div.home-view"));
                globalControl.loadControl("/html/form/list_muc_cung_duong.html", theDiv, {});
            }
            $("body").children("div.container").addClass("hidden");
            $("body").children("div.list_muc_cung_duong-view").removeClass("hidden");
        },
        'list_phat_tu': function() {
            if ($("div.list_phat_tu-view").length == 0) {
                var theDiv = $("<div class='container list_phat_tu-view'/>").insertAfter($("div.home-view"));
                globalControl.loadControl("/html/form/list_phat_tu.html", theDiv, {});
            }
            $("body").children("div.container").addClass("hidden");
            $("body").children("div.list_phat_tu-view").removeClass("hidden");
        },
        'user_info': function() {
            if ($("div.user_info-view").length == 0) {
                var theDiv = $("<div class='container user_info-view'/>").insertAfter($("div.home-view"));
                globalControl.loadControl("/html/form/user_info.html", theDiv, {});
            }
            $("body").children("div.container").addClass("hidden");
            $("body").children("div.user_info-view").removeClass("hidden");
        },
        'list_user': function() {
            if ($("div.list_user-view").length == 0) {
                var theDiv = $("<div class='container list_user-view'/>").insertAfter($("div.home-view"));
                globalControl.loadControl("/html/form/list_user.html", theDiv, {});
            }
            $("body").children("div.container").addClass("hidden");
            $("body").children("div.list_user-view").removeClass("hidden");
        },
        'logout': function() {
            globalControl.doAjax("/logout", {}, function(data) {
                window.location.href = "/";
            });
        }
    });
})();
