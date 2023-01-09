

/************************************************************************************/

function sortableOrderList(itemName){

    div_ID = "sort-"+itemName+"-order";

    var rootDiv = document.getElementById(div_ID);
    var children = rootDiv.children;

    for (var i = 0; i < children.length; i++) {

        var itemID = children[i].getAttribute('value');
        var dom_ID = itemName+'-order-for-'+itemID;

        var inputElement =  document.getElementById(dom_ID);
        inputElement.value = i;
    }
}

/************************************************************************************/

$( "#sort-tournament-order" ).sortable({
    update: function( ) {sortableOrderList('tournament')}
});

/************************************************************************************/

/************************************************************************************/

$("#sort-rules-order" ).sortable({
    update: function(){sortableOrderList('rules')}
});

/************************************************************************************/