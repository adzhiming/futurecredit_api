/**
 * Created by Administrator on 2017/9/1.
 */


$(document).ready(function() {

    $('a').on('click touchend', function(e) {
        var el = $(this);
        var link = el.attr('href');
        window.location = link;
    });

});