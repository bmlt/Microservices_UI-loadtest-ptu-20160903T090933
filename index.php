<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

<?php

include 'getItems.php';
$result = RetrieveItems();

?>

<script>
var RETRY_INTERVAL = 5000;
var items = <?php echo $result?>;

function loadItems(items){
    if (items.error !== undefined) {
        reloadCatalog();
        return;
    }
    var i = 0;
    console.log("Load Items: " + items.rows);
    document.getElementById("loading").innerHTML = "";
    for(i = 0; i < items.rows.length; ++i){
        addItem(items.rows[i].doc);
    }
}

function reloadCatalog() {
    showErrorMessage("The catalog is not currently available, retrying...");
    window.setTimeout(
        function() {
            $.ajax ({
                type: "GET",
                contentType: "application/json",
                url: "ajaxGetItems.php",
                success: function(result) {
                    loadItems(JSON.parse(result));
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    reloadCatalog();
                } 
            })
        },
        RETRY_INTERVAL
    );
}

function showErrorMessage(message) {
    document.getElementById("loading").innerHTML = message;
}

function addItem(item){
	var div = document.createElement('div');
	div.className = 'item';
	div.innerHTML = "<div class ='well'><img width='100%' height='auto' src = '"+item.imgsrc+"'/><br><button onclick='orderItem(\""+item._id+"\")'><b>Buy</b></button><br><u>"+item.name+"</u><br>"+item.description+"<br><b>$"+item.usaDollarPrice + "</b></div>";
	document.getElementById('boxes').appendChild(div);
}

function orderItem(itemID){
    //create a random customer ID and count
    var custID = Math.floor((Math.random() * 999) + 1); 
    var count = Math.floor((Math.random() * 9999) + 1);
    var expedite = window.location.search.indexOf("?expedite") ? 0 : 1;
    var myjson = {"itemid": itemID, "customerid": custID, "count": count, "expedite": expedite};
    
    $.ajax ({
    	type: "POST",
    	contentType: "application/json",
	    url: "submitOrders.php",
	    data: JSON.stringify(myjson),
	    dataType: "json",
	    success: function( result ) {
	        if(result.httpCode != "201" && result.httpCode != "200"){
	        	alert("Failure: check that your JavaOrders API App is running and your user-provided service has the correct URL.");
	        }
	        else{
	        	alert("Order Submitted! Check your Java Orders API to see your orders: \n" + result.ordersURL);
	        }
	    },
	    error: function(XMLHttpRequest, textStatus, errorThrown) { 
	    	alert("Error");
        	console.log("Status: " , textStatus); console.log("Error: " , errorThrown); 
    }  
	});

}

</script>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<link rel="stylesheet" href="style.css">
</head>
<table class="headerTable">
	<tr>
		<td><span class="pageTitle"><h1>Microservices Sample</h1></span></td> 
	</tr>
</table>
<body onload="loadItems(items)">
	<div class="container">
		<div id='boxes' class="notes"></div>
	</div>
	<div id="loading"><br>Loading...</div>
</body>
</html>

