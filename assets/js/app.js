require('jquery');
require('popper.js');
require('bootstrap');


require('select2/dist/js/select2');
// require('../js/default/select2.fr');

require('bootstrap-switch');
require('nouislider');
require('bootstrap-datepicker');

require('../js/now-ui-kit/now-ui-kit');

import '../css/app.scss';

$(document).ready(function () {
    $.fn.select2.defaults.set("theme", "bootstrap");
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

    $(".select2").select2();

    $('.date-picker').each(function () {
        $(this).datepicker({
            format: "dd/mm/yyyy",
            todayBtn: "linked",
            autoclose: true,
            todayHighlight: true,
            templates: {
                leftArrow: '<i class="now-ui-icons arrows-1_minimal-left"></i>',
                rightArrow: '<i class="now-ui-icons arrows-1_minimal-right"></i>'
            }
        }).on('show', function () {
            $('.datepicker').addClass('open');

            let datepicker_color = $(this).data('datepicker-color');
            if (datepicker_color.length !== 0) {
                $('.datepicker').addClass('datepicker-' + datepicker_color + '');
            }
        }).on('hide', function () {
            $('.datepicker').removeClass('open');
        });
    });

});