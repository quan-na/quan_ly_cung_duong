(function() {
    var _listPhatTuControl;
    var _listCungDuongControl;
    var _reportCungDuongControl;
    var _userInfoControl;
    globalControl.assignMenuActions({
        'list_cung_duong': function() {
            if ($("div.list_cung_duong-view").length == 0) {
                if ($("div.list_phat_tu-view").length == 0) {
                    var otherDiv = $("<div class='container list_phat_tu-view'/>").insertAfter($("div.home-view"));
                    globalControl.loadControl("/html/form/list_phat_tu.html", otherDiv, {
                        callback: function(control) {
                            _listPhatTuControl = control;
                            var theDiv = $("<div class='container list_cung_duong-view'/>").insertAfter($("div.home-view"));
                            globalControl.loadControl("/html/form/list_cung_duong.html", theDiv, {
                                listPhatTuControl: _listPhatTuControl,
                                callback: function(cdControl) {
                                    _listCungDuongControl = cdControl;
                                }
                            });
                        }
                    });
                } else {
                    var theDiv = $("<div class='container list_cung_duong-view'/>").insertAfter($("div.home-view"));
                    globalControl.loadControl("/html/form/list_cung_duong.html", theDiv, {
                        listPhatTuControl: _listPhatTuControl,
                        callback: function(cdControl) {
                            _listCungDuongControl = cdControl;
                        }
                    });
                }
            }
            // check if phat tu is being chosen
            $("body").children("div.container").addClass("hidden");
            if (_listCungDuongControl && _listCungDuongControl.isBeingChosen())
                $("body").children("div.list_phat_tu-view").removeClass("hidden");
            else
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
                globalControl.loadControl("/html/form/list_phat_tu.html", theDiv, {
                    callback: function(control) {
                        _listPhatTuControl = control;
                    }
                });
            }
            $("body").children("div.container").addClass("hidden");
            $("body").children("div.list_phat_tu-view").removeClass("hidden");
        },
        'report_cung_duong': function() {
            if ($("div.report_cung_duong-view").length == 0) {
                var theDiv = $("<div class='container report_cung_duong-view'/>").insertAfter($("div.home-view"));
                globalControl.loadControl("/html/form/report_cung_duong.html", theDiv, {
                    callback: function(control) {
                        _reportCungDuongControl = control;
                    }
                });
            } else
                _reportCungDuongControl.reloadMucCungDuong();
            $("body").children("div.container").addClass("hidden");
            $("body").children("div.report_cung_duong-view").removeClass("hidden");
        },
        'user_info': function() {
            if ($("div.user_info-view").length == 0) {
                var theDiv = $("<div class='container user_info-view'/>").insertAfter($("div.home-view"));
                globalControl.loadControl("/html/form/user_info.html", theDiv, {
                    callback: function(control) {
                        _userInfoControl = control;
                    }
                });
            } else
                _userInfoControl.reload();
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
