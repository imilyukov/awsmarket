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

        $("select").chosen();

        $(".question").click(function(e){
            e.preventDefault();
            $(this).toggleClass('active');
            $(this).next('.answer').slideToggle('fast');
        });

        $('input').iCheck({
            checkboxClass: 'icheckbox_flat',
            radioClass: 'iradio_flat'
        });

        // for checkout
        function addCheckStyle(elementIds) {

            var self = this, selector = (elementIds || []).join(),
                onSave = $.proxy(self.onSave, self);

            self.onSave = $.proxy(function(response, json){

                onSave(response, json);
                $(selector).iCheck({
                    checkboxClass: 'icheckbox_flat',
                    radioClass: 'iradio_flat'
                }).each(function(){

                    var element = this, label = $("label[for='" + $(element).attr('id') + "']");
                    $.each([label, $('ins', $(element).closest('div'))], function(){
                        $(this).on('click', function(e){

                            $(element).trigger('click');
                            e.stopPropagation();
                        });
                    });
                });

                $(".checkout select").chosen();

            }, self);
        }

        if ( window.billing && window.billing.onSave ) {

            $.proxy(addCheckStyle, window.billing)(['#s_method_flatrate_flatrate']);
        }

        if ( window.shipping && window.shipping.onSave ) {

            $.proxy(addCheckStyle, window.shipping)(['#s_method_flatrate_flatrate']);
        }

        if ( window.shippingMethod && window.shippingMethod.onSave ) {

            $.proxy(addCheckStyle, window.shippingMethod)(['#p_method_ccsave', '#p_method_paybyway']);
        }

        // add handler for filter form
        $('#sup__form_filter select').on('change', function(e) {

            $(this).closest('form').trigger('submit');
        });

        // pop - up
        $('.promo a, .prodList a, a.product-image, a.product-name, .product-name a').on('click', function popupHandler(e) {

            $('.close-popup').trigger('click');
            var url = $(this).attr('href');
            $('<div />', {class: 'overlay'}).appendTo('body');
            $.ajax({
                url: url,
                success: function loadCart(data) {

                    $('body').append(data);

                    var context = $('.popup');

                    $('.close-popup', context).on('click', function(e) {

                        $(context).prev().remove();
                        $(context).remove();
                        e.preventDefault();
                    });

                    $('.select-color-btn, .select-size-btn', context).on('click', popupHandler);

                    if ( !$('.additional-popup-images a', context).length ) {return;}

                    $(".additional-popup-images > div", context).jCarouselLite({
                        btnNext: ".next-img",
                        btnPrev: ".prev-img",
                        vertical: false,
                        visible: 3,
                        circular: false
                    });

                    var mainContainer = $('.main-popup-image > div', context), iviewer = {},
                        src = $('img', mainContainer).attr('src');

                    $('img', mainContainer).remove();
                    mainContainer.iviewer({
                        src: src,
                        ui_disabled: true,
                        zoom: 100,
                        onStartDrag: function() {

                        },
                        onDrag: function() {

                        },
                        initCallback: function(){

                            iviewer = this;
                            $(".enlarge-plus", context).on('click.zoom', function(){ iviewer.zoom_by(1);});
                            $(".enlarge-minus", context).on('click.zoom', function(){ iviewer.zoom_by(-1);});
                            $(".full-screen-btn", context).on('click.zoom', function(){ iviewer.set_zoom(100); });
                        }
                    });

                    $('.additional-popup-images a', context).on('click', function(e) {

                        var self = this, uri = $(self).attr('href'), index = $(self).data('index');

                        iviewer.loadImage(uri);

                        $('.full-screen-btn', context).attr('href', uri);

                        // counter-img
                        $('.counter-img', context).text(index + '/' + $('.additional-popup-images a', context).length);

                        e.preventDefault();
                        e.stopPropagation();
                    }).eq(0).trigger('click');

                    // cart form
                    /*$('#product_addtocart_form', context).ajaxForm({
                        beforeSubmit: function() {

                            $(context).prev().css({'zIndex': 10000});
                        },
                        success: function(data) {

                            $(context).remove();
                            loadCart(data);
                            $('.popup').prev().css({'zIndex': 1000});
                        }
                    });*/
                    $('#product_addtocart_form', context).append($('<input>', {
                        name: 'return_url',
                        value: window.location.href,
                        type: 'hidden'
                    }));

                },
                cache: true
            });
            e.preventDefault();
        });
    });
})(jQuery);