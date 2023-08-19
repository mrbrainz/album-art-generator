var max_post_size = 8388608;

document.addEventListener('DOMContentLoaded', function() {
  setupButtons();
  });



function setupButtons() {
  document.getElementById('artsubmit').addEventListener('click',function(e){
    e.preventDefault();
    

    var fd = getFormData(),
    djimage = false;
    //console.log(fd.djimage)
    if (fd.djimage.size > 0) {
      if (fd.djimage.size > max_post_size * 0.66) {
        M.toast({html: 'ERROR: Please use an image smaller than 5MB'});
        return false;
      }

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
    });

  document.getElementById('download').addEventListener('click',function(e){
    e.preventDefault();
    
    var base64 = document.getElementById("base64-art").value

    if (!base64) {
      return false;
    } else {
      downloadBase64File(base64, 'albumart.jpg');
      M.toast({html: 'Download triggered!'});
    }

    });



}

function copyToClipboard() {
  // Get the text field
  var copyText = document.getElementById('base64-art');

  if (!copyText.value) {
    return false;
  }

  // Select the text field
  copyText.select();
  copyText.setSelectionRange(0, 99999); // For mobile devices

   // Copy the text inside the text field
  navigator.clipboard.writeText(copyText.value);
  M.toast({html: 'Image Base64 copied to clipboard'});
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
    lockForLoading();

    let xmlhttp= window.XMLHttpRequest ?
    new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

    xmlhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
          if(this.responseText) {
            var image = document.getElementById('art-preview');
            var resp = JSON.parse(this.responseText);
            image.src = resp.img;
            var textarea = document.getElementById('base64-art');
            textarea.value = resp.img;
            unlockForLoading();
          }
        }
    }

    var img = "";

    if (hasImage) {
      var image = document.getElementById('converter-img');
      img = image.src;
    }
    //console.log(img.length);
    var payload = "payload="+encodeURIComponent(JSON.stringify(
                    {"id": formdata.templateid,
                     "text1": encodeURIComponent(formdata.text1),
                     "text2": encodeURIComponent(formdata.text2), 
                     "text3": encodeURIComponent(formdata.text3), 
                     "text4": encodeURIComponent(formdata.text4),
                     "img":   encodeURIComponent(img)
                   }));
    //console.log(payload);
    //console.log(payload.length);
    
    if (payload.length > max_post_size) {
      M.toast({html: 'ERROR: Data transfer too big. Try using a smaller image.'});
      unlockForLoading();
      return false;
    }

    xmlhttp.open("POST","/generate.php",true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send(payload);
}

function downloadBase64File(base64Data, fileName) {
  const linkSource = base64Data;
  const downloadLink = document.createElement("a");
  downloadLink.href = linkSource;
  downloadLink.download = fileName;
  downloadLink.click();
  downloadLink.remove();
}

function lockForLoading() {
  document.getElementsByTagName('body')[0].classList.add('loading');
  
  var buttons = document.getElementsByTagName('button');
  
  for (let i = 0; i < buttons.length; i++) {
    buttons[i].disabled = true;
  }

  var inputs = document.getElementsByTagName('input');
  
  for (let i = 0; i < inputs.length; i++) {
    inputs[i].disabled = true;
  }

  var buttonsclass = document.getElementsByClassName("btn");

  for (let i = 0; i < buttonsclass.length; i++) {
    buttonsclass[i].classList.add('disabled');
  }

}

function unlockForLoading() {
  document.getElementsByTagName('body')[0].classList.remove('loading');

  var buttons = document.getElementsByTagName('button');

  for (let i = 0; i < buttons.length; i++) {
    buttons[i].removeAttribute('disabled');
  }

  var inputs = document.getElementsByTagName('input');
  
  for (let i = 0; i < inputs.length; i++) {
    inputs[i].removeAttribute('disabled');
  }

  var buttonsclass = document.getElementsByClassName("btn");

  for (let i = 0; i < buttonsclass.length; i++) {
    buttonsclass[i].classList.remove('disabled');
  }

}


