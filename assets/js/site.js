$(document).ready(function () {
    //alert('Hello World!');
    $("form[name='destroy']").on('submit', function (submit) {
        var confirm_message = $(this).attr('data-confirm');
        if (!confirm(confirm_message)) {
            submit.preventDefault();
        }
    });

    $('a#disabled').on('click', function (e) {
        e.preventDefault();
    });

});

// TODO INDEKSÖINTI RIKKI
function newKeywordInput(option) {
    var parentElement = document.createElement("div");
    parentElement.setAttribute("class", 'form-group');
    var element = document.createElement("input");

    element.setAttribute("type", 'text');
    var fieldName = "keyword";
    if (option == 0) {
        fieldName += "New";
    }
    var ingredientField = fieldName + "[" + indexCountForKeyword++ + "]";

    element.setAttribute("name", ingredientField);
    element.setAttribute("class", 'form-control');

    var ingredientElement = document.getElementById("newKeyword");
    parentElement.appendChild(element);
    ingredientElement.appendChild(parentElement);
}

function newInput(option) {

    var quantityElement = document.createElement("input");
    var ingredientElement = document.createElement("input");
    var tableElement = document.createElement("table");
    var tr = document.createElement("tr");
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");

    tableElement.setAttribute("class", 'form-group');

    ingredientElement.setAttribute("type", 'text');
    quantityElement.setAttribute("type", 'text');
    var qFieldName = "quantity";
    var iFieldName = "ingredient";
    if (option == 0) {
        qFieldName += "New";
        iFieldName += "New";
    }

    var quantityField = qFieldName + "[" + indexCountForIngredient + "]";
    var ingredientField = iFieldName + "[" + indexCountForIngredient++ + "]";

    ingredientElement.setAttribute("name", ingredientField);
    ingredientElement.setAttribute("class", 'form-control');

    quantityElement.setAttribute("name", quantityField);
    quantityElement.setAttribute("class", 'form-control');
    td1.appendChild(quantityElement);
    td2.appendChild(ingredientElement);

    tr.appendChild(td1);
    tr.appendChild(td2);
    tableElement.appendChild(tr);


    var ingredientElement = document.getElementById("newIngredient");

    ingredientElement.appendChild(tableElement);
}
