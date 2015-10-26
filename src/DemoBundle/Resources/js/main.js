(function($) {
    function initSelectize() {
        $('select').selectize();
        $('.selectizable').selectize();
    }

    function initTooltips() {
        $('[data-toggle="tooltip"]').tooltip();
    }

    $(function() {
        app.page = app.page || 'any';

        initSelectize();
        initTooltips();
    });
})(jQuery);
