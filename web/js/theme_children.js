//dom ready handler
jQuery(function () {
    $('ul.tree > li').attr('unselectable','on').css('user-select','none').on('selectstart',false);
    $('ul.tree').on('click', 'li', function (e) {
        if(window.getSelection){
            // window.getSelection().removeAllRanges();
        }
        //stop propagation else parent li elements click handlers will get triggered
        e.stopPropagation();
        console.log(e.target);
        //use toggleClasss
        $(this).children('ul').toggleClass('hidden visible');
        if($(this).attr('class') != 'noChild')
        {
            $(this).toggleClass('closed open');
        }
    });
});
