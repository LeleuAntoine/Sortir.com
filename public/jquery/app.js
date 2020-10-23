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
    let $lieuField =$('#sortie_lieu')
    let $form = $field.closest('form')
    let target = '#' + $field.attr('id').replace('ville', 'lieu')
    let target2 = '#' + $field.attr('id').replace('ville', 'codePostal')
    let target3 = '#' + $field.attr('id').replace('lieu', 'rue')
    let target4 = '#' + $field.attr('id').replace('lieu', 'latitude')
    let target5 = '#' + $field.attr('id').replace('lieu', 'longitude')
    let data = {}
    data[$villeField.attr('name')] = $villeField.val()
    data[$lieuField.attr('name')] = $lieuField.val()
    $.post($form.attr('action'), data).then(function (data)
    {
        let $input = $(data).find(target)
        $(target).replaceWith($input)

        $input = $(data).find(target2)
        $(target2).replaceWith($input)

        $input = $(data).find(target3)
        $(target3).replaceWith($input)

        $input = $(data).find(target4)
        $(target4).replaceWith($input)

        $input = $(data).find(target5)
        $(target5).replaceWith($input)
    })
})