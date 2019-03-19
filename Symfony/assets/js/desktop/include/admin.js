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

!function( $, undefined ){
    "use strict";

    $.fn.hasAttr = function(name) {
       return this.attr(name) !== undefined;
    };

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
        }
    });

    var previewLauncher = Base.extend({
        /* --------------------------------------------------------------------
        // instantiated by objectifier
        -------------------------------------------------------------------- */

        init: function(conf) {
            var mg = this;

            mg.elm              = conf.elm;

            $(mg.elm).click(function(event){
                event.preventDefault();
                var url = $(event.target).attr('data-url');
                window.open(url);
            });
        }
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
                subhead:        ''
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
        activate: function(){
            var mg = this;

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
                    $(mg.elm).find('>:first-child').triggerHandler('click');
                }
                else if ('form' == mg.type){
                    mg.completedCB(mg);
                }
            });

            $(mg.box).find('h1').html(mg.headline);
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
    var objectifier = Base.extend({
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


    var slugomatic = Class.extend({
        init: function(conf){
            var sm = this;

            sm.box = conf.elm;
            console.log('sup');

            $(sm.box).find('.slugmaster').on('keyup', function(){
                $(sm.box).find('.slugchild').val(sm.slugify(this.value));
            });
        },
        slugify: function(text){
            return jQuery.trim(text)
                .replace(/\s+/g,'-').replace(/[^a-zA-Z0-9\-]/g,'').toLowerCase()
                .replace(/\-{2,}/g,'-');
        }
    });

    // -----------------------------------------------------------------------------------------
    $(document).ready(function(){
        new objectifier();

        $('.slugomatic').each(function( index ){
            new slugomatic({ 'elm': this });
        });

    });
    // -----------------------------------------------------------------------------------------

}( window.jQuery );

