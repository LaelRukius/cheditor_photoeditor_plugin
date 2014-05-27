
function showEditWin(url,width,height){
	var sst = window.open(url,'popwin','top='+((screen.availHeight - height)/2 - 40) +', left='+(screen.availWidth - width)/2+', width='+width+', height='+height+', toolbar=0, directories=0, status=0, menubar=0, scrollbars=0, resizable=1');
	if(sst){
		sst.focus();
	}
}


function showEdit(event,L,T,imgobj,self_id) {
// ----------------------------------------------------------------------------------

	var button = document.getElementById('editImage');

	button.style.left = (L + 95) + 'px';
	button.style.top = (T - 7) + 'px';
	button.style.display = 'block';
	
	button.onclick = function() {
		showEditWin(oEditor.config.editorPath + 'imageModify/modify.php?self_id='+self_id+'&import='+encodeURIComponent(imgobj.firstChild.src),1000,740);
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
	button.style.left = (L + 115) + 'px';
	button.style.top = (T - 7) + 'px';
	button.style.display = 'block';
	button.onmouseover = function(ev) {
		self.className = 'imageBox_theImage_over';
		document.getElementById('selectedImageWidth').innerHTML = imageCompletedList[self.id]['width'];
		document.getElementById('selectedImageHeight').innerHTML = imageCompletedList[self.id]['height'];
        document.getElementById('selectedImageName').innerHTML = imageCompletedList[self.id]['origName'];
	};

	document.getElementById('selectedImageWidth').innerHTML = imageCompletedList[self.id]['width'];
	document.getElementById('selectedImageHeight').innerHTML = imageCompletedList[self.id]['height'];
    document.getElementById('selectedImageName').innerHTML = imageCompletedList[self.id]['origName'];

	button.onclick = function() {
        imageDelete(imageCompletedList[self.id]['filePath']);
		self.removeChild(self.firstChild);
		self.onmouseover = null;
		self.className = 'imageBox_theImage';
		document.getElementById('editImage').style.display = 'none';
		document.getElementById('removeImage').style.display = 'none';

		if (self.parentNode.nextSibling && self.parentNode.nextSibling.id) {
			var wrapper = document.getElementById('imageListWrapper');
			var moveobj = self.parentNode.nextSibling;
			var target = self.parentNode;

			while (moveobj != null) {
                if (moveobj.firstChild && !moveobj.firstChild.firstChild) {
                    break;
                }
                if (/^spacer/.test(moveobj.id)) {
                    moveobj = moveobj.nextSibling;
                    continue;
                }
                wrapper.insertBefore(moveobj, target);
				moveobj = target.nextSibling;
			}
		}

		resetSelectedImageSize();
		reOrder();
        uploadedImageCount();
	};

	if (hideTimer) {
        clearTimeout(hideTimer);
    }
	hideTimer = setTimeout('hideDelete()', 3000);

	showEdit(event,L,T,self,self.id);

}


function hideDelete(event) {
// ----------------------------------------------------------------------------------
	hideEdit(event);
	document.getElementById('removeImage').style.display = 'none';
}