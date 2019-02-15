/*
 */
// ----------------------------------------------------------------------------

import '../css/style.less';

// ----------------------------------------------------------------------------



/*
// full on old school
require('script-loader!./include/inherit.js');
require('script-loader!./include/legacy/jquery.min.js');
require('script-loader!./include/legacy/masonry.pkgd.min.js');
require('script-loader!./include/legacy/modernizr.custom.js');
require('script-loader!./include/legacy/jquery.infinitescroll.js');
require('script-loader!./include/legacy/jquery.visible.min.js');
require('script-loader!./include/legacy/jquery.timeago.js');
require('script-loader!./include/legacy/jquery.raty.js');
require('script-loader!./include/legacy/picker.js');
require('script-loader!./include/legacy/picker.date.js');
require('script-loader!./include/legacy/jquery.autocomplete.min.js');
require('script-loader!./include/jQueryShims.js');
require('script-loader!./include/replaceAll.js');
require('script-loader!./include/bookstash.js');
*/


require('script-loader!./include/inherit.js');

const $                       = require('jquery');
window.$ = $;
window.jQuery = $;

require('script-loader!./include/legacy/masonry.pkgd.min.js');
require('jquery-visible');
require('timeago');
require('raty-js');
//require('picker');
require('script-loader!./include/legacy/picker.js');
require('script-loader!./include/legacy/picker.date.js');
require('devbridge-autocomplete');

require('script-loader!./include/modernizr-custom.js');
require('./include/jquery.infinitescroll.js');
require('./include/jQueryShims.js');
require('./include/replaceAll.js');
require('./include/bookstash.js');




/*

const $                       = require('jquery');
window.$ = $;
window.jQuery = $;

const masonry                 = require('masonry-desandro');
const visible                 = require('jquery-visible');
const timeago                 = require('timeago');
const raty                    = require('raty-js');
const pickadate               = require('picker');
const autocomplete            = require('devbridge-autocomplete');

require('script-loader!./include/inherit.js');
require('script-loader!./include/modernizr-custom.js');
require('./include/jquery.infinitescroll.js');
require('./include/jQueryShims.js');
require('./include/replaceAll.js');
require('./include/bookstash.js');

*/
