$("#form-travel").submit(function(e){
    e.preventDefault();
    var dados = jQuery(this).serialize();
    var url = jQuery(this).attr('action');
    
    $.ajax({
        url: url,
        type: 'post',
        data: dados,
        beforeSend: function()
        {
            $("body").load("../views/carregamento.html");
        }
    }).done(function(){

        $.ajax({
            url: '/create-playlist',
            type: 'get',
            beforeSend: function()
            {
                $("body").load("../views/carregamento.html");
            }
        }).done(function(result){
            $(location).attr('href', "https://open.spotify.com/playlist/" + result);

        }).fail(function(){
            var div = $("<div />");
            div.load("../views/erro.html");
            
            $("body").append(div);
        });
        
    }).fail(function(dado){
        var div = $("<div />");
        div.load("../views/erro.html");
        
        $("body").append(div);
    });
});