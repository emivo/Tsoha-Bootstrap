$(document).ready(function(){
    //alert('Hello World!');
    $("form[name='destroy']").on('submit', function(submit){
        var confirm_message = $(this).attr('data-confirm');
        if(!confirm(confirm_message)){
            submit.preventDefault();
        }
    });
    //function editComment() {
    //    // TÄMÄ EI TOIMI VIELÄ
    //    var e = document.createElement("form");
    //    e.setAttribute("id", 'editCommentForm');
    //    e.setAttribute("method", 'post');
    //    e.setAttribute("action", '{{base_path}}/recipe/{{recipe.id}}/comment/{{user_logged_in.id}}/edit');
    //    e.setAttribute("Label", 'muokkaa kommentia')
    //    var element = document.createElement("div");
    //    element.setAttribute("class", 'form-group');
    //    var elemdiv = document.createElement("div");
    //    elemdiv.setAttribute("class", 'row');
    //    var elemtrat = document.createElement("div");
    //    elemtrat.setAttribute("class", 'col-xs-2');
    //    var select = document.createElement("select");
    //    select.setAttribute("name", 'rating');
    //    select.setAttribute("class", 'form-control');
    //    for (i = 1; i <= 5; i++) {
    //        var sele = document.createElement("option");
    //        select.appendChild(sele);
    //    }
    //    elemtrat.appendChild(select);
    //
    //    var elemtrat2 = document.createElement("div");
    //    elemtrat2.setAttribute("class", 'col-xs-3');
    //    var commentInput = document.createElement("input");
    //    commentInput.setAttribute("class", 'form-control');
    //    commentInput.setAttribute("name", 'comment');
    //    commentInput.setAttribute("type", 'text');
    //    commentInput.setAttribute("value", '{{comment.comment}}');
    //    elemtrat2.appendChild(commentInput);
    //    elemdiv.appendChild(elemtrat);
    //    elemdiv.appendChild(elemtrat2);
    //    element.appendChild(elemdiv);
    //    e.appendChild(element);
    //    var editForm = document.getElementById("{{user_logged_in.id}}");
    //    editForm.appendChild(e);
    //
    //    var button = document.createElement("button");
    //    button.setAttribute("form", 'editCommentForm');
    //    button.setAttribute("formmethod", 'post');
    //    button.setAttribute("formaction", '{{base_path}}/recipe/{{recipe.id}}/comment/{{user_logged_id.id}}/edit');
    //
    //    document.getElementById("editCommentButton").replaceWith(button);
    //}
});
function newKeywordInput(option) {
    var indexCount = option;
    var parentElement = document.createElement("div");
    parentElement.setAttribute("class", 'form-group');
    var element = document.createElement("input");

    element.setAttribute("type", 'text');
    var fieldName = "keyword";
    if (option == 0) {
        fieldName += "New";
    }
    var ingredientField = fieldName + "[" + indexCount++ + "]";

    element.setAttribute("name", ingredientField);
    element.setAttribute("class", 'form-control');

    var ingredientElement = document.getElementById("newKeyword");
    parentElement.appendChild(element);
    ingredientElement.appendChild(parentElement);
}
function newInput() {
    var indexCount = 1;
    var quantityElement = document.createElement("input");
    var ingredientElement = document.createElement("input");
    var tableElement = document.createElement("table");
    var tr = document.createElement("tr");
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");

    tableElement.setAttribute("class", 'form-group');

    ingredientElement.setAttribute("type", 'text');
    quantityElement.setAttribute("type", 'text');

    var quantityField = "quantity[" + indexCount + "]";
    var ingredientField = "ingredient[" + indexCount++ + "]";

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