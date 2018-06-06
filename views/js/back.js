/**
 * @author    debuss-a <alexandre@common-services.com>
 * @copyright Copyright (c) 2018 Common-Services
 * @license   CC BY-SA 4.0
 */

$(document).ready(function () {

    function isValidAjaxResponse(response, keys) {
        if (typeof response === 'undefined' || response == null) {
            return false;
        }

        for (var i in keys) {
            var ind = keys[i];

            if (typeof(response[ind]) === 'undefined' || response[ind] == null) {
                return false;
            }
        }

        return true;
    }

    // Nav
    var conf_nav = $('#conf-nav');
    conf_nav.find('li').click(function() {
        var position = $(this).index();
        var conf_divs = $('#module_form').find('div.panel[id^="fieldset_"]');
        var visible_div_id = parseInt(conf_divs.prevObject.find('div.panel:visible').attr('id').substr(9, 1));

        if (position === visible_div_id) {
            return false;
        }

        conf_nav.find('li').removeClass('active');
        conf_divs.fadeOut('fast');
        $(this).addClass('active');
        conf_divs.eq(position).delay(195).fadeIn('slow');

        $('#selected_tab').val($(this).attr('id'));
    });

    // Selected tab
    var selected_tab = $('#selected_tab');
    if (selected_tab.val() !== 'nav-authentication') {
        conf_nav.find('#' + selected_tab.val()).click();
    }

    // Status Add
    $('.addStatus').click(function () {
        var selected_status = $(this).parent().find('select').val();
        var status_selector = [];

        if (!selected_status) {
            return false;
        }

        for (var i = 0, len = selected_status.length; i < len; i++) {
            status_selector.push('.availableStatus option[value="' + selected_status[i] + '"]');
        }

        status_selector = status_selector.join(', ');

        $(this).parent().find('option:selected').appendTo($(this).parent().parent().find('.selectedStatus'));
        $('#status_mapping').find(status_selector).remove();
    });

    // Status Remove
    $('.removeStatus').click(function () {
        var selected_status = $(this).parent().find('select').val();
        var status_selector = [];

        if (!selected_status) {
            return false;
        }

        for (var i = 0, len = selected_status.length; i < len; i++) {
            status_selector.push('.availableStatus option[value="' + selected_status[i] + '"]');
        }

        status_selector = status_selector.join(', ');

        $(this).parent().find('option:selected').appendTo('.availableStatus');
        $('#status_mapping').find(status_selector).show();
    });

    // Cronjobs
    $('#cronjobs_install').click(function () {
        $.ajax({
            type: 'POST',
            url: $('#cronjobs_url').val(),
            data: {
                'url': $('#module_cron_url').val()
            },
            dataType: 'json',
            success: function (data) {
                window.console && console.log(data);

                if (isValidAjaxResponse(data, ['success', 'message'])) {

                    if (data['message']) {
                        showNoticeMessage($('#cronjobs_error').val() + data['message']);
                    } else {
                        showSuccessMessage($('#cronjobs_success').val());
                    }
                } else {
                    showErrorMessage($('#cronjobs_not_valide_ajax').val());
                }
            },
            error: function (err) {
                window.console && console.log(err);

                showErrorMessage(err.responseText);
            }
        });
    });

    // Before submitting form :
    //    * Select selected status
    $('button[name="submitLaPosteSuiviModule"]').click(function () {
        $('.selectedStatus').find('option').attr('selected', true);
    });

});