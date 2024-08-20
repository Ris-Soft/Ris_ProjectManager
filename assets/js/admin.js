$(document).ready(function () {
    setTimeout(function () {
        search();
    }, 500);
});

document.title = webTitle;
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}
function search_replace() {
    var id = getParameterByName('id');
    if (id === 'home' || id === '' || id === null) {
        search();
        return;
    }
    if ($('#search').val() == undefined) {
        createMessage("请输入搜索内容","danger");
        return;
    }
    history.pushState('', '', './admin.php?s='+$('#search').val());
    fetchAndReplaceContent('./admin.php?s='+$('#search').val(), 'main', 'main')
}
function search() {
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