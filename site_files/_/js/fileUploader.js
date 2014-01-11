/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 file_obj = {"file":null,"status":1};

$.fn.imageUploader = function() {
    // get css
    $('head').append('<link rel="stylesheet" href="/_/css/fileUploader.css" >');
    var csrf = this.siblings('input[name="csrf_token"]').clone();
    this.after('<div id="uploadForm-spacer"></div>');
    var upload_frame = $('<iframe src="/admin/upload_file" id="upload_frame" name="upload_frame" frameborder="0"></iframe>').css('height',0);
    upload_frame.bind('load',uploadFinished);
    // create file upload form
    var url = this.attr('data-url');
    var uploadForm = $('<form method="post" class="fileUploader-form" target="upload_frame" enctype="multipart/form-data" ></form>');
//    console.log($('#uploadForm-spacer').offset());
    uploadForm.attr('action',url).bind('submit',uploadStarted).css(
            {position:'absolute',
            width: $('#uploadForm-spacer').width(),
            top:($('#uploadForm-spacer').offset().top - 40),
            left:$('#uploadForm-spacer').offset().left}
        );
    
    $('body').append(this);
    this.wrap(uploadForm)
        .after(csrf)
        .after('<input type="submit" value="Upload" name="uploadImage">')
        .after(upload_frame);

    $('#uploadForm-spacer').css('height',($('.fileUploader-form').height()*1));
    return this;
};

 uploadFinished = function(){
     var frame = $(this);
     var theForm = frame.parent(".fileUploader-form");
     if(theForm.hasClass("uploading")){
//         console.log(file_obj) // has uploaded file data
         console.log(file_obj);
         // if errors, display them.
        if(file_obj.errors){
             var errs = $('<div class="message error"></div>');
             html = "<ul>";
             for(err in file_obj.errors){
                html+='<li>'+file_obj.errors[err]+'</li>';
             }
             errs.append(html+'</ul>');
             theForm.prepend(errs);
        }else{
            
            var img = $('<img class="uploaded-image" src="'+file_obj.file.filepath+'">');
            theForm.find("img.uploaded-image").remove();
            theForm.prepend(img);
            theForm.find("input[type='submit']").attr('value','Change');
            addOptions();
        }
         theForm.removeClass("uploading");
         var fileInput = $('<input type="hidden" name="" value="">');
         fileInput.attr('name',theForm.find('.imageUploader').attr('name'));
         fileInput.val(JSON.stringify(file_obj));
         $('#uploadForm-spacer input[type="hidden"]').remove();
         $('#uploadForm-spacer').css('height',theForm.height())
            .append(fileInput);
     }
 };
 addOptions = function(){
//     alert('adding');
//     var opts = $()
//     $(".imageUploader.uploading").append(opts);
 };
 
 uploadStarted = function(){
     $(this).addClass('uploading').find('.message.error').remove();
 };