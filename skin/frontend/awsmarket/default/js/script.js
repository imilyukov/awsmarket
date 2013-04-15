(function($){
    $.noConflict();
    $(window).load(function() {



        // Функция выпадающего меню

        $('.mainNav > ul > li').hover(function(){
            $(this).children('ul').slideToggle();
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



        $(".productSlider").jCarouselLite({
            btnNext: ".next",
            btnPrev: ".prev",
            vertical: true,
            visible: 2,
            circular: false
        });


        $(".sortingMenu select").chosen();

    });
})(jQuery);