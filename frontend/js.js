$("#button-blue").on("click", function () {
  var txt_nome = $("#name").val();
  var txt_email = $("#email").val();
  var txt_comentario = $("#comment").val();

  $.ajax({
    url: "http://php-backend-service/index.php",

    type: "post",
    data: { nome: txt_nome, comentario: txt_comentario, email: txt_email },
    beforeSend: function () {
      console.log("Tentando enviar os dados....");
    },
  })
    .done(function (e) {
      alert("Dados Salvos");
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
      console.error(
        "Erro ao enviar dados:",
        textStatus,
        errorThrown,
        jqXHR.responseText
      );
      alert("Erro ao salvar dados. Verifique o console.");
    });
});
