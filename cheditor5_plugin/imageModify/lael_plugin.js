
function showEditwin(url,width,height){
	var sst = window.open(url,'popwin','top='+((screen.availHeight - height)/2 - 40) +', left='+(screen.availWidth - width)/2+', width='+width+', height='+height+', toolbar=0, directories=0, status=0, menubar=0, scrollbars=0, resizable=1');
	if(sst){
		sst.focus();
	}
}


function showEdit(event,L,T,imgobj,self_id) {
// ----------------------------------------------------------------------------------
	getDivCoordinates();
	//alert();

	var self = this;
	var button = document.getElementById('editImage');

	self.className = 'imageBox_theImage_over';
	button.style.left = (L + 106) + 'px';
	button.style.top = (T - 7) + 'px';
	button.style.display = 'block';
	
	button.onclick = function() {
		showEditwin(oEditor.config.editorPath + 'imageModify/modify.php?self_id='+self_id+'&import='+encodeURIComponent(imgobj.firstChild.src),1000,740);
	};

}

function hideEdit(event) {
// ----------------------------------------------------------------------------------
	document.getElementById('editImage').style.display = 'none';
}


function showDelete(event) {
// ----------------------------------------------------------------------------------
	
	getDivCoordinates();

	var self = this;
	var button = document.getElementById('removeImage');
	var L = divXPositions[self.parentNode.id];
	var T = divYPositions[self.parentNode.id];

	self.className = 'imageBox_theImage_over';
	button.style.left = (L + 126) + 'px';
	button.style.top = (T - 7) + 'px';
	button.style.display = 'block';
	button.onmouseover = function() {
		self.className = 'imageBox_theImage_over';
		document.getElementById('selectedImageWidth').innerHTML = imageCompletedList[self.id]['width'];
		document.getElementById('selectedImageHeight').innerHTML = imageCompletedList[self.id]['height'];
	};
	
	document.getElementById('selectedImageWidth').innerHTML = imageCompletedList[self.id]['width'];
	document.getElementById('selectedImageHeight').innerHTML = imageCompletedList[self.id]['height'];

	button.onclick = function() {
		create_request_object(DeleteScript + '?img=' + self.firstChild.src);
		self.removeChild(self.firstChild);
		self.onmouseover = null;
		self.className = 'imageBox_theImage';
		document.getElementById('editImage').style.display = 'none';
		document.getElementById('removeImage').style.display = 'none';

		if (self.parentNode.nextSibling && self.parentNode.nextSibling.id)
		{
			var wrapper = document.getElementById('imageListWrapper');
			var moveobj = self.parentNode.nextSibling;
			var target = self.parentNode;

			while (moveobj != null) {
				wrapper.insertBefore(moveobj, target);
				moveobj = target.nextSibling;
			}
		}

		resetSelectedImageSize();
		reOrder();
	};

	if (hideTimer) clearTimeout(hideTimer);
	hideTimer = setTimeout('hideDelete()', 3000);

	showEdit(event,L,T,self,self.id);

}

function hideDelete(event) {
// ----------------------------------------------------------------------------------
	hideEdit(event);
	document.getElementById('removeImage').style.display = 'none';
}