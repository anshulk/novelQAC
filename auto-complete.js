var MIN_LENGTH = 1;
$(document).ready(function () {
    $('#in').hide();
    setCookie("id", "0",30);
    $('#fetchtrends').click(function () {
        $.get("trends.php", {}).done(function (data) {
            var str = jQuery.parseJSON(data);
            var str1 = str.join('\n');
            alert("Trending Topics for India \n\n" + str1);
        });
    });
    $('#fetchtrends1').click(function () {
        $.get("trends.php", {}).done(function (data) {
            var str = jQuery.parseJSON(data);
            var str1 = str.join('\n');
            alert("Trending Topics for India \n\n" + str1);
        });
    });
    $("#keyword").keyup(function (event) {
        var keyword = $("#keyword").val();

        if (event.keyCode == 13) {
            $.get("entered.php", {
                keyword: keyword,
                id: getCookie("id")
            }).done(function () {
                window.location.replace("http://localhost")
            });
            //"http://www.google.com/search?#newwindow=1&q="+keyword)});
        } else if (keyword.length >= MIN_LENGTH) {
            console.log("Sending id " + getCookie("id"));
            $.get("auto-complete.php", {
                keyword: keyword,
                id: getCookie("id")
            })
                .done(function (data) {
                    $('#results').html('');
                    var results = jQuery.parseJSON(data);
                    $(results).each(function (key, value) {
                        $('#results').append('<div class="item">' + value + '</div>');
                    })

                    $('.item').click(function () {
                        var text = $(this).html();
                        $('#keyword').val(text);
                    })

                });
        } else {
            $('#results').html('');
        }
    });

    $("#keyword").blur(function () {
        //$("#results").fadeOut(500);
    })
        .focus(function () {
            $("#results").show();
        });

});