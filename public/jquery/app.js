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
    let $lieuField = $('#sortie_lieu')
    let $form = $field.closest('form')
    let lieu = '#' + $field.attr('id').replace('ville', 'lieu')
    let codePostal = '#' + $field.attr('id').replace('ville', 'codePostal')
    let rue = '#' + $field.attr('id').replace('lieu', 'rue')
    let latitude = '#' + $field.attr('id').replace('lieu', 'latitude')
    let longitude = '#' + $field.attr('id').replace('lieu', 'longitude')
    let data = {}
    data[$villeField.attr('name')] = $villeField.val()
    data[$lieuField.attr('name')] = $lieuField.val()
    $.post($form.attr('action'), data).then(function (data) {
        let $input = $(data).find(lieu)
        $(lieu).replaceWith($input)

        $input = $(data).find(codePostal)
        $(codePostal).replaceWith($input)

        $input = $(data).find(rue)
        $(rue).replaceWith($input)

        $input = $(data).find(latitude)
        $(latitude).replaceWith($input)

        $input = $(data).find(longitude)
        $(longitude).replaceWith($input)
    })
})