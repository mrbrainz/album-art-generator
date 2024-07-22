var max_post_size = 8388608;

document.addEventListener('DOMContentLoaded', function() {
  setupButtons();
  getNextShows();
  });



function setupButtons() {
  document.getElementById('artsubmit').addEventListener('click',function(e){
    e.preventDefault();
    
    doPost();
    

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
      downloadBase64File(base64, generateFileName()+'.jpg');
      M.toast({html: 'Download triggered!'});
    }

    });

    var elems = document.querySelectorAll('select');
    var instances = M.FormSelect.init(elems, {});

  document.getElementById('dotoday').addEventListener('click',function(e){
    e.preventDefault();
    
    var datefield = document.getElementById('text3'),
    phtext = datefield.placeholder;

    datefield.value = phtext;

    M.toast({html: 'Today date populated!'});

  });

  document.getElementById('upcomingshows').addEventListener('change',function(e){
      updateFormFromUpcomingShow();
    });

  

}

function isLocalImgPDF() {
  return (document.getElementsByTagName('body')[0].classList.contains("localimgpdf"));
}

function doPostBase64() {
  var fd = getFormData(),
  djimage = false;
  //console.log(fd.djimage)
  if (fd.djimage.size > 0) {
    if (fd.djimage.size > 745373) {
      M.toast({html: 'ERROR: Please use an image smaller than 745kb'});
      return false;
    }

    djimage = convertImageToBase64(fd.djimage);
  } else {
    postFormToServer(fd,false);
  }
}

function doPostFileUpload() {
  var fd = getFormData(),
  djimage = false;
  //console.log(fd.djimage)
  if (fd.djimage.size > 0) {
    if (fd.djimage.size > 6000000) {
      M.toast({html: 'ERROR: Please use an image smaller than 6MB'});
      return false;
    }

    postFormToServer(fd,true);

  } else {
    postFormToServer(fd,false);
  }
}

function doPost() {
  if (typeof(localimgpdf) !== 'undefined' && localimgpdf) {
    doPostFileUpload();
  } else { 
    doPostBase64();
  }
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

    return formentries;
}

function postFormToServer(formdata,hasImage) {
    lockForLoading();

    var localimgpdf = isLocalImgPDF();

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
    var imgfile = false;

    if (hasImage && !localimgpdf) {
      var image = document.getElementById('converter-img');
      img = image.src;
    } else if (hasImage && localimgpdf) {
      imgfile = document.getElementById("djimage").files[0];
    }

    var payload = encodeURIComponent(JSON.stringify(
                    {"id": formdata.templateid,
                     "text1": encodeURIComponent(formdata.text1),
                     "text2": encodeURIComponent(formdata.text2), 
                     "text3": encodeURIComponent(formdata.text3), 
                     "text4": encodeURIComponent(formdata.text4),
                     "img":   encodeURIComponent(img)
                   }));
    var formData = new FormData();

    formData.append("payload",payload);

    if (imgfile) {
      formData.append("file",imgfile);
    }
    
    if (payload.length > max_post_size) {
      M.toast({html: 'ERROR: Data transfer too big. Try using a smaller image.'});
      unlockForLoading();
      return false;
    }

    xmlhttp.open("POST","generate.php",true);
    xmlhttp.send(formData);
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

function addDJsToDropdown(djs) {
    var elem = document.getElementById('upcomingshows');
    
    var drops = elem.innerHTML;

    for (var i=0;i<djs.length;i++) {
      drops += '<option value="'+djs[i].cleanname+'" data-timerange="'+djs[i].timerange+'" data-showdate="'+djs[i].showdate+'">'+djs[i].name+'</option>\n\r';
    }

    //console.log(drops);

    elem.innerHTML = drops;

    elem.removeAttribute('disabled');

    var instance = M.FormSelect.init(elem, {});

}

function updateFormFromUpcomingShow() {
    var sel = document.getElementById('upcomingshows');
    var selected = sel.options[sel.selectedIndex];
    if (!selected.value) {
      return false;
    }
    var djname = selected.innerText;
    var showdate = selected.getAttribute('data-showdate');
    var timerange = selected.getAttribute('data-timerange');

    var text1 = document.getElementById('text1');
    var text3 = document.getElementById('text3');
    var text4 = document.getElementById('text4');

    text1.value = djname;
    text3.value = showdate;
    text4.value = timerange;

    doPost();

}

function getNextShows() {
  let xmlhttp= window.XMLHttpRequest ?
    new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

    xmlhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
          if(this.responseText) {
            var resp = JSON.parse(this.responseText);
            addDJsToDropdown(resp);
          }
        }
    }
  xmlhttp.open("GET","getnextshows.php",true);
  xmlhttp.send();
}

function generateCleanName(dj) {
  if (typeof(dj) !== 'undefined' && dj) { 
    return dj.replace(/ /g,"_").replace(/[^a-zA-Z0-9\-_]/g, "");
  } else {
    return false;
  }
}

function generateFileName() {
  var formdata = getFormData();

  var dj = generateCleanName(formdata.text1);
  var date = generateCleanName(formdata.text3);

  dj = (!dj) ? "noname" : dj;
  date = (!date) ? "nodate" : date;

  return dj + "_" + date + "_Sub_FM";

}

