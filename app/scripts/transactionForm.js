var $person = $("#transaction_person");
// When person gets selected ...
$person.change(function () { // ... retrieve the corresponding accounts.
    var $form = $(this).closest('form');
    // Simulate account data, but only include the selected person value.
    var data = {};
    data[$person.attr('name')] = $person.val();
    // Submit data via AJAX to the form's action path.
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) { // Replace current account field ...
            $('#transaction_account').replaceWith(
                // ... with the returned ones from the AJAX response.
                $(html).find('#transaction_account')
            );
            // Account field now displays the appropriate accounts.
        }
    });
});