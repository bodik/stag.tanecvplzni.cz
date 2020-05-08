function updatePayments(url) {
    var currentUrl = window.location.href;
    $.ajax({
        url: url,
        beforeSend: function () {
            $('#spinner').center();
            $('#blackCover').toggle();
            $('#spinner').toggle();
        },
        success: function (response) {
            if (response === 'success') {
                $.redirect(currentUrl, {event: "paymentUpdates", type: "success", message: "Payments successfuly updated"}, 'POST', '_self')
            } else {
                $.redirect(currentUrl, {event: "paymentUpdates", type: "error", message: "Error. No payments were updated"}, 'POST', '_self')
            }
        },
        error: function () {
            $.redirect(currentUrl, {event: "paymentUpdates", type: "error", message: "Error. No payments were updated"}, 'POST', '_self')
        },
        complete: function () {
            $('#blackCover').toggle();
            $('#spinner').toggle();
        }
    });
}

jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +
        $(window).scrollTop()) + "px");
    this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +
        $(window).scrollLeft()) + "px");
    return this;
}