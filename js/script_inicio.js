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
        $("body").load("../views/erro.html");
    });
});