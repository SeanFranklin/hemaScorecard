/******************************************************************************/

function submitAddMultipleToRound(groupID){
// Used for adding multiple fighters to a round at a time
// The button to do this is inside another form, which needs to have it's fields
// manipulated by JS in order to only submit part of it
	
	var form = document.getElementById('roundRosterForm');
	
	var formName = document.createElement('input');
	formName.type = 'hidden';
	formName.name = 'formName';
	formName.value = 'addMultipleFighterToRound';
	form.appendChild(formName);
	
	var groupIDField = document.createElement('input');
	groupIDField.type = 'hidden';
	groupIDField.name = 'groupID';
	groupIDField.value = groupID;
	form.appendChild(groupIDField);

	form.submit();
}

/************************************************************************************/

function poolNumberChange(callingSelect){
	
	var originalIndex = callingSelect.getAttribute('data-current-index')
	var newIndex = callingSelect.selectedIndex;
	callingSelect.setAttribute('data-current-index',newIndex);
	
	var poolSelectDivs = document.getElementsByClassName('pool-number-select');
	
	if(newIndex < originalIndex){

		for(var i = 0; i < poolSelectDivs.length; i++){
			if(poolSelectDivs[i].selectedIndex >= newIndex &&
				poolSelectDivs[i].selectedIndex <= originalIndex ){
		
			if(poolSelectDivs[i].id == callingSelect.id){continue;}

			poolSelectDivs[i].selectedIndex += 1;
			poolSelectDivs[i].setAttribute('data-current-index',poolSelectDivs[i].selectedIndex);
			}
		}

	} else if(newIndex > originalIndex){
		for(var i = 0; i < poolSelectDivs.length; i++){
			if(poolSelectDivs[i].selectedIndex <= newIndex &&
				poolSelectDivs[i].selectedIndex >= originalIndex ){
		
			if(poolSelectDivs[i].id == callingSelect.id){continue;}

			poolSelectDivs[i].selectedIndex -= 1;
			poolSelectDivs[i].setAttribute('data-current-index',poolSelectDivs[i].selectedIndex);
			}
		}
	}

}

/************************************************************************************/

function reOrderPools(button){
	
	var mainDiv = document.getElementById('poolRosterDiv');
	
	if(button.value == 'editing'){
		// Submit Form
		rosterForm = document.getElementById('poolRosterForm');
		
		var formName = document.createElement('input');
		formName.type='hidden';
		formName.name='formName';
		formName.value='changeGroupOrder';
		
		rosterForm.appendChild(formName);
		rosterForm.submit();
		
	} else {
		// Enable Editing
		button.value = 'editing';
		button.innerHTML = 'Done';
		mainDiv.disabled = true;
		disableFields(mainDiv, true);
	}
	
	function disableFields(mainDiv, isEditing){
		var allInputs = document.getElementsByTagName('input');
		for(var i = 0; i < allInputs.length; i++){
			allInputs[i].disabled = isEditing;
		}
		
		var allSelects = document.getElementsByTagName('select');
		for(var i = 0; i < allSelects.length; i++){
			allSelects[i].disabled = isEditing;
		}
		
		var allButtons = document.getElementsByTagName('button');
		for(var i = 0; i < allButtons.length; i++){
			
			if(allButtons[i].classList.contains('dont-disable')){continue;}
			allButtons[i].disabled = isEditing;
		}
	
		var opacityElements = mainDiv.getElementsByClassName('opacity-toggle');
		for(var i = 0; i < opacityElements.length; i++){
		
			if(isEditing){
				if(opacityElements[i].tagName == 'SELECT'){
					opacityElements[i].style.opacity = '0.2';
				} else {
					opacityElements[i].style.opacity = '0.6';
				}
			} else {
				opacityElements[i].style.opacity = 1;
			}
		}
		

		var poolNameDivs = document.getElementsByClassName('hide-toggle');
		for(var i = 0; i < poolNameDivs.length; i++){
			if(poolNameDivs[i].offsetHeight == 0){
				poolNameDivs[i].style.display = 'inline';	
			} else {
				poolNameDivs[i].style.display = 'none';	
			}
		}
		
		var poolSelectDivs = mainDiv.getElementsByClassName('pool-number-select');
		for(var i = 0; i < poolSelectDivs.length; i++){
			poolSelectDivs[i].disabled = !poolSelectDivs[i].disabled;
		}
		
	}
	
}
