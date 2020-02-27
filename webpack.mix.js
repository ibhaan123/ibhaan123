const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css');

mix.combine([
   packages+'jquery/dist/jquery.js',
   packages+'bootstrap/dist/js/bootstrap.bundle.js',
   packages+'selectize/dist/js/standalone/selectize.js',
   packages+'datatables.net/js/jquery.dataTables.js',
   packages+'datatables.net-bs4/js/dataTables.bootstrap4.js',
   packages+'datatables.net-responsive/js/dataTables.responsive.js',
   packages+'datatables.net-responsive-bs4/js/responsive.bootstrap4.js',
   packages+'jquery-circle-progress/dist/circle-progress.js',
   packages+'sweet-modal/dist/dev/jquery.sweet-modal.js',
   packages+'moment/moment.js',
   packages+'timeago.js/dist/timeago.js',
   packages+'file-saver/FileSaver.js',
   packages+'block-ui/jquery.blockUI.js',
   packages+'pnotify/dist/iife/PNotify.js' ,
   packages+'pnotify/dist/iife/PNotifyButtons.js' ,
   packages+'pnotify/dist/iife/PNotifyAnimate.js' ,
   packages+'pnotify/dist/iife/PNotifyCallbacks.js' ,
   packages+'pnotify/dist/iife/PNotifyNonBlock.js' ,
   packages+'pnotify/dist/iife/PNotifyMobile.js' ,
   packages+'pnotify/dist/iife/PNotifyHistory.js' ,
   packages+'pnotify/dist/iife/PNotifyDesktop.js' ,
   packages+'pnotify/dist/iife/PNotifyConfirm.js' ,
   packages+'pnotify/dist/iife/PNotifyStyleMaterial.js' ,
   packages+'pnotify/dist/iife/PNotifyReference.js' ,
   'resources/assets/js/clipboard-polyfill.js',
   packages+'notifyjs-node/dist/notify.js',
   'resources/assets/js/chat.js',
],'public/assets/js/vendor.js'); 

mix.js('resources/assets/js/init.js', 'public/assets/js/app.js');  
mix.js('resources/assets/js/pusher.js', 'public/assets/js/notifs.js');

if (mix.inProduction()) {
      mix.version();
}
