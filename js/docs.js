$(function() {
	$("body > aside.sidebar").load("aside.html");

    if (location.hash !== "") {
        var url = window.location.href.replace('#', '') + '.html';
        $("body > main.content").load(url);
    }

    $(".sidebar").on("click", ".nav li a", function(e){
        window.location = e.target.href;
        window.location.reload();
    });
});