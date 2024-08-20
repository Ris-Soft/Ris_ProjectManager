function bindThings() {
    $('img').on('click', function () {
        var overlay = $('#overlay');
        var largeImage = $('#largeImage');
        $('#pageTitle').text

        largeImage.attr('src', $(this).attr('src'));
        overlay.fadeIn();
    });

    $('.hostLink').on('click', function (event) {
        event.preventDefault();
        history.pushState('', '', this.href);
        fetchAndReplaceContent(this.href, 'main', 'main')
    });

    $('#overlay').on('click', function (event) {
        if ($(event.target).is(this)) {
            $(this).fadeOut();
        }
    });
    $(document).on('keydown', function (event) {
        if (event.keyCode === 13) {
            if (document.activeElement.id == "search") {
                search_replace();
            }
        }
    });
}

$(document).ready(function () {
    setTimeout(function () {
        if ($('#search').length <= 0) {
            insertElementToNav(`<span class="flex items-center">
<input type="text" name="search" id="search" placeholder="搜索项目名称.." value="${searchValue}" class="textEditor textEditor-success textEditor-maxWidth" style="margin-bottom: 0px;font-size: 16px;"><a href="javascript:search_replace()"><i class="bi bi-search"></i></a></span>`, 'left')
        }
         bindThings();
    }, 500);
});
