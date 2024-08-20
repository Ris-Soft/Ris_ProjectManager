$(document).ready(function () {
    setTimeout(function () {
        search();
    }, 500);
});

document.title = webTitle;
function search_replace() {
    if ($('#search').val() == null) {
        return;
    }
    const searchTerm = $('#search').val().toLowerCase();
    if (searchTerm !== "") {
        $('#noticeText').text('“' + searchTerm + '”的搜索结果');
    } else {
        $('#noticeText').text('全部项目');
    }
    $('.project-link').each(function () {
        const projectName = $(this).find('h3').text().toLowerCase();
        const projectDescribe = $(this).find('p').text().toLowerCase();
        if (projectName.includes(searchTerm) || projectDescribe.includes(searchTerm)) { // 如果项目名称包含搜索词
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}