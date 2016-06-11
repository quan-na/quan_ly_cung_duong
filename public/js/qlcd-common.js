
var globalControl = (function () {
    var _this = {};

    // Create language switcher instance
    var lang = new Lang();
    lang.dynamic('vi', '/langs/vi.json');
    lang.init({
        defaultLang: 'en',
        currentLang: 'vi'
    });

    // menu items actions
    var menuActions = {
        // TODO handlers
        'test': function() { alert('test');},
        'home': function() { alert('home');},
        'about': function() { alert('about');}
    };
    _this.hasMenuAction = function(menuId) {
        if (typeof(menuActions[menuId]) == 'function')
            return true;
        return false;
    };
    _this.menuAction = function(menuId) {
        if (typeof(menuActions[menuId]) == 'function')
            return menuActions[menuId];
        return function () {};
    };

    // load tag or forms, and append it after tag
    var tagxCache = {};
    _this.loadControl = function (url, parentTag, options) {
        if (!url.startsWith("/html/"))
            return;
        if (url.startsWith("/html/tagx/") && typeof(tagxCache[url]) == 'object') {
            var html = tagxCache[url].html;
            var init = tagxCache[url].init;
            var tag = $(html).appendTo($(parentTag));
            if (typeof(init) == 'function')
                init(tag, options);
        } else
            $.ajax({
                'url':url,
                type: "GET",
                dataType: "text",
                success: function(data) {
                    var splitData = data.split("===to-lead-people_walk-behind-them===");
                    html = splitData[0];
                    if (splitData.length >=2)
                        init = eval("("+splitData[1]+"\n)");
                    if (url.startsWith("/html/tagx/"))
                        tagxCache[url] = {
                            'html': html,
                            'init': init
                        };
                    var tag = $(html).appendTo($(parentTag));
                    if (typeof(init) == 'function')
                        init(tag, options);
                },
                error: function() {
                    console.log("!!! can not load control : "+url);
                }
            });
    };

    // This will be called after each successful login
    var awaitAuthenticationCallbacks = [];
    var _loggedInCallback = function () {
        // Call all awaiting callbacks
        var callbacks = [].concat(awaitAuthenticationCallbacks);
        awaitAuthenticationCallbacks.length = 0;
        $.each(callbacks, function(idx, fn) {
            fn();
        });
    };
    // do actions with retries
    _this.doAjax = function (url, dataObject, successCallback) {
        // TODO implement
        var retryCount = 0;
        var options = {
            'url': url,
            type: "POST",
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            data: JSON.stringify(dataObject),
            success: function(data) {
                successCallback(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status == 403 && retryCount<5) {
                    $('#loginModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    awaitAuthenticationCallbacks.push(function () {
                        $.ajax(options);
                    });
                } else if (textStatus == "timeout" && retryCount<3) {
                    $.ajax(options);
                }
                retryCount++;
            }
        };
        $.ajax(options);
    };

    // do these after document is loaded
    $(document).ready(function() {
        // TODO sample menu load
        _this.loadControl("/html/tagx/menu_item.html", $("#navbar > .navbar-nav"), {
            menu_id: 'test',
            menu_text: 'Test'
        });
        _this.loadControl("/html/tagx/menu_item.html", $("#navbar > .navbar-nav"), {
            menu_id: 'home',
            menu_text: 'Home'
        });
        _this.loadControl("/html/tagx/menu_item.html", $("#navbar > .navbar-nav"), {
            menu_id: 'about',
            menu_text: 'About'
        });
        // load menu from database, trigger login modal
        _this.doAjax("/menu/list", {}, function(data) {
            console.log("menu data loaded" + JSON.stringify(data));
            $.each(data, function (idx, obj) {
                _this.loadControl("/html/tagx/menu_item.html", $("#navbar > .navbar-nav"), obj);
            });
        });
        // Load login form
        _this.loadControl("/html/form/login_modal.html", $("#loginModal"), {
            modalId: "loginModal",
            loggedInCallback: _loggedInCallback
        });
    });

    return _this;
})();
