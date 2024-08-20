document.title = webTitle;
function search_replace() {
    if ($('#search').val() == undefined) {
        createMessage("请输入搜索内容","danger");
        return;
    }
    history.pushState('', '', './?s='+$('#search').val());
    fetchAndReplaceContent('./?s='+$('#search').val(), 'main', 'main')
}