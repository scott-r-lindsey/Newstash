/* Simple JavaScript Inheritance
 * By John Resig http://ejohn.org/
 * MIT Licensed.
 */
(function(){
  var initializing = false, fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;
  this.Class = function(){};

  Class.extend = function(prop) {
    var _super = this.prototype;
    initializing = true;
    var prototype = new this();
    initializing = false;

    for (var name in prop) {
      prototype[name] = typeof prop[name] == "function" &&
        typeof _super[name] == "function" && fnTest.test(prop[name]) ?
        (function(name, fn){
          return function() {
            var tmp = this._super;
            this._super = _super[name];
            var ret = fn.apply(this, arguments);
            this._super = tmp;
            return ret;
          };
        })(name, prop[name]) :
        prop[name];
    }

    function Class() {
      if ( !initializing && this.init )
        this.init.apply(this, arguments);
    }
    Class.prototype = prototype;
    Class.prototype.constructor = Class;
    Class.extend = arguments.callee;
    return Class;
  };
})();

// ----------------------------------------------------------------------------
bookstash = {};
!function( $, undefined ){
    "use strict";

    $.fn.hasAttr = function(name) {
       return this.attr(name) !== undefined;
    };
    $.scrollbarWidth = function() {
        // http://stackoverflow.com/questions/986937/how-can-i-get-the-browsers-scrollbar-sizes
        var parent, child, width;
        if(width === undefined) {
            parent = $('<div style="width:50px;height:50px;overflow:auto"><div/></div>').appendTo('body');
            child = parent.children();
            width = child.innerWidth()-child.height(99).innerWidth();
            parent.remove();
        }
        return width;
    };
    $.extend($.infinitescroll.prototype, {
       _nearbottom_local: function infscr_nearbottom_local(){
           var opts   = this.options;
           var binder = $(opts.binder);

           return (binder.scrollTop() + binder.innerHeight() >= binder[0].scrollHeight - opts.bufferPx);
       },
        _determinepath_local: function infscr_determinepath_local(obj, path){
            //console.log(obj);
            return 2;
        },
        _showdonemsg:  function infscr_showdonemsg(){
            var opts = this.options;

            opts.loading.msg
            .find('img')
            .hide()
            .parent()
            .find('div').html(opts.loading.finishedMsg).animate({ opacity: 1 }, 2000, function () {
                $(this).parent().fadeOut(opts.loading.speed);
            });

            var scratch = document.createElement('div');
            $(scratch).html($('body > .search-end').html());
            $('body > .browse').append($(scratch).children());
       },
        _loadcallback: function infscr_loadcallback(box, data, url) {
            var opts = this.options,
            callback = this.options.callback, // GLOBAL OBJECT FOR CALLBACK
            result = (opts.state.isDone) ? 'done' : (!opts.appendCallback) ? 'no-append' : 'append', frag;

            // ---------------------------------------------------------------------------------------
            // I ought just rewrite this thing to not be so dumb
            var scratch = document.createElement('div');
            $(scratch).html(data);

            var morelink;
            this.nextLink = undefined;

            if (morelink = $(scratch).find(opts.navSelector).find(opts.nextSelector)[0]){
                this.nextLink = morelink.href;
                this._debug('found link',  morelink.href);
            }
            else{
                opts.state.isInvalidPage = true;
            }

            // ---------------------------------------------------------------------------------------

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['_loadcallback_'+opts.behavior] !== undefined) {
                this['_loadcallback_'+opts.behavior].call(this,box,data,url);
                return;
            }

            switch (result) {
                case 'done':
                    this._showdonemsg();
                    return false;

                case 'no-append':
                    if (opts.dataType === 'html') {
                        data = '<div>' + data + '</div>';
                        data = $(data).find(opts.itemSelector);
                    }

                    // if it didn't return anything
                    if (data.length === 0) {
                        return this._error('end');
                    }

                    break;

                case 'append':
                    var children = box.children();
                    // if it didn't return anything
                    if (children.length === 0) {
                        return this._error('end');
                    }

                    // use a documentFragment because it works when content is going into a table or UL
                    frag = document.createDocumentFragment();
                    while (box[0].firstChild) {
                        frag.appendChild(box[0].firstChild);
                    }

                    this._debug('contentSelector', $(opts.contentSelector)[0]);
                    $(opts.contentSelector)[0].appendChild(frag);

                    // previously, we would pass in the new DOM element as context for the callback
                    // however we're now using a documentfragment, which doesn't have parents or children,
                    // so the context is the contentContainer guy, and we pass in an array
                    // of the elements collected as the first argument.

                    data = children.get();
                    break;
            }

            // loadingEnd function
            opts.loading.finished.call($(opts.contentSelector)[0],opts);

            // smooth scroll to ease in the new content
            if (opts.animate) {
                var scrollTo = $(window).scrollTop() + $(opts.loading.msg).height() + opts.extraScrollPx + 'px';
                $('html,body').animate({ scrollTop: scrollTo }, 800, function () { opts.state.isDuringAjax = false; });
            }

            if (!opts.animate) {
                // once the call is done, we can allow it again.
                opts.state.isDuringAjax = false;
            }

            callback(this, data, url);

            if (opts.prefill) {
                this._prefill();
            }
        },
        beginAjax: function infscr_ajax(opts) {
            var instance = this,
                path = opts.path,
                box, desturl, method, condition;

            // increment the URL bit. e.g. /page/3/
            opts.state.currPage++;

            // Manually control maximum page
            if ( opts.maxPage !== undefined && opts.state.currPage > opts.maxPage ){
                opts.state.isBeyondMaxPage = true;
                this.destroy();
                return;
            }

            // if we're dealing with a table we can't use DIVs
            box = $(opts.contentSelector).is('table, tbody') ? $('<tbody/>') : $('<div/>');

            //----------------------------------------------------------------------------------------------

            var morelink;
            if (undefined !== this.nextLink){
                desturl = this.nextLink;
                instance._debug('used cached next link');
            }
            else if (morelink = $(opts.navSelector).find(opts.nextSelector)[0]){
                desturl = morelink.href;
                instance._debug('used found next link');
            }
            else{
                desturl = (typeof path === 'function') ? path(opts.state.currPage) : path.join(opts.state.currPage);
            }
            //----------------------------------------------------------------------------------------------

            instance._debug('heading into ajax', desturl);

            method = (opts.dataType === 'html' || opts.dataType === 'json' ) ? opts.dataType : 'html+callback';
            if (opts.appendCallback && opts.dataType === 'html') {
                method += '+callback';
            }

            switch (method) {
                case 'html+callback':
                    instance._debug('Using HTML via .load() method');
                    box.load(desturl + ' ' + opts.itemSelector, undefined, function infscr_ajax_callback(responseText) {
                        instance._loadcallback(box, responseText, desturl);
                    });

                    break;

                case 'html':
                    instance._debug('Using ' + (method.toUpperCase()) + ' via $.ajax() method');
                    $.ajax({
                        // params
                        url: desturl,
                        dataType: opts.dataType,
                        complete: function infscr_ajax_callback(jqXHR, textStatus) {
                            condition = (typeof (jqXHR.isResolved) !== 'undefined') ? (jqXHR.isResolved()) : (textStatus === 'success' || textStatus === 'notmodified');
                            if (condition) {
                                instance._loadcallback(box, jqXHR.responseText, desturl);
                            } else {
                                instance._error('end');
                            }
                        }
                    });

                    break;
                case 'json':
                    instance._debug('Using ' + (method.toUpperCase()) + ' via $.ajax() method');
                    $.ajax({
                        dataType: 'json',
                        type: 'GET',
                        url: desturl,
                        success: function (data, textStatus, jqXHR) {
                            condition = (typeof (jqXHR.isResolved) !== 'undefined') ? (jqXHR.isResolved()) : (textStatus === 'success' || textStatus === 'notmodified');
                            if (opts.appendCallback) {
                                // if appendCallback is true, you must defined template in options.
                                // note that data passed into _loadcallback is already an html (after processed in opts.template(data)).
                                if (opts.template !== undefined) {
                                    var theData = opts.template(data);
                                    box.append(theData);
                                    if (condition) {
                                        instance._loadcallback(box, theData);
                                    } else {
                                        instance._error('end');
                                    }
                                } else {
                                    instance._debug('template must be defined.');
                                    instance._error('end');
                                }
                            } else {
                                // if appendCallback is false, we will pass in the JSON object. you should handle it yourself in your callback.
                                if (condition) {
                                    instance._loadcallback(box, data, desturl);
                                } else {
                                    instance._error('end');
                                }
                            }
                        },
                        error: function() {
                            instance._debug('JSON ajax request failed.');
                            instance._error('end');
                        }
                    });

                    break;
            }
        },
    });

    String.prototype.replaceAll = function(search, replace) {
        if (replace === undefined) {
            return this.toString();
        }
        return this.split(search).join(replace);
    }

    var Base = Class.extend({
        createElement: function(type, clss){
            var elm = document.createElement(type);
            if (undefined !== clss){
                $(elm).addClass(clss);
            }
            return elm;
        },
        _debug: function(message){
            if (this.debug){
                console.log(message);
            }
        },
        colorsEqual: function(a, b){
            var canvas, data, context;

            canvas = this.createElement('canvas');
            $(canvas).height(1);
            $(canvas).width(1);
            context = canvas.getContext("2d");
            context.rect(0,0,1,1);
            context.fillStyle = a;
            context.fill();
            data = context.getImageData(0,0,1,1);

            var aColor = data.data[0] + ':' + data.data[1]  + ':' +
                data.data[2] + ':' + data.data[3];

            canvas = this.createElement('canvas');
            $(canvas).height(1);
            $(canvas).width(1);
            context = canvas.getContext("2d");
            context.rect(0,0,1,1);
            context.fillStyle = b;
            context.fill();
            data = context.getImageData(0,0,1,1);

            var bColor = data.data[0] + ':' + data.data[1]  + ':' +
                data.data[2] + ':' + data.data[3];

            return aColor == bColor;
        }
    });
    var flagMaster = Base.extend({
        flagFormRadioClick: function(mg){
            var fm = this;

            var box         = mg.getBox();
            var selected    = $(box).find("input[type='radio']:checked")[0];
            var nag         = $(box).find('.nag')[0];
            var textnag     = $(box).find('.text-nag')[0];

            $(nag).removeClass('visible');
            $(textnag).removeClass('visible');

            if (0 == selected.value){
                $(box).find("textarea").removeClass('hidden').focus();
            }
            else{
                $(box).find("textarea").addClass('hidden');
            }
        },
        processFlagForm: function(mg){
            var fm = this;

            var box         = mg.getBox();
            var form        = $(box).find('form')[0];
            var selected    = $(box).find("input[type='radio']:checked")[0];
            var nag         = $(box).find('.nag')[0];
            var textnag     = $(box).find('.text-nag')[0];

            if (undefined == selected){
                $(nag).html('<i class="fa fa-arrow-up"></i> Please select a reason')
                    .addClass('visible');
                return;
            }
            else if (0 == selected.value){
                var textval = $(box).find("textarea").val();
                if (3 > textval.length){
                    $(textnag).html('<i class="fa fa-arrow-up"></i> Please tell us more')
                        .addClass('visible');
                    $(box).find("textarea").removeClass('hidden').focus();

                    return;
                }
            }

            mg.showProgress();
            var modal = mg;

            $.ajax({
                type: 'POST',
                url: $(form).attr('action'),
                data: $(form).serialize(),
                success: function(data){
                    modal.dismiss();
                    var mg = new modalGeneral({
                        elm:            fm.createElement('button'),
                        type:           'alert',
                        headline:       "Thank you for your feedback",
                        subhead:        fm.flagText.thanksSubHead
                    });
                    mg.activate();
                }
            });
        },
        openFlagModal: function(replace, headline){
            var fm = this;

            var review_id = $(event.target).parents('.review').attr('data-review-id');

            var mg = new modalGeneral({
                elm:            event.target,
                type:           'form',
                template:       fm.flagText.formselector,
                replace:        replace,
                headline:       headline,
                confirmText:    '<i class="fa fa-flag"></i> Flag it',
                setupCB:        function(obj){
                        var box = obj.getBox();
                        $(box).find('input[type=radio]').on('change', function(){
                            fm.flagFormRadioClick(mg);
                        });
                    },
                completedCB:    function(obj){
                        fm.processFlagForm(obj);
                    }
            });
            mg.activate();
        }
    });

    // ------------------------------------------------------------------------
    var backToTopManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            classes:
                - visible
                - hidden
            listens:
                scroll
            interactions:
                - body > .browse
        -------------------------------------------------------------------- */
        init: function (conf){

            var bm = this;
            bm.box = conf.elm;

            var scrollWidth = $.scrollbarWidth();

            $('body > .browse').on('scroll', function(){
                if (600 < $('body > .browse').scrollTop()){
                    $(bm.box).css({'right': scrollWidth + 'px'});
                    $(bm.box).addClass('visible');
                }
                else{
                    $(bm.box).removeClass('visible');
                    $(bm.box).css({'right': '-100px'});
                }
            });

            $(bm.box).find('a').click(function(event){
                event.preventDefault();
                $('body > .browse').animate( { scrollTop: 0}, 500 );
                $(bm.box).addClass('hidden');
                setTimeout(function(){
                    $(bm.box).removeClass('hidden');
                }, 500);
            });
        }
    });
    // ------------------------------------------------------------------------
    var classModLink = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
                - data-selector
                - data-parent-selector
                - data-toremove
                - data-toadd
                - data-toggle
                - data-cml-noprevent
        -------------------------------------------------------------------- */
        init: function(conf){
            var elm = conf.elm;

            $(elm).click( function(event){
                if (!$(elm).hasAttr('data-cml-noprevent')){
                    event.preventDefault();
                }
                var a = this;
                var selector            = $(a).attr('data-selector');
                var parentSelector      = $(a).attr('data-parent-selector');
                var toremove            = $(a).attr('data-toremove');
                var toadd               = $(a).attr('data-toadd');

                var target;
                if (undefined != parentSelector){
                    target = $(a).parents(parentSelector);
                }
                else{
                    target = $(selector);
                }

                if ($(a).hasAttr('data-toggle')){
                    if (target.hasClass(toadd)){
                        target.removeClass(toadd);
                    }
                    else{
                        target.addClass(toadd);
                    }
                }
                else{
                    if (undefined != toadd){
                        target.addClass(toadd);
                    }
                    if (undefined != toremove){
                        target.removeClass(toremove);
                    }
                }
            });
        }
    });
    // ------------------------------------------------------------------------
    var outClickLogger = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            depends on:
                - ga
            attributes:
                - data-outlink-type
        -------------------------------------------------------------------- */
        init: function(conf){
            $(conf.elm).click(function(event){
                if (event.which == 2 ) { // middle click
                    ga('send', {
                        'hitType': 'event',          // Required.
                        'eventCategory': 'link',     // Required.
                        'eventAction': 'click',      // Required.
                        'eventLabel': $(conf.elm).attr('data-outlink-type')
                    });
                }
                else{
                    var url = this.href;
                    event.preventDefault();
                    ga('send', {
                        'hitType': 'event',          // Required.
                        'eventCategory': 'link',     // Required.
                        'eventAction': 'click',      // Required.
                        'eventLabel': $(conf.elm).attr('data-outlink-type'),
                        'hitCallback': function() {
                            document.location = url;
                        }
                    });
                }
            });
        }
    });
    // ------------------------------------------------------------------------
    var historyManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated manually
            classes:
                - visible
            listens:
                - (window) popstate
                - (document) bookster:domchanged
                - bookster:topmenudemure
                - bookster:menubackgroundclick
                - bookster:pagechange
            throws:
            attributes:
                - data-bstsh-nohist
                - pagename
            globals:
                - bookstash.history
            interactions:
                - body > div.browse
                - body > div.focus
                - body > div.browse > div.books
                - body > div.focus > *:nth-child(2)
        -------------------------------------------------------------------- */
        init: function (conf){
            var hm = this;
            bookstash.history = this;

            // -- givens -------------------------------------------------------
            hm.box          = {};
            hm.box.browse   = $('body > div.browse')[0];
            hm.box.focus    = $('body > div.focus')[0];

            hm.pathregex    = {};
            hm.pathregex.focus = {
                about:      RegExp('^/about'),
                admin:      RegExp('^/admin'),
                blog:       RegExp('^/blog'),
                book:       RegExp('^/book/'),
                privacy:    RegExp('^/privacy'),
                reset:      RegExp('^/resetting'),
                welcome:    RegExp('^/register'),
                test:       RegExp('^/test')
            };
            hm.pathregex.browse = {
                root:       RegExp('^/$'),
                news:       RegExp('^/$'),
                node:       RegExp('^/browse/category'),
                top:        RegExp('^/top'),
                search:     RegExp('^/search/')
            };

            // -- state --------------------------------------------------------
            hm.states           = {};
            hm.slug             = hm._makeSlug(document.location);
            hm.initslug         = hm.slug;
            hm.mode             = hm._findUrlType(document.location.pathname);
            hm.debug            = false;
            hm.lastBrowseState  = false;
            hm.sms              = {};

            // -- figure out platform scrollbar width --------------------------
            hm.sbwidth = $.scrollbarWidth();

            // -- set up listeners ---------------------------------------------
            window.addEventListener('popstate', function(event) {
                // popstate fires on back/forward
                hm._handleHistoryPopstate(event);
            });
            $(document).on('bookster:domchanged', function(){
                hm._controlLinks();
            });

            $(document).on('bookster:topmenudemure bookster:menubackgroundclick', function(){
                $(hm.box.browse).focus();
            });

            // -----------------------------------------------------------------
            $(hm.box.browse).focus();
            hm._saveState();
            hm._controlLinks();
            hm._updatePageNameAttr();
            $('body').attr('data-bstsh-mode', hm.mode);

            if ('browse' == hm.mode){
                hm._createScrollManager(hm.slug);
            }
            // -----------------------------------------------------------------
        },
        /**
         * Navigate to a new URL
         **/
        navigate: function(url, callback){
            var hm = this;
            var a = hm.createElement('a');
            a.href = url;
            hm._clickLoad(a, callback);
        },
        closeFocus: function(){
            var hm = this;

            if (hm.slug == hm.initslug){
                hm.navigate('/');
            }
            else{
                if (hm.lastBrowseState){
                    var state = hm.lastBrowseState;
                    history.pushState(state, null, state.url);

                    hm._setSlug(state.slug);
                    document.title = state.title;
                    hm._resumeScrollManager(state.slug)
                    hm._setPageBrowseOrFocus('browse');
                    hm._updatePageNameAttr();
                }
                else{
                    hm.navigate('/');
                }
            }
        },
        setScrollManager: function(sm, slug){
            var hm = this;
            if (undefined == slug){
                slug = hm.slug;
            }
            hm.sms[slug] = sm;
        },
        // --------------------------------------------------------------------
        // private below
        _handleHistoryPopstate: function(event){
            // called immediately after navigation
            var hm = this;

            // ----------------------------------------------------------------
            // save scrollposition on outgoing page before we move on
            var scrollPos = hm._getCurentScrollPos(hm.states[hm.slug].pathname);
            hm.states[hm.slug].scrollTop = scrollPos;

            // ----------------------------------------------------------------
            if (undefined == event.state){
                hm._debug('dropped popstate event');
                return;
            }
            if (undefined == hm.states[event.state.slug]){
                hm._debug('dead state detected, try to fix :(');
                hm._pauseScrollManagers();

                var type = hm._findUrlType(event.state.pathname);
                $.get(event.state.url, function(data){
                    hm._loadNewPage(data, type, event.state.slug, event.state.pathname);
                    hm._saveState();
                    hm._setPageBrowseOrFocus(type);
                });
                return;
            }
            hm._debug('history event, target url: ' + event.state.url);

            hm._pauseScrollManagers();
            hm._restoreState(event.state);
            hm._updatePageNameAttr();
            $(document).trigger( "bookster:pagechange" );

            ga('send', 'pageview', event.state.pathname); // FIXME event?
        },
        _handleClick: function(event){
            var hm      = this;
            var a       = event.currentTarget;

            if( event.which == 2 ) {
                return;
            }
            event.preventDefault();

            hm._clickLoad(a);
        },
        // --------------------------------------------------------------------
        _clickLoad: function(a, callback){
            var hm = this;

            $.get(a.href, function(data){
                // check for redirect, must fix URL
                var scratch = hm.createElement('div', '');
                scratch.innerHTML = data;

                var over_url = false;
                if (over_url = $(scratch).find('[data-bstsh-url]').attr('data-bstsh-url')){

                    var parser = document.createElement('a');
                    parser.href = over_url;

                    if (parser.href != a.href){
                        a = parser;
                    }
                }

                // IE fix
                var pathname = a.pathname;
                if ('/' != pathname.substr(0,1)){
                    pathname = '/' + pathname;
                }

                var type = hm._findUrlType(pathname);
                var slug = hm._makeSlug(a);
                hm._debug('click event id: ' + slug + ' (' + type + ')');

                hm._saveState();
                hm._loadNewPage(data, type, slug, pathname);
                history.pushState({slug: slug}, null, a.href); // just a stub
                hm._saveState();
                hm._setPageBrowseOrFocus(type);
                hm._updatePageNameAttr();
                $(document).trigger( "bookster:pagechange" );

                if (undefined != callback){
                    callback();
                }
            });
        },
        _loadNewPage: function(data, type, slug, pathname){
            var hm = this;
            var sm = false, box;

            var scratch = hm.createElement('div', '');
            scratch.innerHTML = data;

            document.title = $(scratch).find('title').text();
            $(scratch).find('title').remove();

            if ('browse' == type){
                hm._setBrowseContent($(scratch).children());

                $(scratch).find('script').each(function(){
                    if (undefined != this.src){ $.getScript(this.src); }
                })

                hm._createScrollManager(slug);
                $(document).trigger( "bookster:domchanged" );
                $(hm.box.browse).scrollTop(0);
            }
            else if ('focus' == type){
                hm._setFocusContent($(scratch).children('section'));

                $(hm.box.focus).find('script').each(function(){
                    if (undefined != this.src){ $.getScript(this.src); }
                })
                $(document).trigger( "bookster:domchanged" );

                setTimeout(function(){
                    var hash = location.hash;

                    if (hash){
                        var target = $(hash);
                        $('div.focus').animate({
                            scrollTop: target.position().top
                        }, 500);
                    }
                    else{
                        $(hm.box.focus).scrollTop(0);
                    }
                }, 0);
            }
            else{
                console.log('neither browser nor focus');
            }

            // Google Analytics integration
            // FIXME is this needed??????????????
            ga('send', 'pageview', pathname);

            hm._updatePageNameAttr();
            $(document).trigger( "bookster:pagechange" );
            hm._setSlug(slug);
        },
        _controlLinks: function(){
            var hm = this;

            $('a').each(function(index){
                if ($(this).attr('data-bstsh-nohist')){
                    return;
                }
                if (undefined == this.bookHMchecked){
                    this.bookHMchecked = true;
                    var type = hm._findUrlType(this.pathname);

                    if (type){
                        $(this).click(function(event){
                            hm._handleClick(event);
                        });
                    }
                }
            });
        },
        _updatePageNameAttr: function(){
            var hm = this;

            var urldetails = hm._findUrlTypeAndName(document.location.pathname);
            var pagename = $('.pagedata').attr('data-override-pagename');

            if (undefined != pagename){
                $('body').attr({'pagename': pagename})
            }
            else if (!urldetails){
                $('body').attr({'pagename': ''})
            }
            else{
                $('body').attr({'pagename': urldetails.name})
            }
        },
        _getCurentScrollPos:function(pathname){
            var hm = this;
            if ('browse' == hm._findUrlType(pathname)){
                return $(hm.box.browse).scrollTop();
            }
            else if ('focus' == hm._findUrlType(pathname)){
                return $(hm.box.focus).scrollTop();
            }
        },
        _saveState: function(){
            var hm = this;

            // -- history state object (serialized by browser) -----------------
            var state = {
                url: document.location.href,
                pathname: document.location.pathname,
                slug: hm.slug,
                title: $(document).find("title").text()
            };

            history.replaceState(state, null, document.location.href);

            // -- local state object (objects) ---------------------------------
            var content;

            if ('browse' == hm._findUrlType(state.pathname)){
                content = $('body > div.browse').children();
            }
            else if ('focus' == hm._findUrlType(state.pathname)){
                content = $('body > div.focus > *:nth-child(1)')[0];
            }

            hm.states[hm.slug] = {
                url: document.location.href,
                pathname: document.location.pathname,
                scrollmanager: hm.sm,
                pageContent: content,
                scrollTop: hm._getCurentScrollPos(state.pathname)
            }

            if ('browse' == hm._findUrlType(state.pathname)){
                hm.lastBrowseState = state;
            }

            hm._debug('saving state, slug ' + hm.slug + ', path ' + state.url);
        },
        _restoreState: function(state){
            var hm      = this;
            var type    = hm._findUrlType(state.pathname);
            hm._setSlug(state.slug);

            hm._debug('restoring state, slug ' + state.slug + ', path ' + state.url +
                ' scrolltop: ' + hm.states[state.slug].scrollTop);

            if ('browse' == type){
                hm._setBrowseContent(hm.states[state.slug].pageContent, hm.states[state.slug].scrollTop);
            }
            else if ('focus' == type){
                hm._setFocusContent(hm.states[state.slug].pageContent, hm.states[state.slug].scrollTop);
            }

            hm._resumeScrollManager(state.slug);
            document.title = state.title;
            hm._setPageBrowseOrFocus(type);
        },
        _setPageBrowseOrFocus: function(type){
            var hm = this;
            hm._debug('new mode ' + type);

            if ('focus' == hm.mode){
                if ('browse' == type){
                    $(hm.box.focus).removeClass('visible');
                    $(hm.box.browse).focus();
                }
            }
            else{
                if ('focus' == type){
                    $(hm.box.focus).addClass('visible');
                    $(hm.box.focus).focus();
                }
            }
            $('body').attr('data-bstsh-mode', type);

            hm.mode = type;
        },
        _setSlug: function(slug){
            this.slug = slug;
        },
        _setBrowseContent: function(content, scrollPos){
            var hm = this;
            $(hm.box.browse).children().detach();
            $(hm.box.browse).append(content);
            $(hm.box.browse).scrollTop(scrollPos);
        },
        _setFocusContent: function(section, scrollPos){
            var hm = this;
            $(hm.box.focus).children('section').detach();
            $(hm.box.focus).append(section);
            $(hm.box.focus).scrollTop(scrollPos);
        },
        _createScrollManager: function(slug){
            var hm = this;

            var sm = new scrollManager( {
                box: $(hm.box.browse).find('div.books')[0],
                url: document.location.pathname,
                elmToScroll: hm.box.browse
            });
            hm.setScrollManager(sm, slug);
        },
        _resumeScrollManager: function(slug){
            var hm = this;
            if (undefined != hm.sms[slug]){
                hm.sms[slug].resume();
            }
        },
        _pauseScrollManagers: function(){
            var hm = this;
            $.each(hm.sms, function(slug, scrollManager){
                scrollManager.pause();
            });
        },
        _findUrlTypeAndName: function(pathname){
            var hm = this, match, regex, name;

            // -- search for browse links -----------------------------------
            Object.keys(hm.pathregex.browse).forEach(function (key) {
                regex = hm.pathregex.browse[key];
                if (regex.exec(pathname)){
                    name = key;
                    match = 'browse';
                    return false;
                }
            });
            if (match){
                return { type: match, name: name };
            }

            // -- search for focus links ------------------------------------
            Object.keys(hm.pathregex.focus).forEach(function (key) {
                regex = hm.pathregex.focus[key];
                if (regex.exec(pathname)){
                    name = key;
                    match = 'focus';
                    return false;
                }
            });
            if (match){
                return { type: match, name: name };
            }
            return false;
        },
        _findUrlType: function(pathname){
            var hm = this;

            var match = hm._findUrlTypeAndName(pathname);
            if (match){
                return match.type;
            }
            return false;
        },
        _makeSlug: function(a, url){
            if (!a){
                var a = hm.createElement('a');
                a.href = url;
            }
            return (a.pathname + ':' + (new Date()).getTime());
        }
    });
    // ------------------------------------------------------------------------
    var historyCloseButton = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            depends on:
                - bookstash.history
        -------------------------------------------------------------------- */
        init: function(conf){

            var hc = this;
            hc.elm = conf.elm;

            $(conf.elm).click(function(event){
                if (event.which == 1 ) { // main click

                    if (event.target == conf.elm){
                        return bookstash.history.closeFocus();
                    }

                    if ($(hc.elm).is('button')){
                        $(event.target).parents('button').each(function(index){
                            if (this == hc.elm){
                                bookstash.history.closeFocus();
                            }
                        });
                    }
                }
            });
        }
    });
    // ------------------------------------------------------------------------
    var topMenu = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            classes:
                - focused
                - show
            listens:
                - bookster:topmenureveal
                - bookster:domchanged
                - bookster:menubackgroundclick
                - bookster:searchpick
                - bookster:pagechange
            throws:
                - bookster:topmenureveal
                - bookster:topmenudemure
                - bookster:loginrequest
            attributes:
                - data-menu-noautodrop
                - data-user-magic
            globals:
            interactions:
        -------------------------------------------------------------------- */
        init: function(conf) {
            var selector, md = this;

            md.box = {};
            md.state = {};

            md.box.menu = conf.elm;
            if (selector = $(conf.elm).attr('data-menu-target')){
                md.box.dropdown = $(selector)[0];
            }
            md.box.input = $(md.box.menu).find('.query input')[0];
            md.focus = false;

            $(md.box.menu).click(function(event){
                if (md.box.input){
                    if (event.target == md.box.input){
                        return;
                    }
                }
                if (!md.focus){
                    var body = $('body')[0];

                    if (('anon' == $('body').attr('auth')) &&
                        ('magic' == $(md.box.menu).attr('data-user-magic'))){
                        $(document).trigger('bookster:loginrequest');
                        $(document).trigger('bookster:topmenureveal');
                        $(document).trigger('bookster:topmenudemure');
                    }
                    else{
                        md.show();
                    }
                }
                else{
                    md.hide();
                    $(document).trigger( 'bookster:topmenudemure' );
                }
            });

            $(document).on(
                    'bookster:authchange ' +
                    'bookster:pagechange ' +
                    'bookster:topmenureveal ' +
                    'bookster:menubackgroundclick ' +
                    'bookster:searchpick', function(){
                if (md.focus){
                    md.hide();
                }
            });
        },
        hide: function(){
            var md = this;
            md.hideMenu();
            $(md.box.menu).removeClass('focused');
            $(document).trigger('bookster:topmenudemure');
            md.focus = false;
        },
        hideMenu: function(){
            var md = this;
            $(md.box.dropdown).removeClass('show');
        },
        show: function(){
            var md = this;
            $(document).trigger( "bookster:topmenureveal" );
            if (!$(md.box.menu).attr('data-menu-noautodrop')){
                if (undefined != md.box.dropdown){
                    this.showMenu();
                }
            }
            $(md.box.menu).addClass('focused');
            if (md.box.input){
                md.box.input.focus();
            }
            md.focus = true;
        },
        showMenu: function(){
            var md = this;
            $(md.box.dropdown).addClass('show');
        }
    });
    // ------------------------------------------------------------------------
    var blogCommentManager = flagMaster.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            listens:
                - bookster:domchanged
        -------------------------------------------------------------------- */
        init: function(conf){

            var bm = this;
            bm.box = {};
            bm.box.box = conf.elm;
            bm.box.hiddenForm = $(bm.box.box).find('.hidden-reply-form form')[0];

            bm.flagText = {};
            bm.flagText.thanksSubHead = "We'll take a look at that comment.";
            bm.flagText.formselector = '.blog-flag-comment';

            $(document).on( 'bookster:domchanged', function(){
                bm.setup();
            });

            bm.setup();
        },
        setup: function(){
            var bm = this;

            $(bm.box.box).find(
                'button.submit[data-bstsh-new=new]').on(
                'click', function(){
                    var button = this;
                    var shell = $(this).parents('.reply-box')[0];

                    var replyFunction = function(){
                        bm.postReply(shell);
                    };

                    if ('anon' == $('body').attr('auth')){
                        $(document).trigger('bookster:loginrequest');
                        // FIXME trigger post automatically after login?
                        return;
                    }
                    replyFunction();
                }
            );

            $(bm.box.box).find('button.flag').click(function(event){
                var comment_id = $(event.target).parents('article').attr('data-comment-id');
                var replace = {comment_id: comment_id};
                bm.openFlagModal(replace, "I'm flagging this comment because it is..." );
            });

            $(bm.box.box).find('button.reply[data-bstsh-new=new]').on('click',
                function(event){
                    var button = this;
                    var wrap = $(button).parents('.comment-wrap')[0];
                    if ('reading' == $(wrap).attr('mode')){
                        $(wrap).attr('mode', 'replying');
                        $(wrap).find('textarea').focus();
                    }
                    else{
                        $(wrap).attr('mode', 'reading');
                    }
                }
            );
            $(bm.box.box).find('button.cancel[data-bstsh-new=new]').on('click',
                function(event){
                    var button = this;
                    var wrap = $(button).parents('.comment-wrap')[0];
                    $(wrap).attr('mode', 'reading');
                }
            );
            $(bm.box.box).find('button.show[data-bstsh-new=new]').on('click',
                function(event){
                    var button = this;
                    var wrap = $(button).parents('.comment-wrap')[0];
                    var oldArticle = $(button).parents('article.comment')[0];
                    var newArticle = $(oldArticle).find('article.comment').detach();
                    $(wrap).attr('mode', 'revealing');
                    setTimeout(function(){
                        $(oldArticle).replaceWith(newArticle);
                        setTimeout(function(){
                            $(wrap).attr('mode', 'reading');
                        }, 100);
                    }, 200);
                }
            );
            $(bm.box.box).find('*[data-bstsh-new=new]').removeAttr('data-bstsh-new');
        },
        postReply: function(shell){     // shell is the reply-box
            var bm = this;
            var text = $(shell).find('textarea').val();
            if (3 > text.length){
                return;
            }
            var parent_id = $(shell).attr('data-bstsh-comment-id');
            var form = bm.box.hiddenForm;
            $(shell).find('button').attr('disabled', 'disabled');

            if (!parent_id){
                parent_id = 0;
            }
            $(form).find('input[name=parent_id]').val(parent_id);
            $(form).find('input[name=comment]').val(text);

            $(shell).attr('mode', 'loading');

            $.ajax({
                type: 'POST',
                url: $(form).attr('action'),
                data: $(form).serialize(),
                success: function(data){
                    $(shell).removeAttr('mode');
                    if (0 != data.error){
                        $(shell).find('button').removeAttr('disabled');
                        if (1 == data.error){
                            $(document).trigger('bookster:authchange');
                            $(document).trigger('bookster:loginrequest');
                            // FIXME trigger post automatically after login?
                            return;
                        }
                        $(shell).find('.flash').html(data.result);
                        $(shell).find('textarea').focus();
                    }
                    else{
                        var scratch     = bm.createElement('div');
                        $(scratch).html('<ul class="hidden">' + data.reply + '</ul>');
                        var reply       = $(scratch).children()[0];

                        // - reset comment area ------------------------------
                        $(shell).find('textarea').val('').trigger('change');
                        $(shell).find('button').removeAttr('disabled');
                        var wrap = $(shell).parents('.comment-wrap')[0];
                        $(wrap).attr('mode', 'reading');
                        $(shell).find('.flash').html('');

                        if (0 == parent_id){
                            $('.comments-shell').append(reply);
                        }
                        else{
                            $(shell).parent().append(reply);
                        }
                        $(document).trigger('bookster:domchanged');
                        bm.setup();
                        setTimeout(function(){ $(reply).removeClass('hidden') }, 10);
                    }
                }
            });
        }
    });
    // ------------------------------------------------------------------------
    var searchManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            interactions:
                - .searchbox
                - .menu.search .shell
                - .autocomplete-suggestions
            depends on:
                - bookstash.history
            throws:
                - bookster:searchpick
        -------------------------------------------------------------------- */
        init: function(conf){
            var sm = this;

            sm.historyManager   = bookstash.history
            sm.searchMenu       = conf.objects[0];

            sm.box = {};
            sm.box.search = $('.searchbox')[0];
            sm.box.menu = $('.menu.search .shell')[0];
            sm.box.form = $(sm.box.search).parent()[0];

            $(sm.box.form).on('submit', function(event){
                event.preventDefault()
                sm._goURL('/search/books?query=' + encodeURIComponent( sm.box.search.value ));
                return false;
            });

            $(sm.box.search).autocomplete({
                serviceUrl: '/search/json/typeahead',
                width: 400,
                maxHeight: 6000,
                appendTo: $('.autocomplete-holder')[0],
                onSelect: function(suggestion){

                    if ('book' == suggestion.type){
                        sm._goURL(
                            '/search/books?query=' + encodeURIComponent(suggestion.value) +
                            '&work_id=' + suggestion.data.work_id);
                    }
                    else if ('author' == suggestion.type){
                        sm._goURL(
                            '/search/author?query=' + encodeURIComponent(suggestion.value));
                    }
                },
                formatResult: function(suggestion, currentValue){
                    var item;

                    if ('book' == suggestion.type){
                        item = '<span class="book">' + suggestion.value + '</span>';
                    }
                    else{
                        item = '<span class="author">' + suggestion.value + '</span>';
                    }
                    return item;
                }
            });
        },
        _goURL: function(url){
            var sm = this;

            $(sm.box.search).parents('form').attr('data-searching', 'searching');
            $(sm.box.search).attr('disabled', 'disabled');

            sm.historyManager.navigate(
                url,
                function(){
                    $(sm.box.search).parents('form').removeAttr('data-searching');
                    $(sm.box.search).removeAttr('disabled');
                    sm.box.search.value = '';
                }
            );
            sm.box.search.value = 'Searching...';
        }
    });
    // ------------------------------------------------------------------------
    var scrollManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by historyManager
            classes:
                -
            listens:
                -
            throws:
                -
            attributes:
                data-bstsh-scrollobj
            globals:
            interactions:
        -------------------------------------------------------------------- */
        init: function (conf){
            var sm = this;

            sm.box              = {};
            sm.box.box          = conf.box;
            sm.url              = conf.url;
            sm.elmToScroll      = conf.elmToScroll != undefined ? conf.elmToScroll : window;
            sm.paused           = false;
            sm.resize_last      = (new Date() -0);
            sm.debug            = false;
            sm.scrollClients    = [];

            $(sm.box.box).masonry({
                stamp: $(sm.box.box).find('.item-stuck')[0],
                itemSelector: '.item',
                columnWidth: 270,
                isFitWidth: true,
                hiddenStyle: {},
                visibleStyle: {}
            });

            $(sm.box.box).children('.item').each(function(index){
                if (!$(this).visible(true)){
                    $(this).attr('sly', 'sly');
                }
            });

            $(sm.box.box).infinitescroll(
                {
                    //debug: true,
                    behavior: 'local',
                    binder: $(sm.elmToScroll),
                    navSelector: "div.navigation",
                    nextSelector: "a.morelink",
                    itemSelector: '.books div.item',
                    finishedMsg: '',
                    bufferPx: 800,
                    loadingImg: false,
                    loadingText: false,
                    donetext: ''
                },
                function(elms){
                    $(elms).attr('sly', 'sly');
                    $(sm.box.box).masonry('appended', elms, true);
                    $( document ).trigger( "bookster:domchanged" );
                }
            );

            // activate scroll clients
            $(sm.box.box).parent().find('[data-bstsh-scrollobj]').each(function(){
                var elm = this;
                var clss = $(this).attr('data-bstsh-scrollobj');
                var clss = eval(clss);
                var obj = new clss({elm: elm});

                sm.scrollClients.push(obj);
                $(elm).removeAttr('data-bstsh-scrollobj');
            });

            $.guid = 0;

            sm.elmToScroll.addEventListener( 'scroll', function(event){
                sm._onScroll(event);
            }, false );
            window.addEventListener( 'resize', function(event){
                sm._onScroll(event);
            }, false );

            $(sm.box.box).masonry( 'on', 'layoutComplete',
                function( msnryInstance, laidOutItems ) {
                    sm._onScroll();
                });

            sm._onScroll();
            setTimeout(function(){ sm._onScroll()}, 200);
        },
        pause: function(){
            var sm = this;
            sm.paused = true;
            $(sm.box.box).infinitescroll('pause');
        },
        resume: function(){
            var sm = this;
            sm.paused = false;
            $(sm.box.box).infinitescroll('resume');
            sm._onScroll();
        },
        destroy: function(){
            var sm = this;
            $(this.box.box).infinitescroll('destroy');
            sm.box.box.innerHTML = '';
            sm.box.box = undefined;
            sm.box = undefined;
            sm = undefined;
        },
        _onScroll: function(){
            //sm._debug('_onscroll event');
            var sm = this;
            if (sm.paused){ return }

            $(sm.scrollClients).each(function( index, obj){
                obj.scroll($(sm.elmToScroll).scrollTop());
            });

            $(sm.box.box).children('[sly=sly]').each(function(index){
                var item = this;
                if ($(item).visible(true)){
                    $(item).removeAttr('sly');
                    $(item).attr('anim', 'anim');
                    setTimeout(function (){ $(item).removeAttr('anim') }, 500);
                }
            });
        }
    });
    // ------------------------------------------------------------------------
    var paralaxative = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by scrollManager
            attributes:
                data-para-behavior
                data-bstsh-parallax
        -------------------------------------------------------------------- */
        init: function(conf) {
            var pl = this;

            pl.elm = conf.elm;
            pl.parallax = $(conf.elm).attr('data-bstsh-parallax');
            pl.behavior = $(conf.elm).attr('data-para-behavior');
            pl.height = $(conf.elm).attr('data-para-height');
            pl.behavior = 'menubar';

            pl.state = {};
            pl.state.scrollPos = 0;
            pl.state.startPos = parseInt($(pl.elm).css('top'), 10);
        },
        scroll: function(scrollPos){
            var pl = this;

            var height = pl.height;
            var currentPos = parseInt($(pl.elm).css('top'), 10);
            var move = scrollPos - pl.state.scrollPos;
            var newPos = currentPos - (move * pl.parallax);

            if (scrollPos > pl.state.scrollPos){
                if ('menubar' == pl.behavior){
                    if (newPos < (pl.state.startPos - height)){
                        pl.setTop(pl.state.startPos - height);
                    }
                    else{
                        pl.setTop(newPos);
                    }
                }
            }
            else if (scrollPos < pl.state.scrollPos){
                if ('menubar' == pl.behavior){

                    if (newPos > (pl.state.startPos)){
                        pl.setTop(pl.state.startPos);
                    }
                    else{
                        pl.setTop(newPos);
                    }
                }
            }
            pl.state.scrollPos = scrollPos;
        },
        setTop: function(pos){
            $(this.elm).css('top', pos + 'px');
        }
    });
    // ------------------------------------------------------------------------
    var menuWhiteBox = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            classes:
                - visible
            listens:
                - bookster:topmenureveal
                - bookster:searchpick
            throws:
                - bookster:menubackgroundclick
            attributes:
            globals:
            interactions:
        -------------------------------------------------------------------- */
        init: function(conf) {
            var wb = this;

            wb.whitebox = conf.elm;

            $(wb.whitebox).click(function(){
                if (event.which == 1 ) { // middle click
                    $( document ).trigger( "bookster:menubackgroundclick" );
                    wb.hide();
                }
            });

            $(document).on('bookster:topmenureveal', function(){
                wb.show();
            });
            $(document).on('bookster:searchpick bookster:topmenudemure bookster:pagechange', function(){
                wb.hide();
            })
        },
        show: function(){
            $(this.whitebox).addClass('visible');
        },
        hide: function(){
            $(this.whitebox).removeClass('visible');
        }
    });
    // ------------------------------------------------------------------------
    var authCssManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            listens:
                bookster:authchange
            attributes:
                data-bstsh-refresh-on
        -------------------------------------------------------------------- */
        init: function(conf) {
            var am = this;
            am.elm = conf.elm;

            $(document).on('bookster:authchange', function(event){
                am.fire();
            });

            if ($(am.elm).hasAttr('data-bstsh-refresh-on')){
                $(document).on( $(am.elm).attr('data-bstsh-refresh-on'), function(event){
                    am.fire('?' + new Date().valueOf());
                });
            }
            am.fire();
        },
        fire: function(append){
            var am = this;

            if (undefined == append){
                append = '';
            }

            if ('anon' == $('body').attr('auth')){
                var url = $(am.elm).attr('data-bstsh-anon');
                if (undefined == url){
                    $(am.elm).removeAttr('href');
                }
                else{
                    $(am.elm).attr('href', $(am.elm).attr('data-bstsh-anon') + append);
                }
            }
            else{
                $(am.elm).attr('href', $(am.elm).attr('data-bstsh-auth') + append);
            }
        }
    });
    // ------------------------------------------------------------------------
    var resetManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            listens:
                bookster:resetPasswordSubmit
                bookster:resetPassordEyeClick
                bookster:resetPasswordCancel
            globals:
                - bookstash.history

        -------------------------------------------------------------------- */
        init: function(conf) {
            var rm = this;

            rm.box = {};
            rm.box.box = conf.elm;
            rm.box.form = $(conf.elm).find('form.password')[0];
            rm.box.pw = $(conf.elm).find('input.password')[0];
            rm.box.nag = $(conf.elm).find('.nag')[0];
            rm.box.ender = $(conf.elm).find('.ender')[0];
            rm.box.pw.focus();

            $(document).on('bookster:resetPasswordSubmit', function(event){
                rm.submit();
            });
            $(document).on('bookster:resetPasswordCancel', function(event){
                bookstash.history.closeFocus();
            });

            $(document).on('bookster:resetPassordEyeClick', function(event){
                // do stuff
                event.preventDefault();
                var pw          = rm.box.pw;
                var hidden      = $(rm.box.form).find('button.showpass')[0]
                var visible     = $(rm.box.form).find('button.showpass')[1]

                if ('password' == $(pw).attr('type')){
                    $(hidden).show();
                    $(visible).hide();
                    $(pw).attr({type: 'text', placeholder: 'New Password (visible)'});
                }
                else{
                    $(hidden).hide();
                    $(visible).show();
                    $(pw).attr({type: 'password', placeholder: 'New Password'});
                }
                rm.box.pw.focus();
            });
            $(rm.box.pw).on('keydown', function(event){
                if ( event.keyCode === 13 ) {
                    event.preventDefault();
                    $(document).trigger('bookster:resetPasswordSubmit');
                    return false;
                }
            });
        },
        submit: function(emailField, passwordField){
            var rm  = this;
            var pw  = rm.box.pw;

            //$(rm.box.box).find('.password-reset').addClass('endgame');
            //return;

            if ((!pw.value) || (pw.value.length < 6)){
                $(pw).addClass('required').focus();
                $(rm.box.nag).addClass('visible');
            }
            else{
                var form = $(rm.box.box).find('.fos_user_resetting_reset')[0];
                $(form).find('#fos_user_resetting_form_plainPassword')[0].value = pw.value;

                $.ajax({
                    type: 'POST',
                    url: $(form).attr('action'),
                    data: $(form).serialize(),
                    success: function(data){
                        var scratch = rm.createElement('div');
                        $(scratch).html(data);

                        var success = $(scratch).find('.flash-success')[0];
                        if (undefined != success){
                            rm.endGame($(success).html());
                        }
                        else{
                            // mysterious error, reset form
                            $(form).html($(scratch).find('.fos_user_resetting_reset').html());
                        }
                    }
                });
            }
        },
        endGame: function(){
            var rm  = this;
            $(rm.box.nag).removeClass('visible');
            $('body').attr('auth', 'auth');
            $(document).trigger('bookster:loginsuccess');
            $(document).trigger('bookster:authchange');
            $(rm.box.box).find('.password-reset').addClass('endgame');
        }
    });
    // ------------------------------------------------------------------------
    var loginManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            listens:
                bookster:loginrequest           // opens login overlay
                bookster:logincloserequest      // closes login overlay
                bookster:facebooklogin          // initiates fb login
                bookster:gpluslogin             // initiates gplus login
                bookster:bookstashlogin         // opens bookstash login panel
            global:
                bookstash.loginManager
            throw:
                bookster:loginsuccess
                bookster:authchange
            interactions:
                '#loginwhitebox'
                '#ingress-forms'

        -------------------------------------------------------------------- */
        init: function(conf) {
            var lc = this;

            bookstash.loginManager = lc;

            lc.overlay      = conf.elm;
            lc.whitebox     = $('#loginwhitebox')[0];
            lc.form         = $(lc.overlay).find('form')[0];
            lc.focused      = lc.lastFocused = false;
            lc.ingress      = $('#ingress-forms')[0];
            lc.flash        = $(lc.form).find('.flash')[0];

            $(lc.form).find('.nag').each(function(){
                this.defaultContent = $(this).html();
            });

            $(lc.form).find('input, button').focus(function(){
                lc.lastFocused = lc.focused;
                lc.focused = this;
            });

            $(lc.whitebox).on('click', function(event){
                event.preventDefault();
            });

            $(document).on('bookster:logineyeclick', function(event){
                event.preventDefault();
                var pw          = $(lc.form).find('.field.password')[0];
                var hidden      = $(lc.form).find('button.showpass')[0]
                var visible     = $(lc.form).find('button.showpass')[1]

                if ('password' == $(pw).attr('type')){
                    $(hidden).show();
                    $(visible).hide();
                    $(pw).attr({type: 'text', placeholder: 'Password (visible)'});
                }
                else{
                    $(hidden).hide();
                    $(visible).show();
                    $(pw).attr({type: 'password', placeholder: 'Password'});
                }
                $(lc.lastFocused).focus();

            });
            $(document).on('bookster:loginrequest', function(event){
                $(lc.overlay).addClass('visible');
                $(lc.whitebox).addClass('visible');
                lc.loadIngressForms();
            });
            $(document).on('bookster:logoutrequest', function(event){
                $.get('/logout', function( data ) {
                    lc.logout();
                });
            });
            $(document).on('bookster:loginFormSubmit', function(event){
                lc.submit();
            });
            $(document).on('bookster:logincloserequest', function(event){
                lc.hideModal();
            });
            $(document).on('bookster:facebooklogin', function(event){
                /* -----------------------------------------------------------
                        fb      perm   local
                1                               new user: "unknown"             X
                2                *              returning user: "unknown"
                3        *                      new user: "not_authorized"
                4        *       *              returning login: "connected"
                5        *       *      *       logged in: ?
                ----------------------------------------------------------- */

                FB.getLoginStatus(function(response) {

                    var final_url = '/auth/socialreflector';
                    var auth_url = '/login/facebook?_destination=' + final_url;

                    if (('unknown' === response.status) || ('not_authorized' === response.status)){
                        var left  = ($(window).width()/2)-(430/2);
                        var top   = ($(window).height()/2)-(300/2);
                        var options = 'width=430, height=300, top='+top+', left='+left;
                        var popup = window.open(auth_url, 'Login_via_facebook', options);
                    }
                    else if ('connected' === response.status) {
                        var i = lc.createElement('iframe');
                        i.src = auth_url;
                        $(i).attr('id', 'userauthiframe');
                        $('body').append(i);
                    }
                    else{
                        console.log('FB auth fail :(');
                    }
                });
            });
            $(document).on('bookster:gpluslogin', function(event){
                var final_url = '/auth/socialreflector';
                var auth_url = '/login/google?_destination=' + final_url;

                var left  = ($(window).width()/2)-(430/2);
                var top   = ($(window).height()/2)-(600/2);
                var options = 'width=430, height=600, top='+top+', left='+left;

                var popup = window.open(auth_url, 'Login_via_google', options);
            });
            $(document).on('bookster:bookstashlogin', function(event){
                var locallog = $(lc.overlay).find('.locallog')[0];
                var loginmain = $(lc.overlay).find('.loginmain')[0];
                $(locallog).addClass('slow');
                $(loginmain).attr('suppresstooltips', 'suppress');
                if ('bookstash' == $(loginmain).attr('mode')){
                    // collapse
                    $(lc.overlay).find('.loginmain').attr('mode', '');
                    $(locallog).css({height: ''});
                }
                else{
                    // expand
                    $(loginmain).attr('mode', 'bookstash');
                    lc.formMode('login');
                }
                setTimeout(function(){
                    $(locallog).removeClass('slow');
                    $(loginmain).attr('suppresstooltips', '');
                    $(lc.form).find('input.email')[0].focus();
                }, 800);
            });
            $(document).on('bookster:createAccountRequest', function(event){
                lc.formMode('create');
            });
            $(document).on('bookster:loginAccountRequest', function(event){
                lc.formMode('login');
            });
            $(document).on('bookster:resetPasswordRequest', function(event){
                lc.formMode('reset');
            });

            $(lc.overlay).find('.sociallog').hover(
                function(){
                    $(this).parents('.loginmain').attr('hovered', $(this).attr('data-social'));
                }, function(){
                    $(this).parents('.loginmain').attr('hovered', '');
            });
            lc.setupCrHandler();
        },
        logout: function(){
            $('body').attr('auth', 'anon');
            $(document).trigger('bookster:logout');
            $(document).trigger('bookster:authchange');
        },
        loadIngressForms: function(){
            var lc = this;
            $.get('/auth/ingressForms', function( data ) {
                $(lc.ingress).html(data);
            });
        },
        setupCrHandler: function(){
            var lc = this;

            var emailField = $(lc.form).find('.field.email')[0];
            var firstField = $(lc.form).find('.field.firstname')[0];
            var lastField = $(lc.form).find('.field.lastname')[0];
            var passwordField = $(lc.form).find('.field.password')[0];

            $(emailField).on('keydown', function(event){
                if ( event.keyCode === 13 ) {
                    event.preventDefault();
                    if ('login' == $(lc.form).attr('mode')){
                        $(passwordField).focus();
                    }
                    else if ('create' == $(lc.form).attr('mode')){
                        $(firstField).focus();
                    }
                    else if ('reset' == $(lc.form).attr('mode')){
                        $(document).trigger('bookster:loginFormSubmit');
                    }
                }
            });
            $(firstField).on('keydown', function(event){
                if ( event.keyCode === 13 ) {
                    event.preventDefault();
                    $(lastField).focus();
                }
            });
            $(lastField).on('keydown', function(event){
                if ( event.keyCode === 13 ) {
                    event.preventDefault();
                    $(passwordField).focus();
                }
            });
            $(passwordField).on('keydown', function(event){
                if ( event.keyCode === 13 ) {
                    event.preventDefault();
                    if ('login' == $(lc.form).attr('mode')){
                        $(document).trigger('bookster:loginFormSubmit');
                    }
                    else if ('create' == $(lc.form).attr('mode')){
                        $(document).trigger('bookster:loginFormSubmit');
                    }
                }
            });
        },
        formMode: function(mode){
            /*
                modes:
                    login
                    create
                    reset
            */
            var lc = this;
            $(lc.form).attr('mode', mode);
            $(lc.form).find('input').val('').removeClass('required');
            $(lc.form).find('.nag').removeClass('visible');
            $(lc.form).find('input.email')[0].focus();

            lc.resetNags();
            lc.resetFlash();
            lc.localLogHeight();

            var firstField = $(lc.form).find('.field.firstname')[0];
            var lastField = $(lc.form).find('.field.lastname')[0];
            var passwordField = $(lc.form).find('.field.password')[0];

            // set tabindex of hidden stuff to -1
            if ('login' == mode){
                $(firstField).attr('tabindex', -1);
                $(lastField).attr('tabindex', -1);
                $(passwordField).attr('tabindex', 4);
            }
            else if ('reset' == mode){
                $(firstField).attr('tabindex', -1);
                $(lastField).attr('tabindex', -1);
                $(passwordField).attr('tabindex', -1);
            }
            else if ('create' == mode){
                $(firstField).attr('tabindex', 2);
                $(lastField).attr('tabindex', 3);
                $(passwordField).attr('tabindex', 4);
            }
            else if ('success' == mode){
            }
        },
        localLogHeight: function(clear){
            var lc = this;
            var mode = $(lc.form).attr('mode');
            var locallog = $(lc.overlay).find('.locallog')[0];

            if (true == clear){
                return $(locallog).height(0);
            }

            var height = 0;
            height += $(lc.flash).height();

            if ('login' == mode){
                height += 260;
            }
            else if ('reset' == mode){
                height += 230;
            }
            else if ('create' == mode){
                height += 340;
            }
            if ($(lc.form).find('.nag.email').hasClass('visible')){
                height += 15;
            }
            if (($(lc.form).find('.nag.first').hasClass('visible')) ||
                ($(lc.form).find('.nag.last').hasClass('visible'))){
                height += 15;
            }

            // FIXME must speed up animation
            $(locallog).height(height);
        },
        resetNags: function(){
            var lc = this;
            $(lc.form).find('.nag').each(function(){
                $(this).html(this.defaultContent);
            });
        },
        resetFlash: function(){
            this.setFlash(false);
        },
        setFlash: function(message){
            var lc = this;

            if (false == message){
                $(lc.flash).removeClass('visible')
                    .html('');
            }
            else{
                $(lc.flash).addClass('visible')
                    .html(message);
            }
            lc.localLogHeight();
        },
        submit: function(){
            var lc = this;

            var mode = $(lc.form).attr('mode');
            var emailField = $(lc.form).find('.field.email')[0];
            var firstField = $(lc.form).find('.field.firstname')[0];
            var lastField = $(lc.form).find('.field.lastname')[0];
            var passwordField = $(lc.form).find('.field.password')[0];

            emailField.value = emailField.value.trim();
            firstField.value = firstField.value.trim();
            lastField.value = lastField.value.trim();

            $(lc.form).find('input').removeClass('required');
            $(lc.form).find('.nag').removeClass('visible');

            lc.resetNags();
            lc.resetFlash();
            lc.localLogHeight();

            if ('login' == mode){
                var fail = false;
                if (!lc.validEmail(emailField.value)){
                    $(emailField).addClass('required').focus();
                    $(lc.form).find('.nag.email').addClass('visible');
                    fail = true;
                }
                if (!passwordField.value){
                    $(passwordField).addClass('required').focus();
                    $(lc.form).find('.nag.password').addClass('visible');
                    fail = true;
                }
                if (false == fail){
                    return lc.submitLogin(emailField, passwordField);
                }
            }
            else if ('reset' == mode){
                if (!lc.validEmail(emailField.value)){
                    $(emailField).addClass('required').focus();
                    $(lc.form).find('.nag.email').addClass('visible');
                }
                else{
                    return lc.submitReset(emailField);
                }
            }
            else if ('create' == mode){
                var fail = false;
                if (!lc.validEmail(emailField.value)){
                    $(emailField).addClass('required').focus();
                    $(lc.form).find('.nag.email').addClass('visible');
                    fail = true;
                }
                if ((!firstField.value) ||
                        (firstField.value.length < 2) ||
                        (firstField.value.length > 255)){
                    $(firstField).addClass('required').focus();
                    $(lc.form).find('.nag.first').addClass('visible');
                    fail = true;
                }
                if ((!lastField.value) ||
                        (lastField.value.length < 2) ||
                        (lastField.value.length > 255)){
                    $(lastField).addClass('required').focus();
                    $(lc.form).find('.nag.last').addClass('visible');
                    fail = true;
                }
                if ((!passwordField.value) ||
                        (passwordField.value.length < 6)){
                    $(passwordField).addClass('required').focus();
                    $(lc.form).find('.nag.password').addClass('visible');
                    fail = true;
                }
                if (false == fail){
                    return lc.submitRegistration(emailField, firstField, lastField, passwordField);
                }
            }
            lc.localLogHeight();
        },
        submitLogin: function(emailField, passwordField){
            var lc = this;
            var log = $(lc.ingress).find('#loginbox')[0];
            $(log).find('#username').val(emailField.value);
            $(log).find('#password').val(passwordField.value);
            $(log).find('form').serialize();

            $.ajax({
                type: 'POST',
                url: $(log).find('form').attr('action'),
                data: $(log).find('form').serialize(),
                success: function(data){
                    console.log(data);
                    var scratch = lc.createElement('div');
                    $(scratch).html(data);
                    var error = $(scratch).find('.error')[0];

                    if (undefined != error){
                        lc.setFlash($(error).html());
                    }
                    else{
                        lc.loginComplete();
                    }
                }
            });
        },
        submitRegistration: function(emailField, firstField, lastField, passwordField){
            var lc = this;
            var reg = $(lc.ingress).find('#registerbox')[0];
            $(reg).find('#fos_user_registration_form_email').val(emailField.value);
            $(reg).find('#fos_user_registration_form_firstName').val(firstField.value);
            $(reg).find('#fos_user_registration_form_lastName').val(lastField.value);
            $(reg).find('#fos_user_registration_form_plainPassword').val(passwordField.value);
            $(reg).find('form').serialize();

            $.ajax({
                type: 'POST',
                url: $(reg).find('form').attr('action'),
                data: $(reg).find('form').serialize(),
                success: function(data){
                    var scratch = lc.createElement('div');
                    $(scratch).html(data);

                    var success = $(scratch).find('.flash-success')[0];
                    if (undefined != success){
                        lc.endGame($(success).html());
                    }
                    else{
                        var ul = $(scratch).find('ul')[0];
                        if (undefined != ul){
                            lc.setFlash('<ul>' + $(ul).html() + '</ul>');
                        }
                    }
                }
            });
        },
        submitReset: function(emailField){
            var lc = this;
            var reset = $(lc.ingress).find('#resetbox')[0];
            $(reset).find('#fos_user_resetting_form_username').val(emailField.value);

            $.ajax({
                type: 'POST',
                url: $(reset).find('form').attr('action'),
                data: $(reset).find('form').serialize(),
                success: function(data){
                    var scratch = lc.createElement('div');
                    $(scratch).html(data);

                    var success = $(scratch).find('.message')[0];
                    if (undefined != success){
                        lc.endGame($(success).html());
                    }
                    else{
                        var error = $(scratch).find('.flash-error')[0];
                        if (undefined != error){
                            lc.setFlash($(error).html());
                        }
                    }
                }
            });
        },
        endGame: function(html){
            var lc = this;
            $(lc.overlay).find('.ender .message').html(html);
            $(document).trigger('bookster:domchanged');
            $(lc.overlay).find('.window').attr('final', 'final');
        },
        validEmail: function(string){
            var f = new RegExp (/^([\w-\.\+]+)@((?:[\w]+\.)+)([a-zA-Z]{2,4})/);
            return f.test(string);
        },
        loginComplete: function(){
            var lc = this;
            lc.hideModal();
            $('#userauthiframe').remove();
            $('body').attr('auth', 'auth');
            $(document).trigger('bookster:loginsuccess');
            $(document).trigger('bookster:authchange');
        },
        hideModal: function(){
            var lc = this;
            if ('end' != $(lc.overlay).find('.loginmain').attr('mode')){
                $(lc.overlay).find('.loginmain').attr('mode', '');
            }
            $(lc.overlay).removeClass('visible');
            $(lc.whitebox).removeClass('visible');
            $(lc.overlay).find('.locallog').css({height: ''});
            $(document).trigger( "bookster:topmenudemure" );
        }
    });
    // ------------------------------------------------------------------------
    var welcomeManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
        -------------------------------------------------------------------- */
        init: function(conf) {
            var wm = this;

            wm.box      = conf.elm;
            console.log('hey');

            setTimeout(function(){
                $(wm.box).find('h2').each(function(i, elem){
                    console.log(elem);
                    setTimeout(function(){
                        $(elem).addClass('visible');
                    }, 800*i);

                });
            }, 1000);
            setTimeout(function(){
                $(wm.box).find('a.prompt').addClass('visible');
                $(wm.box).find('a.escape').addClass('visible');
            }, 8000);
        }
    });
    // ------------------------------------------------------------------------
    var workSwitcher = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
                data-bstsh-lgcover
                data-bstsh-lgcoverx
                data-bstsh-lgcovery
                data-bstsh-asin
        -------------------------------------------------------------------- */
        init: function(conf) {
            var ws = this;

            ws.box = {};
            ws.box.box = conf.elm;
            ws.box.cover = $(ws.box.box).parent().find('img.cover')[0];

            $(ws.box.box).find('.edition').click(function(event){
                ws.switch(this);
            });
        },
        switch: function(elm){
            var ws = this;

            var i           = $(elm).find('img.format')[0];
            var newsrc      = $(i).attr('data-bstsh-lgcover');
            var newsrcx     = $(i).attr('data-bstsh-lgcoverx');
            var newsrcy     = $(i).attr('data-bstsh-lgcovery');
            var newasin     = $(i).attr('data-bstsh-asin');

            ws.box.cover.src = newsrc;
            $(ws.box.cover).css({
                width: newsrcx +'px',
                height: newsrcy +'px',
            });
            ws.box.cover.src = newsrc;

            $('[data-bstsh-workcoverXadjust]').each(function(){
                var obj = this;
                var x = newsrcx;
                var exp = $(obj).attr('data-bstsh-workcoverxadjust');
                var width = eval(exp);
                $(obj).css({width: width + 'px'});
            })

            $('[data-bstsh-asin]').removeClass('selected');
            $('[data-bstsh-asin=' + newasin + ']').addClass('selected');

            var newDesc = $('[data-bstsh-asin=' + newasin + ']')
                .find('.edition-description').html();

            $('.description article').html(newDesc);

            $('.middle').addClass('truncate');
        }
    });
    // ------------------------------------------------------------------------
    var partialReloadButton = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
                data-bstsh-target
                data-bstsh-source
            throws:
                bookster:domchanged
        -------------------------------------------------------------------- */
        init: function(conf) {
            var prb = this;
            prb.elm = conf.elm;

            $(prb.elm).on('click',
                function(event){
                    var button = prb.elm;
                    $(button).find('i').addClass('fa-spin');
                    $(button).attr('disabled', 'disabled');
                    var target = $(button).attr('data-bstsh-target');
                    $(button).attr('mode', 'reloading');

                    setTimeout(function(){
                        $.ajax({
                            type: 'GET',
                            url: $(target).attr('data-bstsh-source'),
                            success: function(data){
                                var scratch = prb.createElement('div', '');
                                scratch.innerHTML = data;

                                $(scratch).find('*[data-bstsh-function=extra-update]').each(
                                    function(){
                                        var replace = this;
                                        var subtarget = $(replace).attr('data-bstsh-target');
                                        var html = $(replace).html();
                                        $(replace).remove();
                                        $(subtarget).html(html);
                                    }
                                );

                                data = scratch.innerHTML;

                                $(target).html(data);
                                $(button).find('i').removeClass('fa-spin');
                                $(button).removeAttr('disabled');
                                $(document).trigger( "bookster:domchanged" );

                                $(button).attr('mode', 'reloaded');
                                setTimeout(function(){
                                    $(button).removeAttr('mode');
                                }, 100);
                            }
                        });
                    }, 200);
                }
            );
        }
    });
    // ------------------------------------------------------------------------
    var eventDomUpdater = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
                data-event
                data-source
                data-replace-target
                data-replace-source
            throws:
                bookster:domchanged

        -------------------------------------------------------------------- */
        init: function(conf) {
            var edu = this;

            edu.box = conf.elm;
            edu.targets = [];
            edu.sources = [];

            var targets = $(edu.box).attr('data-replace-target');
            if (undefined != targets){
                $.each(targets.split(' '), function(index, value){
                    edu.targets.push(value);
                });
            }
            var sources = $(edu.box).attr('data-replace-source');
            if (undefined != sources){
                $.each(sources.split(' '), function(index, value){
                    edu.sources.push(value);
                });
            }

            $(document).on($(edu.box).attr('data-event'), function(){
                var url = $(edu.box).attr('data-source');

                $.get(url, function( data ) {
                    $(edu.box).html(data);

                    var html;
                    $.each(edu.targets, function(index, selector){
                        html = $(edu.box).find(edu.sources[index]).html();
                        $(selector).html(html);
                    });

                    $(document).trigger( "bookster:domchanged" );
                });
            });
        },
    });
    // ------------------------------------------------------------------------
    var modalGeneral = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
                data-modal-template
                data-modal-type (alert|confirm|form)
                data-modal-cancel-text
                data-modal-confirm-text
                data-headline
                data-subhead
            interactions:
                - 'body > #modal'[0]

        -- alert, confirmation, or form
        -- either click through or
        -- handled by elm.modalHandler.handleModealConfir(elm)

        -------------------------------------------------------------------- */
        init: function(conf) {
            var mg = this;

            mg.box              = $('body > #modalGeneral')[0];
            mg.elm              = conf.elm;

            var base = {
                cancelText:     'Cancel',
                confirmText:    'Ok',
                headline:       '',
                subhead:        '',
                confirmCB:      false,
                messageClass:   'message'
            }

            if ($(mg.elm).hasAttr('data-modal-type')){
                base.type             = $(mg.elm).attr('data-modal-type');
            }
            if ($(mg.elm).hasAttr('data-modal-cancel-text')){
                base.cancelText = $(mg.elm).attr('data-modal-cancel-text');
            }
            if ($(mg.elm).hasAttr('data-modal-confirm-text')){
                base.confirmText = $(mg.elm).attr('data-modal-confirm-text');
            }
            if ($(mg.elm).hasAttr('data-headline')){
                base.headline = $(mg.elm).attr('data-headline');
            }
            if ($(mg.elm).hasAttr('data-subhead')){
                base.subhead = $(mg.elm).attr('data-subhead');
            }
            if ($(mg.elm).hasAttr('data-modal-template')){
                base.template         = $(mg.elm).attr('data-modal-template');
            }

            $.extend(mg, base, conf);

            $(mg.elm).on('click', function(event){
                event.preventDefault();
                mg.activate();
            });
        },
        getBox: function(){
            return this.box;
        },
        triggerConfirmHandler: function(){
            var mg = this;
            if (mg.confirmCB){
                mg.confirmCB();
            }
            else{
                $(mg.elm).find('>:first-child').triggerHandler('click');
            }
        },
        activate: function(){
            var mg = this;

            $(mg.box).find('[data-message=message]').attr('class', mg.messageClass);

            $(mg.box).find('button').off('click');
            $(mg.box).find('.form-body').html('');

            $(mg.box).find('button.cancel').click(function(){
                mg.dismiss();
            });
            $(mg.box).find('button.confirm').click(function(event){
                event.preventDefault();
                if ('alert' == mg.type){
                    mg.dismiss();
                }
                else if ('confirm' == mg.type){
                    mg.dismiss();
                    mg.triggerConfirmHandler();
                }
                else if ('form' == mg.type){
                    mg.completedCB(mg);
                }
            });

            $(mg.box).find('span.big').html(mg.headline);
            $(mg.box).find('p').html(mg.subhead);

            $(mg.box).find('button.cancel').html(mg.cancelText);
            $(mg.box).find('button.confirm').html(mg.confirmText);

            if ('form' == mg.type){
                var tempHTML = $(mg.template).html();
                if (undefined != mg.replace){
                    $.each(mg.replace, function(key, value) {
                        tempHTML = tempHTML.replaceAll('{{ ' + key + ' }}', value);
                    });
                }
                $(mg.box).find('.form-body').html(tempHTML);
                // IE, still the worst brower
                // https://connect.microsoft.com/IE/feedback/details/811408/ie10-11-a-textareas-placeholder-text-becomes-its-actual-value-when-using-innertext-innerhtml-or-outerhtml
                $(mg.box).find('textarea').val('');

                if (undefined != mg.setupCB){
                    mg.setupCB(mg);
                }
            }

            $(mg.box).attr('mode', mg.type);

            mg.reveal();
        },
        reveal: function(){
            var mg = this;
            $(mg.box).attr('active', 'active');
        },
        dismiss: function(){
            var mg = this;
            $(mg.box).find('.visible').removeClass('visible');
            $(mg.box).removeAttr('active');
        },
        showProgress: function(){
            var mg = this;
            $(mg.box).find('.progress').addClass('visible');
        }
    });
    // ------------------------------------------------------------------------
    var timeagator = Base.extend({
        /* --------------------------------------------------------------------
            listens:
                bookster:domchanged
        -------------------------------------------------------------------- */
        init: function(conf) {
            var tm = this;
            $(document).on('bookster:domchanged', function(){
                tm.run();
            });
            this.run();
        },
        run: function(){
            $('time:not([timeago=timeago])').each(function(){
                $(this).timeago();
                $(this).attr('timeago', 'timeago');
            });
        }
    });
    var shareButton = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
                data-share-type
                data-url
                data-description
                data-image
        -------------------------------------------------------------------- */
        init: function(conf) {
            var sb = this;
            sb.elm = conf.elm;

                var url = $(sb.elm).attr('data-url');
                if (undefined == url){
                    url = document.location.href;
                }
                var description = $(sb.elm).attr('data-description');
                if (undefined == description){
                    description = '';
                }
                var image = $(sb.elm).attr('data-image');
                if (undefined == image){
                    image = '';
                }

            $(sb.elm).click(function(event){
                if ('facebook' == $(sb.elm).attr('data-share-type')){
                    FB.ui({
                        method: 'share',
                        app_id: document.cred.facebook_app_id,
                        href: url
                    }, function(response){});
                }
                else if ('pinterest' == $(sb.elm).attr('data-share-type')){
                    var purl = 'https://www.pinterest.com/pin/create/button/?' +
                        'media=' + encodeURI(image) +
                        '&description=' + encodeURI(description) +
                        '&url=' + encodeURI(url);
                    window.open(purl,'','menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');
                }
                else if ('google' == $(sb.elm).attr('data-share-type')){
                    var gurl = 'https://plus.google.com/share?url=' + encodeURI(url);
                    window.open(gurl,'','menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');
                }
                else if ('twitter' == $(sb.elm).attr('data-share-type')){
                    var text = description;
                    var turl = 'https://twitter.com/intent/tweet?' +
                        '&url=' + encodeURI(url) +
                        '&via=' + 'bookstash' +
                        '&text=' + text;

                    window.open(turl,'','menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');
                }
            });
        }
    });
    // ------------------------------------------------------------------------
    var modalConfirm = Base.extend({
        /* --------------------------------------------------------------------
        // FIXME old cruft delete?
        // instantiated by objectifier
            attributes:
                data-headline
                data-subhead
            interactions:
                - 'body > #modal'[0]

        -------------------------------------------------------------------- */
        init: function(conf) {
            var mc = this;

            mc.elm = conf.elm;
            mc.box = $('body > #modal')[0];
            $(mc.elm).addClass('clickable');

            $(mc.elm).on('click', function(event){
                event.preventDefault();
                mc.show();
            });
        },
        show: function(){
            var mc = this;

            $(mc.box).find('.cancel').off('click');
            $(mc.box).find('.confirm').off('click');

            $(mc.box).find('h1').html($(mc.elm).attr('data-headline'));
            $(mc.box).find('p').html($(mc.elm).attr('data-subhead'));

            $(mc.box).find('.cancel').on('click', function(event){
                event.preventDefault();
                mc.hide();
            });
            $(mc.box).find('.confirm').on('click', function(event){
                event.preventDefault();
                mc.hide();
                $(mc.elm).find('>:first-child').triggerHandler('click');
            });

            $(mc.box).addClass('visible');
        },
        hide: function(){
            var mc = this;
            $(mc.box).removeClass('visible');
        }
    });
    // ------------------------------------------------------------------------
    var eventThrower = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
                data-event
        -------------------------------------------------------------------- */
        init: function(conf) {
            var et = this;

            et.elm = conf.elm;
            $(et.elm).addClass('clickable');

            $(et.elm).on('click', function(event){
                event.preventDefault();
                var evnt = $(et.elm).attr('data-event');

                if (undefined != evnt){
                    if (-1 == evnt.indexOf(',')){
                        $(document).trigger(evnt);
                    }
                    else{
                        var evnts = evnt.split(',');
                        var delay = 0;
                        $.each(evnts, function(index, ev){
                            setTimeout(function(){
                                $(document).trigger(ev);
                            }, delay);
                            delay += 100;
                        });
                    }
                }
            });
        }
    });
    // ------------------------------------------------------------------------
    var textCounter = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
                maxlength
        -------------------------------------------------------------------- */
        init: function(conf) {
            var tc = this;
            tc.textarea = conf.elm;
            tc.countdown = $(tc.textarea).next('div.countdown')[0];
            tc.max = $(tc.textarea).attr('maxlength');

            if ((undefined == tc.countdown) || (tc.max == undefined)){
                return;
            }

            $(tc.textarea).on('change keyup', function(event){
                var chars = $(tc.textarea).val();
                tc.left(tc.countdown, tc.max - chars.length);
            });

            tc.left(tc.countdown, tc.max);
        },
        left: function(s, chars){
            var tc = this;
            if ((tc.max / 10) > chars){
                $(s).addClass('visible');
                s.innerHTML = chars + ' characters remaining';
            }
            else{
                $(s).removeClass('visible');
            }
        }
    });
    // ------------------------------------------------------------------------
    var anonGate = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
        -------------------------------------------------------------------- */
        init: function(conf){
            $(conf.elm).click(function(event){
                if ('anon' == $('body').attr('auth')){
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    bookstash.user.registerPostLoginCallback(function(){
                        $(event.target).click();
                    });
                    $(document).trigger('bookster:loginrequest');
                }
            });
        }
    });
    // ------------------------------------------------------------------------
    var userDataManager = Base.extend({
        /* --------------------------------------------------------------------
            listens:
                bookster:authchange
            throws:
                bookster:userprefchange
                bookster:loginrequest
        -------------------------------------------------------------------- */
        init: function(conf) {
            var ud = this;

            ud.lists        = {};
            ud.cb           = {};
            ud.cb.lists     = {};
            ud.prefs        = {};
            ud.postLoginCallback = false;

            $(document).on('bookster:authchange', function(event){
                ud._setup();
                if ('auth' == $('body').attr('auth')){
                    if (ud.postLoginCallback){
                        setTimeout(function(){
                            ud.postLoginCallback();
                            ud.postLoginCallback = false;
                        }, 200);
                    };
                }
            });
            $(document).on('bookster:pagechange', function(event){
                ud.postLoginCallback = false;
            });
            ud._setup();
        },
        registerPostLoginCallback: function(callback){
            var ud = this;

            ud.postLoginCallback = callback;
        },
        registerListCallback: function(list, callback){
            var ud = this;

            if (undefined == ud.cb.lists[list]){
                ud.cb.lists[list] = [];
            }
            ud.cb.lists[list].push(callback);
            if (undefined != ud.lists[list]){
                callback(ud.lists[list]);
            }
        },
        delegatedUpdate: function(url, data, callback){
            // will do an update on behalf of a client
            var ud = this;
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                success: function(data){

                    if (data.error){
                        if (1 == data.error){
                            callback(data);

                            $.each(ud.lists, function(name, members){
                                var a = {}
                                a[name] = {}
                                ud._integrate(a);
                            });

                            bookstash.loginManager.logout();
                            $(document).trigger('bookster:loginrequest');
                        }
                        return;
                    }

                    if (undefined != callback){
                        callback(data);
                    }
                    ud._integrate(data.lists);
                    if (undefined != data.prefs){
                        ud.prefs = data.prefs;
                        $(document).trigger('bookster:userprefchange');
                    };
                }
            });
        },
        isAuthenticated: function(){
            if ('anon' == $('body').attr('auth')){
                return false;
            }
            return true;
        },
        getUserId: function(){
            var ud = this;

            return $('#bstsh-user-data-id').attr('data-user-id');
        },
        getUserPrefs: function(){
            var ud  = this;
            return ud.prefs
        },
        _setup: function(){
            var ud = this;

            if ('anon' == $('body').attr('auth')){
                ud._callListsCallbacksNull();
                ud.lists        = {};
            }
            else{
                setTimeout(function(){
                    var url = '';
                    $.each(ud.cb.lists, function(list, stuff){
                        if (url){
                            url += ',';
                        }
                        url += list;
                    });
                    url = '/user/get/' + url;
                    $.ajax({
                        type: 'GET',
                        url: url,
                        success: function(data){
                            ud._integrate(data.lists);
                            ud.prefs = data.prefs;
                            $(document).trigger('bookster:userprefchange');
                        }
                    });
                }, 200);
            }
        },
        _integrate: function(lists){
            var ud = this;
            $.each(lists, function(list, value){
                ud.lists[list] = value;
                ud._callListCallbacks(list);
            });
        },
        _callListsCallbacksNull: function(){
            var ud = this;

            $.each(ud.cb.lists, function(list, val){
                if (!(undefined == ud.cb.lists[list])){
                    $.each(ud.cb.lists[list], function(index, callback){
                        callback([]);
                    });
                }
            });
        },
        _callListCallbacks: function(list){
            var ud = this;
            // if not authenticated, return
            if ('anon' == $('body').attr('auth')){
                return;
            }
            if (!(undefined == ud.cb.lists[list])){
                $.each(ud.cb.lists[list], function(index, callback){
                    callback(ud.lists[list]);
                });
            }
        }
    });
    // ------------------------------------------------------------------------
    var readItWidget = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
                data-bstsh-workid
        -------------------------------------------------------------------- */
        init: function(conf){
            var rw = this;

            rw.elm          = conf.elm;
            rw.form         = $(rw.elm).find('form')[0];
            rw.work_id      = $(rw.elm).attr('data-bstsh-workid');
            rw.pause        = false;
            rw.statuses     = {
                1: 'toread',
                2: 'reading',
                3: 'readit'
            };

            $(rw.form).change(function(event){
                rw._handleChange(event);
            });

            bookstash.user.registerListCallback('readit', function(members){
                rw._cbUbdate(members);
            });

            $(rw.elm).find('button.remove').click(function(event){
                event.preventDefault();
                $(rw.form).find('input').attr('checked', false);
                rw._handleChange();
            });
        },
        _getClicked: function(){
            var rw = this;

            var clicked = $(rw.form).find('input:checked')[0];
            if (undefined == 0){
                return 0;
            }
            return $(rw.form).find('input:checked').val();
        },
        _cbUbdate: function(members){
            var rw = this;
            var found = false;
            $.each(members, function(index, status){
                if (rw.work_id == index){

                    rw.pause = true;
                    $(rw.form).find('input:radio[value=' + status + ']').prop('checked', true);
                    rw.pause = false;

                    $(rw.elm).addClass('noanim');
                    rw._setMode(rw.statuses[status]);
                    setTimeout(function(){ $(rw.elm).removeClass('noanim'); }, 100);
                    found = true;
                }
            });
            if (false == found){
                rw.pause = true;
                $(rw.form).find('input').attr('checked', false);
                rw._setMode('');
                rw.pause = false;
            }
        },
        _handleChange: function(event){
            var rw = this;
            if (rw.pause){
                return;
            }
            var status = rw._getClicked();
            rw._setMode('saving');

            bookstash.user.delegatedUpdate( '/user/readit/' + rw.work_id, {
                    status: status
                }, function(data){
                    rw._setMode('saved');
                    rw._updateStatus();
            });
        },
        _updateStatus: function(){
            var rw = this;
            var status = rw._getClicked();
            setTimeout(function(){ rw._setMode(rw.statuses[status]); }, 100);
        },
        _setMode: function(mode){
            var rw = this;
            if (undefined == mode){
                $(rw.elm).attr('mode', false);
            }
            else{
                $(rw.elm).attr('mode', mode);
            }
        },
        _clear: function(){
        }
    });
    var passInputManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
        -------------------------------------------------------------------- */
        init: function(conf) {
            var pim = this;

            pim.elm = conf.elm;

            pim.button = {};
            pim.button.hide = $(pim.elm).nextAll('button.showpass.hide')[0];
            pim.button.show = $(pim.elm).nextAll('button.showpass.show')[0];

            pim.mode = 'hidden';

            $(pim.button.hide).click(function(event){
                pim._toggle(event);
            });
            $(pim.button.show).click(function(event){
                pim._toggle(event);
            });
        },
        _toggle: function(event){
            var pim = this;
            event.preventDefault();

            if ('visible' == pim.mode){
                pim.mode = 'hidden';

                $(pim.elm).attr('type', 'password');

                $(pim.show).css('display', 'none');
                $(pim.hide).css('display', '');

                $(pim.button.show).css('display', 'none');
                $(pim.button.hide).css('display', '');
            }
            else{
                pim.mode = 'visible';

                $(pim.elm).attr('type', 'input');

                $(pim.hide).css('display', 'none');
                $(pim.show).css('display', '');

                $(pim.button.hide).css('display', 'none');
                $(pim.button.show).css('display', '');
            }
            $(pim.elm).focus();
        }
    });
    // ------------------------------------------------------------------------
    var userDropWidget = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:

        -------------------------------------------------------------------- */
        init: function(conf){
            var ud = this;

            var elm         = ud.elm = conf.elm;

            ud.state = {};
            ud.state.read           = 0;
            ud.state.reading        = 0;
            ud.state.toread         = 0;
            ud.state.ratings        = 0;
            ud.state.reviews        = 0;
            ud.state.comments       = 0;

            ud.nag_one = $(ud.elm).find('.nag')[0];
            ud.nag_two = $(ud.elm).find('.nag')[1];

            ud.progress = $(ud.elm).find('.progress')[0];

            ud.oldpw = $(ud.elm).find('input.old-password')[0];
            ud.newpw = $(ud.elm).find('input.new-password')[0];

            bookstash.user.registerListCallback('ratings', function(members){
                ud._cbUbdateRatings(members);
            });
            bookstash.user.registerListCallback('reviews', function(members){
                ud._cbUbdateReviews(members);
            });
            bookstash.user.registerListCallback('readit', function(members){
                ud._cbUbdateReadit(members);
            });
            bookstash.user.registerListCallback('comments', function(members){
                ud._cbUbdateComments(members);
            });

            $(ud.elm).find('.change-password').click(function(event){
                ud._passwordMode(event);
            });
            $(ud.elm).find('.cancel').click(function(event){
                ud._reset(event);
            });
            $(ud.elm).find('.save-password').click(function(event){
                ud._submitPasswordChange(event);
            });
            $(ud.elm).find('button.ok').click(function(event){
                ud._reset(event);
            });

            $(ud.oldpw).on('keypress', function(event){
                if(event.keyCode == '13'){
                    event.preventDefault();
                    $(ud.newpw).focus();
                }
            });
            $(ud.newpw).on('keypress', function(event){
                if(event.keyCode == '13'){
                    event.preventDefault();
                    ud._submitPasswordChange(event);
                }
            });

            ud._draw();
        },
        _showProgress: function(){
            var ud = this;
            $(ud.progress).addClass('visible');
        },
        _hideProgress: function(){
            var ud = this;
            $(ud.progress).removeClass('visible');
        },
        _submitPasswordChange: function(){
            var ud = this;
            event.preventDefault();

            var oldpw = $(ud.elm).find('input.old-password').val();
            var newpw = $(ud.elm).find('input.new-password').val();

            var fail = false;
            if (0 == oldpw.length){
                ud._setNagOne('Please enter your current password');
                fail = true;
            }
            if (6 > newpw.length){
                ud._setNagTwo('Please enter at least 6 characters');
                fail = true;
            }

            if (!fail){
                ud._clearNags();
                ud._showProgress();
                $.ajax({
                    type: 'GET',
                    url: '/profile/change-password',
                    success: function(data){
                        var scratch = document.createElement('div');
                        $(scratch).html(data);
                        var form = $(scratch).find('form');

                        $(form).find('input#fos_user_change_password_form_current_password').val(oldpw);
                        $(form).find('input#fos_user_change_password_form_plainPassword_first').val(newpw);
                        $(form).find('input#fos_user_change_password_form_plainPassword_second').val(newpw);

                        $.ajax({
                            type: 'POST',
                            url: $(form).attr('action'),
                            data: $(form).serialize(),
                            success: function(data){
                                ud._hideProgress();
                                var scratch = document.createElement('div');
                                $(scratch).html(data);
                                if (1 == $(scratch).find('.flash-success').length){
                                    ud._endgame();
                                }
                                else{
                                    ud._setNagOne('Your password was not accepted.');
                                }
                            }
                        });
                    }
                });
            }
        },
        _endgame: function(){
            var ud = this;
            $(ud.elm).attr('data-password-reset', 'complete');
        },
        _setNagOne: function(text){
            var ud = this;

            $(ud.nag_one).addClass('visible')
                .find('span').html(text);
        },
        _setNagTwo: function(text){
            var ud = this;

            $(ud.nag_two).addClass('visible')
                .find('span').html(text);
        },
        _clearNags: function(){
            var ud = this;
            $(ud.elm).find('.nag').removeClass('visible');
        },
        _reset: function(event){
            var ud = this;
            event.preventDefault();
            ud._clearNags();

            ud.oldpw.value = '';
            ud.newpw.value = '';

            $(ud.elm).attr('data-password-reset', '');
        },
        _passwordMode: function(event){
            var ud = this;
            event.preventDefault();

            $(ud.elm).attr('data-password-reset', 'reset');
            $(ud.elm).find('input.old-password').focus();
        },
        _cbUbdateRatings: function(members){
            var ud = this;
            ud.state.ratings = 0;
            $.each(members, function(index, score){
                ud.state.ratings++;
            });
            ud._draw();
        },
        _cbUbdateReadit: function(members){
            var ud = this;
            ud.state.read           = 0;
            ud.state.reading        = 0;
            ud.state.toread         = 0;
            $.each(members, function(index, status){
                if ( 3 == status){
                    ud.state.read++;
                }
                if ( 2 == status){
                    ud.state.reading++;
                }
                if ( 1 == status){
                    ud.state.toread++;
                }
            });
            ud._draw();
        },
        _cbUbdateReviews: function(members){
            var ud = this;
            ud.state.reviews = 0;
            $.each(members, function(index, score){
                ud.state.reviews++;
            });
            ud._draw();
        },
        _cbUbdateComments: function(data){
            var ud = this;
            ud.state.comments = data.count;
            ud._draw();
        },
        _draw: function(){
            var ud = this;
            $(ud.elm).find('[data-name=read]').html(ud.state.read);
            $(ud.elm).find('[data-name=reading]').html(ud.state.reading);
            $(ud.elm).find('[data-name=toread]').html(ud.state.toread);
            $(ud.elm).find('[data-name=ratings]').html(ud.state.ratings);
            $(ud.elm).find('[data-name=reviews]').html(ud.state.reviews);
            $(ud.elm).find('[data-name=comments]').html(ud.state.comments);
        }
    });
    // ------------------------------------------------------------------------
    var starsWidget = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
                data-fixed-value

        -------------------------------------------------------------------- */
        init: function(conf){
            var sw = this;

            var elm         = sw.elm = conf.elm;
            sw.work_id      = $(sw.elm).attr('data-bstsh-workid');
            sw.shell        = $(sw.elm).parents('.my-stars-shell')[0];
            sw.score        = false;
            sw.stop         = false;

            if (undefined != $(elm).attr('data-fixed-value')){
                $(elm).raty({
                    score: $(elm).attr('data-fixed-value'),
                    readOnly: true,
                    starHalf: 'istar fa fa-star-half-o',
                    starOff: 'istar fa fa-star-o',
                    starOn: 'istar fa fa-star',
                    starType : 'i'
                });
                return;
            }
            else{
                $(elm).raty({
                    readOnly: false,
                    starHalf: 'istar fa fa-star-half-o',
                    starOff: 'istar fa fa-star-o',
                    starOn: 'istar fa fa-star',
                    starType : 'i' ,
                    hints: ['One Star', 'Two Stars', 'Three Stars', 'Four Stars', 'Five Stars'],
                    click: function(score, event){
                        if (sw.stop){
                            sw.score = score;
                            return;
                        }
                        if (bookstash.user.isAuthenticated()){
                            sw.score = score;
                            sw._save();
                        }
                        else{
                            bookstash.user.registerPostLoginCallback(function(){
                                setTimeout(function(){
                                    sw.score = score;
                                    sw._save();
                                    $(sw.elm).raty('click', score);
                                },300);
                            });
                            $(document).trigger('bookster:loginrequest');
                            return false;
                        }
                    }
                });

                sw._setMode('blank');

                $(sw.shell).find('button.clear').click(function(event){
                    if (bookstash.user.isAuthenticated()){
                        sw.score = 0;
                        $(elm).raty('reload');
                        sw._save();
                    }
                    else{
                        $(document).trigger('bookster:loginrequest');
                        return false;
                    }
                });

                bookstash.user.registerListCallback('ratings', function(members){
                    sw._cbUbdate(members);
                });
            }
        },
        _cbUbdate: function(members){
            var sw = this;
            var newScore = false;
            $.each(members, function(index, score){
                if (sw.work_id == index){
                    newScore = score;
                    if (score != sw.score){
                        sw.stop = true;
                        $(sw.elm).raty('click', score);
                        sw._setMode('picked-cb');
                        sw.stop = false;
                    }
                }
            });
            if (false == newScore){
                sw._setMode('blank');
                sw.score = 0;
                $(sw.elm).raty('reload');
            }
        },
        _save: function(){
            var sw = this;
            sw._setMode('saving');

            bookstash.user.delegatedUpdate( '/user/rating/' + sw.work_id, {
                    stars: sw.score
                }, function(data){
                    sw._setMode('saved');
                    $('[data-bstsh-ratings-work=' + sw.work_id + ']').html(data.html);
                    $(document).trigger( "bookster:domchanged" );
                    if (0 == sw.score){
                        setTimeout(function(){ sw._setMode('blank'); }, 100);
                    }
                    else{
                        setTimeout(function(){ sw._setMode('picked'); }, 100);
                    }
            });
        },
        _setMode: function(mode){
            var sw = this;
            $(sw.shell).attr('mode', mode);
        }
    });
    var bookstashEditor = Base.extend({
        /* --------------------------------------------------------------------
        // yeah wrote my own
        -------------------------------------------------------------------- */
        init: function(conf){
            var be = this;

            be.box              = {};
            be.stateCb          = conf.stateCb;
            be.box.text         = conf.elm;
            be.box.toolbar      = conf.toolbar;
            be.box.extra        = conf.extra;
            be.pause            = false;
            be.countdown        = $(conf.elm).next('div.countdown')[0];
            be.maxLen           = 20000;
            be.orig             = [];
            be.orig.extra       = '';
            be.orig.text        = '';

            be.box.style = ['bold', 'italic', 'underline', 'strikethrough', 'removeFormat'];

            $(be.box.toolbar).find('button').on('mousedown mouseup', function(event){
                // no stealing focus plz
                event.preventDefault();
                event.stopImmediatePropagation();
            });
            $(be.box.toolbar).find('button').click(function(event){
                event.preventDefault();
                event.stopImmediatePropagation();
                be._click(event);
                be._updateCountdown();
                be._updateStateCb();
                return false;
            });
            $(be.box.text).on('blur', function(){
                $(be.box.toolbar).find('button').removeClass('active');
            });
            $(be.box.text).on('paste', function(event){
                setTimeout(function(){
                    be._textTidy();
                    be._updateCountdown();
                    be._updateStateCb();
                    be.box.text.focus();
                },0);
            });
            $(be.box.text).on('keypress', function(event){
                if(event.keyCode == '13'){
                    document.execCommand('formatBlock', false, 'p');
                }
            });
            $(be.box.text).on('focus mouseup keyup', function(){
                be._updateCountdown();
                be._updateStateCb();

                setTimeout(function(){ be._setActive(); }, 1);
            });
            $(be.box.text).on('change', function(){
                be._updateStateCb();
                be._updateCountdown();
            })
            $(be.box.extra).on('change', function(){
                if (!be.pause){
                    be._updateStateCb();
                }
            })
            $(be.box.extra).on('focus mouseup keyup', function(){
                be._updateStateCb();
            });
        },
        getText: function(){
            var be = this;
            return $(be.box.text).html();
        },
        getExtra: function(){
            var be = this;
            return $(be.box.extra).val();
        },
        getState: function(){
            var be = this;

            var text = $(be.box.text).html();
            var extra = $(be.box.extra).val();

            if ((text != be.orig.text) || (extra != be.orig.extra)){
                return 'dirty';
            }
            return 'clean';
        },
        set: function(text, extra){
            var be = this;

            be.pause = true;

            be.orig.extra   = extra;
            $(be.box.extra).val(extra);

            $(be.box.text).html(text);
            be.orig.text    = $(be.box.text).html();

            be.pause = false;
        },
        reset: function (text, extra){
            var be = this;

            be.orig.text    = text;
            be.orig.extra   = extra;

            be._updateCountdown();
        },
        _updateStateCb: function(){
            var be = this;
            if (undefined == be.stateCb){ return; }

            var text = $(be.box.text).html();
            var extra = $(be.box.extra).val();

            if ((text != be.orig.text) || (extra != be.orig.extra)){
                be.stateCb('dirty', text, extra);
            }
            else{
                be.stateCb('clean', text, extra);
            }
        },
        _textTidy: function(){
            var be = this;

            // first strip out all the attributes
            $(be.box.text).children().replaceWith(function(){
                return $('<' + this.nodeName + '>').append($(this).contents());
            });

            // convert blocks to p's
            var text = $(be.box.text).html();
            text = text.replace(/\n/g, '');
            text = text.replace(/<br\s*\/?>/gi, '\n');
            text = text.replace(/<p><\/p>/gi, '\n');
            text = text.replace(/\n+/g, '\n');
            text = text.replace(/\n/g, '</p><p>');
            text = '<p>' + text + '</p>';
            $(be.box.text).html(text);

            // remove stray tags
            var valid = ['p','i','b','u','strike'];
            $(be.box.text).find('*').each(function(index){
                if (-1 == valid.indexOf(this.nodeName.toLowerCase())){
                    $(this).replaceWith($(this).contents());
                }
            });
        },
        _updateCountdown: function(){
            var be = this;
            var text = $(be.box.text).html();
            var chars = be.maxLen - text.length;

            if (1000 > chars){
                $(be.countdown).addClass('visible');
                be.countdown.innerHTML = chars + ' characters remaining';
            }
            else{
                $(be.countdown).removeClass('visible');
            }
        },
        _click: function(event){
            event.preventDefault();
            event.stopImmediatePropagation();

            var be = this;
            var button = event.currentTarget;

            $.each(be.box.style, function(index, style){
                if ($(button).hasClass(style)){
                    document.execCommand(style, false, null);
                }
            });
            setTimeout(function(){ be._setActive(); }, 1);
        },
        _setActive: function(){
            var be = this;

            $.each(be.box.style, function(index, style){
                if ('removeFormat' == style){
                    return;
                }

                if ('strikethrough' == style){
                    // because strike is not styled with a strikethrough actually
                    var fore = document.queryCommandValue('ForeColor');
                    if (( 'rgb(170, 170, 170)' == fore) ||
                        ('11184810' == fore)){

                        $(be.box.toolbar).find('button.' + style).addClass('active');
                    }
                    else{
                        $(be.box.toolbar).find('button.' + style).removeClass('active');
                    }
                }
                else{
                    if (document.queryCommandState(style)){
                        $(be.box.toolbar).find('button.' + style).addClass('active');
                    }
                    else{
                        $(be.box.toolbar).find('button.' + style).removeClass('active');
                    }
                }
            });
        }
    });
    // ------------------------------------------------------------------------
    var reviewWidget = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            listens:
                bookster:workreviewrequest:{{ work.id }}
                bookster:authchange
            throws:
                bookstash:reviews-stale-for-{{ work.id }}
        -------------------------------------------------------------------- */
        init: function(conf){
            var rw = this;

            var elm = rw.elm = conf.elm;
            rw.box          = {};
            rw.work_id      = $(elm).attr('data-workid');
            rw.review       = undefined;
            rw.maxLen       = 20000;
            rw.minLen       = 20;
            rw.visible      = false;
            rw.undef        = true;
            rw.started      = $('.my-review-large').find('.datepicked.started-reading')[0];
            rw.finished     = $('.my-review-large').find('.datepicked.finished-reading')[0];

            $(document).on('bookster:workreviewrequest:' + rw.work_id, function(event){
                rw._reviewToggle(event);
            });
            $(document).on('bookster:workreviewsaverequest:' + rw.work_id, function(event){
                rw._save(event);
            });
            bookstash.user.registerListCallback('reviews', function(members){
                rw._cbUbdate(members);
            });
            $(rw.started).on('change', function(){
                rw._checkState();
            });
            $(rw.finished).on('change', function(){
                rw._checkState();
            });
            $('.my-review-large').find('.reviewdelete').click(function(event){
                rw._reviewDelete(event);
            });
            $(document).on('bookster:authchange', function(event){
                rw._close();
            });

            rw._setupEditor();
        },
        stateChange: function(state, text, title){
            var rw = this;

            if ((text.length <= rw.minLen) || ('clean' == state)){
                rw._setSaveState(false);
            }
            else{
                rw._setSaveState(true);
            }
        },
        _reviewDelete: function(event){
            var rw = this;

            var url = $(event.target).attr('data-url');

            var mg = new modalGeneral({
                elm:            rw.createElement('button'),
                type:           'confirm',
                headline:       'Are you sure?',
                subhead:        'Deleted reviews can not be recovered.',
                confirmCB:      function(){

                    bookstash.user.delegatedUpdate( url, {},
                        function(data){
                            rw._setFlash('deleted', true);
                            rw._setSaveState(false);
                            rw._setPrevioulySaved(true);
                            rw.editor.set('', '');

                            rw.started.saved_value = rw.started.value = '';
                            rw.finished.saved_value = rw.finished.value = '';

                            $(rw.started).pickadate('picker').set(
                                'select', undefined, {format: 'mmmm d, yyyy'});
                            $(rw.finished).pickadate('picker').set(
                                'select', undefined, {format: 'mmmm d, yyyy'});

                            setTimeout(function(){
                                rw._close();
                                rw._setFlash('', true);
                            }, 1500);

                            rw._updateWorkReviewCounts(data.work_review_count);

                            $( document ).trigger( 'bookstash:reviews-stale-for-' + rw.work_id);
                        }
                    );
                }
            });
            mg.activate();
        },
        _setSaveState: function(saveable){
            var rw = this;
            if (saveable){
                $('.my-review-large').find('button.save').removeAttr('disabled');
                rw._setFlash("don't forget to save");
            }
            else{
                $('.my-review-large').find('button.save').attr('disabled', 'disabled');
                rw._setFlash('');
            }
        },
        _setPrevioulySaved: function(unsaved){
            if (undefined == unsaved){
                $('.my-review-large').attr('data-saved', 'saved');
            }
            else{
                $('.my-review-large').attr('data-saved', '');
            }
        },
        _checkState: function(){
            var rw = this;

            var clean = true;
            var text = rw.editor.getText();

            if ('dirty' == rw.editor.getState()){
                clean = false;
            }
            if (rw.started.saved_value != rw.started.value){
                clean = false;
            }
            if (rw.finished.saved_value != rw.finished.value){
                clean = false;
            }

            if ((text.length <= rw.minLen) || (true == clean)){
                rw._setSaveState(false);
            }
            else{
                rw._setSaveState(true);
            }
        },
        _setupEditor: function(){
            var rw = this;

            var reviewBox       = $('.my-review-large').find('div.review-text')[0];
            var reviewControl   = $('.my-review-large').find('div.review-control')[0];
            var reviewExtra     = $('.my-review-large').find('.review-title')[0];

            rw.editor = new bookstashEditor({
                elm: reviewBox,
                toolbar: reviewControl,
                maxLen: rw.maxLen,
                extra: reviewExtra,
                stateCb: function(state, text, title){
                    rw.stateChange(state, text, title); }
            });

            $('.my-review-large').find('.datepicked').pickadate({
                format: 'mmmm d, yyyy'
            });

        },
        _reviewToggle: function(event){
            var rw = this;
            event.preventDefault();

            if (rw.visible){
                rw._close();
            }
            else{
                if (rw.undef){
                    rw._setLoading(true);
                    $.ajax({
                        type: 'GET',
                        url: '/user/review/' + rw.work_id,
                        success: function(data){
                            rw.undef = false;
                            rw._setLoading(false);
                            rw.editor.set(data.review, data.title);

                            rw.started.value = rw.started.saved_value = data.started_at;
                            rw.finished.value = rw.finished.saved_value = data.finished_at;

                            if (rw.started.value){
                                $(rw.started).pickadate('picker').set(
                                    'select', data.started_at, {format: 'mmmm d, yyyy'});
                            }
                            if (rw.finished.value){
                                $(rw.finished).pickadate('picker').set(
                                    'select', data.finished_at, {format: 'mmmm d, yyyy'});
                            }

                            if (data.exists){
                                rw._setPrevioulySaved();
                            }
                        }
                    });
                }
                rw._open();
            }
        },
        _close: function(){
            var rw = this;
            $('body').attr('hidetrim', '');
            $('section.work').attr('data-my-review-reveal', 'false');
            rw.visible = false;
        },
        _open: function(){
            var rw = this;
            $('body').attr('hidetrim', 'hidetrim');
            $('.my-review-large').find('div.review-text').height($(window).height() -535);
            $('section.work').attr('data-my-review-reveal', 'reveal');
            rw.visible = true;
        },
        _cbUbdate: function(members){
            var rw = this;
            var found = false;

            $.each(members, function(index, status){
                if (rw.work_id == index){
                    if (undefined == rw.review){
                        rw.review = false;
                    }
                    $(rw.elm).attr('data-reviewed', 'reviewed');
                    found = true;
                }
            });
            if (false == found){
                rw.review = undefined;
                $(rw.elm).attr('data-reviewed', '');
            }
        },
        _setLoading: function(loading){
            var rw = this;
            // FIXME stub
        },
        _setFlash: function(flash, fadeout){
            var rw = this;
            if (undefined == fadeout){
                $('.my-review-large').find('div.flash').removeClass('fadeout');
            }
            else{
                $('.my-review-large').find('div.flash').addClass('fadeout');
            }
            $('.my-review-large').find('div.flash').html(flash);
        },
        _save: function(event){
            var rw = this;
            event.preventDefault();

            rw._setFlash('<i class="fa fa-spinner fa-spin"></i>saving...');

            var text = rw.editor.getText();
            var title = rw.editor.getExtra();
            var started = $('.my-review-large').find('.datepicked.started-reading').val();
            var finished = $('.my-review-large').find('.datepicked.finished-reading').val();

            bookstash.user.delegatedUpdate( '/user/review/' + rw.work_id, {
                    text: text,
                    title: title,
                    started: started,
                    finished: finished
                }, function(data){

                    if (data.error){

                        var mg = new modalGeneral({
                            elm:            rw.createElement('button'),
                            type:           'alert',
                            headline:       "Could not save!",
                            subhead:        data.result
                        });
                        mg.activate();

                        rw._setFlash('');
                    }
                    else{
                        rw._setFlash('saved.', true);
                        rw._setSaveState(false);
                        rw.editor.reset(text, title);

                        rw.started.saved_value = rw.started.value;
                        rw.finished.saved_value = rw.finished.value;

                        setTimeout(function(){
                            rw._close();
                            rw._setFlash('', true);
                        }, 1500);

                        rw._updateWorkReviewCounts(data.work_review_count);
                        $( document ).trigger( 'bookstash:reviews-stale-for-' + rw.work_id);
                        rw._setPrevioulySaved();
                    }
            });
        },
        _updateWorkReviewCounts: function(count){
            var rw = this;
            $('[data-dynamic-review-count=' + rw.work_id + ']').each(function(index, element){
                var text = count;

                if (0 == count){
                    var fallback = false;
                    if (fallback = $(element).attr('data-count-zero')){
                        text = fallback;
                    }
                    else{
                        text = 'No Reviews';
                    }
                }
                if (1 == count){
                    text = '1 Review';
                }
                else{
                    text = count + ' Reviews';
                }
                $(element).html(text);
            });
        }
    });
    // ------------------------------------------------------------------------
    var smoothScroller = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
        -------------------------------------------------------------------- */
        init: function(conf){
            var ss = this;
            ss.elm = conf.elm;

            ss.height = false;
            ss.offset = 0;

            if ($(ss.elm).hasAttr('data-override-height')){
                ss.height = $(ss.elm).attr('data-override-height');
            }
            if ($(ss.elm).hasAttr('data-scroll-offset')){
                ss.offset = $(ss.elm).attr('data-scroll-offset');
            }

            $(ss.elm).click(function(){
                if (false === ss.height){
                    var target = $(this.hash);
                    $('div.focus').animate({
                        scrollTop: target.position().top - ss.offset
                    }, 500);
                }
                else{
                    var target = $(this.hash);
                    $('div.focus').animate({
                        scrollTop: ss.height
                    }, 500);
                }
                return false;
            });
        }
    });
    // ------------------------------------------------------------------------
    var workScrollManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
        -------------------------------------------------------------------- */

        init: function(conf){
            var wsm = this;

            wsm.clients = []
            wsm.paused = false;

            $('body > div.focus').on('scroll', function(event){
                wsm._handleScroll(event);
            });
            window.addEventListener( 'resize', function(event){
                wsm._handleScroll(event);
            });

            var catPickerElm = $('.also-in-cat-shell')[0];
            if (undefined != catPickerElm){
                var catPicker = new categoryPicker({elm: catPickerElm});
                wsm._addClient(catPicker);
            }

            var reviewBrowseElm = $('.book-reviews')[0];
            if (undefined != reviewBrowseElm){
                var rb = new reviewBrowser({elm: reviewBrowseElm});
                wsm._addClient(rb);
                wsm.rb = rb;
            }
        },
        pause: function(){
            var wsm = this;
            wsm.paused = true;
            if (undefined != wsm.rb){
                wsm.rb.pause();
            }
        },
        resume: function(){
            var wsm = this;
            wsm.paused = false;
            if (undefined != wsm.rb){
                wsm.rb.resume();
            }
        },
        _addClient: function(client){
            this.clients.push(client);
        },
        _handleScroll: function(event){
            var wsm = this;
            if (!this.paused){
                $.each(wsm.clients, function(index, client){
                    client.handleScroll();
                });
            }
        }
    });
    // ------------------------------------------------------------------------
    var reviewBrowser = flagMaster.extend({
        /* --------------------------------------------------------------------
        // instantiated by workScrollManager
            - listens:
                bookstash:focus-reflow-request
                bookstash:reviews-stale-for-{{ work_id }}
        -------------------------------------------------------------------- */
        init: function(conf){
            var rb = this;

            rb.elm      = conf.elm;
            rb.shell    = $(rb.elm).find('.review-shell')[0];
            rb.mode     = 'new';
            rb.stars    = false
            rb.workid   = $(rb.elm).attr('data-bstsh-workid');
            rb.cachebust = false;

            rb.flagText = {};
            rb.flagText.thanksSubHead = "We'll take a look at that review.";
            rb.flagText.formselector = '.flag-review';

            $(rb.elm).find('.controls').find('button').click(function(eveent){
                event.preventDefault();
                rb._handleControlClick(event);
            });
            $(rb.elm).find('.controls').find('select').change(function(eveent){
                rb.stars = $(this).val();
                rb._loadContent();
            });

            $(document).on('bookster:authchange', function(event){
                setTimeout(function(){ rb._reset(); }, 1000);
            });

            $(rb.elm).find('.controls').find('input[type=checkbox]').on('change', function(event){
                rb._loadContent();
            });

            $(document).on('bookstash:focus-reflow-request', function(){
                $(rb.shell).masonry();
            });

            $(document).on('bookstash:reviews-stale-for-' + rb.workid, function(){
                rb.cachebust = new Date().valueOf();
                rb._loadContent();
            });

            // FIXME must be a better way to fire after layout is done
            setTimeout(function(){
                rb._masonize();
            }, 1000);

            rb._setup();
        },
        handleScroll: function(){
            var rb = this;
            // not in use actually
        },
        _masonize: function(){
            var rb = this;

            $(rb.shell).masonry({
                itemSelector: '.review',
                isFitWidth: true,
                hiddenStyle: {},
                visibleStyle: {},
                gutter: 20
            });
            $(rb.shell).infinitescroll({
                    //debug: true,
                    behavior: 'local',
                    binder: $('body > .focus'),
                    navSelector: "body > .focus div.navigation",
                    nextSelector: "body > .focus a.morelink",
                    itemSelector: '.review',
                    finishedMsg: '',
                    bufferPx: 800,
                    loadingImg: false,
                    loadingText: false,
                    donetext: ''
                },
                function(elms){
                    $(rb.shell).masonry('appended', elms, true);
                    $( document ).trigger( "bookster:domchanged" );
                    rb._setup();
                }
            );
        },
        pause: function(){
            var rb = this;
            $(rb.shell).infinitescroll('pause');
        },
        resume: function(){
            var rb = this;
            $(rb.shell).infinitescroll('resume');
        },
        _setup: function(){
            var rb = this;

            $(rb.shell).find('.review').each(function(){

                var review = this;
                if (undefined == $(review).attr('configured')){
                    if (0 == $(review).find('.review-body').find('strike').length){
                        $(review).attr('spoilers', 'nospoiler');
                    }

                    $(review).attr('configured', true);

                    $(review).find('button.flag').click(function(event){

                        var review_id = $(event.target).parents('.review').attr('data-review-id');
                        var replace = {review_id: review_id};

                        rb.openFlagModal(replace, "I'm flagging this review because it is..." );

                    });
                    $(review).find('button.showspoiler').click(function(event){
                        $(event.target).parents('.review').attr('spoilers', 'spoil');
                    });
                    $(review).find('button.hidespoiler').click(function(event){
                        $(event.target).parents('.review').attr('spoilers', false);
                    });

                    $(review).find('.reviewlike, .reviewunlike').click(function(event){
                        if ('anon' == $('body').attr('auth')){
                            event.preventDefault();
                            event.stopImmediatePropagation();
                            event.stopPropagation();
                            $(document).trigger('bookster:loginrequest');
                            return;
                        }

                        var url = $(event.target).attr('data-url');

                        var review = $(event.target).parents('.review')[0];
                        $(review).find('.likes').attr('loading', 'loading');

                        $.ajax({
                            type: 'POST',
                            url: url,
                            success: function(data){
                                $(review).find('.likes').attr('loading', false);
                                var likes = '';

                                if (undefined != data.likes){
                                    if (0 == data.likes){ }
                                    else if (1 == data.likes){
                                        likes = '<span>1 like</span>';
                                    }
                                    else{
                                        likes = '<span>' + data.likes + ' like</span>';
                                    }

                                    $(review).find('.likecount').html(likes);
                                    $(document).trigger( "bookster:review-like-" + rb.workid );
                                }
                            }
                        });
                    });
                }
            });
        },
        _loadContent: function(){
            var rb = this;
            var url = rb._buildUrl();
            $.ajax({
                type: 'GET',
                url: url,
                success: function(data){
                    $(rb.shell).masonry('destroy');
                    $(rb.shell).infinitescroll('destroy');
                    $(rb.shell).data('infinitescroll', null);

                    // prevent the page from bouncing around during render
                    var h = $(rb.shell).height();
                    $(rb.shell).css('min-height', h + 'px');

                    $(rb.shell).html(data);

                    $(document).trigger( "bookster:domchanged" );
                    rb._masonize();
                    rb._setup();

                    $(rb.shell).css('min-height', '');
                }
            });
        },
        _buildUrl: function(){
            var rb = this;

            var user_id;
            if ($(rb.elm).find('.controls').find('input[type=checkbox]').is(':checked')){
                user_id = bookstash.user.getUserId();
            }
            var suffix = '';
            if (rb.cachebust){
                suffix = '?bcb=' + rb.cachebust;
            }

            if (undefined == user_id){
                if (rb.stars){
                    return '/book/reviews/' + rb.workid + '/' + rb.mode + '/1/' + rb.stars + suffix;
                }
                else{
                    return '/book/reviews/' + rb.workid + '/' + rb.mode + '/1' + suffix;
                }
            }
            else{
                if (rb.stars){
                    return '/book/reviews/' + rb.workid + '/' + rb.mode + '/1/' + rb.stars + '/' + user_id + suffix;
                }
                else{
                    return '/book/reviews/' + rb.workid + '/' + rb.mode + '/1/any/' + user_id + suffix;
                }
            }
        },
        _reset: function(){
            var rb = this;

            $(rb.shell).children().detach();
            rb._loadContent();
        },
        _handleControlClick: function(event){
            var rb = this;
            var button = event.target;

            $(rb.elm).find('.controls').find('button').removeClass('selected');
            $(button).addClass('selected');
            rb.mode = $(button).attr('data-mode');
            rb._loadContent();
        }
    });
    var userTabManager = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            listens:
                bookster:userprefchange
                bookster:pagechange
            throws:
                bookster:topmenureveal
                bookster:topmenudemure
        -------------------------------------------------------------------- */
        init: function(conf){
            var ut = this;
            ut.elm = conf.elm;

            ut.state = {};
            ut.state.listcounts = {
                readit:     0,
                reading:    0,
                toread:     0,
                ratings:    0,
                reviews:    0
            }
            ut.state.hidden = { };

            $(document).on('bookster:menubackgroundclick bookster:pagechange', function(event){
                $(ut.elm).find('.tab').attr('data-selected', '');
                if ('expanded' == $(ut.elm).attr('data-mode')){
                    ut._setMode('');
                }
            });
            $(document).on('bookster:authchange', function(event){
                if ( 'expanded' == ut._getMode() ){
                    if (bookstash.user.isAuthenticated()){
                        // on login draw content
                        ut._drawContent();
                    }
                    else{
                        // on logout pack up shop
                        $(ut.elm).find('.content.user-list').remove();
                        $(ut.elm).find('.tab').attr('data-selected', '');
                        ut._setMode('');
                        ut._drawContent();
                    }
                }
            });
            $(document).on('bookster:topmenureveal', function(event){
                if ('expanded' == $(ut.elm).attr('data-mode')){
                    ut._setMode('', true);
                }
            })

            $(document).on('bookster:userprefchange', function(event){
                ut._updateFromPrefs();
            });

            $(ut.elm).find('[data-action=lessen-tabs]').click(function(event){
                $(ut.elm).find('.tab').attr('data-selected', '');
                if ('expanded' == $(ut.elm).attr('data-mode')){
                    ut._setMode('');
                }
                else{
                    ut._setMode('collapsed');
                }
            });
            $(ut.elm).find('[data-action=show-tabs]').click(function(event){
                ut._setMode('');
            });
            $(ut.elm).find('[data-action=expand]').click(function(event){
                var tab = event.currentTarget;
                if (( 'expanded' == ut._getMode() ) &&
                    (tab == ut._getTab())){

                    $(ut.elm).find('.tab').attr('data-selected', '');
                    ut._setMode('');
                }
                else{
                    console.log('expand');
                    ut._setMode('expanded');
                    ut._setTab(event.currentTarget);
                    ut._drawContent(500);
                }
            });

            $(ut.elm).find('.content.my-lists input[type=checkbox]').on('change', function(){
                ut._savePrefs();
            });

            bookstash.user.registerListCallback('readit', function(members){
                ut._cbUbdateReadit(members);
            });
            bookstash.user.registerListCallback('ratings', function(members){
                ut.state.listcounts.ratings = Object.keys(members).length;
                ut._drawTabs();
            });
            bookstash.user.registerListCallback('reviews', function(members){
                ut.state.listcounts.reviews = Object.keys(members).length;
                ut._drawTabs();
            });

            ut._setup();
        },
        _cbUbdateReadit: function(members){
            var ut = this;
            var readit = 0, reading = 0, toread = 0;

            $.each(members, function(index, status){
                if ( 3 == status){
                    readit++;
                }
                if ( 2 == status){
                    reading++;
                }
                if ( 1 == status){
                    toread++;
                }
            });

            ut.state.listcounts.toread     = toread;
            ut.state.listcounts.reading    = reading;
            ut.state.listcounts.readit     = readit;

            ut._drawTabs();
        },
        _drawTabs: function(go){
            var ut = this;

            if (undefined == go){
                // squelch extraneous calls
                window.clearTimeout(ut.drawTabsTimeout);
                ut.drawTabsTimeout = setTimeout(function(){
                    ut._drawTabs(true);
                },0);
                return;
            }

            // show and hide tabs
            var tabsToHide = [];

            if (0 == ut.state.listcounts.reviews){
                tabsToHide.push('reviews');
            }
            if (0 == ut.state.listcounts.ratings){
                tabsToHide.push('ratings');
            }

            $.each(ut.state.hidden, function(index, value){
                tabsToHide.push(value);
            });

            $(ut.elm).find('.tab.hidden').removeClass('hidden');
            $.each(tabsToHide, function(index, value){
                $(ut.elm).find('.tab.' + value ).addClass('hidden');
            });

            // update tab counts
            var current;
            $.each(ut.state.listcounts, function(name, count){
                current = $(ut.elm).find('[data-count-of=' + name + ']').html();
                if (current != count){

                    $(ut.elm).find('[data-count-of=' + name + ']')
                        .html(count);

                    if (current > count){
                        $(ut.elm).find('[data-count-of=' + name + ']').addClass('blowup');
                        setTimeout(function(){
                            $(ut.elm).find('[data-count-of=' + name + ']').removeClass('blowup');
                        }, 600);
                    }
                    else{
                        $(ut.elm).find('[data-count-of=' + name + ']').addClass('blowdown');
                        setTimeout(function(){
                            $(ut.elm).find('[data-count-of=' + name + ']').removeClass('blowdown');
                        }, 600);
                    }
                }

                // delete tab content that has changed
                $(ut.elm).find('.content.user-list[data-cname=' + name + ']').remove();
            });
        },
        _savePrefs: function(){
            var ut = this;
            var url = $(ut.elm).find('.content.my-lists form').attr('action');

            var hide = [];
            $(ut.elm).find('.content.my-lists input:checkbox:not(:checked)').each(function(){
                hide.push($(this).attr('value'));
            });
            hide = hide.join();

            $(ut.elm).find('.content.my-lists li.status').attr('mode', 'saving');

            bookstash.user.delegatedUpdate( url, {
                    hide: hide
                }, function(data){

                    $(ut.elm).find('.content.my-lists li.status').attr('mode', 'saved');

                    setTimeout(function(){
                        $(ut.elm).find('.content.my-lists li.status').attr('mode', '');
                    }, 500);
            });
        },
        _updateFromPrefs: function(){
            var ut = this;

            var prefs = bookstash.user.getUserPrefs();
            ut.state.hidden = prefs.hide;

            // fix checkboxes on My Lists tab
            $(ut.elm).find('.content.my-lists input[type=checkbox]')
                .attr('checked', 'checked');

            $.each(prefs.hide, function(index, value){
                $(ut.elm).find(
                    '.content.my-lists input[type=checkbox][value=' + value + ']')
                    .attr('checked', false);
            });

            ut._drawTabs();
        },
        _setup: function(){
            var ut = this;
        },
        _drawContent: function(delay){
            // reveals appropriate content panel
            var ut = this;

            if (undefined == delay){
                delay = 0;
            }

            if (bookstash.user.isAuthenticated()){
                var cname = $(ut.elm).find('.tab[data-selected=selected]').attr('data-cname');

                $(ut.elm).find('.tabs-content .content').removeClass('visible');
                var cdiv = $(ut.elm).find('.content' + '.' + cname)[0];

                if (undefined == cdiv){

                    setTimeout(function(){
                        $(ut.elm).find('.tabs-content .content.loading').addClass('visible');

                        var url = '/user/tabcontent/' + cname;

                        $.ajax({
                            type: 'GET',
                            url: url,
                            success: function(data){

                                var div = document.createElement('div');

                                $(div).addClass('content user-list ' + cname)
                                    .attr('data-cname', cname)
                                    .html(data);

                                $(ut.elm).find('.tabs-content .content.loading')
                                    .removeClass('visible');

                                $(ut.elm).find('.tabs-content').append(div);

                                $(document).trigger( "bookster:domchanged" );

                                return ut._drawContent();
                            }
                        });
                    }, delay);
                }
                else{
                    $(cdiv).addClass('visible');
                }
            }
            else{
                $(ut.elm).find('.tabs-content .content').removeClass('visible');
                $(ut.elm).find('.tabs-content .anon-tease').addClass('visible');
            }
        },
        _setMode: function(mode, noevent){
            var ut = this;

            if (undefined == noevent){
                if ('' == mode){
                    $(document).trigger('bookster:topmenudemure');
                }
                if ('expanded' == mode){
                    $(document).trigger('bookster:topmenureveal');
                }
            }
            $(ut.elm).attr('data-mode', mode);
        },
        _getMode: function(){
            var ut = this;
            return $(ut.elm).attr('data-mode');
        },
        _setTab: function(elm){
            var ut = this;

            $(ut.elm).find('.tab').attr('data-selected', '');
            $(elm).attr('data-selected', 'selected');
        },
        _getTab: function(){
            var ut = this;
            return $(ut.elm).find('.tab[data-selected=selected]')[0];
        }
    });
    var userTab = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
            attributes:
        -------------------------------------------------------------------- */
        init: function(conf){
            var bt          = this;
            bt.elm          = conf.elm;
            bt.url          = '/user/tabcontent';
            bt.listname     = $(bt.elm).attr('data-listname');

            // dynamically shows number of books based on available space
            // notices resize event and marks dirty on resize
            // maybe adjust pages appropa

            $(bt.elm).find('.controls button.sort').on('click', function(event){
                $(bt.elm).find('.controls button.sort').removeClass('selected');
                $(this).addClass('selected');
                bt._reload();
            });
            $(bt.elm).find('.controls input.reverse').on('change', function(event){
                bt._reload();
            });

            bt._setup();
        },
        _setup: function(){
            var bt = this;
            $(bt.elm).find('button.paginate').on('click', function(event){
                bt._paginate(event.currentTarget);
            });
        },
        _reload: function(){
            var bt = this;

            var list = $(bt.elm).attr('data-listname');
            var sort = $(bt.elm).find('.controls > button.selected').attr('data-sort');
            var reverse = $('.controls input.reverse').is(':checked') ? 1 : 0;

            var url = bt.url + '/' + list + '?sort=' + sort + '&' + 'reverse=' + reverse;

            $.ajax({
                type: 'GET',
                url: url,
                success: function(data){
                    var scratch = document.createElement('div');
                    $(scratch).html(data);
                    $(bt.elm).find('.side-scroll').html($(scratch).find('.side-scroll').html());
                    bt._setup();
                }
            });
        },
        _paginate: function(button){
            var bt = this;

            var url = $(button).attr('data-url');

            $.ajax({
                type: 'GET',
                url: url,
                success: function(data){
                    var scratch = document.createElement('div');
                    $(scratch).html(data);
                    $(bt.elm).find('.side-scroll').html($(scratch).find('.side-scroll').html());
                    bt._setup();
                }
            });
        }
    });

    var categoryPicker = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by workScrollManager
            attributes:
        -------------------------------------------------------------------- */
        init: function(conf){
            var cp = this;
            cp.elm = conf.elm;

            $(cp.elm).find('div.cat').click(function(event){
                $(cp.elm).find('.book-list-shell').addClass('truncate');
                $(cp.elm).find('div.cat').removeClass('selected');
                $(this).addClass('selected');

                var node_id     = $(this).attr('data-target-node');
                var panel       = $(cp.elm).find('#related-' + node_id)[0];

                $(cp.elm).find('.book-list').removeClass('selected');
                $(panel).addClass('selected');

                if ($(panel).hasAttr('data-source')){
                    var source = $(panel).attr('data-source');
                    $(panel).removeAttr('data-source');

                    $.ajax({
                        type: 'GET',
                        url: source,
                        success: function(data){

                            var existing = $(panel).children();
                            $(panel).children().detach();
                            $(panel).html(data);
                            $(panel).append(existing);

                            cp.masonize(panel);

                            $(document).trigger( "bookster:domchanged" );
                            cp.handleScroll();
                        }
                    });
                }
            });

            setTimeout(function(){
                cp.handleScroll();
            }, 1);

            cp.masonize($(cp.elm).find('.book-list.selected')[0]);
        },
        masonize: function(elm){
            var cp = this;

            $(cp.elm).find(elm).masonry({
                itemSelector: '.item',
                isFitWidth: true,
                hiddenStyle: {},
                visibleStyle: {},
                stamp: $(cp.elm).find('.cat-picker')[0]
            });
        },
        handleScroll: function(event){
            var cp = this;

            $(cp.elm).find('.book-list.selected').children('[sly=sly]').each(function(index){
                var item = this;
                if ($(item).visible(true)){
                    $(item).removeAttr('sly');
                }
            });
        }
    });

    // ------------------------------------------------------------------------
    var objectifier = Base.extend({
        /* --------------------------------------------------------------------
        // where the magic happens
        -------------------------------------------------------------------- */
        init: function(){
            var ob = this;
            ob.debug = false;

            $(document).on('bookster:domchanged', function(){
                ob.setup();
            });
            ob.setup();
        },
        setup: function(){
            $('[data-bstsh-object]').each(function(){
                var obj, objects = [], objByClass = {}, elm = this;
                var clsses = $(this).attr('data-bstsh-object').split(' ');

                $(clsses).each(function(){
                    var clss = eval(this);

                    obj = new clss({elm: elm, objects: objects});
                    objByClass[clss] = obj;
                    objects.push(obj);
                });
                $(this).removeAttr('data-bstsh-object');
                this.owningObjects = objByClass;
            });
        }
    });

    // -------------------------------------------------------------------------
    $(document).ready(function(){
        bookstash.history   = new historyManager();
        bookstash.user      = new userDataManager();
        new timeagator();
        new objectifier();

        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=" + document.cred.facebook_app_id + "&version=v2.0";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    });

    // -- Facebook -------------------------------------------------------------
    window.fbAsyncInit = function() {
        FB.init({
            appId      : document.cred.facebook_app_id,
            channelUrl : '//' + document.location.hostname + '/channel.php',
            status     : true,
            xfbml      : false,
            version    : 'v2.2'
        });
    };


    // -------------------------------------------------------------------------
}( window.jQuery );
