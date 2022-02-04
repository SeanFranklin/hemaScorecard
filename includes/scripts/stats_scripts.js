/**********************************************************************/

function matchLengthEventSelect(index){

	eventID = document.getElementById('match-length-event-'+index).value;

	var query = "mode=getEventTournaments&eventID="+eventID;

	var xhr = new XMLHttpRequest();
	xhr.open("GET", AJAX_LOCATION+"?"+query, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send();

	xhr.onreadystatechange = function (){
		if(this.readyState == 4 && this.status == 200){
			if(this.responseText.length > 1){
				
				tournamentList = JSON.parse(this.responseText);

				select = document.getElementById('match-length-tournament-'+index);
				select.length = 0;

				var option = document.createElement('option');
				option.value = 0;
				option.selected = true;
				select.appendChild(option);

				for(var i in tournamentList){
					var option = document.createElement('option');
					option.value = tournamentList[i]['tournamentID'];
					option.innerHTML = tournamentList[i]['name'];
					select.appendChild(option);
				}

			}
		}
	}
}