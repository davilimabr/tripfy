$("#btn-start").click(function(){
    $.ajax({
        url: '/auth',
        type: 'GET',
        data: "",
        beforeSend: function()
        {
            $("body").load("../views/carregamento.html");
        }
    }).done(function(resposta){
        $(location).attr('href', resposta);
    }).fail(function(){
        var div = $("<div />");
        div.load("../views/erro.html");
        
        $("body").append(div);
    });
});