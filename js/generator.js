document.addEventListener('DOMContentLoaded', function() {
  setupButtons();
  });



function setupButtons() {
  document.getElementById('artsubmit').addEventListener('click',function(e){
    e.preventDefault();
    

    var fd = getFormData(),
    djimage = false;
    //console.log(fd.djimage)
    if (fd.djimage) {
      djimage = convertImageToBase64(fd.djimage);
    } else {
      postFormToServer(fd,false);
    }

  });

  document.getElementById('base64-art').addEventListener('click',function(e){
    e.preventDefault();
    return false;
    });

  document.getElementById('copy-base64').addEventListener('click',function(e){
    e.preventDefault();
    copyToClipboard();
    M.toast({html: 'Image Base64 copied to clipboard'});
    });


}

function copyToClipboard() {
  // Get the text field
  var copyText = document.getElementById('base64-art');

  // Select the text field
  copyText.select();
  copyText.setSelectionRange(0, 99999); // For mobile devices

   // Copy the text inside the text field
  navigator.clipboard.writeText(copyText.value);
}

function convertImageToBase64(theimage) {
     var image = document.getElementById('converter-img');

     if (theimage.size > 0) {
        var reader = new FileReader();
        reader.onload = function(e) {

           //console.log(e.target);
           image.src = e.target.result;
           postFormToServer(getFormData(),true);
        }
        reader.readAsDataURL(theimage);
     }
}

function getFormData() {
  var formfields = document.getElementById('djcreds'),
    formData = new FormData(formfields),
    formentries = Object.fromEntries(formData);

    //console.log(formentries);
    return formentries;
}

function postFormToServer(formdata,hasImage) {
    let xmlhttp= window.XMLHttpRequest ?
    new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

    xmlhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200)
          if(this.responseText) {
            var image = document.getElementById('art-preview');
            var resp = JSON.parse(this.responseText);
            image.src = resp.img;
            var textarea = document.getElementById('base64-art');
            textarea.value = resp.img;
          }
    }

    var img = "";

    if (hasImage) {
      var image = document.getElementById('converter-img');
      img = image.src;
    }

    var payload = "payload="+encodeURIComponent(JSON.stringify(
                    {"id": formdata.templateid,
                     "text1": encodeURIComponent(formdata.text1),
                     "text2": encodeURIComponent(formdata.text2), 
                     "text3": encodeURIComponent(formdata.text3), 
                     "text4": encodeURIComponent(formdata.text4),
                     "img":   encodeURIComponent(img)
                   }));
    //console.log(payload);
    xmlhttp.open("POST","/",true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send(payload);
}

