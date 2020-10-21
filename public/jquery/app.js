/**
 * gestion ajout de photo
 */
$('.custom-file-input').on('change', function (event) {
    var inputFile = event.currentTarget;
    $(inputFile).parent()
        .find('.custom-file-label')
        .html(inputFile.files[0].name);
})


/**
 * gestion champ de formulaire sortie
 */
$(document).on('change', '#sortie_ville, #sortie_lieu', function () {
    let $field = $(this)
    let $villeField = $('#sortie_ville')
    let $form = $field.closest('form')
    let target = '#' + $field.attr('id').replace('lieu').replace('ville', 'lieu')
    let target2 = '#' + $field.attr('id').replace('lieu').replace('ville', 'codePostal')
    let data = {}
    data[$villeField.attr('name')] = $villeField.val()
    $.post($form.attr('action'), data).then(function (data)
    {
        let $input = $(data).find(target)
        $(target).replaceWith($input)
        $input = $(data).find(target2)
        $(target2).replaceWith($input)
    })
})
// $(document).on('change', '#sortie_lieu', function (){
//     let $field = $(this)
//     let $lieuField = $('#sortie_lieu')
//     let $form = $field.closest('form')
//     let data = {}
//     data[$lieuField.attr('name')] = $lieuField.val()
//     $.post($form.attr('action'), data).then(function (){
//         let $input = $(data).find('#sortie_rue')
//         $('#sortie_rue').replaceWith($input)
//     })
// })
