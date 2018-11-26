require('jquery');
require('popper.js');
require('bootstrap');


require('select2/dist/js/select2');
require('../js/default/select2.fr');

require('bootstrap-switch');
require('nouislider');
require('bootstrap-datepicker');

require('../js/now-ui-kit/now-ui-kit');

import '../css/app.scss';
import '../img/blurred-image-1.jpg'

$(document).ready(function () {
    $.fn.select2.defaults.set("theme", "bootstrap");
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

    $(".select2").select2({
        language: 'fr'
    });

});