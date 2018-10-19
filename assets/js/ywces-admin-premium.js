jQuery(function ($) {

    $('body')
        .on('click', 'button.ywces-purge-coupon', function () {

            var result = $('.ywces-clear-result'),
                data = {
                    action: 'ywces_clear_expired_coupons'
                };

            result.show();
            $(this).hide();

            $.post(ywces_admin.ajax_url, data, function (response) {

                result.removeClass('clear-progress');

                if (response.success) {

                    result.addClass('clear-success');
                    result.html(response.message);

                } else {

                    result.addClass('clear-fail');
                    result.html(response.error);

                }

            });

        });

    $(document).ready(function ($) {

        var collapse = $('.ywces-collapse');

        collapse.each(function () {
            $(this).toggleClass('expand').nextUntil('tr.ywces-collapse').slideToggle(100);
        });

        collapse.click(function () {
            $(this).toggleClass('expand').nextUntil('tr.ywces-collapse').slideToggle(100);
        });

        $('#ywces_mail_template_enable').change(function () {

            if ($(this).is(':checked')) {

                $('#ywces_mail_template').val('base').prop("disabled", true);
                $('.ywces-mailskin').hide();


            } else {

                $('#ywces_mail_template').prop("disabled", false);
                $('.ywces-mailskin').show();

            }

        }).change();

    });

});
