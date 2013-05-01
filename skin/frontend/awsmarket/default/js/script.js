(function($){
    $.noConflict();
    $(window).load(function() {

        // Функция выпадающего меню
        $('.mainNav > ul > li').hover(function(e){

            $('ul', this).slideDown();
            e.preventDefault();
        }, function(e){

            $('ul', this).slideUp();
            e.preventDefault();
        });

        // Функция очистки поля поиска при фокусе

        $(function() {
            $.fn.autoClear = function () {
                $(this).each(function() {
                    $(this).data("autoclear", $(this).attr("value"));
                });
                $(this)
                    .bind('focus', function() {
                        if ($(this).attr("value") == $(this).data("autoclear")) {
                            $(this).attr("value", "").addClass('autoclear-normalcolor');
                        }
                    })
                    .bind('blur', function() {
                        if ($(this).attr("value") == "") {
                            $(this).attr("value", $(this).data("autoclear")).removeClass('autoclear-normalcolor');
                        }
                    });
                return $(this);
            }
        });

        $(function() {
            $('#query').autoClear();
        });

        if ( $(".productSlider ul li").length > 2 ) {

            $(".productSlider").jCarouselLite({
                btnNext: ".next",
                btnPrev: ".prev",
                vertical: true,
                visible: 2,
                circular: false
            });
        } else {

            $('.productSliderWrapper button').hide();
            $('.productSliderWrapper, .productSlider').addClass('height-auto');
        } // if

        $(".sortingMenu select, .checkout select").chosen();

        $(".question").click(function(e){
            e.preventDefault();
            $(this).toggleClass('active');
            $(this).next('.answer').slideToggle('fast');
        });

        $('input').iCheck({
            checkboxClass: 'icheckbox_flat',
            radioClass: 'iradio_flat'
        });

        // add handler for filter form
        $('#sup__form_filter select').on('change', function(e) {

            console.log($(this).val());
            $(this).closest('form').trigger('submit');
        });

    });
})(jQuery);