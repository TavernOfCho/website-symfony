require('jquery');
require('popper.js');
require('bootstrap');


require('select2/dist/js/select2');
require('../js/default/select2.fr');

import '../css/app.scss';

$(document).ready(function () {
    $.fn.select2.defaults.set("theme", "bootstrap");
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

    $(".select2").select2({
        language: 'fr'
    });

});